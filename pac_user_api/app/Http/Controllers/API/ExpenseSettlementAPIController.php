<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateEpsTAppAPIRequest;
use App\Http\Requests\API\SaveExpenseInputDataAPIRequest;
use App\Http\Requests\API\UpdateEpsTAppAPIRequest;
use App\Http\Requests\API\UpdateExpenseCircularContentAPI;
use App\Http\Requests\API\UpdateExpenseCircularInfoAPIRequest;
use App\Http\Utils\AppUtils;
use App\Http\Utils\ExpenseUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class ExpenseSettlementAPIController extends AppBaseController
{
    const CONFIG_ROOT_FOLDER = 'app.s3_storage_root_folder_expense_settlement';
    const ES_DIRECTORY = '/expenseSettlement';
    const CONFIG_SERVER_ENV = 'app.server_env';
    const CONFIG_EDITION_FLG = 'app.edition_flg';
    const CONFIG_SERVER_FLG = 'app.server_flg';
    const FILE_NAME_ATTACHMENT = 'ES_';
    const DIR_MAKE = 0;
    const DIR_CHECK_ONLY = 1;

    private $controllerName = 'ExpenseSettlementAPIController';
    private $bbsIdDirectory = '';

    public function __construct()
    {
        $this->bbsIdDirectory = '/' . config(self::CONFIG_SERVER_ENV) . '/' . config(self::CONFIG_EDITION_FLG)
            . '/' . config(self::CONFIG_SERVER_FLG);

    }


    public function index(Request $request)
    {
        $data = $request->all();

        $limit = isset($data['limit']) ? $data['limit'] : AppUtils::DEFAULT_LIMIT_PAGE;

        $orderBy = $request->get('orderBy', "eps_t_app.id");
        $orderDir = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
        $user = $request->user();

        try {
            $result = DB::table('eps_t_app')
                ->join('eps_m_purpose', function ($join) {
                    $join->on('eps_t_app.mst_company_id', 'eps_m_purpose.mst_company_id');
                    $join->on('eps_t_app.purpose_name', 'eps_m_purpose.purpose_name');
                })
                ->join('eps_m_form', function ($join) {
                    $join->on('eps_m_form.mst_company_id', 'eps_t_app.mst_company_id');
                    $join->on('eps_m_form.form_code', 'eps_t_app.form_code');
                })
                ->selectRaw("eps_t_app.id,
                    eps_t_app.form_code,
                    eps_m_form.form_type,
                    eps_m_form.form_name,
                    eps_m_purpose.purpose_name,
                    eps_t_app.id,
                    eps_t_app.suspay_amt,
                    eps_t_app.eps_diff,
                    eps_t_app.target_period_from,
                    eps_t_app.target_period_to,
                    eps_t_app.eps_amt,
                    eps_t_app.filing_date,
                    eps_t_app.status,
                    eps_t_app.update_at"
                )->where([
                    'eps_t_app.mst_company_id' => $user->mst_company_id,
                    'eps_t_app.mst_user_id' => $user->id,
                    'eps_t_app.deleted_at' => null,
                ])
                ->orderBy($orderBy, $orderDir);

            $where = ['1=1'];
            $where_arg = [];

            if (isset($data['form_code'])) {
                $where[] = 'INSTR(eps_t_app.form_code, ?)';
                $where_arg[] = $data['form_code'];
            }

            $listFormType = [];
            if (isset($data['advance'])) {
                $listFormType[] = AppUtils::EPS_M_FORM_FORM_TYPE_ADVANCE;
            }
            if (isset($data['settlement'])) {
                $listFormType[] = AppUtils::EPS_M_FORM_FORM_TYPE_SETTLEMENT;
            }
            if ($listFormType) {
                $result = $result->whereIn('eps_m_form.form_type', $listFormType);
            }
            if (isset($data['form_name'])) {
                $where[] = 'INSTR(eps_m_form.form_name, ?)';
                $where_arg[] = $data['form_name'];
            }
            if (isset($data['purpose_name'])) {
                $where[] = 'eps_m_purpose.purpose_name = ?';
                $where_arg[] = $data['purpose_name'];
            }

            $listStatus = [];
            if (isset($data['before_circulation'])) {
                $listStatus[] = AppUtils::EPS_T_APP_STATUS_BEFORE_CIRCULAR;
            }
            if (isset($data['circulating'])) {
                $listStatus[] = AppUtils::EPS_T_APP_STATUS_CIRCULATION;
            }
            if (isset($data['approved'])) {
                $listStatus[] = AppUtils::EPS_T_APP_STATUS_APPROVED;
                $listStatus[] = AppUtils::EPS_T_APP_STATUS_APPROVED_AFTER_DOWNLOAD;
            }
            if (isset($data['rejected'])) {
                $listStatus[] = AppUtils::EPS_T_APP_STATUS_REMAND;
            }
            if ($listStatus) {
                $result = $result->whereIn('eps_t_app.status', $listStatus);
            }
            if (isset($data['target_period_form'])) {
                $result = $result->where('eps_t_app.target_period_from',
                    '>=', $data['target_period_form']);
            }
            if (isset($data['target_period_to'])) {
                $result = $result->where('eps_t_app.target_period_to',
                    '<=', $data['target_period_to']);
            }

            $result = $result->whereRaw(implode(" AND ", $where), $where_arg)
                ->paginate($limit)->appends(request()->input());

            return $this->sendResponse($result, 'Get data success');

        } catch (\Exception $ex) {
            Log::error("$this->controllerName@index");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function getListTAppItems(Request $request)
    {
        $user = $request->user();
        try {
            $data = $request->all();
            $tAppItems = null;
            $info = null;
            $validityPeriod = null;
            if (isset($data['id'])) {
                $id = $data['id'];
                $tAppItems = DB::table('eps_t_app_items')
                    ->where([
                        'eps_t_app_items.mst_company_id' => $user->mst_company_id,
                        'eps_t_app_items.t_app_id' => $id,
                        'eps_t_app_items.deleted_at' => null,
                    ])
                    ->select(
                        'eps_t_app_items.id',
                        'eps_t_app_items.t_app_id',
                        'eps_t_app_items.wtsm_name',
                        'eps_t_app_items.expected_pay_date',
                        'eps_t_app_items.unit_price',
                        'eps_t_app_items.quantity',
                        'eps_t_app_items.expected_pay_amt',
                        'eps_t_app_items.numof_ppl',
                        'eps_t_app_items.tax',
                        'eps_t_app_items.from_station',
                        'eps_t_app_items.to_station',
                        'eps_t_app_items.roundtrip_flag',
                        'eps_t_app_items.remarks',
                        'eps_t_app_items.submit_method',
                        'eps_t_app_items.submit_other_memo',
                        'eps_t_app_items.nonsubmit_type',
                        'eps_t_app_items.nonsubmit_reason',
                        'eps_t_app_items.traffic_facility_name'
                    )->get();

                $info = DB::table('eps_t_app')
                    ->select(
                        'eps_t_app.target_period_from',
                        'eps_t_app.target_period_to',
                        'eps_t_app.purpose_name',
                        'eps_t_app.form_dtl',
                        'eps_t_app.expected_amt',
                        'eps_t_app.eps_diff',
                        'eps_t_app.desired_suspay_amt',
                        'eps_t_app.eps_amt',
                        'eps_t_app.circular_id',
                        'eps_t_app.suspay_amt'
                    )->where([
                        'eps_t_app.id' => $id,
                        'eps_t_app.mst_company_id' => $user->mst_company_id,
                        'eps_t_app.deleted_at' => null,

                    ]);
                $files = DB::table('eps_t_app_files')
                    ->where('t_app_id', $id)
                    ->get();

                $appFiles = $files->filter(function ($_item) {
                    return !$_item->t_app_items_id;
                });
                $tAppItems = $tAppItems->map(function ($_item) use ($files) {
                    $_item->files = $files->filter(function ($_file) use ($_item) {
                        return $_file->t_app_items_id === $_item->id;
                    })->values()->all();
                    return $_item;
                });
                $info = $info->first();
                $info->files = $appFiles;

            }

            $result['data'] = $tAppItems;
            $result['info'] = $info;

            return $this->sendResponse($result, 'Get ListTAppItems Success');

        } catch (\Exception $ex) {
            Log::error("$this->controllerName@getListTAppItems");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts
            [\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR],
                \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    private function getFolderName($user, $esIdFolder)
    {
        return config(self::CONFIG_ROOT_FOLDER) . self::ES_DIRECTORY . $this->bbsIdDirectory . '/' . $user->mst_company_id . '/' . $esIdFolder . '_' . $user->id;
    }

    private function checkDirectory($kbn, $user, $esIdFolder, &$s3path)
    {
        $ret = false;
        try {
            if (!$s3path) {
                $s3path = $this->getFolderName($user, $esIdFolder);
            }
            $isDirectory = Storage::disk('s3')->exists($s3path);

            switch ($kbn) {
                case self::DIR_MAKE:
                    if (!$isDirectory) {
                        Storage::disk('s3')->makeDirectory($s3path);
                    }
                    break;
                case self::DIR_CHECK_ONLY:
                    return $isDirectory;
                    break;
            }
            $ret = true;
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $ret;
        }
        return $ret;
    }

    public function saveExpense(CreateEpsTAppAPIRequest $request)
    {
        $errmsg = '投稿追加処理で異常が発生しました。';
        $dataPayload = $request->all();
        $user = $request->user();
        $files = $request->file('files');
        try {
            DB::beginTransaction();
            // Check target_period_from and target_period_to
            $result = null;
            if (isset($dataPayload['t_app'])) {
                $data = $dataPayload['t_app'];
                unset($data['items_delete']);
                if (isset($dataPayload['t_app_items'])) {
                    $tAppItems = $dataPayload['t_app_items'];
                    foreach ($tAppItems as $item) {
                        $resultCheckTargetPeriodItem = $this->checkTargetPeriodItem(
                            $data['target_period_from'],
                            $data['target_period_to'],
                            $item['expected_pay_date']
                        );
                        if (!$resultCheckTargetPeriodItem) {
                            return $this->sendError('日付は期間の間に入力してください。');
                        }
                        if($dataPayload['form_type'] == AppUtils::EPS_M_FORM_FORM_TYPE_SETTLEMENT) {
                            if (!in_array($item['wtsm_name'], ExpenseUtils::EXPENSE_LIST_WTSM_NAME_TRANSPORT)) {
                                $resultCheckTaxItem = $this->checkTaxItem(
                                    $user->mst_company_id,
                                    $data['form_code'],
                                    $item['tax'],
                                    $item['wtsm_name']
                                );
                                if (!$resultCheckTaxItem) {
                                    return $this->sendError('消費税は正しく入力してください。');
                                }
                            }
                        }
                    }
                    $resultChetUnitPrice = $this->checkExpectedAmt(
                        $user->mst_company_id,
                        $data['form_code'],
                        $tAppItems,
                        $dataPayload['form_type']
                    );

                    $unitPriceCorrect = false;
                    if (isset($resultChetUnitPrice['resultMax']) && isset($resultChetUnitPrice['resultMin'])) {
                        if ($resultChetUnitPrice['resultMax'] && $resultChetUnitPrice['resultMin']) {
                            $unitPriceCorrect = true;
                            if (isset($resultChetUnitPrice['expected_pay_amt'])) {
                                $data['expected_amt'] = $resultChetUnitPrice['unit_price'];
                            }
                        } else {
                            if (isset($resultChetUnitPrice['message'])) {
                                return $this->sendError($resultChetUnitPrice['message']);
                            }
                        }
                    }
                    if (!$unitPriceCorrect) {
                        Log::error("$this->controllerName@updateExpense");
                        return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR],
                            \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
                if (!isset($data['target_period_to']) || !$data['target_period_to']) {
                    $data['target_period_to'] = null;
                }
                $resultCheckTargetPeriod = $this->checkTargetPeriod(
                    $data['target_period_from'],
                    $data['target_period_to'],
                    $user->mst_company_id,
                    $data['form_code']
                );
                if (!$resultCheckTargetPeriod) {
                    return $this->sendError('開始時間は終了時間よりも前に設定してください。');
                }
                $data['create_at'] = Carbon::now();
                $data['update_at'] = Carbon::now();
                $data['create_user'] = $user->email;
                $data['update_user'] = $user->email;
                $data['mst_company_id'] = $user->mst_company_id;
                $data['mst_user_id'] = $user->id;
                $data['status'] = AppUtils::EPS_T_APP_STATUS_BEFORE_CIRCULAR;
                if (isset($data['desired_suspay_amt']) && ($dataPayload['form_type'] == AppUtils::EPS_M_FORM_FORM_TYPE_ADVANCE)) {
                    $data['suspay_amt'] = $data['desired_suspay_amt'];
                }
                if (isset($data['eps_amt']) && isset($data['suspay_amt']) &&
                    $dataPayload['form_type'] == AppUtils::EPS_M_FORM_FORM_TYPE_SETTLEMENT) {
                    $data['eps_diff'] = $data['suspay_amt'] - $data['eps_amt'];
                }
                $result['id'] = DB::table('eps_t_app')->insertGetId($data);
            }
            $s3path = '';
            //S3ディレクトリ存在確認と作成
            $ret = $this->checkDirectory(self::DIR_MAKE, $user, $result['id'], $s3path);
            if (!$ret) {
                DB::rollBack();
                return $this->sendError($errmsg, \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if ($result['id']) {
                // Todo recheck with from don't have form
                if ($files) {


                    $file_items = [];

                    foreach ($files as $file) {
                        $s3_filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                        $filename = $file->getClientOriginalName();
                        $file_item = [];
                        $file_item['mst_company_id'] = $user->mst_company_id;
                        $file_item['t_app_id'] = $result['id'];
                        $file_item['t_app_items_id'] = null;
                        $file_item['s3_file_name'] = $s3_filename;
                        $file_item['original_file_name'] = $filename;
                        $file_item['create_at'] = Carbon::now();
                        $file_item['update_at'] = Carbon::now();
                        $file_item['create_user'] = $user->email;
                        $file_item['update_user'] = $user->email;
                        $path = Storage::disk('s3')->putFileAs($s3path, $file, $s3_filename);
                        if ($path) {
                            $file_item['s3_path'] = $path;
                            array_push($file_items, $file_item);
                        }
                    }
                    if ($file_items && count($file_items) > 0) {
                        DB::table('eps_t_app_files')
                            ->insert($file_items);
                    }
                }

            }
            if (isset($result['id']) && isset($dataPayload['t_app_items'])) {
                $tAppItems = $dataPayload['t_app_items'];
                foreach ($tAppItems as $item) {
                    $item['t_app_id'] = $result['id'];
                    $item['mst_company_id'] = $user->mst_company_id;
                    $item['create_user'] = $user->email;
                    $item['create_at'] = Carbon::now();
                    $item['update_user'] = $user->email;
                    $item['update_at'] = Carbon::now();
                    $_files = null;
                    // Todo check action in client (put file for t_app_item)
                    if (isset($item['files'])) {
                        $_files = $item['files'];
                        unset($item['files']);
                    }

                    $item_id = DB::table('eps_t_app_items')
                        ->insertGetId($item);

                    if ($item_id && $_files && count($_files) > 0) {
                        $item_files = [];

                        foreach ($_files as $file) {
                            $s3_filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                            $filename = $file->getClientOriginalName();
                            $file_item = [];
                            $file_item['mst_company_id'] = $user->mst_company_id;
                            $file_item['t_app_id'] = $result['id'];
                            $file_item['t_app_items_id'] = $item_id;
                            $file_item['s3_file_name'] = $s3_filename;
                            $file_item['original_file_name'] = $filename;
                            $file_item['create_at'] = Carbon::now();
                            $file_item['update_at'] = Carbon::now();
                            $file_item['create_user'] = $user->email;
                            $file_item['update_user'] = $user->email;
                            $path = Storage::disk('s3')->putFileAs($s3path, $file, $s3_filename);
                            if ($path) {
                                $file_item['s3_path'] = $path;
                                array_push($item_files, $file_item);
                            }
                        }
                        if ($item_files && count($item_files) > 0) {
                            DB::table('eps_t_app_files')
                                ->insert($item_files);
                        }
                    }

                }
            }

            DB::commit();
            return $this->sendResponse($result, 'データ作成処理に成功しました。');

        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);

        }

    }

    public function updateExpenseCircularInfo(UpdateExpenseCircularInfoAPIRequest $request)
    {
        // Todo middleware create file circular_if t_app (deleted_at = null, status = [0, 4])
        $data = $request->all();

        $circularId = $data['circular_id'];
        try {
            $dataUpdate = [
                'circular_id' => $circularId,
            ];

            DB::table('eps_t_app')
                ->where([
                    'id' => $data['t_app_id'],
                    'status' => AppUtils::EPS_T_APP_STATUS_BEFORE_CIRCULAR,
                    'deleted_at' => null,
                ])->update($dataUpdate);
            return $this->sendSuccess('updateExpenseCircularInfo success');
        } catch (\Exception $ex) {
            Log::error("$this->controllerName@updateExpenseCircularInfo");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function downloadFile($id, Request $request)
    {
        $attachment = DB::table('eps_t_app_files')
            ->where('id', $id)
            ->first();

        if (!$attachment) {
            return $this->sendError('', Response::HTTP_NOT_FOUND);
        }
        try {

            $url = Storage::disk('s3')->temporaryUrl(
                $attachment->s3_path,
                now()->addDays(1),
                [
                    'ResponseContentType' => 'application/octet-stream',
                    'ResponseContentDisposition' => "attachment; filename = " . urlencode($attachment->original_file_name)
                ]
            );
            return \Response::json([
                'success' => true,
                'url' => $url,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . $e->getTraceAsString());
            return $this->sendError('', Response::HTTP_NOT_FOUND);
        }
    }

    public function deleteFile($id, Request $request)
    {
        DB::beginTransaction();
        $attachment = DB::table('eps_t_app_files')
            ->where('id', $id)
            ->first();

        if (!$attachment) {
            DB::rollBack();
            return $this->sendError('', Response::HTTP_NOT_FOUND);
        }
        try {
            Storage::disk('s3')->delete($attachment->s3_path);
            DB::table('eps_t_app_files')
                ->where('id', $id)
                ->delete();
            DB::commit();
            return $this->sendResponse("", 'データ削除処理に成功しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateExpense($id, UpdateEpsTAppAPIRequest $request)
    {
        $user = $request->user();
        $dataPayload = $request->all();
        $files = $request->file('files');

        try {
            DB::beginTransaction();
            if (isset($dataPayload['t_app'])) {
                $data = $dataPayload['t_app'];
                if (array_key_exists('items_delete', $data)) {
                    if (isset($data['items_delete'])) {
                        $data['items_delete'] = explode(",", $data['items_delete']);
                        $infoDeleteUpdate['deleted_at'] = Carbon::now();
                        DB::table('eps_t_app_items')
                            ->where([
                                'mst_company_id' => $user->mst_company_id,
                                't_app_id' => $id,
                                'deleted_at' => null,
                            ])->whereIn('id', $data['items_delete'])
                            ->update($infoDeleteUpdate);

                    }
                    unset($data['items_delete']);
                }
                if (isset($dataPayload['t_app_items'])) {
                    $tAppItems = $dataPayload['t_app_items'];
                    foreach ($tAppItems as $item) {
                        $resultCheckTargetPeriodItem = $this->checkTargetPeriodItem(
                            $data['target_period_from'],
                            $data['target_period_to'],
                            $item['expected_pay_date']
                        );
                        if (!$resultCheckTargetPeriodItem) {
                            return $this->sendError('日付は期間の間に入力してください。');
                        }
                    }
                    $resultChetUnitPrice = $this->checkExpectedAmt(
                        $user->mst_company_id,
                        $data['form_code'],
                        $tAppItems,
                        $dataPayload['form_type']
                    );

                    $unitPriceCorrect = false;
                    if (isset($resultChetUnitPrice['resultMax']) && isset($resultChetUnitPrice['resultMin'])) {
                        if ($resultChetUnitPrice['resultMax'] && $resultChetUnitPrice['resultMin']) {
                            $unitPriceCorrect = true;
                        } else {
                            if (isset($resultChetUnitPrice['message'])) {
                                return $this->sendError($resultChetUnitPrice['message']);
                            }
                        }
                    }
                    if (!$unitPriceCorrect) {
                        Log::error("$this->controllerName@updateExpense");
                        return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR],
                            \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
                $data['update_at'] = Carbon::now();
                $data['update_user'] = $user->email;
                if ($dataPayload['form_type'] == AppUtils::EPS_M_FORM_FORM_TYPE_SETTLEMENT) {
                    unset($data['suspay_amt']);
                    $infos = DB::table('eps_t_app')
                        ->select(
                            'eps_t_app.suspay_amt',
                            'eps_t_app.eps_amt'
                        )->where([
                            'id' => $id,
                            'mst_company_id' => $user->mst_company_id,
                            'deleted_at' => null,
                        ])->first();
                    if ($infos && isset($resultChetUnitPrice['eps_amt']) && isset($infos->suspay_amt)) {
//                        $data['eps_diff'] = $infos->eps_amt - $infos->suspay_amt;
                        $data['eps_diff'] = $infos->suspay_amt - $resultChetUnitPrice['eps_amt'];
                    }
                }
                if ($dataPayload['form_type'] == AppUtils::EPS_M_FORM_FORM_TYPE_ADVANCE) {
                    if (isset($data['desired_suspay_amt'])) {
                        $data['suspay_amt'] = $data['desired_suspay_amt'];
                    } else {
                        $data['suspay_amt'] = null;
                    }
                }
                if (!$data['target_period_to']) {
                    $data['target_period_to'] = null;
                }
                $resultCheckTargetPeriod = $this->checkTargetPeriod(
                    $data['target_period_from'],
                    $data['target_period_to'],
                    $user->mst_company_id,
                    $data['form_code']
                );
                if (!$resultCheckTargetPeriod) {
                    return $this->sendError('開始時間は終了時間よりも前に設定してください。');
                }
                DB::table('eps_t_app')
                    ->where([
                        'id' => $id,
                        'mst_company_id' => $user->mst_company_id,
                        'deleted_at' => null,
                    ])->update($data);
            }

            $s3path = '';
            //S3ディレクトリ存在確認と作成
            $ret = $this->checkDirectory(self::DIR_MAKE, $user, $id, $s3path);
            if (!$ret) {
                DB::rollBack();
                return $this->sendError("投稿追加処理で異常が発生しました。", \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            if ($files) {

                $file_items = [];

                foreach ($files as $file) {
                    $s3_filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $filename = $file->getClientOriginalName();
                    $file_item = [];
                    $file_item['mst_company_id'] = $user->mst_company_id;
                    $file_item['t_app_id'] = $id;
                    $file_item['t_app_items_id'] = null;

                    $file_item['s3_file_name'] = $s3_filename;
                    $file_item['original_file_name'] = $filename;
                    $file_item['create_at'] = Carbon::now();
                    $file_item['update_at'] = Carbon::now();
                    $file_item['create_user'] = $user->email;
                    $file_item['update_user'] = $user->email;
                    $path = Storage::disk('s3')->putFileAs($s3path, $file, $s3_filename);
                    if ($path) {
                        $file_item['s3_path'] = $path;
                        array_push($file_items, $file_item);
                    }
                }
                if ($file_items && count($file_items) > 0) {
                    DB::table('eps_t_app_files')
                        ->insert($file_items);
                }

            }
            if (isset($dataPayload['t_app_items'])) {
                $tAppItems = $dataPayload['t_app_items'];
                foreach ($tAppItems as $item) {
                    $_files = [];
                    if (isset($item['files']) && $item['files']) {
                        $_files = $item['files'];
                        unset($item['files']);
                    }
                    if (isset($item['id']) && $item['id']){
                        $item_id = $item['id'];
                    }
                    if (isset($item_id) && $item_id && isset($item['id']) && $item['id']) {
                        $tAppItemId = $item['id'];
                        unset($item['id']);
                        $item['update_at'] = Carbon::now();
                        $item['update_user'] = $user->email;
                        DB::table('eps_t_app_items')
                            ->where([
                                'id' => $tAppItemId,
                                'mst_company_id' => $user->mst_company_id,
                                't_app_id' => $id,
                                'deleted_at' => null,
                            ])->update($item);
                        if($dataPayload['form_type'] == AppUtils::EPS_M_FORM_FORM_TYPE_SETTLEMENT) {
                            if (!in_array($item['wtsm_name'], ExpenseUtils::EXPENSE_LIST_WTSM_NAME_TRANSPORT)) {
                                $resultCheckTaxItem = $this->checkTaxItem(
                                    $user->mst_company_id,
                                    $data['form_code'],
                                    $item['tax'],
                                    $item['wtsm_name']
                                );
                                if (!$resultCheckTaxItem) {
                                    return $this->sendError('消費税は正しく入力してください。');
                                }
                            }
                        }
                    } else {
                        $item['t_app_id'] = $id;
                        $item['mst_company_id'] = $user->mst_company_id;
                        $item['create_user'] = $user->email;
                        $item['create_at'] = Carbon::now();
                        $item['update_user'] = $user->email;
                        $item['update_at'] = Carbon::now();
                        $item_id = DB::table('eps_t_app_items')
                            ->insertGetId($item);
                        if($dataPayload['form_type'] == AppUtils::EPS_M_FORM_FORM_TYPE_SETTLEMENT) {
                            if (!in_array($item['wtsm_name'], ExpenseUtils::EXPENSE_LIST_WTSM_NAME_TRANSPORT)) {
                                $resultCheckTaxItem = $this->checkTaxItem(
                                    $user->mst_company_id,
                                    $data['form_code'],
                                    $item['tax'],
                                    $item['wtsm_name']
                                );
                                if (!$resultCheckTaxItem) {
                                    return $this->sendError('消費税は正しく入力してください。');
                                }
                            }
                        }
                    }

                    if ($item_id && $_files && count($_files) > 0) {
                        $item_files = [];
                        foreach ($_files as $file) {
                            $s3_filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                            $filename = $file->getClientOriginalName();
                            $file_item = [];
                            $file_item['mst_company_id'] = $user->mst_company_id;
                            $file_item['t_app_id'] = $id;
                            $file_item['t_app_items_id'] = $item_id;
                            $file_item['s3_file_name'] = $s3_filename;
                            $file_item['original_file_name'] = $filename;
                            $file_item['create_at'] = Carbon::now();
                            $file_item['update_at'] = Carbon::now();
                            $file_item['create_user'] = $user->email;
                            $file_item['update_user'] = $user->email;
                            $path = Storage::disk('s3')->putFileAs($s3path, $file, $s3_filename);
                            if ($path) {
                                $file_item['s3_path'] = $path;
                                array_push($item_files, $file_item);
                            }
                        }
                        if ($item_files && count($item_files) > 0) {
                            DB::table('eps_t_app_files')
                                ->insert($item_files);
                        }
                    }
                }
            }
            DB::commit();
            return $this->sendSuccess('データ更新処理に成功しました。');

        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error("$this->controllerName@updateExpense");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function checkTaxItem ($companyId, $formCode, $tax, $wtsmName)
    {
        try {
            $isSuccess = true;
            $taxOption = DB::table('eps_m_wtsm')
                ->join('eps_m_form_wtsm', function ($join) {
                    $join->on('eps_m_wtsm.mst_company_id', 'eps_m_form_wtsm.mst_company_id');
                    $join->on('eps_m_wtsm.wtsm_name', 'eps_m_form_wtsm.wtsm_name');
                })->where([
                    'eps_m_wtsm.mst_company_id' => $companyId,
                    'eps_m_form_wtsm.form_code' => $formCode,
                    'eps_m_wtsm.wtsm_name' => $wtsmName,
                ])->select('eps_m_wtsm.tax_option')
                ->first();
            $taxOption = (array) $taxOption;

            if ($taxOption) {
                if (in_array(AppUtils::EPS_M_WTSM_TAX_OPTION_DISPLAY, $taxOption)) {
                    if ($tax <= 0) {
                        $isSuccess = false;
                    }
                }
            }

        } catch (\Exception $ex) {
            Log::error("$this->controllerName@checkTaxItem");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            $isSuccess = false;
        }
        return $isSuccess;
    }

    public function checkTargetPeriod($targetPeriodFrom, $targetPeriodTo, $companyId, $formCode)
    {
        try {
            $resultCheckTargetPeriod = true;
            if ($targetPeriodTo) {
                if ($targetPeriodFrom > $targetPeriodTo) {
                    $resultCheckTargetPeriod = false;
                }
            }
            $targetPeriod = null;
            if ($resultCheckTargetPeriod) {
                // Todo: try use function get validity_period
                $targetPeriod = DB::table('eps_m_form')
                    ->where([
                        'mst_company_id' => $companyId,
                        'form_code' => $formCode
                    ])->select('validity_period_from', 'validity_period_to')
                    ->first();
            }

            if (!$targetPeriod) {
                $resultCheckTargetPeriod = false;
            }
            if ($resultCheckTargetPeriod && isset($targetPeriod->validity_period_from)) {
                if ($targetPeriodFrom < $targetPeriod->validity_period_from) {
                    $resultCheckTargetPeriod = false;
                }
            }
            if ($resultCheckTargetPeriod && isset($targetPeriod->validity_period_to) && $targetPeriodTo) {
                if ($targetPeriodTo > $targetPeriod->validity_period_to) {
                    $resultCheckTargetPeriod = false;
                }
            }
        } catch (\Exception $ex) {
            Log::error("$this->controllerName@checkTargetPeriod");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            $resultCheckTargetPeriod = false;
        }

        return $resultCheckTargetPeriod;
    }

    public function checkTargetPeriodItem($targetPeriodFrom, $targetPeriodTo, $expectedPayDate)
    {
        try {
            $resultCheckTargetPeriodItem = true;
            if ($resultCheckTargetPeriodItem && isset($targetPeriodFrom) && isset($expectedPayDate)) {
                if ($targetPeriodFrom > $expectedPayDate) {
                    $resultCheckTargetPeriodItem = false;
                }
            }
            if ($resultCheckTargetPeriodItem && isset($targetPeriodTo) && isset($expectedPayDate)) {
                if ($targetPeriodTo < $expectedPayDate) {
                    $resultCheckTargetPeriodItem = false;
                }
            }
        } catch (\Exception $ex) {
            Log::error("$this->controllerName@checkTargetPeriodItem");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            $resultCheckTargetPeriodItem = false;
        }

        return $resultCheckTargetPeriodItem;
    }

    public function getValidityPeriod($id, $companyId)
    {
        try {
            $validityPeriod = null;
            $isSuccess = false;
            $tAppData = DB::table('eps_t_app')
                ->where([
                    'eps_t_app.id' => $id,
                    'eps_t_app.mst_company_id' => $companyId,
                ])->select('eps_t_app.form_code')->first();
            if ($tAppData) {
                $formCode = $tAppData->form_code;
                $validityPeriod = DB::table('eps_m_form')
                    ->where([
                        'eps_m_form.mst_company_id' => $companyId,
                        'eps_m_form.form_code' => $formCode,
                    ])->select('eps_m_form.validity_period_from',
                        'eps_m_form.validity_period_to'
                    )->first();
                if ($validityPeriod) {
                    $isSuccess = true;
                }
            }
        } catch (\Exception $ex) {
            Log::error("$this->controllerName@getValidityPeriod");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
        }
        $result['success'] = $isSuccess;
        $result['validity_period'] = $validityPeriod;

        return $result;

    }

    public function checkExpectedAmt($companyId, $formCode, $tAppItems, $formType)
    {
        try {
            $isSuccess['max']  = false;
            $isSuccess['min']  = false;
            $sumExpectedPayAmt = 0;
            foreach ($tAppItems as $item) {
                if (isset($item['expected_pay_amt'])) {
                    $sumExpectedPayAmt += $item['expected_pay_amt'];
                }
            }

            $validateTotal = DB::table('eps_m_form')
                ->where([
                    'eps_m_form.mst_company_id' => $companyId,
                    'eps_m_form.form_code' => $formCode
                ])->select('eps_m_form.total_amt_min',
                    'eps_m_form.total_amt_max'
                )->first();

            if ($sumExpectedPayAmt >= 0) {
                if ($validateTotal) {
                    if ((isset($validateTotal->total_amt_max) && $sumExpectedPayAmt <= $validateTotal->total_amt_max) ||
                        !isset($validateTotal->total_amt_max)) {
                            $isSuccess['max'] = true;
                    }
                    if ((isset($validateTotal->total_amt_min) && $sumExpectedPayAmt >= $validateTotal->total_amt_min) ||
                        !isset($validateTotal->total_amt_min)) {
                            $isSuccess['min'] = true;
                    }
                }
            }
        } catch (\Exception $ex) {
            Log::error("$this->controllerName@checkExpectedAmt");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
        }
        $result['resultMax'] = $isSuccess['max'];
        $result['resultMin'] = $isSuccess['min'];
        if (!$isSuccess['max']) {
            $result['message'] = '金額は最大金額を超えている。';
        } else if (!$isSuccess['min']){
            $result['message'] = '最小金額により、予定支出金額は正しくありません。';
        } else {
            if ($formType == AppUtils::EPS_M_FORM_FORM_TYPE_ADVANCE) {
                $result['expected_amt'] = $sumExpectedPayAmt;
            }
            if ($formType == AppUtils::EPS_M_FORM_FORM_TYPE_SETTLEMENT) {
                $result['eps_amt'] = $sumExpectedPayAmt;
            }
        }

        return $result;
    }

    public function saveExpenseInputData(SaveExpenseInputDataAPIRequest $request)
    {
        $data = $request->all();
        $user = $request->user();
        try {

            $tAppId = $data['t_app_id'];
            $circularId = $data['circular_id'];
            $formCode = $data['form_code'];

            $listExpenseInput = [];
            $dataTAppItem = DB::table('eps_t_app_items')
                ->where([
                    'mst_company_id' => $user->mst_company_id,
                    't_app_id' => $tAppId,
                    'deleted_at' => null,
                ])->select('wtsm_name',
                    'expected_pay_date',
                    'expected_pay_amt',
                    'remarks',
                    'from_station',
                    'to_station',
                    'expected_pay_amt'
                )
                ->get()->toArray();

            $placeHolderExpenseForm = DB::table('expense_placeholder_data')
                ->where([
                    'mst_company_id' => $user->mst_company_id,
                    'eps_m_form_code' => $formCode,
                    'deleted_at' => null,
                ])->pluck('template_placeholder_name')->toArray();
            $placeHolderDataItem = null;
            $placeHolderCommon = null;
            if ($placeHolderExpenseForm) {
                $placeHolderCommon = array_filter($placeHolderExpenseForm, function ($item) {
                    if (in_array($item, ExpenseUtils::EXPENSE_FORM_PLACE_HOLDER_COMMON)) {
                        return true;
                    }
                    return false;
                });
                if ($placeHolderCommon) {
                    $placeHolderDataItem = array_diff($placeHolderExpenseForm, $placeHolderCommon);
                }
            }

            $companyName = null;
            $departmentName = null;
            $userName = $user->family_name . $user->given_name;
            // get company_name, department_name,
            $userInfoInCompany = DB::table('mst_company')
                ->where('mst_company.id', $user->mst_company_id)
                ->leftJoin('mst_department',
                    'mst_department.mst_company_id',
                    'mst_company.id')
                ->leftJoin('mst_user_info', 'mst_user_info.mst_department_id',
                    'mst_department.id')
                ->where([
                    'mst_user_info.mst_user_id' => $user->id
                ])->select('mst_department.department_name',
                    'mst_company.company_name')
                ->first();
            if ($userInfoInCompany) {
                $companyName = $userInfoInCompany->company_name;
                $departmentName = $userInfoInCompany->department_name;
            }


            $formInfo = DB::table('eps_m_form')
                ->where([
                    'mst_company_id' => $user->mst_company_id,
                    'form_code' => $formCode,
                    'deleted_at' => null,
                ])->select('form_type')->first();
            $formType = AppUtils::EPS_M_FORM_FORM_TYPE_UNKNOWN;
            if ($formInfo) {
                $formType = $formInfo->form_type;
            }
            $tAppData = DB::table('eps_t_app')
                ->where([
                    'mst_company_id' => $user->mst_company_id,
                    'id' => $tAppId,
                    'form_code' => $formCode,
                ])->select('suspay_amt', 'eps_amt')->first();
            $suspayAmt = 0;
            $epsAmt = 0;
            if ($tAppData) {
                $suspayAmt = $tAppData->suspay_amt;
                $epsAmt = $tAppData->eps_amt;
            }

            if ($placeHolderCommon) {
                foreach ($placeHolderCommon as $value) {
                    $item = [
                        'expense_placeholder_name' => $value,
                        't_app_id' => $tAppId,
                        'circular_id' => $circularId,
                        'user_id' => $user->id,
                        'create_at' => Carbon::now(),
                        'create_user' => $userName,
                    ];
                    $data = null;

                    switch ($value) {
                        case ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_COMPANY_NAME:
                            $data = $companyName;
                            break;
                        case ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_DEPARTMENT_NAME:
                            $data = $departmentName;
                            break;
                        case ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_USER_NAME:
                            $data = $userName;
                            break;
                        case ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_TOTAL:
                            if ($formType == AppUtils::EPS_M_FORM_FORM_TYPE_ADVANCE) {
                                $data = $suspayAmt;
                            } else {
                                $data = $epsAmt;
                            }
                            break;
                    }

                    $infoDataInputForPlaceHolder = $this->getDataInputForPlaceHolder($data);
                    $item['data_type'] = $infoDataInputForPlaceHolder['data_type'];
                    $item['date_data'] = $infoDataInputForPlaceHolder['date_data'];
                    $item['num_data'] = $infoDataInputForPlaceHolder['num_data'];
                    $item['text_data'] = $infoDataInputForPlaceHolder['text_data'];
                    $listExpenseInput[] = $item;
                }
            }
            if ($placeHolderDataItem && $dataTAppItem) {
                $listMFormPlaceHolderData = [
                    ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_WTSM_NAME,
                    ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_EXPECTED_PAY_DATE,
                    ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_DESCRIBE,
                    ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_EXPECTED_PAY_AMT,
                ];
                foreach ($dataTAppItem as $key => $item) {
                    $index = $key + 1;

                    $describe = null;
                    if (in_array($item->wtsm_name, ExpenseUtils::EXPENSE_LIST_WTSM_NAME_TRANSPORT)) {
                        if ($item->from_station && $item->to_station) {
                            $describe = $item->from_station . ' → ' . $item->to_station;
                        } else {
                            if ($item->from_station) {
                                $describe = $item->from_station;
                            }
                            if ($item->to_station) {
                                $describe = $item->to_station;
                            }
                        }
                    } else {
                        $describe = $item->remarks;
                    }

                    $dataForPlaceHolder = null;
                    foreach ($listMFormPlaceHolderData as $mFormPlaceHolder) {
                        $fieldData = null;
                        $placeHolderSearch = '${' . $mFormPlaceHolder . "$index}";
                        if (in_array($placeHolderSearch, $placeHolderDataItem)) {

                            // Todo: remove element in  $placeHolderDataItem (search faster) ??
                            $expensePlaceHolderName = $placeHolderSearch;
                            $fieldData = $this->getFieldDataFormTAppItem($placeHolderSearch);
                            if ($fieldData == ExpenseUtils::EXPENSE_INPUT_FOR_PLACE_HOLDER_WTSM_NAME) {
                                $dataForPlaceHolder = $item->$fieldData;
                            }
                            if ($fieldData == ExpenseUtils::EXPENSE_INPUT_FOR_PLACE_HOLDER_EXPECTED_PAY_DATE) {
                                $dataForPlaceHolder = $item->$fieldData;
                            }
                            if ($fieldData == ExpenseUtils::EXPENSE_INPUT_FOR_PLACE_HOLDER_EXPECTED_PAY_AMT) {
                                $dataForPlaceHolder = $item->$fieldData;
                            }
                            if ($fieldData == ExpenseUtils::EXPENSE_INPUT_FOR_PLACE_HOLDER_DESCRIBE) {
                                $dataForPlaceHolder = $describe;
                            }
                            $itemInsert = [
                                'expense_placeholder_name' => $expensePlaceHolderName,
                                't_app_id' => $tAppId,
                                'circular_id' => $circularId,
                                'user_id' => $user->id,
                                'create_at' => Carbon::now(),
                                'create_user' => $userName,
                            ];
                            $infoDataInputForPlaceHolder = $this->getDataInputForPlaceHolder($dataForPlaceHolder);

                            $itemInsert['data_type'] = $infoDataInputForPlaceHolder['data_type'];
                            $itemInsert['date_data'] = $infoDataInputForPlaceHolder['date_data'];
                            $itemInsert['num_data'] = $infoDataInputForPlaceHolder['num_data'];
                            $itemInsert['text_data'] = $infoDataInputForPlaceHolder['text_data'];
                            $listExpenseInput[] = $itemInsert;

                        }
                    }
                }
            }
            if ($listExpenseInput) {
                DB::table('expense_input_data')
                    ->insert($listExpenseInput);
            }
            return $this->sendSuccess('saveExpenseInputData Success');

        } catch (\Exception $ex) {
            Log::error("$this->controllerName@saveExpenseInputData");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);

        }

    }

    public function getFieldDataFormTAppItem($placeHolderParam)
    {
        $field = null;
        if (strpos($placeHolderParam, ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_WTSM_NAME) !== false) {
            $field = ExpenseUtils::EXPENSE_INPUT_FOR_PLACE_HOLDER_WTSM_NAME;
        }
        if (strpos($placeHolderParam, ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_EXPECTED_PAY_DATE) !== false) {
            $field = ExpenseUtils::EXPENSE_INPUT_FOR_PLACE_HOLDER_EXPECTED_PAY_DATE;
        }
        if (strpos($placeHolderParam, ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_DESCRIBE) !== false) {
            $field = ExpenseUtils::EXPENSE_INPUT_FOR_PLACE_HOLDER_DESCRIBE;
        }
        if (strpos($placeHolderParam, ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_EXPECTED_PAY_AMT) !== false) {
            $field = ExpenseUtils::EXPENSE_INPUT_FOR_PLACE_HOLDER_EXPECTED_PAY_AMT;
        }

        return $field;

    }

    public function getDataInputForPlaceHolder($data)
    {

        $item = [
            'date_data' => null,
            'num_data' => null,
            'text_data' => null,
        ];
        if (ExpenseUtils::isDate($data)) {
            $item['data_type'] = ExpenseUtils::EXPENSE_INPUT_DATA_TYPE_DATE;
            $item['date_data'] = Carbon::parse($data);
        } else if (is_numeric($data)) {
            $item['data_type'] = ExpenseUtils::EXPENSE_INPUT_DATA_TYPE_NUMERIC;
            $item['num_data'] = $data;
        } else {
            $item['data_type'] = ExpenseUtils::EXPENSE_INPUT_DATA_TYPE_TEXT;
            $item['text_data'] = $data;
        }
        return $item;

    }

    // Todo: consider delete this function if not use

    public function updateExpenseCircularContent(UpdateExpenseCircularContentAPI $request)
    {
        $data = $request->all();
        $user = $request->user();

        try {
            $formCode = $data['form_code'];
            $tAppId = $data['t_app_id'];
            $circularId = $data['circular_id'];

            $isError = false;

            $inputWithPlaceHolder = DB::table('expense_placeholder_data')
                ->where([
                    'mst_company_id' => $user->mst_company_id,
                    'eps_m_form_code' => $formCode,
                ])->leftJoin('expense_input_data',
                    'expense_input_data.expense_placeholder_name',
                    'expense_placeholder_data.template_placeholder_name'
                )
                ->select('expense_placeholder_data.template_placeholder_name',
                    'expense_placeholder_data.cell_address',
                    'expense_input_data.data_type',
                    'expense_input_data.date_data',
                    'expense_input_data.num_data',
                    'expense_input_data.text_data'
                )->get()->toArray();


            $mFormInfo = DB::table('eps_m_form')
                ->where([
                    'form_code' => $formCode,
                    'mst_company_id' => $user->mst_company_id,
                ])
                ->select('s3_path',
                    's3_file_name',
                    'origin_file_name'
                )->first();
            $file_name = null;
            $path = null;
            $filePath = null;
            if ($mFormInfo && $mFormInfo->s3_file_name &&
                $mFormInfo->s3_file_name && $mFormInfo->origin_file_name) {
                $local_path = ExpenseUtils::localExpensePath($user->mst_company_id, $user->id);
                $s3PathFile = $mFormInfo->s3_path . '/' .$mFormInfo->s3_file_name;
                if (Storage::disk('s3')->exists($s3PathFile)) {
                    $getFile = Storage::disk('s3')->get($s3PathFile);
                    Log::info('Get Expense Form: ' . $s3PathFile);
                    Storage::disk('local')->put($local_path . $mFormInfo->s3_file_name, $getFile);
                }
//                Log::info('$local_path');
//                Log::info($local_path);
                $filePath = storage_path('app/' . $local_path . $mFormInfo->s3_file_name);
                $fileEncode = \base64_encode(\file_get_contents($filePath));
                $file_name = $mFormInfo->s3_file_name;
            } else {
                $isError = true;
                Log::error("$this->controllerName@updateExpenseCircularContent: error s3 info error");
            }
            if ($isError) {
                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
            }
            if ($inputWithPlaceHolder) {
                Log::info('Start editing Excel file');
                $reader = new XlsxReader();
                $reader->setReadDataOnly(false);
                $spreadsheet = $reader->load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                foreach ($inputWithPlaceHolder as $item) {
                    $dataType = $item->data_type;
                    $dataReplace = null;
                    if ($dataType == ExpenseUtils::EXPENSE_INPUT_DATA_TYPE_DATE) {
                        $dataReplace = $item->date_data;
                    } else if ($dataType == ExpenseUtils::EXPENSE_INPUT_DATA_TYPE_NUMERIC) {
                        $dataReplace = $item->num_data;
                    } else if ($dataType == ExpenseUtils::EXPENSE_INPUT_DATA_TYPE_TEXT) {
                        $dataReplace = $item->text_data;
                    }
                    $sheet->setCellValue($item->cell_address, $dataReplace);
                }
                $writer = new XlsxWriter($spreadsheet);
                $path = explode(".", (microtime(true) . ""))[0] . '_' . $user->id . '.xlsx';
                $writer->save($path);
//                 Todo unlink file path
//                unlink($filePath);

            }

            return $this->sendResponse([
                'file_name' => $file_name,
                'file_data' => \base64_encode(\file_get_contents(public_path() . '/' . $path)),
                'storage_file_name' => $path, 'no_placeHolder' => 0
            ],
                'updateExpenseCircularContent done');

        } catch (\Exception $ex) {
            Log::error("$this->controllerName@updateExpenseCircularContent");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "ファイルを取得できませんでした。"];
        }


    }

}
