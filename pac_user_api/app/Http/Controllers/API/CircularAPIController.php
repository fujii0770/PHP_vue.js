<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CheckAccessCodeRequest;
use App\Http\Requests\API\CreateCircularAPIRequest;
use App\Http\Requests\API\TransferCircularAPIRequest;
use App\Http\Requests\API\UpdateCircularAPIRequest;
use App\Http\Requests\API\UpdateCircularStatusAPIRequest;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\CircularOperationHistoryUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Delegate\EnvApiDelegate;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\OperationsHistoryUtils;
use App\Http\Utils\SpecialApiUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Mail\SendCircularReNotificationMail;
use App\Http\Utils\TemplateRouteUtils;
use App\Models\CircularUserRoutes;
use App\Repositories\CompanyRepository;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\CircularDocumentUtils;
use App\Http\Utils\UserApiUtils;
use App\Http\Utils\ExpenseUtils;

use Matrix\Exception;
use PDF;
use Response;
use Session;
use App\Http\Utils\MailUtils;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */

class CircularAPIController extends AppBaseController
{

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * Create a new instance.
     *
     * @param CompanyRepository $companyRepository
    */
    public function __construct(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

    /**
     * Store a newly created Circular in storage.
     * POST /circulars
     *
     * @param CreateCircularAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateCircularAPIRequest $request)
    {
        try {
            $user = $request->user();
            $circularUser = null;
            $usingHash = $request['usingHash'];
            if($usingHash) {
                $user = $request['user'];
				$user_name = $request['current_name'];
                $circularUser = $request['current_circular_user'];
                $isInCurrentEnv = ($circularUser->edition_flg == config('app.edition_flg') && $circularUser->env_flg == config('app.server_env') && $circularUser->server_flg == config('app.server_flg'));
            }else{
				$user_name = $user->getFullName();
                $isInCurrentEnv = true;
            }
            $pdf_data = $request['pdf_data'];
            $file_name = $request['file_name'];
            $circular_id = $request['circular_id'];
            $is_special_flg = $request->get('isSpecialSiteFlg') ? 1 : 0;
            $templateId = $request['templateId'];

            $hasReqSendBack = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where('circular_status', CircularUserUtils::SUBMIT_REQUEST_SEND_BACK)
                ->count();

            if($hasReqSendBack) return $this->sendError("", \Illuminate\Http\Response::HTTP_BAD_REQUEST);

            $storeData = $this->storeCir($user, $usingHash, $user_name, $circularUser, $isInCurrentEnv, $pdf_data, $file_name, $circular_id, $is_special_flg,$templateId);
            if (is_array($storeData)) {
                return $this->sendResponse($storeData,'回覧登録処理に成功しました。');
            } else {
                return $this->sendError($storeData->original['message']);
            }
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('回覧登録処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Store a newly Circular in storage.
     * @param $user
     * @param $usingHash
     * @param $user_name
     * @param $circularUser
     * @param $isInCurrentEnv
     * @param $pdf_data
     * @param $file_name
     * @param $circular_id
     * @param int $special_site_flg
     * @return array
     */
    public function storeCir($user, $usingHash, $user_name, $circularUser, $isInCurrentEnv, $pdf_data, $file_name, $circular_id,int $special_site_flg = 0,$templateId=null) {
            // check constraint
            if($isInCurrentEnv) {
                $settingConstraints = DB::table('mst_constraints')
                    ->where('mst_company_id', $user->mst_company_id)->first();

                $num_request = DB::table('circular_document')
                    ->where('create_company_id', $user->mst_company_id)
                    ->where('origin_env_flg', config('app.server_env'))
                    ->where('origin_edition_flg', config('app.edition_flg'))
                    ->where('origin_server_flg', config('app.server_flg'))
                    ->where('create_at', '>=', Carbon::today())
                    ->count();

                // check max_requests: number of transmissions per day (0 is unlimited)
                if($settingConstraints && $num_request >= $settingConstraints->max_requests AND $settingConstraints->max_requests > 0){
                    return $this->sendError("このファイルはアップロードできません。1 日のアップロード数(".$settingConstraints->max_requests.")回を超えています。");
                }

                // check user_storage_size: Disk capacity, disk space per user
            // 企業の使用容量取得（前日夜間バッチ算出したもの）
            $disk_usage_situation = DB::table('usage_situation_detail')
                ->where('mst_company_id', $user->mst_company_id)
                ->whereNull('guest_company_id')
                ->orderBy('target_date','desc')
                ->first();
            if($disk_usage_situation){
                $storage_size = $disk_usage_situation->storage_sum_re;
            }else{
                $storage_size = 0;
            }
            // ユーザー数
            $user_valid_num = DB::table('mst_user')
                ->join('mst_user_info', 'mst_user.id', '=', 'mst_user_info.mst_user_id')
                ->where('mst_company_id', $user->mst_company_id)
                ->where(function ($query) use ($user){
                    if(config('app.fujitsu_company_id') && config('app.fujitsu_company_id') == $user->mst_company_id){
                        // 富士通(K5)場合、
                        // 有効でパスワードが設定してあるユーザー
                        $query->whereNotNull('password_change_date');
                    }
                    $query->whereIn('state_flg',[AppUtils::STATE_VALID]);
                })
                ->where(function ($query) {
                    $query->where('mst_user.option_flg', AppUtils::USER_NORMAL)
                        ->orWhere(function ($query){
                            $query->where('mst_user.option_flg', AppUtils::USER_OPTION)
                                ->where('mst_user_info.gw_flg', 1);
                        });
                })
                ->count();
            $company = DB::table('mst_company')->where('id',$user->mst_company_id)->first();
            // 容量チェック（バッチでの計算値：MB）
            if( ($storage_size > ($user_valid_num + $company->add_file_limit) * 1024 )) {
                $size = $user_valid_num + $company->add_file_limit . " GB";
                    return $this->sendError("このファイルはアップロードできません。データ容量($size)を超えています。");
                }
            }

            DB::beginTransaction();
            $current_circular_user = null;
            $parent_send_order = 0;
            $document_no = 0;
            if(!$circular_id) {
                $circular_id = DB::table('circular')->insertGetId([
                    'mst_user_id' => $user->id,
                    'address_change_flg' => 0,
                    'access_code_flg' => CircularUtils::ACCESS_CODE_INVALID,
                    'access_code' => '',
                    'hide_thumbnail_flg' => CircularUtils::HIDE_THUMBNAIL_VALID,
                    'circular_status' => CircularUtils::SAVING_STATUS,
                    'env_flg' => config('app.server_env'),
                    'edition_flg' => config('app.edition_flg'),
                    'server_flg' => config('app.server_flg'),
                    'create_at' => Carbon::now(),
                    'create_user' => $user->email,
                    'update_at' => Carbon::now(),
                    'final_updated_date' => Carbon::now(),
                    'update_user' => $user->email,
                    'text_append_flg' => CircularUtils::TEXT_APPEND_FLG_INVALID,
                    'special_site_flg' => $special_site_flg
                ]);

            }else {
                $document_no = DB::table('circular_document')->where('circular_id', $circular_id)->max('document_no');

                DB::table('circular')->where('id', $circular_id)->update([
                    'update_at' => Carbon::now(),
                    'update_user' => $user->email,
                ]);
                if ($isInCurrentEnv){
                    $current_circular_user = DB::table('circular_user')->where('circular_id', $circular_id)
                        ->where('email', $user->email)
                        ->whereIn('circular_status', [CircularUserUtils::NOT_NOTIFY_STATUS, CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS])
                        ->orderBy('parent_send_order')
                        ->orderBy('child_send_order')
                        ->first();
                    if ($current_circular_user){
                        $parent_send_order = $current_circular_user->parent_send_order;
                    }
                }else{
                    $parent_send_order = $circularUser->parent_send_order;
                }
            }

            if(!empty($templateId) && (!empty($company) && $company->template_flg == 1 && $company->template_approval_route_flg == 1 && $company->template_route_flg == 1)) {
                $transferredCircular = [];
                $transferredCircular[0] = [
                    "name" => $user_name,
                    "email" => $user->email,
                    "child_send_order" => 0,
                    'env_flg' => config('app.server_env'),
                    'edition_flg' => config('app.edition_flg'),
                    'server_flg' => config('app.server_flg'),
                    "company_id" => $user->mst_company_id,
                    "company_name" => $user->mst_company_name,
                    "is_maker" => true,
                ];
                //circular_user_templates
                $multiple_department_position_flg = DB::table('mst_company')->where('id', $user->mst_company_id)->select('multiple_department_position_flg')->first()->multiple_department_position_flg ?? 0;

                $arrTemplates = DB::table('template_file')
                    ->join('circular_user_templates', 'template_file.template_route_id', '=', 'circular_user_templates.id')
                    ->join('circular_user_template_routes', 'circular_user_templates.id', '=', 'circular_user_template_routes.template')
                    ->join('mst_position', function ($query) {
                        $query->on('circular_user_template_routes.mst_position_id', '=', 'mst_position.id');
                        $query->where('mst_position.state', 1);
                    })
                    ->join('mst_department', function ($query) {
                        $query->on('circular_user_template_routes.mst_department_id', '=', 'mst_department.id');
                        $query->where('mst_department.state', 1);
                    })
                    ->leftjoin('mst_user_info', function ($query) use ($multiple_department_position_flg) {
                        if ($multiple_department_position_flg === 1) {
                            // 部署と役職
                            $query->on(function ($query) {
                                $query->on('mst_position.id', '=', 'mst_user_info.mst_position_id')
                                    ->on('mst_department.id', '=', 'mst_user_info.mst_department_id');
                            })->orOn(function ($query) {
                                $query->on('mst_position.id', '=', 'mst_user_info.mst_position_id_1')
                                    ->on('mst_department.id', '=', 'mst_user_info.mst_department_id_1');
                            })->orOn(function ($query) {
                                $query->on('mst_position.id', '=', 'mst_user_info.mst_position_id_2')
                                    ->on('mst_department.id', '=', 'mst_user_info.mst_department_id_2');
                            });
                        } else {
                            $query->on('mst_position.id', '=', 'mst_user_info.mst_position_id')
                                ->on('mst_department.id', '=', 'mst_user_info.mst_department_id');
                        }
                    })
                    ->join('mst_user', function ($query) use ($user) {
                        $query->on('mst_user_info.mst_user_id', '=', 'mst_user.id');
                        $query->where('mst_user.state_flg', 1);
                        $query->where('mst_user.mst_company_id', $user->mst_company_id);
                    })
                    ->where('circular_user_templates.mst_company_id', $user->mst_company_id)
                    ->where('circular_user_templates.state', AppUtils::TEMPLATE_VALID)
                    ->where('template_file.id', $templateId);

                $arrTemplates = $arrTemplates->select(DB::raw('circular_user_templates.id, circular_user_template_routes.id route_id, circular_user_templates.name, mst_position.position_name,
                mst_department.department_name,mst_department.id as department_id,circular_user_template_routes.child_send_order,
                circular_user_template_routes.mode, circular_user_template_routes.option, circular_user_template_routes.wait,
                    mst_user.family_name, mst_user.given_name, mst_user.id as user_id, mst_user.email, mst_user_info.id as user_info_id'))
                    ->orderBy('circular_user_templates.id', 'asc')
                    ->orderBy('circular_user_template_routes.child_send_order', 'asc')
                    ->get();
                $arrTemplateInfo = [];
                foreach ($arrTemplates as $template) {
                    // template route
                    if (!key_exists($template->route_id, $arrTemplateInfo)) {
                        $arrTemplateInfo[$template->route_id]["position_name"] = $template->position_name;
                        $arrTemplateInfo[$template->route_id]["department_name"] = $template->department_name;
                        $arrTemplateInfo[$template->route_id]["department_id"] = $template->department_id;
                        $arrTemplateInfo[$template->route_id]["mode"] = $template->mode;
                        $arrTemplateInfo[$template->route_id]["option"] = $template->option;
                        $arrTemplateInfo[$template->route_id]["wait"] = $template->wait;
                        $arrTemplateInfo[$template->route_id]["route_id"] = $template->route_id;
                        $arrTemplateInfo[$template->route_id]["users"] = [];
                    }
                    // template route user
                    if ($template->user_info_id) {
                        $arrTemplateInfo[$template->route_id]["users"][] = ["id" => $template->user_id, "family_name" => $template->family_name, "given_name" => $template->given_name, "email" => $template->email];
                    }
                }
                // 下付き変換 $template->route_id => 0,1,...
                $template_rotes = [];
                foreach ($arrTemplateInfo as $template_rote) {
                    // 設定有効無効
                    $template_rote["template_route_valid"] = false;
                    if ($template_rote["mode"] == TemplateRouteUtils::TEMPLATE_MODE_ALL_MUST) {
                        if (count($template_rote["users"]) > 0) {
                            $template_rote["template_route_valid"] = true;
                        } else {
                            $template_rote["template_route_valid"] = false;
                        }
                    } else if ($template_rote["mode"] == TemplateRouteUtils::TEMPLATE_MODE_MORE_THAN) {
                        if (count($template_rote["users"]) >= $template_rote["option"]) {
                            $template_rote["template_route_valid"] = true;
                        } else {
                            $template_rote["template_route_valid"] = false;
                        }
                    }
                    $template_rotes[] = $template_rote;
                }
                foreach ($template_rotes as $temp) {
                    if (!$temp['template_route_valid']) {
                        continue;
                    }
                    foreach ($temp['users'] as $ku => $uv) {
                        $arrUser = [];
                        $arrUser['child_send_order'] = $ku + 1;
                        $arrUser['email'] = $uv['email'];
                        $arrUser['name'] = $uv['family_name'] . ' ' . $uv['given_name'];
                        $arrUser['edition_flg'] = config('app.edition_flg');
                        $arrUser['env_flg'] = config('app.server_env');
                        $arrUser['server_flg'] = config('app.server_flg');
                        $arrUser['company_id'] = $user->mst_company_id;
                        $arrUser['company_name'] = $user->mst_company_name;
                        $arrUser['is_maker'] = false;
                        $arrUser['template_rotes_id'] = $temp['route_id'];
                        $arrUser['template_mode'] = $temp['mode'];
                        $arrUser['template_wait'] = $temp['wait'];
                        $arrUser['template_score'] = $temp['option'];
                        $summary = $temp['department_name'] . " " . $temp['position_name'] . " (" . ($temp['option'] === 0 ? "" : $temp['option']) .
                            TemplateRouteUtils::TEMPLATE_MODE[$temp['mode']] . ")";
                        $arrUser['template_detail'] = json_encode([
                            'summary' => $summary,
                            'agreement' => [
                                'type' => $temp['mode'],
                                'detail' => $temp['option'],
                                'wait' => $temp['wait'],
                            ]
                        ],JSON_UNESCAPED_UNICODE);
                        array_push($transferredCircular, $arrUser);
                    }
                }
                $flag = false;
                $flag = $this->storeCircularUser($circular_id, $templateId, $user, $transferredCircular);
                if(!$flag){
                    DB::rollBack();
                    return false;
                }
            }
            $default_confidential_flg = CircularUtils::CONFIDENTIAL_INVALID;

        if(!$usingHash && config('app.fujitsu_company_id') && $user->mst_company_id == config('app.fujitsu_company_id') && $document_no > 0) {
                $default_confidential_flg = CircularUtils::CONFIDENTIAL_VALID;
            }
            $circular_document = [
                'circular_id'=> $circular_id,
                'origin_env_flg' => $isInCurrentEnv?config('app.server_env'):$circularUser->env_flg,
                'origin_edition_flg' => $isInCurrentEnv?config('app.edition_flg'):$circularUser->edition_flg,
                'origin_server_flg' => $isInCurrentEnv?config('app.server_flg'):$circularUser->server_flg,
                'create_user_id' => $user->id,
                'confidential_flg'=> $default_confidential_flg,
            'file_name'=> $file_name,
                'create_at' => Carbon::now(),
                'create_user' => $user->email,
                'update_at' => Carbon::now(),
                'update_user' => $user->email,
                'file_size' => AppUtils::getFileSize($pdf_data),
                'document_no' => ($document_no + 1),
                'origin_document_id' => -1,
                'parent_send_order' =>$parent_send_order,
                'create_company_id' => $isInCurrentEnv?$user->mst_company_id:$circularUser->mst_company_id,
            ];

            $circular_document_id = DB::table('circular_document')->insertGetId($circular_document);
            $circular_document['id'] = $circular_document_id;

            DB::table('document_data')->insert([
                'circular_document_id'=> $circular_document_id,
                'file_data'=> AppUtils::encrypt($pdf_data),
                'create_at' => Carbon::now(),
                'create_user' => $user->email,
                'update_at' => Carbon::now(),
                'update_user' => $user->email,
            ]);

            $circular = DB::table('circular')->where('id', $circular_id)->first();

			// PAC_5-539 承認履歴情報登録
			DB::table('circular_operation_history')->insert([
				'circular_id'=> $circular_id,
				'circular_document_id' => $circular_document_id,
				'operation_email' => $user->email,
				'operation_name' => $user_name,
				'acceptor_email'=> '',
				'acceptor_name'=> '',
				'circular_status'=> CircularOperationHistoryUtils::CIRCULAR_CREATE_STATUS,
				'create_at' => Carbon::now(),
			]);

        $storeData['circular'] = $circular;
        $storeData['circular_document_id'] = $circular_document_id;
        $storeData['circular_document'] = $circular_document;
        // PDF変換後の改ページ調整で楽観ロック用にdocument_dataの更新日時を取得
        $document_data_update_at = DB::table('document_data')
            ->where('circular_document_id', $circular_document_id)->select('update_at')->first()->update_at;
        $storeData['document_data_update_at'] = $document_data_update_at;
			DB::commit();

        return $storeData;
    }

    public function exportWorkListToPdf(Request $request) {
        $datas = [];
        $selectMonth = $request['selectMonth'];
        $selectAdjustUnit = $request['selectAdjustUnit']; // 調整単位(分) 5,10,15,30,60
        $selectAdjustType = $request['selectAdjustType']; // 調整方法 0:調整なし 1:切上げ 2:切捨て
        $user = $request->user();
        $data['year'] = substr($selectMonth, 0, 4);
        $data['month'] = substr($selectMonth, 4,2);
        $company = DB::table('mst_company')
            ->where('id', $user->mst_company_id)
            ->first();
        $data['company_name'] = $company->company_name;
        $data['user_name'] = $user->family_name . ' ' . $user->given_name;
        $assigned_company = DB::table('mst_hr_info')
            ->where('mst_user_id', $user->id)
            ->orderBy('create_at', 'DESC')->first();
        $data['assigned_company'] = $assigned_company?$assigned_company->assigned_company:'';

        $timeCardDetails = [];
        $totalDays = \Carbon\Carbon::createFromFormat('Ym', $selectMonth)->daysInMonth;
        $data['totalDays'] = $totalDays;

        $timeCardExisted = DB::table('hr_timecard_detail')
            ->where('mst_user_id', $user->id)
            ->where('mst_company_id', $user->mst_company_id)
            ->where(DB::raw('SUBSTRING(work_date, 1, 6)'), '=', $selectMonth)
            ->get()->keyBy('work_date')->toArray();

        $attendanceDays = 0;       // number
        $totalAttendanceTime = 0;  // calculate in minute
        $totalWorkingTime = 0;     // calculate in minute
        for ($i = 1; $i <= $totalDays; $i++) {
            $dayOfWeek = Carbon::createFromFormat('Ymd', $selectMonth.$i);
            $timeCardDetail['date'] = $dayOfWeek->locale('Ja')->shortDayName;
            if (array_key_exists($dayOfWeek->format('Ymd'), $timeCardExisted)) {

                $absent_flg = $timeCardExisted[$dayOfWeek->format('Ymd')]->absent_flg;
                $paid_Flg = $timeCardExisted[$dayOfWeek->format('Ymd')]->paid_vacation_flg;
                $sp_flg = $timeCardExisted[$dayOfWeek->format('Ymd')]->sp_vacation_flg;
                $dayOff_flg = $timeCardExisted[$dayOfWeek->format('Ymd')]->day_off_flg;

                // check if it is one of three days off or absent
                if ($paid_Flg == 1 || $sp_flg == 1 || $dayOff_flg == 1 || $absent_flg == 1) {
                    $timeCardDetail['work_start_time'] = '';
                    $timeCardDetail['work_end_time'] = '';
                    $timeCardDetail['break_time'] = '';
                    $timeCardDetail['total'] = '';
                    $timeCardDetail['actual'] = '';
                    if ($paid_Flg == 1) {
                        $timeCardDetail['vacation'] = '有給';
                    } elseif ($sp_flg == 1) {
                        $timeCardDetail['vacation'] = '特休';
                    } elseif ($dayOff_flg == 1) {
                        $timeCardDetail['vacation'] = '代休';
                    } elseif ($absent_flg == 1) {
                        $timeCardDetail['vacation'] = '欠勤';
                    }
                } else {
                    $break = $timeCardExisted[$dayOfWeek->format('Ymd')]->break_time;
                    $timeCardDetail['vacation'] = '';

                    // display Total time format to HH:mm
                    if (!empty($timeCardExisted[$dayOfWeek->format('Ymd')]->work_start_time)) {
                        $start = Carbon::parse($timeCardExisted[$dayOfWeek->format('Ymd')]->work_start_time);
                        $workingTime = $timeCardExisted[$dayOfWeek->format('Ymd')]->working_time;
                        $workingTimeReal = 0;
                        if ($timeCardExisted[$dayOfWeek->format('Ymd')]->late_flg == 1) {
                            $timeCardDetail['vacation'] = '遅刻';
                        }
                        if (!empty($timeCardExisted[$dayOfWeek->format('Ymd')]->work_end_time)) {
                            $end = Carbon::parse($timeCardExisted[$dayOfWeek->format('Ymd')]->work_end_time);
                            $totalInMinute = $workingTime + $break;

                            $hTotal = (int)($totalInMinute/60);
                            $mTotal = $totalInMinute - $hTotal*60;
                            if ($mTotal < 10) {
                                $total = $hTotal .':0'. $mTotal;
                            } else {
                                $total = $hTotal .':'. $mTotal;
                            }

                            // 実働時間 $actual の 時間調整  (調整方法 0:調整なし 1:切上げ 2:切捨て)
                            // 0:調整なし
                            $hActual = (int)($workingTime/60);
                            $mActual = $workingTime - $hActual*60;
                            if ($selectAdjustType == AppUtils::WORK_TIME_ADJUST_ROUND_UP){
                                // 1:切上げ
                                // Log::debug("★selectAdjustUnit:=".$selectAdjustUnit . "★c->minute:=".$c->minute . "丸め:=" . $c->minute % $selectAdjustUnit);
                                if ($mActual > 0 && ($mActual % $selectAdjustUnit) > 0) {
                                    $mActual = $mActual + ($selectAdjustUnit - $mActual % $selectAdjustUnit);
                                }
                                if($mActual / 60 > 0){
                                    $rHour = $mActual / 60;
                                    $rMinute = $rHour - (int)$rHour;
                                    $hActual += (int)$rHour;
                                    $mActual = 60 * $rMinute;
                                }
                            } elseif ($selectAdjustType == AppUtils::WORK_TIME_ADJUST_TRUNCATE) {
                                // 2:切捨て
                                // Log::debug("★selectAdjustUnit:=".$selectAdjustUnit . "★c->minute:=".$c->minute . "丸め:=" . $c->minute % $selectAdjustUnit);
                                if ($mActual > 0) {
                                    $mActual = $mActual - ($mActual % $selectAdjustUnit);
                                }
                                if($mActual / 60 > 0){
                                    $rHour = $mActual / 60;
                                    $rMinute = $rHour - (int)$rHour;
                                    $hActual += (int)$rHour;
                                    $mActual = 60 * $rMinute;
                                }
                            }
                           
                            if ($mActual < 10) {
                                $actual = $hActual .':0'. $mActual;
                            } else {
                                $actual = $hActual .':'. $mActual;
                            }
                            $tArry=explode(":",$actual);        // 実働時間(時/分)を分割する
                            $hour=$tArry[0]*60;                 // 時を分に変換する
                            $workingTimeReal=$hour+$tArry[1];   // 分割した分を足す 
                            if ($timeCardExisted[$dayOfWeek->format('Ymd')]->earlyleave_flg == 1) {
                                $timeCardDetail['vacation'] = $timeCardDetail['vacation'].'早退';
                            }
                        } else {
                            $end = null;
                            $totalInMinute = 0;
                            $total = null;
                            $actual = null;
                        }
                        $attendanceDays ++;
                        $totalAttendanceTime = $totalAttendanceTime + $totalInMinute;
                        $totalWorkingTime = $totalWorkingTime + $workingTimeReal;
                        $timeCardDetail['break_time'] = Carbon::parse($break * 60)->format('H:i');
                    } else {
                        $start = null;
                        $end = null;
                        $total = null;
                        $actual = null;
                        if ($break) {
                            $timeCardDetail['break_time'] =  Carbon::parse($break  * 60)->format('H:i');
                        } else {
                            $timeCardDetail['break_time'] = '';
                        }
                    }

                    if ($paid_Flg == 2) {
                        $timeCardDetail['vacation'] = '有給半休';
                    } elseif ($sp_flg == 2) {
                        $timeCardDetail['vacation'] = '特休半休';
                    } elseif ($dayOff_flg == 2) {
                        $timeCardDetail['vacation'] = '代休半休';
                    }
                    if ($start) {
                        $timeCardDetail['work_start_time'] = $start->format('H:i');
                    } else {
                        $timeCardDetail['work_start_time'] = $start;
                    }
                    if ($end) {
                        $timeCardDetail['work_end_time'] = $end->format('H:i');
                    } else {
                        $timeCardDetail['work_end_time'] = $end;
                    }
                    $timeCardDetail['total'] = $total;
                    $timeCardDetail['actual'] = $actual;
                }

                $timeCardDetail['memo'] = $timeCardExisted[$dayOfWeek->format('Ymd')]->memo;
                $timeCardDetail['work_detail'] = $timeCardExisted[$dayOfWeek->format('Ymd')]->work_detail;
                $timeCardDetail['admin_memo'] = $timeCardExisted[$dayOfWeek->format('Ymd')]->admin_memo;
            } else {
                $timeCardDetail['work_start_time'] = '';
                $timeCardDetail['work_end_time'] = '';
                $timeCardDetail['break_time'] = '';
                $timeCardDetail['total'] = '';
                $timeCardDetail['actual'] = '';
                $timeCardDetail['vacation'] = '';
                $timeCardDetail['memo'] = '';
                $timeCardDetail['work_detail'] = '';
                $timeCardDetail['admin_memo'] = '';
            }
            $timeCardDetails[$i] = $timeCardDetail;
        }

        $data['timeCardDetails'] = $timeCardDetails;
        $data['attendanceDays'] = $attendanceDays;

        $hAttendance = (int)($totalAttendanceTime/60);
        $mAttendance = $totalAttendanceTime - $hAttendance*60;
        if ($mAttendance < 10) {
            $data['totalAttendanceTime'] = $hAttendance .':0'. $mAttendance;
        } else {
            $data['totalAttendanceTime'] = $hAttendance .':'. $mAttendance;
        }

        $hWorkingTime = (int)($totalWorkingTime/60);
        $mWorkingTime = $totalWorkingTime - $hWorkingTime*60;
        if ($mWorkingTime < 10) {
            $data['totalWorkingTime'] = $hWorkingTime .':0'. $mWorkingTime;
        } else {
            $data['totalWorkingTime'] = $hWorkingTime .':'. $mWorkingTime;
        }

        $datas['datas'][0] = $data;

        $pdf = PDF::loadView('pdf_template.work_list', $datas);
        $file_name = '勤務表.pdf';
        $pdf_data = base64_encode($pdf->output($file_name, "S"));

        $circularUser = null;
        $usingHash = null;
        $user_name = $user->getFullName();
        $isInCurrentEnv = true;
        $circular_id = null;

        $storeData = $this->storeCir($user, $usingHash, $user_name, $circularUser, $isInCurrentEnv, $pdf_data, $file_name, $circular_id);
        if (is_array($storeData)) {
            $newCircularId = $storeData['circular']->id;
            return $this->sendResponse($newCircularId,$data['month'].'月の勤務表を回覧します。');
        } else {
            return $this->sendError($storeData->original['message']);
        }
    }

    /**
     * Get a Circular in storage.
     * GET /circulars/{circular_id}
     *
     * @param Request $request
     *
     * @return Response
     */
    public function show($circular_id, Request $request)
    {
        try {
            // 回覧完了日時
            if (isset($request['finishedDate'])) {  // 完了一覧
                $finishedDateKey = $request->get('finishedDate');
                // 当月
                if (!$finishedDateKey) {
                    $finishedDate = '';
                } else {
                    $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
                }
            } else {    // 完了一覧以外
                $finishedDate = '';
            }

            return $this->getById($circular_id, $request->user()->email, config('app.edition_flg'), config('app.server_env'), config('app.server_flg'), $request->user()->mst_company_id, $finishedDate);

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('回覧取得処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Get a Circular in storage.
     * GET /circulars/{hash}
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getByHash(Request $request)
    {
        try {
            Log::info('current_circular: ' .$request['current_circular']);

            $edition_flg = -1;
            $env_flg = -1;
            $server_flg = -1;
            $mst_company_id = -1;

            // 回覧完了日時
            $finishedDateKey = $request->get('finishedDate');
            // 当月
            if (!$finishedDateKey) {
                $finishedDate = '';
            } else {
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            }

            if (isset($request['current_circular_user']) && !empty($request['current_circular_user'])) {
                $edition_flg = $request['current_circular_user']->edition_flg;
                $env_flg = $request['current_circular_user']->env_flg;
                $server_flg = $request['current_circular_user']->server_flg;
                $mst_company_id = $request['current_circular_user']->mst_company_id;
            }elseif (isset($request['current_viewing_user']) && !empty($request['current_viewing_user'])) {
                // 閲覧ユーザーの場合
                $circular_user = DB::table("circular_user$finishedDate")
                    ->where('circular_id', $request['current_circular'])
                    ->where('parent_send_order', $request['current_viewing_user']->parent_send_order)
                    ->first();

                if ($circular_user) {
                    $edition_flg = $circular_user->edition_flg;
                    $env_flg = $circular_user->env_flg;
                    $server_flg = $circular_user->server_flg;
                    $mst_company_id = $circular_user->mst_company_id;
                }
            }

            return $this->getById($request['current_circular'], $request['current_email'],$edition_flg,$env_flg,$server_flg,$mst_company_id,$finishedDate);
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('回覧取得処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * get user info by hash
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getUserByHash(Request $request)
    {
        $user = new \stdClass;
        $user->id = $request['user']->id;
        $user->email = $request['current_email'];
        $user->name = $request['current_name'];
        $user->is_external = $request['is_external'];
        // 完了一覧
        if (isset($request['finishedDate'])) {
            // 回覧完了日時
            $finishedDateKey = $request->get('finishedDate');
        } else {    // 完了一覧以外
            $finishedDateKey = '';
        }
        if ($request['current_circular_user']) {
            $user->mst_company_id = $request['current_circular_user']->mst_company_id;
        } else {
            $user->mst_company_id = $request['user']->mst_company_id;
        }
        $user->current_env_flg = $request['current_env_flg'];
        $user->current_edition_flg = $request['current_edition_flg'];
        $user->current_server_flg = $request['current_server_flg'];
        $user->storage = [];
        $get_user_info_success = true;

        if (!$request['is_external'] && $request['current_edition_flg'] == config('app.edition_flg') && $request['current_env_flg'] == config('app.server_env') &&
            $request['current_server_flg'] == config('app.server_flg')) {

            // PAC_5-1572 ▼
            $userInfo = DB::table('mst_user')->where('email', $user->email)
                ->where('state_flg', AppUtils::STATE_VALID)
                ->first();
            // 有効ユーザ判定追加
            if (!$userInfo) {
                return $this->sendError(__('message.false.invalidUser'), \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }
            // PAC_5-1572 ▲

            $sender = DB::table('circular')->join('mst_user', 'mst_user.id', '=', 'circular.mst_user_id')
                ->where('circular.id', $request['current_circular'])
                ->where('circular.edition_flg', config('app.edition_flg'))
                ->where('circular.env_flg', config('app.server_env'))
                ->where('circular.server_flg', config('app.server_flg'))
                ->select('mst_user.mst_company_id', 'circular.circular_status')->first();

            if ($sender) {
                $company_limit = DB::table('mst_limit')->where('mst_company_id', $user->mst_company_id)->first();
                if ($company_limit && $company_limit->link_auth_flg === 1) {
                    $loginUrl = rtrim(str_replace('/site/approval', '', config('app.circular_approval_url')), '/');
                    if ($sender->circular_status == CircularUtils::CIRCULAR_COMPLETED_STATUS || $sender->circular_status == CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS) {
                        $linkRouter = $loginUrl . '/completed/' . $request['current_circular'] . '?' . Hash::make($request['current_email']);
                    } else {
                        if ($request['current_circular_user']->circular_status == CircularUserUtils::REVIEWING_STATUS) {
                            $linkRouter = $loginUrl . '/received-reviewing/' . $request['current_circular'] . '?' . Hash::make($request['current_email']);
                        } elseif ($request['current_circular_user']->circular_status == CircularUserUtils::APPROVED_WITH_STAMP_STATUS || $request['current_circular_user']->circular_status == CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS || $request['current_circular_user']->circular_status == CircularUserUtils::NODE_COMPLETED_STATUS) {
                            $linkRouter = $loginUrl . '/received-view/' . $request['current_circular'] . '?' . Hash::make($request['current_email']);
                        } elseif ($request['current_circular_user']->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK) {
                            $linkRouter = $loginUrl . '/received-approval-sendback/' . $request['current_circular'] . '?' . Hash::make($request['current_email']);
                        } else {
                            $linkRouter = $loginUrl . '/received/' . $request['current_circular'] . '?' . Hash::make($request['current_email']);
                            // PAC_5-2567  通知メールから文書表示する際の認証機能がONの場合、引戻した回覧を承認者がメールから申請できる  START
                            $objCurrentCircular = DB::table('circular')->where('id',$request['current_circular'])->first();

                            $intCountNotiesNum = DB::table("circular_user")->where("circular_id",$objCurrentCircular->id)
                                ->whereIn("circular_status",[CircularUserUtils::READ_STATUS,CircularUserUtils::NOTIFIED_UNREAD_STATUS])
                                ->where("email",$request['current_email'])
                                ->count();
                            // 差戻　（差戻直後のみこの状態。差戻後に再度承認を行うと回覧中に戻る。）
                            if($objCurrentCircular && $objCurrentCircular->circular_status == CircularUtils::SEND_BACK_STATUS){
                                if(!$intCountNotiesNum){
                                    $linkRouter = $loginUrl.'/received';
                                }
                            }
                            // circular_user   引戻し
                            if($objCurrentCircular && $objCurrentCircular->circular_status == CircularUtils::CIRCULATING_STATUS){
                                $objCurrenPullBackUser = DB::table("circular_user")->where("circular_id",$objCurrentCircular->id)
                                    ->whereIn("circular_status",[CircularUserUtils::PULL_BACK_TO_USER_STATUS,CircularUserUtils::SUBMIT_REQUEST_SEND_BACK])
                                    ->first();
                                if($objCurrenPullBackUser  && !$intCountNotiesNum){
                                    $linkRouter = $loginUrl.'/received';
                                }
                            }
                            // circular 引戻（削除と同様。依頼者の引き戻し）
                            if($objCurrentCircular && $objCurrentCircular->circular_status == CircularUtils::RETRACTION_STATUS){
                                $objCreateUser = DB::table("circular_user")->where("circular_id",$objCurrentCircular->id)
                                    ->where("parent_send_order",0)
                                    ->where("child_send_order",0)
                                    ->first();
                                if($objCreateUser && $objCreateUser->email != $request['current_email']){
                                    $linkRouter = $loginUrl.'/received';
                                }
                            }
                            // PAC_5-2567 END
                        }
                    }
                    $company = DB::table('mst_company')->where('id', $sender->mst_company_id)->first();
                    if(empty($request['user']->remember_token)){
                        $linkRouter = CircularUserUtils::getEnvAppUrlByEnv(config('app.server_env'), config('app.server_flg'), CircularUserUtils::NEW_EDITION, $company) . '?redirectUrl=' . $linkRouter;
                    }
                    return response()->json([
                        'return_url' => $linkRouter,
                        'status' => StatusCodeUtils::HTTP_PARTIAL_CONTENT
                    ]);
                }
            }

            // PAC_5-1264 かたむけて捺印をユーザー単位で非表示にできるようにする　対応　▼
            $user_info = DB::table('mst_user_info')
                ->select('date_stamp_config', 'circular_info_first', 'last_stamp_id', 'default_rotate_angle', 'default_opacity', 'comment1', 'comment2', 'comment3', 'comment4', 'comment5',
                    'comment6', 'comment7', 'rotate_angle_flg', 'last_text_font', 'last_text_size', 'last_text_color', 'withdrawal_caution', 'sticky_note_flg')
                ->where('mst_user_id', $user->id)->first();
            // PAC_5-1264 かたむけて捺印をユーザー単位で非表示にできるようにする　対応　▲
            $mst_constrains = DB::table('mst_constraints')->select('max_document_size', 'max_attachment_size')->where('mst_company_id', $user->mst_company_id)->first();
            // PAC_5-1383 log追加
            try {
                $user->max_document_size = $mst_constrains->max_document_size;
                $user->date_stamp_config = $user_info->date_stamp_config;
                $user->circular_info_first = $user_info->circular_info_first;
                $user->last_stamp_id = $user_info->last_stamp_id;
                $user->default_rotate_angle = $user_info->default_rotate_angle;
                $user->default_opacity = $user_info->default_opacity;
                $user->last_text_font = $user_info->last_text_font; //テキストフォント
                $user->last_text_size = $user_info->last_text_size; //テキストフォントサイズ
                $user->last_text_color = $user_info->last_text_color; //テキストフォント色
                $user->sticky_note_flg = $user_info->sticky_note_flg;
                $user = CircularUserUtils::setComment($user, $user_info);
                //PAC_5-1322 受信のみ企業対応
                $mst_company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
                if ($mst_company) {
                    $user->received_only_flg = $mst_company->received_only_flg;
                    $user->repage_preview_flg = $mst_company->repage_preview_flg;
                    $user->long_term_storage_option_flg = $mst_company->long_term_storage_option_flg;
                } else {
                    $user->received_only_flg = 0;
                    $user->repage_preview_flg = 0;
                    $user->long_term_storage_option_flg = 0;
                }
                $user->rotate_angle_flg = $user_info->rotate_angle_flg;
                //PAC_5-1398 添付ファイル機能
                $user->max_attachment_size = $mst_constrains->max_attachment_size;
                //PAC_5-2386 受信専用利用者　
                $user->option_flg = $userInfo->option_flg;
                // PAC_5-1488 クラウドストレージを追加する
                $user->withdrawal_caution = $user_info->withdrawal_caution;
                /*PAC_5-2161 S*/
                $user_company_limit = DB::table('mst_limit')->where('mst_company_id', $user->mst_company_id)->select('storage_local', 'storage_box', 'storage_google', 'storage_dropbox', 'storage_onedrive', 'enable_any_address', 'require_approve_flag')->first();
                $user->storage = [
                    'storage_local' => $user_company_limit->storage_local,
                    'storage_box' => $user_company_limit->storage_box,
                    'storage_google' => $user_company_limit->storage_google,
                    'storage_dropbox' => $user_company_limit->storage_dropbox,
                    'storage_onedrive' => $user_company_limit->storage_onedrive,
                ];
                $user->enable_any_address = $user_company_limit->enable_any_address;
                $user->require_approve_flag = $user_company_limit->require_approve_flag;
            } catch (\Exception $e) {
                Log::debug('[getUserByHash]user_info:' . json_encode($user_info) . ';user_id:' . $user->id . ';current_circular:' . $request['current_circular'] . ';');
            }
        } else if (!$request['is_external'] && $request['current_edition_flg'] == config('app.edition_flg') && ($request['current_env_flg'] != config('app.server_env') || $request['current_server_flg'] != config('app.server_flg'))) {
            $envClient = EnvApiUtils::getAuthorizeClient($request['current_env_flg'], $request['current_server_flg']);
            if (!$envClient) throw new \Exception('Cannot connect to Env Api');

            $response = $envClient->get("getUserInfo/" . $user->email, []);
            if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                $envUserInfo = json_decode($response->getBody())->data;
                if ($envUserInfo) {
                    $user->max_document_size = $envUserInfo->max_document_size;
                    $user->date_stamp_config = $envUserInfo->date_stamp_config;
                    $user->circular_info_first = $envUserInfo->circular_info_first;
                    $user->last_stamp_id = $envUserInfo->last_stamp_id;
                    $user->default_rotate_angle = $envUserInfo->default_rotate_angle;
                    $user->default_opacity = $envUserInfo->default_opacity;
                    $user->sticky_note_flg = $envUserInfo->sticky_note_flg;

                    //PAC_5-1322 受信のみ企業対応
                    $user->received_only_flg = $envUserInfo->received_only_flg;
                    $user->long_term_storage_option_flg = $envUserInfo->long_term_storage_option_flg;
                    //PAC_5-711 おじぎ印マスタ制御対応
                    $user->rotate_angle_flg = $envUserInfo->rotate_angle_flg_user;
                    //PAC_5-759 改ページ調整機能対応
                    $user->repage_preview_flg = $envUserInfo->repage_preview_flg;
                    $user->id = $envUserInfo->mst_user_id;
                    //PAC_5-2386 受信専用利用者　
                    $user->option_flg = AppUtils::USER_NORMAL;
                    // 有効ユーザ判定追加
                    if ($envUserInfo->state_flg != 1) {
                        // 無効な場合、エラーメッセージ出力
                        return $this->sendError(__('message.false.invalidUser'), \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
                    }

                    $user->max_attachment_size = $envUserInfo->max_attachment_size;
                    $user->storage = [
                        'storage_local' => $envUserInfo->storage_local ?? 1,
                        'storage_box' => $envUserInfo->storage_box ?? 1,
                        'storage_google' => $envUserInfo->storage_google ?? 1,
                        'storage_dropbox' => $envUserInfo->storage_dropbox ?? 1,
                        'storage_onedrive' => $envUserInfo->storage_onedrive ?? 1,
                        'require_approve_flag' => $envUserInfo->require_approve_flag ?? 0,
                    ];
                    $user->enable_any_address = $envUserInfo->enable_any_address ?? 0;
                    $user->withdrawal_caution = $envUserInfo->withdrawal_caution ?? 0;
                    $user = CircularUserUtils::setComment($user, $envUserInfo);
                } else {
                    $get_user_info_success = false;
                    Log::warning('getUserByHash: Get Env UserInfo from other env return empty');
                }
            } else {
                $get_user_info_success = false;
                Log::warning('getUserByHash: Cannot get Env UserInfo from other env');
                Log::warning($response->getBody());
            }
        } else {
            $get_user_info_success = false;
        }
        if (!$get_user_info_success) {
            $user->max_document_size = 10;
            $user->date_stamp_config = 1;
            $user->circular_info_first = '回覧先';
            $user->sticky_note_flg = 0;
            $user->last_stamp_id = 0;
            $user->default_rotate_angle = 0;
            $user->default_opacity = 0;
            $user->received_only_flg = 0;
            $user->long_term_storage_option_flg = 0;
            $user->rotate_angle_flg = 1;
            $user->repage_preview_flg = 0;
            $user->max_attachment_size = 500;
            $user->option_flg = AppUtils::USER_NORMAL;
            $user->storage = [
                'storage_local' => 1,
                'storage_box' => 1,
                'storage_google' => 1,
                'storage_dropbox' => 1,
                'storage_onedrive' => 1,
                'require_approve_flag' => 0,
            ];
            $user->enable_any_address = 0;
            $user->withdrawal_caution = 0;
            $user = CircularUserUtils::setComment($user);
        }
        $user->finishedDate = $finishedDateKey;
        return response()->json([
            'token' => $request['hash'],
            'expires_in' => \Carbon\Carbon::now()->addDay(1)->toDateTimeString(),
            'user' => $user,
            'status' => StatusCodeUtils::HTTP_OK
        ]);
    }

	/**
	 * パラメーターから社外アクセスコード認証
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse|void
	 */
    public function checkOutsideAccessCodeByHash(Request $request)
    {
        try {
            $accessCodeArr = explode('=', CircularUtils::decryptOutsideAccessCode($request['accessCodeHash']));
            if (count($accessCodeArr) > 1) {
                // 回覧ユーザーの場合
                if (isset($request['current_circular_user'])) {
                    if ($request['current_circular_user']->id == $accessCodeArr[1]) {
                    return $this->sendResponse(['accessCodeAuth' => true], '社外アクセスコード認証処理に成功しました。');
                    } else {
                        $circular_user_ids = DB::table('circular_user')
                            ->where('edition_flg', $request['current_circular_user']->edition_flg)
                            ->where('env_flg', $request['current_circular_user']->env_flg)
                            ->where('server_flg', $request['current_circular_user']->server_flg)
                            ->where('email', $request['current_circular_user']->email)
                            ->pluck('id')
                            ->toArray();
                        if (in_array($accessCodeArr[1], $circular_user_ids)) {
                            return $this->sendResponse(['accessCodeAuth' => true], '社外アクセスコード認証処理に成功しました。');
                }
                    }
                }
                // 閲覧ユーザーの場合
                if (isset($request['current_viewing_user'])) {
                    $circular_users = DB::table('circular_user')
                        ->where('circular_id', $request['current_circular'])
                        ->where('parent_send_order', $request['current_viewing_user']->parent_send_order)
                        ->pluck('id')
                        ->toArray();
                    if (in_array($accessCodeArr[1], $circular_users)) {
                        return $this->sendResponse(['accessCodeAuth' => true], '社外アクセスコード認証処理に成功しました。');
                    }
                }
            }

            return $this->sendResponse(['accessCodeAuth' => false], '社外アクセスコード認証処理に失敗しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('社外アクセスコード認証処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id circular_userのcircular_id
     * @param $currentEmail circular_userの現在回覧者のメールアドレス
     * @param $edition_flg
     * @param $env_flg
     * @param $server_flg
     * @param $mst_company_id
     * @param $finishedDate
     * @return \Illuminate\Http\JsonResponse
     */
    public function getById($id, $currentEmail, $edition_flg, $env_flg, $server_flg, $mst_company_id, $finishedDate)
    {
        try {
            $circular = DB::table("circular$finishedDate")->where('id', $id)->first();
            //PAC_5-2526 企業間の回覧で同名ユーザーがいると文書が消えて承認できない
            $max_parent_send_order = DB::table("circular_document$finishedDate")
                ->where('circular_id', $id)
                ->orderByDesc('parent_send_order')
                ->first()->parent_send_order;

            $current_circular_user = DB::table("circular_user$finishedDate")->where('circular_id', $id)
                ->where('email', $currentEmail)
                ->where('circular_status', '<>', CircularUserUtils::NOT_NOTIFY_STATUS)
                ->where('edition_flg', $edition_flg)
                ->where('env_flg', $env_flg)
                ->where('server_flg', $server_flg)
                ->where('parent_send_order', '<=', $max_parent_send_order)
                ->orderByDesc('parent_send_order')
                ->orderByDesc('child_send_order')
                ->first();
            unset($circular->access_code);
            unset($circular->outside_access_code);

            // PAC_5-263 前の企業までの履歴しか表示できなかったです。
            $parent_send_order = $max_parent_send_order;
            if (!$current_circular_user) {
                // すべて回覧ユーザー取得
                $circular_users = DB::table("circular_user$finishedDate")
                    ->where('circular_id', $id)
                    ->where('parent_send_order', '>', 0)
                    ->get()->toArray();

                // 統合ID側からユーザー情報取得
                $client = IdAppApiUtils::getAuthorizeClient();
                if (!$client) {
                    return response()->json(['status' => false,
                        'message' => ['Cannot connect to ID App']
                    ]);
                }

                $result = $client->post("users/checkEmail", [
                    RequestOptions::JSON => ['email' => $currentEmail]
                ]);

                $resData = json_decode((string)$result->getBody());
                $id_app_users = $resData->data;
                // 統合ID返す結果と回覧ユーザー比較、現在の回覧者回覧位置確認
                foreach ($id_app_users as $user) {
                    foreach ($circular_users as $circular_user) {
                        // PAC_5-1939 新規作成時に宛先に社外企業を指定すると一部条件で文書が消える問題対応
                        if ($circular_user->parent_send_order <= $max_parent_send_order && $circular_user->email == $user->email && $circular_user->mst_company_id == $user->company_id && $circular_user->edition_flg == $user->edition_flg && $circular_user->env_flg == $user->env_flg && $circular_user->server_flg == $user->server_flg) {
                            $parent_send_order = $circular_user->parent_send_order;
                            break;
                        }
                    }
                }
            }

            if (!$circular || !$circular->id) {
                return $this->sendError('回覧が見つかりません。', \Illuminate\Http\Response::HTTP_NOT_FOUND);
            }

            $documents = DB::table("circular_document$finishedDate as D")
                ->select('D.id as circular_document_id', 'file_name', 'confidential_flg', 'create_company_id', 'create_user_id', 'file_data', 'origin_env_flg', 'origin_edition_flg', 'origin_server_flg', 'parent_send_order', 'create_company_id', 'D.create_at')
                ->leftJoin("document_data$finishedDate as DD", 'D.id', '=', 'DD.circular_document_id')
                ->where('circular_id', $id)
                ->where(function ($query) use ($edition_flg, $env_flg, $server_flg, $mst_company_id, $parent_send_order) {
                    $query->where(function ($query0) use ($parent_send_order) {
                        // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                        $query0->where('confidential_flg', 0);
                        $query0->where(function ($query01) use ($parent_send_order) {
                            // 回覧終了時：origin_document_id＝0のレコード
                            $query01->where('origin_document_id', 0);
                            // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                            $query01->orWhere('parent_send_order', $parent_send_order);
                        });
                    });
                    $query->orWhere(function ($query1) use ($edition_flg, $env_flg, $server_flg, $mst_company_id) {
                        // 社外秘：origin_document_idが-1固定
                        // 同社メンバー参照可
                        $query1->where('confidential_flg', 1);
                        $query1->where('origin_edition_flg', $edition_flg);
                        $query1->where('origin_env_flg', $env_flg);
                        $query1->where('origin_server_flg', $server_flg);
                        $query1->where('create_company_id', $mst_company_id);
                    });
                })
                ->orderBy('document_no')
                ->get();

            $documentIds = [];
            foreach ($documents as $key => $document) {
                if ($document->file_data) {
                    $document->file_data = AppUtils::decrypt($document->file_data);
                }
                $document->total_timestamp = 0;
                $documentIds[] = $document->circular_document_id;

                // 社内すべてparent_send_order取得
                $parent_send_orders = DB::table("circular_user$finishedDate")
                    ->where('circular_id', $circular->id)
                    ->where('mst_company_id', $mst_company_id)
                    ->where('edition_flg', $edition_flg)
                    ->where('env_flg', $env_flg)
                    ->where('server_flg', $server_flg)
                    ->pluck('parent_send_order');

                // コメント取得
                $comments = DB::table('document_comment_info')->select(['name', 'email', 'text', 'private_flg', 'create_at'])
                    ->where('circular_document_id', $document->circular_document_id)
                    ->where(function ($query) use ($parent_send_orders) {
                        $query->where('private_flg', CircularOperationHistoryUtils::DOCUMENT_COMMENT_PUBLIC)
                            ->Orwhere(function ($query) use ($parent_send_orders) {
                                $query->where('private_flg', CircularOperationHistoryUtils::DOCUMENT_COMMENT_PRIVATE)
                                    ->whereIn('parent_send_order', ($parent_send_orders->isNotEmpty()) ? $parent_send_orders : [0]);
                            });
                    })->get();
                $document->comments = $comments;
                // 付箋取得
                $sticky_notes = DB::table('sticky_notes')
                    ->select('id', 'note_format', 'note_text', 'page_num', 'top', 'left', 'removed_flg', 'deleted_flg', 'edition_flg',
                        'env_flg', 'server_flg', 'operator_email')
                    ->where('document_id', $document->circular_document_id)
                    ->orderBy('page_num')
                    ->orderBy('top')
                    ->orderBy('left')
                    ->get();
                foreach ($sticky_notes as $sticky_note){
                    $sticky_note->is_author = ($sticky_note->edition_flg == $edition_flg && $sticky_note->env_flg == $env_flg && $sticky_note->server_flg == $server_flg
                        && $sticky_note->operator_email == $currentEmail) ? 1 : 0;
                }
                $document->sticky_notes = $sticky_notes;
            }
            $brandings = [];

            $countTimestampInfos = DB::table('time_stamp_info')->select(['circular_document_id', DB::raw('Count(id) as total_timestamp')])
                ->whereIn('circular_document_id', $documentIds)
                ->groupBy('circular_document_id')
                ->get()
                ->keyBy('circular_document_id');
            foreach ($documents as $document) {
                if ($countTimestampInfos->has($document->circular_document_id)) {
                    $document->total_timestamp = $countTimestampInfos[$document->circular_document_id]->total_timestamp;
                }
            }
            /*PAC_5-2288 S*/
            $countStamps = DB::table('stamp_info')
                ->whereIn('circular_document_id', $documentIds)
                ->count();
            $countTexts = DB::table('text_info')
                ->where('circular_document_id', $documentIds)
                ->count();
            /*PAC_5-2288 E*/
            $currentViewingUser = request('current_viewing_user', null);
            $users = DB::table("circular_user$finishedDate as C")
                ->leftJoin('circular_user_routes as R', function ($query) {
                    $query->on('C.circular_id', '=', 'R.circular_id');
                    $query->on('C.child_send_order', '=', 'R.child_send_order');
                })
                ->where('C.circular_id', $id)
                ->orderBy('parent_send_order')
                ->orderBy('child_send_order')
                ->selectRaw('C.*, R.id as user_routes_id, R.detail, R.mode, R.wait, R.score, R.template_id')
                ->get();
            $current_edition = null; //現在の回覧者現新フラグ
            $current_env = null; //現在の回覧者環境フラグ
            $current_server = null; //現在の回覧者サーバーID
            $current_company = null; //現在の回覧者企業ID
            if (isset($current_circular_user) && isset($users)) {
                $comments = DB::table('mail_text')
                    ->select('mail_text.*', 'circular_user.name')
                    ->join('circular_user', 'circular_user.id', '=', 'mail_text.circular_user_id')
                    ->whereIn('mail_text.circular_user_id', $users->pluck('id')->all())
                    ->orderBy('mail_text.id')
                    ->get();
                $current_edition = $current_circular_user->edition_flg; //現在の回覧者現新フラグ
                $current_env = $current_circular_user->env_flg; //現在の回覧者環境フラグ
                $current_server = $current_circular_user->server_flg; //現在の回覧者サーバーID
                $current_company = $current_circular_user->mst_company_id; //現在の回覧者企業ID
            } else {
                if ($currentViewingUser) {
                    $comments = DB::table('mail_text')
                        ->select('mail_text.*', 'circular_user.name')
                        ->join('circular_user', 'circular_user.id', '=', 'mail_text.circular_user_id')
                        ->join('viewing_user', 'viewing_user.circular_id', '=', 'circular_user.circular_id')
                        ->where('viewing_user.id', $currentViewingUser->id)
                        ->orderBy('mail_text.id')
                        ->get();
                } else {
                    $comments = [];
                }
            }
            /*PAC_5-2288 S*/
            $thisCompanies=[];
            $otherCompanies=[];
            $system_edition_flg = config('app.edition_flg');
            $system_env_flg = config('app.server_env');
            $system_server_flg = config('app.server_flg');
            foreach ($users as $user){
                if (is_null($user->mst_company_id)){
                    continue;
                }
                if ($system_edition_flg!=$user->edition_flg || $system_env_flg!=$user->env_flg || $system_server_flg!=$user->server_flg){
                    $otherCompanies[$user->mst_company_id.'-'.$user->env_flg.'-'.$user->server_flg.'-'.$user->edition_flg]=[
                        'company_id'=>$user->mst_company_id,
                        'env_flg'=>$user->env_flg,
                        'server_flg'=>$user->server_flg,
                        'edition_flg'=>$user->edition_flg
                    ];
                }else{
                    $thisCompanies[$user->mst_company_id.$user->env_flg.$user->server_flg.$user->edition_flg]=[
                        'company_id'=>$user->mst_company_id,
                        'env_flg'=>$user->env_flg,
                        'server_flg'=>$user->server_flg,
                        'edition_flg'=>$user->edition_flg
                    ];
                }
            }
            /*PAC_5-2404 S*/
            $otherCompaniesGroup = [];
            foreach ($otherCompanies as $k => $company) {
                $env_arr = explode('-', $k);
                array_shift($env_arr);
                $env_str = implode('-', $env_arr);
                $otherCompaniesGroup[$env_str][] = $company;
            }
            /*PAC_5-2404 E*/
            Log::info("GET thisCompanies",$thisCompanies);
            $esigned_flg = 0;
            $is_other_env = 0;
            $thisCompanies_esigned_flg = DB::table('mst_company')
            ->whereIn('id',array_column($thisCompanies,'company_id'))
            ->where('esigned_flg',1)
            ->count();
            if ($thisCompanies_esigned_flg>0){
                $esigned_flg = 1;
            }else{
                foreach ($otherCompaniesGroup as $key => $companies){
                    $otherCompanyIds = implode(',', array_column($companies, 'company_id'));
                    $env_info= explode('-',$key);
                    $env_client = EnvApiUtils::getAuthorizeClient($env_info[0], $env_info[1]);
                    if (!$env_client) throw new \Exception('Cannot connect to Env Api');
                    $response = $env_client->get("getCompanies?ids=" . $otherCompanyIds);
                    if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                        $res = json_decode($response->getBody())->data;
                        $other_esigned_flg = collect($res)->some(function ($item) {
                            return $item->esigned_flg == 1;
                        });
                        if ($other_esigned_flg){
                            $esigned_flg = 1;
                            break;
                        }
                    }
                    /*PAC_5-2404 E*/
                }
            }
            /*PAC_5-2288 E*/
            $checkEnableAdd = true;
            $limit_text_append_flg = 0;
            $userSendMail_id = 0;
            $checkNoExistCircularUser = false;
            $applicant_edition = null; //申請者現新フラグ
            $applicant_env = null; //申請者環境フラグ
            $applicant_server = null; //申請者サーバーID
            $applicant_company = null; //申請者企業ID
            if ($users) {
                $company_id = null;
                foreach ($users as $user) {
                    if ($user->parent_send_order == 0 && $user->child_send_order == 0) {
                        $company_id = $user->mst_company_id;
                        $userSendMail_id = $user->mst_user_id;
                        $applicant_edition = $user->edition_flg;
                        $applicant_env = $user->env_flg;
                        $applicant_server = $user->server_flg;
                        $applicant_company = $user->mst_company_id;
                        break;
                    }
                }
                if ($company_id) {
                    $limit = DB::table('mst_limit')
                        ->where('mst_company_id', $company_id)
                        ->first();
                    if ($limit && $limit->receiver_permission == 0) {
                        $checkEnableAdd = false;
                    } else {
                        $checkEnableAdd = true;
                    }
                    if($limit && $limit->text_append_flg == 1){
                        $limit_text_append_flg=1;
                    }else{
                        $limit_text_append_flg=0;
                    }
                }
            }
            if (count($users) == 0) {
                $checkNoExistCircularUser = true;
            }

            //特設サイトの場合
            $special_site_group_name = '';
            if ($circular->special_site_flg) {
                $special_circular_user = DB::table('circular_user')
                    ->where('circular_id', $id)
                    ->where('special_site_receive_flg', 1)
                    ->first();
                if ($special_circular_user) {
                    $receiver_company_id = $special_circular_user->mst_company_id;
                    $receiver_edition_flg = $special_circular_user->edition_flg;
                    $receiver_env_flg = $special_circular_user->env_flg;
                    $receiver_server_flg = $special_circular_user->server_flg;
                } else {
                $special_site_circular = DB::table('special_site_circular')->where('circular_id',$id)->first();
                if ($special_site_circular) {
                        $receiver_company_id = $special_site_circular->receive_mst_company_id;
                        $receiver_edition_flg = $special_site_circular->receive_edition_flg;
                        $receiver_env_flg = $special_site_circular->receive_env_flg;
                        $receiver_server_flg = $special_site_circular->receive_server_flg;
                    } else {
                        Log::error('特設サイト回覧ID取得失敗しました。');
                        return $this->sendError('受取側情報取得失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }

                    // SRS-017 受取連携宛先情報取得
                    $client = SpecialApiUtils::getAuthorizeClient();
                    if (!$client) {
                        Log::error(__('message.false.auth_client'));
                    }
                    $response = $client->post("/sp/api/get-receive-address", [
                        RequestOptions::JSON => [
                        'company_id' => $receiver_company_id,
                        "env_flg" => $receiver_env_flg,
                        "edition_flg" => $receiver_edition_flg,
                        "server_flg" => $receiver_server_flg,
                        ]
                    ]);
                    $response_dencode = json_decode($response->getBody(), true);  //配列へ
                    if ($response->getStatusCode() == 200) {
                        $response_body = json_decode($response->getBody(), true);  //配列へ
                        if ($response_body['status'] == "success") {
                            $special_site_group_name = $response_body['result'];
                        } else {
                            Log::error('特設サイトget-receive-address呼出失敗しました。');
                            Log::error($response_dencode);
                        }
                    } else {
                        Log::error('特設サイトget-receive-address呼出失敗しました。');
                        Log::error($response_dencode);
                    }
                }
            //PAC_5-2467大文字から小文字に転換
            foreach ($users as $user){
                $user->email = mb_strtolower($user->email);
            }

            $identity = 1; // 現在の回覧者 0:社内 1:社外
            $circular->checkNoExistCircularUser = $checkNoExistCircularUser;
            $circular->checkEnableAdd = $checkEnableAdd;
            $circular->userSendMail_id = $userSendMail_id;
            $circular->users = $users;
            $circular->comments = $comments;
            $circular->limit_text_append_flg = $limit_text_append_flg;
            $circular->limit_require_print = $circular->require_print;
            $circular->special_site_group_name = $special_site_group_name;
            // 状態確認
            if ($applicant_edition == $current_edition && $applicant_env == $current_env && $applicant_server == $current_server && $applicant_company == $current_company) {
                $identity = 0;
            }
            /*PAC_5-2307 S*/
            if ($currentViewingUser && $applicant_edition == config('app.edition_flg') && $applicant_env == config('app.server_env') && $applicant_server == config('app.server_flg') && $applicant_company == $currentViewingUser->mst_company_id  ){
                $identity = 0;
            }
            /* PAC_5-2307 E*/
            $circular->current_user_identity = $identity;
            /*PAC_5-2288 S*/
            $circular->countStamps=$countStamps;
            $circular->countTexts=$countTexts;
            /*PAC_5-2288 E*/
            /*PAC_5-2288 S*/
            $circular->esigned_flg=$esigned_flg;
            /*PAC_5-2288 E*/
            // PAC_5-1698 get circular user plan list
            $plans=DB::table('circular_user_plan')
                ->where('circular_id','=',$id)
                ->select('circular_id','child_send_order','mode','score','id')
                ->get()
                ->keyBy('id');
            $circular->plans=$plans;

            return $this->sendResponse(['circular' => $circular, 'documents' => $documents, 'current_viewing_user' => $currentViewingUser,
                'company_logos' => $brandings, 'title' => $current_circular_user ? $current_circular_user->title : ''], '回覧取得処理に成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('回覧取得処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update a Circular in storage.
     * PUT /circulars/{circular_id}
     *
     * @param UpdateCircularAPIRequest $request
     *
     * @return Response
     */
    public function update($circular_id,UpdateCircularAPIRequest $request)
    {
        try {
            $user = $request->user();
            $pdf_data = $request['pdf_data'];
            DB::beginTransaction();

            DB::table('circular')->where('id', $circular_id)->update([
                'update_at' => Carbon::now(),
                'update_user' => $user->email,
                'final_updated_date' => Carbon::now(),
            ]);

            DB::table('circular_document')->where('circular_id', $circular_id)->update([
                'update_at' => Carbon::now(),
                'update_user' => $user->email,
            ]);

            $circular_document = DB::table('circular_document')->where('circular_id', $circular_id)->first();

            DB::table('document_data')->where('circular_document_id', $circular_document->id)->update([
                'file_data'=> AppUtils::encrypt($pdf_data),
                'update_at' => Carbon::now(),
                'update_user' => $user->email,
            ]);

            DB::commit();

            return $this->sendResponse(['circular_id'=>$circular_id],'回覧更新処理に成功しました。');

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('回覧更新処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Update a Circular Status in storage.
     * PATHC /circulars/{circular_id}/updateStatus
     *
     * @param UpdateCircularStatusAPIRequest $request
     *
     * @return Response
     */
    public function updateStatus($circular_id,UpdateCircularStatusAPIRequest $request)
    {
        try {
            $user = $request->user();

            if(!$user || !$user->id) {
                $user = $request['user'];
                $email = $request['current_email'];
            }else{
                $email = $user->email;
            }

            // 完了一覧
            if (isset($request['finishedDate']) && $request['finishedDate']) {  // 回覧完了日時、当月以外
                $finishedDateKey = $request['finishedDate'];
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            } else {    // 完了一覧以外
                $finishedDate = '';
            }

            DB::table("circular$finishedDate")->where('id', $circular_id)->update([
                'circular_status' => $request['status'],
                'update_at' => Carbon::now(),
                'update_user' => $email,
                'final_updated_date' => Carbon::now(),
            ]);
            // PAC_5-1092 BEGIN
            // ダウンロードのステータス変更処理
            $circulars = DB::table("circular$finishedDate as C")
                ->join("circular_user$finishedDate as U", 'C.id', '=', 'U.circular_id')
                ->select('C.id', 'U.edition_flg', 'U.env_flg', 'U.server_flg', 'C.origin_circular_id', 'C.edition_flg as origin_edition_flg', 'C.env_flg as origin_env_flg', 'C.server_flg as origin_server_flg','C.completed_date')
                ->where('C.id', $circular_id)
                ->groupBy('C.id', 'U.edition_flg', 'U.env_flg', 'U.server_flg', 'C.origin_circular_id')
                ->get();
            foreach ($circulars as $circular) {
                // クロス環境ファイルかどうかを判別します
                if ($circular->edition_flg == config('app.edition_flg') && ($circular->env_flg != config('app.server_env') || $circular->server_flg != config('app.server_flg'))) {
                    // クロス環境
                    $envClient = EnvApiUtils::getAuthorizeClient($circular->env_flg, $circular->server_flg);
                    if (!$envClient) {
                        //TODO message
                        throw new \Exception('Cannot connect to Env Api');
                    }
                    // この環境ファイルかどうかを判断します
                    if ($circular->origin_circular_id) {
                        $circular_status = [
                            RequestOptions::JSON => ['origin_circular_id' => $circular->origin_circular_id, 'status' => CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS, 'origin_env_flg' => $circular->origin_env_flg,
                                'origin_edition_flg' => $circular->origin_edition_flg, 'origin_server_flg' => $circular->origin_server_flg, 'user' => $user, 'finishedDate' => $finishedDate,'completed_date' => $circular->completed_date]
                        ];
                    } else {
                        $circular_status = [
                            RequestOptions::JSON => ['origin_circular_id' => $circular->id, 'status' => $request['status'], 'origin_env_flg' => $circular->origin_env_flg,
                                'origin_edition_flg' => $circular->origin_edition_flg, 'origin_server_flg' => $circular->origin_server_flg, 'user' => $user, "finishedDate" => $finishedDate,'completed_date' => $circular->completed_date]
                        ];
                    }
                    // ファイルステータスの変更
                    $response = $envClient->post("updateEnvStatus", $circular_status);
                    if (!$response) {
                        return $this->sendError('回覧更新処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
            }
            // PAC_5-1092 END
            return $this->sendResponse(['circular_id'=>$circular_id],'回覧更新処理に成功しました。');

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('回覧更新処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * PAC_5-1092
     * ダウンロードのステータス変更処理
     * クロス環境circular状態の変更
     * @param Request $request
     */
    public function updateEnvStatus(Request $request)
    {
        $origin_circular_id = $request->input('origin_circular_id');
        $status = $request->input('status');
        $origin_env_flg = $request->input('origin_env_flg');
        $origin_edition_flg = $request->input('origin_edition_flg');
        $origin_server_flg = $request->input('origin_server_flg');
        $completed_date = $request->input('completed_date');
        $user = $request->input('user');
        // 完了一覧
        if (isset($request['finishedDate']) && $request['finishedDate']) {  // 回覧完了日時  当月:0 1ヶ月前:1
            $finishedDateKey = $request['finishedDate'];
            $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
        } else {    // 完了一覧以外
            $finishedDate = '';
        }
        try {
            // 差出環境時
            if ($origin_edition_flg == config('app.edition_flg') && $origin_env_flg == config('app.server_env') && $origin_server_flg == config('app.server_flg')) {
                DB::table("circular$finishedDate")
                    ->where('id', $origin_circular_id)
                    ->where('env_flg', $origin_env_flg)
                    ->where('edition_flg', $origin_edition_flg)
                    ->where('server_flg', $origin_server_flg)
                    ->update([
                        'circular_status' => $status,
                        'update_at' => Carbon::now(),
                        'update_user' => $user['email'],
                        'final_updated_date' => Carbon::now(),
                        'completed_date' => (isset($completed_date) && $completed_date) ? $completed_date : null,
                    ]);
            } else {
                // クロス環境ファイル
                DB::table("circular$finishedDate")
                    ->where('origin_circular_id', $origin_circular_id)
                    ->where('env_flg', $origin_env_flg)
                    ->where('edition_flg', $origin_edition_flg)
                    ->where('server_flg', $origin_server_flg)
                    ->update([
                        'circular_status' => $status,
                        'update_at' => Carbon::now(),
                        'update_user' => $user['email'],
                        'final_updated_date' => Carbon::now(),
                        'completed_date' => (isset($completed_date) && $completed_date) ? $completed_date : null,
                    ]);
            }
            return $this->sendResponse(true, '回覧更新処理に成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('回覧更新処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function actionMultiple($action, Request $request){
        if(\method_exists($this, $action))
            return $this->$action($request);
        else return $this->sendError("Circulars not found action: " . $action);
    }

    /**
     * download for document
     */
    public function downloadDocument(Request $request){
        $circularId   = $request['circular_id'];
        $email   = $request['email'];
        $env_flg   = $request['env_flg'];
        $edition_flg   = $request['edition_flg'];
        $server_flg   = $request['server_flg'];
        if (!$circularId || !$email){
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => 'Invalid parameter'];
        }
        try{
            $circular = DB::table('circular')
                ->select('id')
                ->where('origin_circular_id', $circularId)
                ->where('env_flg', $env_flg)
                ->where('edition_flg', $edition_flg)
                ->where('server_flg', $server_flg)
                ->where('circular_status', '!=', CircularUtils::DELETE_STATUS)
                ->first();
            if (!$circular){
                return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => 'Circular is not exist'];
            }
            $circularId = $circular->id;
            $circularUser =  DB::table('circular_user')
                ->where('email', $email)
                ->where('circular_id', $circularId)
                ->first();

            if (!$circularUser){
                return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => 'Circular user is not exist'];
            }

            $circular_docs  =   DB::table('circular_document')
                ->where('circular_id', $circularId)
                ->where(function($query) use ($circularUser){
                    $query->where(function($query1) use ($circularUser){
                        $query1->where('origin_document_id', 0);
                        $query1->where('confidential_flg', 0);
                    });
                    $query->orWhere(function($query1) use ($circularUser){
                        $query1->where('parent_send_order', $circularUser->parent_send_order);
                    });
                })
                ->select('id','circular_id','file_name')
                ->get()->keyBy('id');

            $document_datas = DB::table('document_data')
                ->whereIn('circular_document_id', $circular_docs->keys())
                ->select('circular_document_id','file_data')
                ->get();
            if(count($document_datas)){
                $fileName = "download-circular-" . time() . ".zip";
                $path = sys_get_temp_dir()."/download-circular-" . AppUtils::getUniqueName($circularUser->edition_flg, $circularUser->env_flg, $circularUser->server_flg, $circularUser->mst_company_id, $circularUser->id) . ".zip";

                $zip = new \ZipArchive();
                $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

                $zip->addEmptyDir($circularId);
                $countFilename = [];

                foreach($document_datas as $document_data){
                    $document_id = $document_data->circular_document_id;
                    if(!isset($circular_docs[$document_id])) continue;

                    $circular_document = $circular_docs[$document_id];

                    $filename = mb_substr($circular_document->file_name, mb_strrpos($circular_document->file_name,'/'));
                    $filename = mb_substr($filename, 0, mb_strrpos($circular_document->file_name,'.'));
                    $filename = $circularId.'/'.$filename;
                    if(key_exists($filename, $countFilename)) {
                        $countFilename[$filename]++;
                        $filename = $filename.' ('.$countFilename[$filename].') ';
                    } else {
                        $countFilename[$filename] = 0;
                    }
                    $zip->addFromString ($filename.'.pdf', base64_decode( AppUtils::decrypt($document_data->file_data)));
                }

                $zip->close();
                return ['status' => \Illuminate\Http\Response::HTTP_OK, 'message' => "文書ダウンロード処理に成功しました。", 'zip_document' => \base64_encode(\file_get_contents($path))];
            }else{
                return ['status' => \Illuminate\Http\Response::HTTP_NO_CONTENT, 'message' => "送信文書のダウンロード処理に失敗しました。"];
            }

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => $ex->getMessage()];
        }
    }

    /**
     * PDFダウンロード処理
     * download for list sent/ completed
     */
    public function downloadFile(Request $request){
        $user   = $request->user();
        $cids   = $request->get('cids',[]);
        if(count($cids)){
            try {
                //PAC_5-1026
                //複数のファイル名のクエリ
                $query_sub = DB::table('circular as C')
                    ->join('circular_document as D', 'C.id', '=', 'D.circular_id')
                    ->whereIn('C.id', $cids)
                    ->select(DB::raw('C.id, GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \') as file_names'))
                    ->groupBy('C.id');
                // 閲覧者取得用のクエリ
                $viewer_circulars = DB::table('circular')
                    ->select(DB::raw('circular.*, viewing_user.parent_send_order as parent_send_order, D.file_names, \'\' as title'))
                    ->leftJoinSub($query_sub, 'D', function ($join) {
                        $join->on('circular.id', '=', 'D.id');
                    })
                    ->join('viewing_user', 'circular.id', '=', 'viewing_user.circular_id')
                    ->where('viewing_user.mst_user_id', $user->id)
                    ->whereIn('circular.id', $cids)
                    ->where('circular.circular_status', '!=', CircularUtils::DELETE_STATUS);

                // 回覧取得
                $circulars = DB::table('circular')
                    ->select(['circular.*', 'circular_user.parent_send_order as parent_send_order', 'D.file_names', 'circular_user.title as title'])
                    ->leftJoinSub($query_sub, 'D', function ($join) {
                        $join->on('circular.id', '=', 'D.id');
                    })
                    ->join('circular_user', 'circular.id', '=', 'circular_user.circular_id')
                    ->where('circular_user.email', $user->email)
//                    ->where('circular_user.env_flg', config('app.server_env'))
                    ->where('circular_user.edition_flg', config('app.edition_flg'))
                    ->whereIn('circular.id', $cids)
                    ->where('circular.circular_status', '!=', CircularUtils::DELETE_STATUS)
                    ->union($viewer_circulars)
                    ->get()->keyBy('id');

                // 他環境env
                $other_env = null;
                // 他環境回覧ID集合
                $origin_env_circulars = [];
                $current_circulars_exits = false;

                foreach($circulars as $key => $circular) {
                    // 他環境存在の場合
                    if($circular->edition_flg == config('app.edition_flg') && ($circular->env_flg != config('app.server_env') || $circular->server_flg != config('app.server_flg'))) {
                        $origin_env_circulars[$circular->env_flg.$circular->server_flg][] =['circular_id' => $circular->id, 'origin_circular_id' => $circular->origin_circular_id];
//                        $other_env = $circular->env_flg;
//                        unset($circulars[$key]);
                    }
                    //todo 現行側
                    if($circular->edition_flg == 0){
                        $current_circulars_exits = true;
                    }
                }
                // 他環境ファイル集合
                $env_document_data = [];
                if(!empty($origin_env_circulars)) {

                    foreach ($origin_env_circulars as $key => $origin_env_circular){
                        $env = substr($key,0,1);
                        $server = substr($key,1,strlen($key)-1);
                        $envClient = EnvApiUtils::getAuthorizeClient($env,$server);

                        if (!$envClient){
                            //TODO message
                            throw new \Exception('Cannot connect to Env Api');
                        }

                        // 他環境処理を呼び出し
                        $response = $envClient->post("getEnvDocuments",[
                            RequestOptions::JSON => ['create_company_id' => $user->mst_company_id, 'origin_env_flg' => config('app.server_env'), 'origin_server_flg' => config('app.server_flg'),
                                'origin_edition_flg' => config('app.edition_flg'), 'circulars' => $origin_env_circular]
                        ]);

                        if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                            Log::error($response->getBody());
                            throw new \Exception('Cannot get env documents');
                        }
                        $result = json_decode($response->getBody(), true);

                        $env_document_data = collect($result['document_data']);
                    }
                }

                //  現在環境にファイルを取得
                $cids           =   $circulars->keys();
                $circular_docs  =   DB::table('circular_document')
                        ->whereIn('circular_id', $cids)
                        ->where(function($query) use ($user){
                            $query->where(function($query1){
                                $query1->where('origin_document_id', 0);
                                $query1->where('confidential_flg', 0);
                            });
                            $query->orWhere(function($query1) use ($user){
                                $query1->where('create_company_id', $user->mst_company_id)
                                    ->where('origin_env_flg', config('app.server_env'))
                                    ->where('origin_edition_flg', config('app.edition_flg'))
                                    ->where('origin_server_flg', config('app.server_flg'));
                            });
                        })
                        ->select('id','circular_id','file_name')
                        ->get()->keyBy('id');

                $document_datas = DB::table('document_data')
                        ->whereIn('circular_document_id', $circular_docs->keys())
                        ->select('circular_document_id','file_data')
                        ->get();

                if(count($document_datas) == 1 && count($env_document_data) == 0){       // 現在の環境に一つがあるの場合
                    $fileName = $circular_docs[$document_datas[0]->circular_document_id]->file_name;
                    return $this->sendResponse([ 'numfile'=> 1, 'fileName' => $fileName,
                                        'file_data' => AppUtils::decrypt( $document_datas[0]->file_data) ]
                                    ,'文書ダウンロード処理に成功しました。');
                }elseif(count($env_document_data) == 1 && count($document_datas) == 0){  // 他の環境に一つがあるの場合
                    $fileName = $env_document_data[0]['file_name'];
                    return $this->sendResponse([ 'numfile'=> 1, 'fileName' => $fileName,
                            'file_data' => AppUtils::decrypt( $env_document_data[0]['file_data']) ]
                        ,'文書ダウンロード処理に成功しました。');
                }elseif(count($document_datas) > 0 || count($env_document_data) > 0){    // 複数の場合
                    // 現在の環境に複数の場合
                    if (count($document_datas) > 0) {
                        foreach($document_datas as $document_data){
                            $document_id = $document_data->circular_document_id;
                            if(!isset($circular_docs[$document_id])) continue;
                            $circular_document = $circular_docs[$document_id];

                            $circular_id = $circular_document->circular_id;
                            if(!isset($circulars[$circular_id]->docs)) $circulars[$circular_id]->docs = [];

                            $circulars[$circular_id]->docs[] = ['fileName' => $circular_document->file_name,
                                'data' => AppUtils::decrypt($document_data->file_data)];
                        }
                    }

                    // ファイル名を設定
                    $fileName = "download-circular-" . time() . ".zip";
                    // ファイルパスをを設定
                    $path = sys_get_temp_dir()."/download-circular-" . AppUtils::getUniqueName(config('app.edition_flg'), config('app.server_env'), config('app.server_flg'), $user->mst_company_id, $user->id) . ".zip";

                    $zip = new \ZipArchive();
                    $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

                    //ファイル名
                    $fileList = array();
                    foreach ($circulars as $circular) {
                        if (isset($circular->docs)) {

                            // PAC_5-1026 BEGIN
                            // 完了一覧から一括ダウンロードした際のファイル名
                            //文書が格納されるフォルダ名は「文書名_申請日時_完了日時」
                            //申請日は数字のみで西暦から秒まで。　コンマ秒はなし。
                            //yyyymmddhhmmss
                            $apply_time = date("YmdHis", strtotime($circular->applied_date));
                            //名前はありますか
                            if (!trim($circular->title)) {
                                //ファイル名の生成
                                $fileNameList = explode(',', $circular->file_names);
                                $subjectName = $fileNameList[0] . '_' . $apply_time;
                            } else {
                                //ファイル名の生成
                                $subjectName = $circular->title . '_' . $apply_time;
                            }
                            //ファイル名が繰り返されていますか , 繰り返されるときにサフィックスを追加する
                            if (array_key_exists($subjectName, $fileList)) {
                                $suffix = $fileList[$subjectName] + 1;
                                $fileList[$subjectName] = $suffix;
                                $subjectName = $subjectName . '_' . $suffix;
                            } else {
                                $fileList[$subjectName] = 1;
                            }
                            //フォルダーを作る
                            $zip->addEmptyDir($subjectName);
                            //PAC_5-1026 END

                            // 重複するファイル名の集合
                            $countFilename = [];
                            foreach($circular->docs as $index => $doc){
                                // ファイル名を取得
                                $filename = mb_substr($doc['fileName'], mb_strrpos($doc['fileName'],'/'));
                                $filename = mb_substr($filename, 0, mb_strrpos($doc['fileName'],'.'));
                                // ファイル名が存在の場合
                                if(key_exists($filename, $countFilename)) {
                                    $countFilename[$filename]++;
                                    $filename = $filename.' ('.$countFilename[$filename].') ';
                                } else {
                                    $countFilename[$filename] = 0;
                                }
                                // zipにPDFファイルを追加
                                $zip->addFromString ($subjectName.'/'.$filename.'.pdf', base64_decode($doc['data']));
                            }
                        }
                    }

                    // 他の環境に複数の場合、zipにファイルを追加
                    if(count($env_document_data) > 0){
                        // 重複するファイル名の集合
                        $countFilename = [];
                        // フォルダ集合
                        $circular_id_dir = [];
                        // 重複するフォルダ
                        $fileList = [];
                        // 同じアプリケーションのドキュメント
                        $circular_id = [];
                        foreach ($env_document_data as $document_data) {
                            // フォルダ不存在の場合
                            if (!key_exists($document_data['circular_id'], $circular_id_dir)) {

                                // PAC_5-1026 BEGIN
                                // 完了一覧から一括ダウンロードした際のファイル名
                                //文書が格納されるフォルダ名は「文書名_申請日時_完了日時」
                                //申請日は数字のみで西暦から秒まで。　コンマ秒はなし。
                                //yyyymmddhhmmss
                                foreach ($circulars as $circular) {
                                    if ($document_data['origin_circular_id'] == $circular->origin_circular_id) {
                                        $apply_time = date("YmdHis", strtotime($circular->applied_date));
                                        // 名前はありますか
                                        if (!trim($circular->title)) {
                                            // フォルダ名の生成
                                            $fileNameList = explode(',', $circular->file_names);
                                            $subjectName = $fileNameList[0] . '_' . $apply_time;
                                        } else {
                                            // フォルダ名の生成
                                            $subjectName = $circular->title . '_' . $apply_time;
                                        }
                                        break;
                                    }
                                }
                                // 同じアプリケーションのドキュメント
                                if (array_key_exists($document_data['circular_id'], $circular_id)) {
                                    $subjectName = $circular_id[$document_data['circular_id']];
                                } else {
                                    $circular_id[$document_data['circular_id']] = $subjectName;
                                    // フォルダ名が繰り返されていますか , 繰り返されるときにサフィックスを追加する
                                    if (array_key_exists($subjectName, $fileList)) {
                                        $suffix = $fileList[$subjectName] + 1;
                                        $fileList[$subjectName] = $suffix;
                                        $subjectName = $subjectName . '_' . $suffix;
                                    } else {
                                        $fileList[$subjectName] = 1;
                                    }
                                }
                                // フォルダの生成
                                $zip->addEmptyDir($subjectName);
                            }
                            //PAC_5-1026 END

                            // ファイル名を取得
                            $file_data = ['fileName' => $document_data['file_name'],
                                'data' => AppUtils::decrypt($document_data['file_data'])];
                            $filename = pathinfo($file_data['fileName'])['filename'];
                            // ファイル名が存在の場合
                            if(key_exists($filename, $countFilename)) {
                                $countFilename[$filename]++;
                                $filename = $filename.' ('.$countFilename[$filename].') ';
                            } else {
                                $countFilename[$filename] = 0;
                            }
                            // zipにPDFファイルを追加
                            $zip->addFromString ($subjectName.'/'.$filename.'.pdf', base64_decode($file_data['data']));
                        }
                    }

                    $zip->close();
                    $message = $current_circulars_exits ? "\r\ncorporate版をご利用のお客様からの回覧文書は文書を開いた状態でのみダウンロードすることが可能です。":"";
                    return $this->sendResponse([ 'numfile'=> 1, 'fileName' => $fileName,
                                        'file_data' => \base64_encode(\file_get_contents($path)) ]
                                    ,"文書ダウンロード処理に成功しました。$message");
                }else{
                    $message = $current_circulars_exits ? "corporate版をご利用のお客様からの回覧文書は文書を開いた状態でのみダウンロードすることが可能です。" : "送信文書のダウンロード処理に失敗しました。";
                    return $this->sendError($message);
                }

            }catch (\Exception $ex) {
                Log::error($ex->getMessage().$ex->getTraceAsString());
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        }
    }

    /**
     * @return void
     */
    public function getEnvCircularHistoryAndOtherData(Request $request){
        $create_company_id = $request->input('create_company_id');
        $origin_env_flg = $request->input('origin_env_flg');
        $origin_edition_flg = $request->input('origin_edition_flg');
        $origin_server_flg = $request->input('origin_server_flg');
        $intOriginCircularId = $request->input('origin_circular_id');
        $finishedDate=$request->input('finishedDate');
        try {
            $strNowDateTime = date("Ym");
            $finishedDate = ($finishedDate >= $strNowDateTime) || ($finishedDate < 202007) ? '' : $finishedDate;

            $arrCircularDocs  =   DB::table("circular_document$finishedDate")
                ->where('circular_id', $intOriginCircularId)
                ->where(function($query) use($create_company_id, $origin_env_flg, $origin_edition_flg, $origin_server_flg){
                    $query->where(function($query1) {
                        $query1->where('origin_document_id', 0);
                        $query1->where('confidential_flg', 0);
                    });
                    $query->orWhere(function($query1) use($create_company_id, $origin_env_flg, $origin_edition_flg, $origin_server_flg){
                        $query1->where('create_company_id', $create_company_id)
                            ->where('origin_env_flg', $origin_env_flg)
                            ->where('origin_edition_flg', $origin_edition_flg)
                            ->where('origin_server_flg', $origin_server_flg);
                    });
                })
                ->get()->keyBy('id');
            $arrCircularDocs=json_decode(json_encode($arrCircularDocs,true),true);
            if(empty($arrCircularDocs)){
                return Response::json(['status' => false, 'message' =>  "の履歴を取得することが失敗です。", 'data' => null], 500);
            }
            $arrIDs = array_column($arrCircularDocs,'id');
            $arrHistoryData = DB::table('circular_operation_history')->where("circular_id",$intOriginCircularId)->get();
            $arrTextData = DB::table('text_info')->whereIn("circular_document_id",$arrIDs)->get();
            $arrCommentData = DB::table('document_comment_info')->whereIn("circular_document_id",$arrIDs)->get();
            $arrStampData = DB::table('stamp_info')->whereIn("circular_document_id",$arrIDs)->get();
            $arrReturnData = [
                'circular_document' => $arrCircularDocs,
                'history' => $arrHistoryData,
                'text' => $arrTextData,
                'comment' => $arrCommentData,
                'stamp' => $arrStampData,
            ];
        }catch (\Exception $exception){
            Log::info("--------------------------------".$exception->getMessage().$exception->getLine());
            return Response::json(['status' => false, 'message' =>  "の履歴を取得することが失敗です。", 'data' => null], 500);
        }
        return Response::json(['status' => true, 'all_data' => $arrReturnData]);
    }

    /**
     * @param Request $request
     *  transfer document data from diff env
     */
    public function transferDocumentData(Request $request) {
        $create_company_id = $request->input('create_company_id');
        $origin_env_flg = $request->input('origin_env_flg');
        $origin_edition_flg = $request->input('origin_edition_flg');
        $origin_server_flg = $request->input('origin_server_flg');
        $origin_circulars = $request->input('circulars');
        $check_add_stamp_history = $request->input('check_add_stamp_history',false);
        $is_get_file_data = $request->input('is_get_file_data',true);

        // 完了一覧
        if (isset($request['finishedDate']) && $request['finishedDate']) {    // 回覧完了日時、当月以外
            $finishedDateKey = $request['finishedDate'];
            $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
        } else {    // 完了一覧以外
            $finishedDate = '';
        }

        $cids = [];
        foreach ($origin_circulars as $circular) {
            $cids[$circular['origin_circular_id']] = $circular['circular_id'];
        }

        $circular_docs  =   DB::table("circular_document$finishedDate")
            ->whereIn('circular_id', array_keys($cids))
            ->where(function($query) use($create_company_id, $origin_env_flg, $origin_edition_flg, $origin_server_flg){
                $query->where(function($query1) {
                    $query1->where('origin_document_id', 0);
                    $query1->where('confidential_flg', 0);
                });
                $query->orWhere(function($query1) use($create_company_id, $origin_env_flg, $origin_edition_flg, $origin_server_flg){
                    $query1->where('create_company_id', $create_company_id)
                        ->where('origin_env_flg', $origin_env_flg)
                        ->where('origin_edition_flg', $origin_edition_flg)
                        ->where('origin_server_flg', $origin_server_flg);
                });
            })
            ->select('id','circular_id','file_name','parent_send_order')
            ->get()->keyBy('id');

        //完了一覧クロス環境情報の取得
        $select_document_items = ['D.file_name', 'D.file_size', 'circular_document_id', 'D.circular_id as origin_circular_id'];
        if ($is_get_file_data) array_push($select_document_items,'file_data');
        $document_data = DB::table("document_data$finishedDate as DD")
            ->join("circular_document$finishedDate as D", 'D.id', '=', 'DD.circular_document_id')
            ->whereIn('DD.circular_document_id', $circular_docs->keys())
            ->select($select_document_items)
            ->get()
            ->keyBy('circular_document_id')
            ->toArray();

        // 件名，最終更新日
        $circular_info = DB::table("circular$finishedDate as c")
            ->join("circular_user$finishedDate as cu", function($join) {
                $join->on('c.id', 'cu.circular_id')
                    ->on('parent_send_order', DB::raw('0'))
                    ->on('child_send_order', DB::raw('0'));
            })
            ->whereIn('c.id',  array_keys($cids))
            ->select('c.id','c.update_at', 'cu.title')
            ->get()
            ->keyBy('id');

        // 履歴取得
        if ($check_add_stamp_history && $is_get_file_data) {
            $stampApiClient = UserApiUtils::getStampApiClient();
            foreach ($circular_docs as $circular_doc) {
                $resultBody = CircularDocumentUtils::getHistory($circular_doc->circular_id, $circular_doc->id, $create_company_id, $origin_edition_flg, $origin_env_flg, $origin_server_flg, $finishedDate, $check_add_stamp_history);
                if ($resultBody['status']) {
                    $hasSignature = isset($request['hasSignature']) ? $request['hasSignature'] : 0;

                    $result = $stampApiClient->post("signatureAndImpress", [
                        RequestOptions::JSON => [
                            'signature' => $hasSignature,
                            'signatureKeyFile' => null,
                            'signatureKeyPassword' => null,
                            'data' => [
                                [
                                    'circular_document_id' => $circular_doc->id,
                                    'pdf_data' => $resultBody['circular_document']->file_data,
                                    'append_pdf_data' => $resultBody['circular_document']->append_pdf,
                                    'stamps' => [],
                                    'texts' => [],
                                    'usingTas' => $request['usingTas'] ? $request['usingTas'] : 0
                                ],
                            ],
                        ]
                    ]);
                    $resData = json_decode((string)$result->getBody());
                    if ($resData->data) {
                        $document_data[$circular_doc->id]->file_data = AppUtils::encrypt($resData->data[0]->pdf_data);
                    }
                } else {
                    Log::error('Log getCircularDoc: ' . $circular_doc->file_name);
                    return Response::json(['status' => false, 'message' => $circular_doc->file_name . "の履歴を取得することが失敗です。", 'data' => null], 500);
                }
            }
        }

        foreach ($document_data as $data) {
            $data->circular_id = $cids[$data->origin_circular_id];
            $data->title = $circular_info[$data->origin_circular_id]->title;
            $data->circular_update_at = $circular_info[$data->origin_circular_id]->update_at;
        }
        return Response::json(['status' => true, 'document_data' => $document_data]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getEnvDocumentsData(Request $request)
    {
        $company_id = $request->input('company_id'); //承認者企業ID
        $env_flg = $request->input('env_flg'); //承認者
        $edition_flg = $request->input('edition_flg'); //承認者
        $server_flg = $request->input('server_flg'); //承認者
        $origin_circulars = $request->input('circulars');
        $check_add_stamp_history = $request->input('check_add_stamp_history', false);

        // 完了一覧
        if (isset($request['finishedDate']) && $request['finishedDate']) {    // 回覧完了日時、当月以外
            $finishedDateKey = $request['finishedDate'];
            $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
        } else {    // 完了一覧以外
            $finishedDate = '';
        }

        $cids = [];
        foreach ($origin_circulars as $circular) {
            $cids[$circular['origin_circular_id']] = $circular['circular_id'];
        }

        $document_data = CircularDocumentUtils::getLocalDocumentsDataByCircular($cids, $company_id, $edition_flg, $env_flg, $server_flg, $finishedDate, $check_add_stamp_history, false);

        return Response::json(['status' => true, 'document_data' => $document_data]);
    }
    /**
     * renotification for list sent
     */
    public function reNotification(Request $request){
        $user = $request->user();
        $cids = $request->get('cids',[]);
        if(count($cids)){
            try{
                $circulars      =   DB::table('circular')
                                    ->where('mst_user_id', $user->id)
                                    ->where('edition_flg', config('app.edition_flg'))
                                    ->where('env_flg', config('app.server_env'))
                                    ->where('server_flg', config('app.server_flg'))
                                    ->whereIn('id', $cids)
                                    ->get()->keyBy('id');
                $cids           =   $circulars->keys();
                $circular_users = DB::table('circular_user')
//                    ->leftJoin('mail_text', function($join){
//                        $join->on('circular_user.id', '=', 'mail_text.circular_user_id');
//                        $join->on('mail_text.id', '=', DB::raw("(select max(id) from mail_text WHERE mail_text.circular_user_id = circular_user.id)"));
//                    })
                    ->whereIn('circular_user.circular_status',[CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS])
                    ->whereIn('circular_user.circular_id', $cids)
                    ->select('circular_user.id','circular_id','parent_send_order','env_flg','edition_flg','server_flg','title','email','circular_user.mst_company_id','name')->get();
                $circular_docs = DB::table('circular_document')
                    ->whereIn('circular_id', $cids)
                    ->select('circular_id','file_name','confidential_flg','origin_edition_flg','origin_env_flg','origin_server_flg','create_company_id','origin_document_id','parent_send_order')
                    ->orderBy('id')
                    ->get();

//                $countPrivateDocuments = DB::table('circular_document')
//                    ->whereIn('circular_id', $cids)
//                    ->where('confidential_flg', 1)
//                    ->select('circular_id')
//                    ->groupBy('circular_id')
//                    ->get()
//                    ->keyBy('circular_id')->toArray();
                $mapSameEnvCompanies = [];
                $mapOtherEnvCompanies = [];
                foreach($circular_users as $circular_user ){
                    // 各回覧の送信宛先を編集（次の未承認者、一人のみ）
                    $circular = $circulars[$circular_user->circular_id];
                    if(!isset($circular->users)) $circular->users = [];
                    $circular->users[] = $circular_user;

                    if ($circular_user->edition_flg == config('app.edition_flg') && $circular_user->mst_company_id){
                        if ($circular_user->env_flg == config('app.server_env') && $circular_user->server_flg == config('app.server_flg')){
                            $mapSameEnvCompanies[$circular_user->mst_company_id] = null;
                        }else{
                            if (isset($mapOtherEnvCompanies[$circular_user->env_flg])){
                                if (isset($mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg])){
                                    $mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg][$circular_user->mst_company_id] = null;
                                }else{
                                    $mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg] = [$circular_user->mst_company_id => null];
                                }
                            }else{
                                $mapOtherEnvCompanies[$circular_user->env_flg] = [$circular_user->server_flg =>[[$circular_user->mst_company_id => null]]];
                            }
                        }
                    }
                }

                $mapSameEnvCompanies = $this->companyRepository->getSameEnvCompanies($mapSameEnvCompanies);

                $mapOtherEnvCompanies = EnvApiDelegate::getOtherEnvCompanies($mapOtherEnvCompanies);

//                $firstDocument = null;
                foreach($circular_docs as $circular_doc ){
//                    if ($firstDocument === null || $firstDocument->circular_id < $circular_doc->circular_id){
//                        $firstDocument = $circular_doc;
//                    }
                    // 各回覧の文書を編集
                    $circular = $circulars[$circular_doc->circular_id];
                    if(!isset($circular->docs)) $circular->docs = [];
                    $circular->docs[] = $circular_doc;
                }

                $previewPath = null;
                $noPreviewPath =  public_path()."/images/no-preview.png";
                $list_cids  = [];
                $mails_to   = [];
                foreach($circulars as $circular){
                    $list_cids[] = $circular->id;
                    $mail_to = [];
                    if(isset($circular->users) AND count($circular->users)){
                        // make file image
                        if (!$circular->hide_thumbnail_flg) {
                            if($circular->first_page_data){
                                $previewPath = AppUtils::getPreviewPagePath($circular->edition_flg, $circular->env_flg, $circular->server_flg, $user->mst_company_id, $user->id);
                                file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                            }else{
                                $previewPath = $noPreviewPath;
                            }
                        }

                        $data = [];

                        foreach($circular->users as $circular_user){
                            if(!CircularUserUtils::checkAllowReceivedEmail($circular_user->email, 'approval',$circular_user->mst_company_id,$circular_user->env_flg,$circular_user->edition_flg,$circular_user->server_flg)) {
                                continue;
                            }
                            // hide_thumbnail_flg 0:表示 1:非表示
                            if (!$circular->hide_thumbnail_flg) {
                                if(isset($circular->docs) && is_array($circular->docs)){
                                    $firstDocument = $circular->docs[0];
                                    // thumbnail表示
                                    if ($firstDocument && $firstDocument->confidential_flg
                                        && $firstDocument->origin_edition_flg == $circular_user->edition_flg
                                        && $firstDocument->origin_env_flg == $circular_user->env_flg
                                        && $firstDocument->origin_server_flg == $circular_user->server_flg
                                        && $firstDocument->create_company_id == $circular_user->mst_company_id){
                                        // 一ページ目が社外秘　＋　upload会社＝宛先会社
                                    $data['image_path'] = $previewPath;
                                }else if ($firstDocument && !$firstDocument->confidential_flg){
                                        // 一ページ目が社外秘ではない
                                    $data['image_path'] = $previewPath;
                                }else{
                                        // no-preview（一ページ目が社外秘　＋　upload会社 <> 宛先会社）
                                    $data['image_path'] = $noPreviewPath;
                                }
                                }else{
                                    $data['image_path'] = $noPreviewPath;
                                }
                            }
                            // 最新メールメッセージ取得
                            $circular_comment_users = DB::table('circular_user')->where('circular_id',$circular_user->circular_id)->pluck('id')->toArray();
                            $comment =  DB::table('mail_text')->whereIn('circular_user_id',$circular_comment_users)->orderBy('id', 'desc')->first();

                            $data['title']      = $circular_user->title;
							$data['text']       = $comment?$comment->text:'';
                            $filterDocuments    = isset($circular->docs)?$circular->docs:[];
                            $filterDocuments    = array_filter($filterDocuments, function($item) use($circular_user){
                                if ($item->confidential_flg
                                    && $item->origin_edition_flg == $circular_user->edition_flg
                                    && $item->origin_env_flg == $circular_user->env_flg
                                    && $item->origin_server_flg == $circular_user->server_flg
                                    && $item->create_company_id == $circular_user->mst_company_id){
                                    // 社外秘：origin_document_idが-1固定
                                    // 同社メンバー参照可
                                    return true;
                                }else if (!$item->confidential_flg
                                    && (!$item->origin_document_id || $item->parent_send_order == $circular_user->parent_send_order)){
                                    // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                                    // 回覧終了時：origin_document_id＝0のレコード
                                    // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                                    return true;
                                }
                                return false;
                            });
//                            $data['hide_circular_approval_url'] = (key_exists($circular->id, $countPrivateDocuments) && $circular_user->parent_send_order == 0)?true:false;
                            // hide_circular_approval_url false:表示 true:非表示
                            // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                            // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                            $data['hide_circular_approval_url'] = false;
                            if(isset($circular->docs) && is_array($circular->docs)){
                                foreach($circular->docs as $document){
                                    if($document->confidential_flg) {
                                        if($document->origin_edition_flg == $circular_user->edition_flg
                                            && $document->origin_env_flg == $circular_user->env_flg
                                            && $document->origin_server_flg == $circular_user->server_flg
                                            && $document->create_company_id == $circular_user->mst_company_id){
                                            $data['hide_circular_approval_url'] = true;
                                        }
                                    }
                                }
                            }

                            $data['docs']       = array_column($filterDocuments, 'file_name');
                            if(count($data['docs'])){
                                $data['docstext'] = '';
                                foreach($data['docs'] as $filename){
                                    if ($data['docstext'] == '') {
                                        $data['docstext'] = $filename . '\r\n';
                                        continue;
                                    }
                                    $data['docstext'] .= '　　　　　　'.$filename . '\r\n';
                                }
                            }else{
                                $data['docstext'] = '';
                            }

                            $data['user_name']  = $user->getFullName();
                            $data['circular_id'] = $circular->id;// PAC_5-2490
                            $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                            $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($circular_user->email, $circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular->id);
                            if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                                $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                            }else{
                                $data['circular_approval_url_text'] = '';
                            }
                            // check to use SAML Login URL or not
                            $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompanies($circular_user, $mapSameEnvCompanies, $mapOtherEnvCompanies);

//                            Mail::to($circular_user->email)->queue(new SendCircularReNotificationMail($data));
                            $title = $data['title'];
                            if(!trim($data['title'])) {
//                                $title = \implode('-', $data['docs']);
                                $title = $data['docs'][0];
                            }

                            $data['mail_name'] = $title;
                            $data['receiver_name'] = $circular_user->name;
                            // 送信者(申請者)
                            $creator = DB::table('circular_user')
                                ->where('circular_id', $circular->id)
                                ->where('parent_send_order', 0)
                                ->where('child_send_order', 0)
                                ->first();
                            $data['creator_name'] = $creator->name;

                            $param = json_encode($data,JSON_UNESCAPED_UNICODE);
                            unset($data['docs']);

                            //利用者:回覧文書の送信（再送）
                            MailUtils::InsertMailSendResume(
                                // 送信先メールアドレス
                                $circular_user->email,
                                // メールテンプレート
                                MailUtils::MAIL_DICTIONARY['CIRCULAR_RE_NOTIFICATION']['CODE'],
                                // パラメータ
                                $param,
                                // タイプ
                                AppUtils::MAIL_TYPE_USER,
                                // 件名
                                config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.circular_notify_template.subject', ['title' => $title, 'creator_name' => $data['creator_name']]),
                                // メールボディ
                                trans('mail.circular_notify_template.body', $data)
                            );

                            // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                            if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                                if ($circular->access_code_flg == CircularUtils::ACCESS_CODE_VALID
                                    && $creator->mst_company_id == $circular_user->mst_company_id
                                    && $creator->edition_flg == $circular_user->edition_flg
                                    && $creator->env_flg == $circular_user->env_flg
                                    && $creator->server_flg == $circular_user->server_flg) {
                                    $access_data['title'] = $data['mail_name'];
                                    $access_data['access_code'] = $circular->access_code;

                                    //利用者:アクセスコードのお知らせ
                                    MailUtils::InsertMailSendResume(
                                        // 送信先メールアドレス
                                        $circular_user->email,
                                        // メールテンプレート
                                        MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                                        // パラメータ
                                        json_encode($access_data,JSON_UNESCAPED_UNICODE),
                                        // タイプ
                                        AppUtils::MAIL_TYPE_USER,
                                        // 件名
                                        trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $access_data['title']]),
                                        // メールボディ
                                        trans('mail.SendAccessCodeNoticeMail.body', $access_data)
                                    );
                                }elseif ($circular->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                                    && ($creator->mst_company_id != $circular_user->mst_company_id
                                        || $creator->edition_flg != $circular_user->edition_flg
                                        || $creator->env_flg != $circular_user->env_flg
                                        || $creator->server_flg != $circular_user->server_flg)) {
                                    // 次の宛先が社外の場合
                                    $access_data['title'] = $data['mail_name'];
                                    $access_data['access_code'] = $circular->outside_access_code;

                                    //利用者:アクセスコードのお知らせ
                                    MailUtils::InsertMailSendResume(
                                        // 送信先メールアドレス
                                        $circular_user->email,
                                        // メールテンプレート
                                        MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                                        // パラメータ
                                        json_encode($access_data,JSON_UNESCAPED_UNICODE),
                                        // タイプ
                                        AppUtils::MAIL_TYPE_USER,
                                        // 件名
                                        trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $access_data['title']]),
                                        // メールボディ
                                        trans('mail.SendAccessCodeNoticeMail.body', $access_data)
                                    );
                                }
                            }

                            $mail_to[] = $circular_user->email;
                        }
                    }
                    $mails_to[] = implode(",", $mail_to);
                }
                Session::flash('emails', $mails_to);
                Session::flash('cids', $list_cids);
                return $this->sendResponse(true,'再通知メールを送信しました。');
            }catch (\Exception $ex) {
                Log::error($ex->getMessage().$ex->getTraceAsString());
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    /**
     * delete list sent
     */
    public function deleteSent(Request $request){
        $user = $request->user();
        $requestCids = $request->get('cids',[]);
        if(count($requestCids)){
            try{
                $listCircular   =   DB::table('circular')
                            ->where('mst_user_id', $user->id)
                            ->whereIn('id', $requestCids)->get();

                $cids = $listCircular->pluck('id')->toArray();

                $fileNames  = [];
                $circular_docs  =   DB::table('circular_document')
                    ->whereIn('circular_id', $cids)
                    ->where(function($query) use ($user){
                        $query->where('confidential_flg', 0);
                        $query->orWhere(function($query1) use ($user){
                            $query1->where('confidential_flg', 1);
                            $query1->where('create_company_id', $user->mst_company_id);
                            $query1->where('origin_edition_flg', config('app.edition_flg'));
                            $query1->where('origin_env_flg', config('app.server_env'));
                            $query1->where('origin_server_flg', config('app.server_flg'));
                        });
                    })
                    ->select('id','circular_id','file_name')
                    ->get()->keyBy('id');

                if(count($circular_docs)){
                    foreach($circular_docs as $circular_doc){
                        $cids[]         = $circular_doc->circular_id;
                        $fileNames[]    = $circular_doc->file_name;
                    }
                    Session::flash('fileNames', $fileNames);
                }

                $filtered = $listCircular->filter(function ($item, $index) {
                    return $item->circular_status == CircularUtils::CIRCULATING_STATUS;
                });
                if(count($filtered)){
                    return $this->sendError('回覧中のため削除できませんでした。');
                }
                /*PAC_5-2435 S*/
                $notDelCount=DB::table('circular_user')
                    ->join('circular','circular_user.circular_id','=','circular.id')
                    ->whereIn('circular.id',$cids)
                    ->where('circular.circular_status',CircularUtils::SEND_BACK_STATUS)
                    ->where('circular_user.parent_send_order','=',0)
                    ->where('circular_user.child_send_order','=',0)
                    ->whereNotIn('circular_user.circular_status',[ CircularUserUtils::NOTIFIED_UNREAD_STATUS,CircularUserUtils::READ_STATUS,CircularUserUtils::PULL_BACK_TO_USER_STATUS,CircularUserUtils::REVIEWING_STATUS])
                    ->count();
                if($notDelCount){
                    return $this->sendError('回覧中のため削除できませんでした。');
                }
                /*PAC_5-2435 E*/

                DB::beginTransaction();

                // TODO update transfer Circular
                // change status 2,3, 9 check circular_user.parent_send_order = 0, set circular_user.del_flg = 1
                DB::update('UPDATE circular AS C, circular_user AS CU'
                    .' SET CU.del_flg = '.CircularUtils::DEL_FLG
                    ." ,CU.update_at = '".Carbon::now()."'"
                    ." ,CU.update_user = '".$user->email."'"
                    .' WHERE C.id = CU.circular_id '
                    .' AND C.mst_user_id = ' . $user->id
                    .' AND C.circular_status IN ('.implode(',', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS, CircularUtils::DELETE_STATUS ]) .') '
                    .' AND C.id IN ('.DB::raw(\implode(',',$cids)).') '
                    .' AND CU.parent_send_order = 0');

                // set viewing_user.del_flg = 1
                DB::update('UPDATE viewing_user AS V'
                    .' SET V.del_flg = '.CircularUtils::DEL_FLG
                    ." ,V.update_at = '".Carbon::now()."'"
                    ." ,V.update_user = '".$user->email."'"
                    .' WHERE V.id IN ('.DB::raw(\implode(',',$cids)).') ');

                $circularUserIds = DB::table("circular_user")
                    ->join(DB::raw("(SELECT circular_id, CONCAT(LPAD(parent_send_order, 3, '0'), LPAD(child_send_order, 3, '0')) AS send_order FROM circular_user
                                      WHERE circular_status = ".CircularUserUtils::SEND_BACK_STATUS." AND circular_id IN (".DB::raw(\implode(",",$cids)).")) s"),
                        function($join) {
                            $join->on('s.circular_id', '=', 'circular_user.circular_id');
                            $join->on('s.send_order', '>', DB::raw("CONCAT(LPAD(circular_user.parent_send_order, 3, '0'), LPAD(circular_user.child_send_order, 3, '0'))"));
                        })
                    ->join("circular", "circular.id", "=", "circular_user.circular_id")
                    ->where("circular.circular_status", CircularUtils::SEND_BACK_STATUS)
                    ->where("circular.mst_user_id",$user->id)
                    ->whereIn("circular_user.circular_id",$cids)
                    ->pluck('circular_user.id')->toArray();

                // change status 4 to 9
                if (count($circularUserIds)){
                    DB::table('circular_user')
                        ->whereIn('id', $circularUserIds)
                        ->update([
                            'circular_status' => CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS,
                            'update_at' => Carbon::now(),
                            'update_user' => $user->email
                        ]);
                }

                //PAC_5-1398 回覧中のすべての添付ファイルを削除します。
                $del_cids = DB::table('circular')
                    ->where('mst_user_id', $user->id)
                    ->where('circular_status', CircularUtils::SEND_BACK_STATUS)
                    ->whereIn('id', $cids)
                    ->pluck('id');
                if ($del_cids){
                    CircularAttachmentUtils::deleteAttachments($del_cids);
                }

                DB::table('circular')
                    ->where('mst_user_id', $user->id)
                    ->where('circular_status', CircularUtils::SEND_BACK_STATUS)
                    ->whereIn('id', $cids)
                    ->update([
                        'circular_status' => CircularUtils::DELETE_STATUS,
                        'update_at' => Carbon::now(),
                        'update_user' => $user->email,
                        'final_updated_date' => Carbon::now(),
                    ]);
                DB::table('circular_document')
                    ->whereIn('circular_id', $cids)
                    ->where('parent_send_order', 0)
                    ->where('confidential_flg', 0)
                    ->update([
                        'origin_document_id' => 0,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $user->email
                    ]);

                 $check=DB::table('eps_t_app')
                        ->whereIn('circular_id',$cids)
                        ->first();
        
                if(!($check==null)){
                        
                    DB::table('eps_t_app')
                        ->where('mst_company_id', $user->mst_company_id)
                        ->whereIn('circular_id', $cids)
                        ->update([
                            'circular_id' =>null,
                            'status' => ExpenseUtils::EXPENSE_CIRCULAR_STATUS,
                            'update_user' => $user->email,
                            'update_at' => Carbon::now(),
                    ]);   
                }
    
                DB::commit();

            }catch (\Exception $ex) {
                DB::rollBack();
                Log::error($ex->getMessage().$ex->getTraceAsString());
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        return $this->sendResponse(true,'送信文書の削除処理に成功しました。');
    }

    /**
     * delete list completed
     */
    public function deleteCompleted(Request $request){
        $user = $request->user();
        $requestCids = $request->get('cids',[]);
        // 回覧完了日時
        $finishedDateKey = $request->get('finishedDate');
        $finishedDates = [];
        // 当月
        if (!$finishedDateKey) {
            $finishedDates[] = '';
            $finishedDates[] = date('Ym');
        } else {
            $finishedDates[] = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
        }

        // 当月の時、付表と原表コピー完了データも削除
        foreach ($finishedDates as $finishedDate) {
            if(count($requestCids)){
                try{
                    $listCircular   =   DB::table("circular$finishedDate")
                        ->whereIn('id', $requestCids)->get();

                    $cids = $listCircular->pluck('id')->toArray();

                    if (count($cids)){

                        $fileNames  = [];
                        $circular_docs = DB::table("circular_document$finishedDate")
                            ->whereIn('circular_id', $cids)
                            ->where(function ($query) use ($user) {
                                $query->where('confidential_flg', 0);
                                $query->orWhere(function ($query1) use ($user) {
                                    $query1->where('confidential_flg', 1);
                                    $query1->where('create_company_id', $user->mst_company_id);
                                    $query1->where('origin_edition_flg', config('app.edition_flg'));
                                    $query1->where('origin_env_flg', config('app.server_env'));
                                    $query1->where('origin_server_flg', config('app.server_flg'));
                                });
                            })
                            ->select('id', 'circular_id', 'file_name')
                            ->get()->keyBy('id');

                        if(count($circular_docs)){
                            foreach($circular_docs as $circular_doc){
                                $cids[]         = $circular_doc->circular_id;
                                $fileNames[]    = $circular_doc->file_name;
                            }
                            Session::flash('fileNames', $fileNames);
                        }
                        $filtered = $listCircular->filter(function ($item, $index) {
                            return $item->circular_status == CircularUtils::CIRCULATING_STATUS;
                        });
                        if(count($filtered)){
                            return $this->sendError('回覧中のため削除できませんでした。');
                        }

                        DB::beginTransaction();

                        // TODO update transfer Circular
                        // change status 2,3, check circular_user.child_send_order = 0|1, set circular_user.del_flg = 1
                        DB::update("UPDATE circular$finishedDate AS C, circular_user$finishedDate AS CU"
                            .' SET CU.del_flg = '.CircularUserUtils::DELETED
                            ." ,CU.update_at = '".Carbon::now()."'"
                            ." ,CU.update_user = '".$user->email."'"
                            ." WHERE C.id = CU.circular_id AND CU.email = '".$user->email."'"
                            .' AND C.circular_status IN ('.implode(',', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS, CircularUtils::DELETE_STATUS ]) .') '
                            .' AND C.id IN ('.DB::raw(\implode(',',$cids)).') '
                            .' AND ((CU.parent_send_order = 0 && CU.child_send_order = 0) || ((CU.parent_send_order > 0 && CU.child_send_order = 1)))');

                        DB::update("UPDATE circular_user$finishedDate s, circular_user$finishedDate u SET s.del_flg = ".CircularUserUtils::DELETED.'
                            WHERE s.circular_id = u.circular_id AND s.parent_send_order = u.parent_send_order AND u.circular_id IN ('.DB::raw(\implode(',',$cids)).')
                            AND u.del_flg = '.CircularUserUtils::DELETED.' AND s.del_flg = '.CircularUserUtils::NOT_DELETE);

                        DB::update("UPDATE viewing_user v, circular_user$finishedDate u SET v.del_flg = ".CircularUserUtils::DELETED.'
                            WHERE v.circular_id = u.circular_id AND v.parent_send_order = u.parent_send_order AND u.circular_id IN ('.DB::raw(\implode(',',$cids)).')
                            AND u.del_flg = '.CircularUserUtils::DELETED.' AND v.del_flg = '.CircularUserUtils::NOT_DELETE);

                        //set viewing_user.del_flg = 1
                        DB::update("UPDATE circular$finishedDate AS C, viewing_user AS V"
                            .' SET V.del_flg = '.CircularUserUtils::DELETED
                            ." ,V.update_at = '".Carbon::now()."'"
                            ." ,V.update_user = '".$user->email."'"
                            ." WHERE C.id = V.circular_id AND V.mst_user_id = '".$user->id."'"
                            .' AND C.circular_status IN ('.implode(',', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS, CircularUtils::DELETE_STATUS ]) .') '
                            .' AND C.id IN ('.DB::raw(\implode(',',$cids)).') ');

                        DB::update("UPDATE viewing_user v, circular_user$finishedDate u SET u.del_flg = ".CircularUserUtils::DELETED.'
                            WHERE v.circular_id = u.circular_id AND v.parent_send_order = u.parent_send_order AND u.circular_id IN ('.DB::raw(\implode(',',$cids)).')
                            AND v.del_flg = '.CircularUserUtils::DELETED.' AND u.del_flg = '.CircularUserUtils::NOT_DELETE);

                        DB::commit();
                    }
                }catch (\Exception $ex) {
                    DB::rollBack();
                    Log::error($ex->getMessage().$ex->getTraceAsString());
                    return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }
        return $this->sendResponse(true,'送信文書の削除処理に成功しました。');
    }

    /**
     * Delete list saved
     */
    public function deleteSaved(Request $request){
        $user = $request->user();
        $cids = $request->get('cids',[]);
        if(count($cids)){
            try{
                $cids   =   DB::table('circular')
                            ->where('mst_user_id', $user->id)
                            ->whereIn('id', $cids)
                            ->pluck('id');
                $fileNames = [];

                while(true){
                    $listDocument = DB::table('circular_document')
                            ->whereIn('circular_id', $cids)
                            ->limit(100)->get();

                    $circular_document_id = $listDocument->pluck('id')->toArray();
                    $fileNames = array_merge($listDocument->pluck('file_name')->toArray(), $fileNames);

                    if(count($listDocument)){
                        DB::table('text_info')->whereIn('circular_document_id', $circular_document_id)->delete();
                        DB::table('document_data')->whereIn('circular_document_id', $circular_document_id)->delete();
                        if(count($listDocument) == 100)
                            continue;
                    }
                    break;
                }
                Session::flash('fileNames', $fileNames);

                CircularAttachmentUtils::deleteAbsoluteAttachments($cids);//PAC_5-1398 回覧中のすべての添付ファイルを削除します。

                DB::table('circular_document')->whereIn('circular_id', $cids)->delete();

                DB::table('circular_user')->whereIn('circular_id', $cids)->delete();;

                DB::table('viewing_user')->whereIn('circular_id', $cids)->delete();

                /*PAC_5-2415 S*/
                DB::table('guest_user')->whereIn('circular_id', $cids)->delete();
                /*PAC_5-2415 E*/
                DB::table('circular')->whereIn('id', $cids)->delete();

                $check=DB::table('eps_t_app')
                ->whereIn('circular_id',$cids)
                ->first();

                if(!($check==null)){
                    
                    DB::table('eps_t_app')
                        ->where('mst_company_id', $user->mst_company_id)
                        ->whereIn('circular_id', $cids)
                        ->update([
                            'circular_id' =>null,
                            'status' => ExpenseUtils::EXPENSE_CIRCULAR_STATUS,
                            'update_user' => $user->email,
                            'update_at' => Carbon::now(),
                        ]);   
                }

            }catch (\Exception $ex) {
                Log::error($ex->getMessage().$ex->getTraceAsString());
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        //PAC_5-976 文言修正　送信文書の→文書の
        return $this->sendResponse(true,'文書の削除処理に成功しました。');
    }

    /**
     * アクセスコードチェック
     *
     * @param $circular_id
     * @param CheckAccessCodeRequest $request
     * @return mixed
     */
    public function checkAccessCode($circular_id,CheckAccessCodeRequest $request) {
        try {
            $finishedDateKey = $request['finishedDate'];
            // 当月
            if (!$finishedDateKey) {
                $finishedDate = '';
            } else {
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            }

            $circular = DB::table("circular$finishedDate")->where('id', $circular_id)->first();
            if(!$circular || !$circular->id) {
                return $this->sendResponse(false,'回覧が見つかりません');
            }
            // # PAC_5-2196 申請時にアクセスコードで保護する(社外用)のみにチェックを入れた場合アクセスコードを適当に入力しても文書を開くことができる
            // 正規のアクセスコードでのみ文書を開くことができる
            if('' == $request['access_code'] && !$circular->access_code_flg) {
                return $this->sendResponse(true,'');
            }
            // 社内回覧
            if(!$request['current_user_identity'] && $circular->access_code == $request['access_code']){
                return $this->sendResponse(true,'アクセスコードが正解です。');
            }
            // 社外回覧
            if($request['current_user_identity'] && $circular->outside_access_code == $request['access_code']) {
                return $this->sendResponse(true,'アクセスコードが正解です。');
            }
            return $this->sendResponse(false,'アクセスコードが正しくありません');

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTotalCircularUnread(Request $request) {
        try {
            $user = $request->user();

            // PAC_5-2114 Start
            // 統合ID側からユーザー情報取得
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                throw new \Exception('Cannot connect to ID App');
            }

            $id_app_user_id = 0;
            $response = $client->post("users/checkEmail", [
                RequestOptions::JSON => ['email' => $user->email]
            ]);
            if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK) {
                $resData = json_decode((string) $response->getBody());
                if(!empty($resData) && !empty($resData->data)){
                    $id_app_users = $resData->data;
                    // 統合ID返す結果と回覧ユーザー比較、現在の回覧者回覧位置確認
                    foreach ($id_app_users as $id_app_user) {
                        if ($user->mst_company_id == $id_app_user->company_id && config('app.edition_flg') == $id_app_user->edition_flg && config('app.server_env') == $id_app_user->env_flg && config('app.server_flg') == $id_app_user->server_flg) {
                            $id_app_user_id = $id_app_user->id;
                            break;
                        }
                    }
                }
            }
            // PAC_5-2114 End
            $query_sub = DB::table('circular as C')
                ->join('circular_user as U', 'C.id', '=', 'U.circular_id')
                ->join('circular_document as D', function($join) use ($user){
                    $join->on('C.id', '=', 'D.circular_id');
                    $join->on(function($condition) use ($user){
                        $condition->on('confidential_flg', DB::raw('0'));
                        $condition->orOn(function($condition1) use ($user){
                            $condition1->on('confidential_flg', DB::raw('1'));
                            $condition1->on('origin_edition_flg', DB::raw(config('app.edition_flg')));
                            $condition1->on('origin_env_flg', DB::raw(config('app.server_env')));
                            $condition1->on('origin_server_flg', DB::raw(config('app.server_flg')));
                            $condition1->on('create_company_id', DB::raw($user->mst_company_id));
                        });
                    });
                    $join->on(function($condition) use ($user){
                        $condition->on('origin_document_id', DB::raw('0'));
                        $condition->orOn(function($condition1) use ($user){
                            $condition1->on('D.parent_send_order', 'U.parent_send_order');
                        });
                    });
                })
                // PAC_5-2114 Start
                ->where(function ($query) use ($user, $id_app_user_id) {
                    if ($id_app_user_id !== 0) {
                        $query->where('U.mst_user_id', $user->id)
                            ->orWhere('U.mst_user_id', $id_app_user_id);
                    } else {
                        $query->where('U.mst_user_id', $user->id);
                    }
                })
                // PAC_5-2114 End
                ->select(DB::raw('C.id, U.parent_send_order'))
                ->where('U.email', $user->email)
                ->where('U.edition_flg', config('app.edition_flg'))
                ->where('U.env_flg', config('app.server_env'))
                ->where('U.server_flg', config('app.server_flg'))
                ->groupBy(['C.id', 'U.parent_send_order']);

            $num_unread = DB::table('circular as C')
                ->leftJoinSub($query_sub, 'D', function ($join) {
                    $join->on('C.id', '=', 'D.id');
                })
                ->join('circular_user as U', function($join){
                    $join->on('U.circular_id', '=', 'C.id');
                    $join->on('D.parent_send_order','=','U.parent_send_order');
                })
                ->where('U.email', $user->email)
                ->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
                ->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS])
                ->count();
            return $this->sendResponse($num_unread, "");
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a transferred Circular in storage.
     * POST /storeCircular
     *
     * @param TransferCircularAPIRequest $request
     *
     * @return Response
     */
    public function storeTransfer(TransferCircularAPIRequest $request)
    {
        Log::debug("TransferCircularAPIRequest param: " . print_r($request->input('circular_users'), true));
        try {
            DB::beginTransaction();
            $existCircular = DB::table('circular')->where('origin_circular_id', $request['circular_id'])
                                ->where('env_flg', $request['env_flg'])
                                ->where('edition_flg', $request['edition_flg'])
                                ->where('server_flg', $request['server_flg'])
                                ->first();
            if ($existCircular){
                $circular_id = $existCircular->id;
                foreach ($request['circular_users'] as $circularUser){
                    DB::table('circular_user')->where('circular_id', $circular_id)->where('parent_send_order', $circularUser['parent_send_order'])
                            ->where('child_send_order', $circularUser['child_send_order'])
                            ->update(['circular_status' => $circularUser['circular_status'],
                                    'received_date' => isset($request['received_date'])?$request['received_date']:Carbon::now()]);
                }
            }else{
                $circular_id = DB::table('circular')->insertGetId([
                    'mst_user_id' => $request['mst_user_id'],
                    'origin_circular_id' => $request['circular_id'],
                    'address_change_flg' => $request['address_change_flg'],
                    // 現行からすれば、access_codeに登録してない。
                    'access_code_flg' =>  $request['edition_flg'] ? $request['access_code_flg'] : 0,
                    'access_code' => $request['edition_flg'] ? $request['access_code'] : '',
					// 現行からすれば、outside_access_codeにリクエストのaccess_codeを設定する。
					'outside_access_code_flg' => $request['edition_flg'] ? $request['outside_access_code_flg'] : $request['access_code_flg'],
					'outside_access_code' => $request['edition_flg'] ? $request['outside_access_code'] : $request['access_code'],
                    "current_aws_circular_id" => $request['current_aws_circular_id'],
                    "current_k5_circular_id" => $request['current_k5_circular_id'],
                    'hide_thumbnail_flg' => $request['hide_thumbnail_flg'],
                    'first_page_data' => $request['first_page_data'],
                    're_notification_day' => $request['re_notification_day'],
                    'circular_status' => $request['circular_status'],
                    'applied_date' => $request['applied_date'],
                    'env_flg' => $request['env_flg'],
                    'edition_flg' => $request['edition_flg'],
                    'server_flg' => $request['server_flg'],
                    'create_at' => Carbon::now(),
                    'create_user' => $request['create_user'],
                    'update_at' => Carbon::now(),
                    'update_user' => $request['create_user'],
                    'final_updated_date' => Carbon::now(),
                    'special_site_flg' => CommonUtils::isNullOrEmpty($request['special_site_flg']) ? 0 : $request['special_site_flg'],
                    'require_print' => isset($request['require_print']) ? $request['require_print'] : 0,
                ]);
                $subject = '';
                foreach ($request['circular_users'] as $circularUser){
                    if ($circularUser['title']){
                        $subject = $circularUser['title'];
                        break;
                    }
                }

                $circular_users = [];
                $mapCircularText =[];
                foreach ($request['circular_users'] as $circularUser){
                    $circular_user = $circularUser;

                    $circular_user['title'] = $subject;
                    $circular_user['circular_id'] = $circular_id;
                    $circular_user['create_at'] = Carbon::now();
                    $circular_user['received_date'] = isset($request['received_date'])?$request['received_date']:Carbon::now();
                    $circular_user['update_at'] = Carbon::now();
                    $circular_user['create_user'] = $request['create_user'];
                    $circular_user['update_user'] = $request['create_user'];
                    $circular_user['origin_circular_url'] = isset($circularUser['view_url'])?$circularUser['view_url']:'';
                    $circular_user['del_flg'] = 0;
                    $circular_user['mst_company_name'] = isset($circularUser['mst_company_name'])?$circularUser['mst_company_name']:null;

                    $mapCircularText[$circularUser['parent_send_order'].'-'.$circularUser['child_send_order']] = $circularUser['text']?:'';

                    unset($circular_user['view_url']);
                    unset($circular_user['text']);

                    $circular_users[] = $circular_user;
                }

                DB::table('circular_user')->insert($circular_users);

                //get id circular_user value just added
                $insertedCircularUsers = DB::table('circular_user')
                                    ->where('circular_id',$circular_id)
                                    ->select('id', 'parent_send_order', 'child_send_order')
                                    ->get();
                $mail_text = [];
                foreach($insertedCircularUsers as $insertedCircularUser){
                    $key = $insertedCircularUser->parent_send_order.'-'.$insertedCircularUser->child_send_order;
                    if (key_exists($key, $mapCircularText)){
                        $mail_text[] = ['circular_user_id' => $insertedCircularUser->id,
                                        'create_at' => Carbon::now(),
                                        'text' => $mapCircularText[$key]];
                    }
                }
                if (count($mail_text)){
                    DB::table('mail_text')->insert($mail_text);
                }
            }

            $circular_documents = [];
            foreach ($request['circular_documents'] as $circularDocument){
                $circular_document = $circularDocument;

                $circular_document['circular_id'] = $circular_id;
                $circular_document['origin_env_flg'] = $circular_document['env_flg'];
                $circular_document['origin_edition_flg'] = $circular_document['edition_flg'];
                $circular_document['origin_server_flg'] = $circular_document['server_flg'];
                $circular_document['create_at'] = Carbon::now();
                $circular_document['update_at'] = Carbon::now();
                $circular_document['create_user'] = $request['create_user'];
                $circular_document['update_user'] = $request['create_user'];
                $circular_document['file_name'] = preg_replace('#[/*\"|\'\`]#', '', $circular_document['file_name']);
                unset($circular_document['env_flg']);
                unset($circular_document['edition_flg']);
                unset($circular_document['server_flg']);
                $circular_documents[] = $circular_document;
            }
            DB::table('circular_document')->insert($circular_documents);

            if (isset($request['document_datas']) || isset($request['stamp_infos']) || isset($request['text_infos']) || isset($request['time_stamp_infos'])){
                $insertedCircularDocs = DB::table('circular_document')->select('id', 'origin_document_id')->where('circular_id', $circular_id)->get();

                $mapNewDocumentId = [];
                foreach($insertedCircularDocs as $insertedCircularDoc){
                    $mapNewDocumentId[$insertedCircularDoc->origin_document_id] = $insertedCircularDoc->id;
                }

                if (isset($request['document_datas'])){
                    $circular_datas = [];
                    foreach ($request['document_datas'] as $circularData){
                        $circular_data = $circularData;

                        $circular_data['circular_document_id'] = $mapNewDocumentId[$circularData['circular_document_id']];
                        $circular_data['create_at'] = Carbon::now();
                        $circular_data['update_at'] = Carbon::now();
                        $circular_data['create_user'] = $request['create_user'];
                        $circular_data['update_user'] = $request['create_user'];

                        $circular_datas[] = $circular_data;
                    }
                    if (count($request['document_datas'])){
                        DB::table('document_data')->insert($circular_datas);
                    }
                }

                if (isset($request['time_stamp_infos'])){
                    $circular_timestamps = [];
                    foreach ($request['time_stamp_infos'] as $circularTimestamp){
                        $circular_timestamp = $circularTimestamp;

                        $circular_timestamp['circular_document_id'] = $mapNewDocumentId[$circularTimestamp['circular_document_id']];

                        $circular_timestamps[] = $circular_timestamp;
                    }
                    if (count($request['time_stamp_infoss'])){
                        DB::table('time_stamp_info')->insert($circular_timestamps);
                    }
                }

                if (isset($request['stamp_infos'])){
                    $circular_stamps = [];
                    foreach ($request['stamp_infos'] as $circularStamp){
                        $circular_stamp = $circularStamp;

                        $circular_stamp['circular_document_id'] = $mapNewDocumentId[$circularStamp['circular_document_id']];

                        $circular_stamps[] = $circular_stamp;
                    }
                    if (count($request['stamp_infos'])){
                        DB::table('stamp_info')->insert($circular_stamps);
                    }
                }

				// PAC_5-368 document_comment_info コピー
				if (isset($request['comment_infos'])){
					$circular_comments = [];
					foreach ($request['comment_infos'] as $circularStamp){
						$circular_comment = $circularStamp;

						$circular_comment['circular_document_id'] = $mapNewDocumentId[$circularStamp['circular_document_id']];

						$circular_comments[] = $circular_comment;
					}
					if (count($request['comment_infos'])){
						DB::table('document_comment_info')->insert($circular_comments);
					}
				}
                // sticky_notes コピー
                if (isset($request['sticky_notes'])){
                    $sticky_notes = [];
                    foreach ($request['sticky_notes'] as $sticky_note){
                        $sticky_note_arr = $sticky_note;
                        $sticky_note_arr['document_id'] = $mapNewDocumentId[$sticky_note['document_id']];
                        $sticky_notes[] = $sticky_note_arr;
                    }
                    if (count($request['sticky_notes'])){
                        DB::table('sticky_notes')->insert($sticky_notes);
                    }
                }

                if (isset($request['text_infos'])){
                    $circular_texts = [];
                    foreach ($request['text_infos'] as $circularText){
                        $circular_text = $circularText;

                        $circular_text['circular_document_id'] = $mapNewDocumentId[$circularText['circular_document_id']];

                        $circular_texts[] = $circular_text;
                    }
                    if (count($request['text_infos'])){
                        DB::table('text_info')->insert($circular_texts);
                    }
                }
            }

            DB::commit();
            CircularUserUtils::summaryInProgressCircular($circular_id);
            return $this->sendApiResponse('回覧登録処理に成功しました。', \Illuminate\Http\Response::HTTP_CREATED);

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError('回覧登録処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function discard($id, Request $request) {
        try {
            $user = Auth::user();
            if(!$user || !$user->id) {
                $user = $request['user'];
            }
            $circularUsers = DB::table('circular_user')
                ->where('circular_id', $id)
                ->get();
            if(!$circularUsers->some(function($item) {
                return $item->circular_status === CircularUserUtils::SEND_BACK_STATUS;
            })) {
                return $this->sendError("回覧破棄ができません。", \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }
            $authorCircularUser = $circularUsers->first(function ($item) {
                return $item->parent_send_order == 0 && $item->child_send_order == 0;
            });
            if(!$authorCircularUser || ($authorCircularUser && !in_array($authorCircularUser->circular_status, [CircularUserUtils::NOTIFIED_UNREAD_STATUS,CircularUserUtils::READ_STATUS]))) {
                return $this->sendError("回覧破棄ができません。", \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }
            DB::beginTransaction();

            DB::table('circular')
                ->where('id', $id)
                ->update([
                    'circular_status' => CircularUtils::DELETE_STATUS,
                    'update_at'=> Carbon::now(),
                    'update_user'=> $user->email,
                    'final_updated_date' => Carbon::now(),
                ]);

            $sendbackUser = DB::table('circular_user')
                ->where('circular_id', $id)
                ->where('circular_status', CircularUserUtils::SEND_BACK_STATUS)
                ->first();

            if ($sendbackUser){
                DB::table('circular_user')
                    ->where('circular_id', $id)
                    ->whereRaw("CONCAT(LPAD(parent_send_order, 3, '0'),LPAD(child_send_order, 3, '0')) < "
                        .str_pad($sendbackUser->parent_send_order,3,'0', STR_PAD_LEFT).str_pad($sendbackUser->child_send_order,3,'0', STR_PAD_LEFT))
                    ->update([
                        'circular_status' => CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $user->email,
                    ]);
                $objSendbackUser = DB::table('circular_user')
                        ->where('circular_id', $id)->where('circular_status', CircularUserUtils::SEND_BACK_STATUS)
                        ->orderBy('id','desc')
                        ->first();
                if(!empty($objSendbackUser)){
                    DB::table('circular')
                        ->where('id', $id)
                        ->update([
                            'circular_status' => CircularUtils::DELETE_STATUS,
                            'update_at'=> Carbon::now(),
                            'update_user'=> $user->email,
                            'completed_date' => Carbon::now(),
                            'final_updated_date' => Carbon::now(),
                        ]);

                    $arrAllData = DB::table('circular_user')
                        ->where("circular_id",$id)
                        ->where(function ($query) use($objSendbackUser) {
                            $query->where('id','<=',$objSendbackUser->id)->orWhere('child_send_order',$objSendbackUser->child_send_order);
                        })
                        ->select("id",'email','name')
                        ->orderBy('id','asc')
                        ->get()->toArray();
                    $arrInsertData = [
                        'sender_name' => '',
                        'sender_email' => '',
                        'receiver_name' => '',
                        'receiver_email' => '',
                        'receiver_name_email' => '',
                    ];
                    if(!empty($arrAllData)){
                        $arrInsertData['sender_name'] = $arrAllData[0]->name;
                        $arrInsertData['sender_email'] = $arrAllData[0]->email;
                    }
                    $arrIDs = [];
                    foreach($arrAllData as $dKey => $dVal){
                        $arrIDs[] = $dVal->id;
                        $arrInsertData['receiver_name'] = $dKey == 0 ? '': $arrInsertData['receiver_name'].','.$dVal->name;
                        $arrInsertData['receiver_email'] = $dKey == 0 ? '': $arrInsertData['receiver_email'].','.$dVal->email;
                        $arrInsertData['receiver_name_email'] = $dKey == 0 ? '': $arrInsertData['receiver_name_email']. $dVal->name.'&lt;'.$dVal->email.'&gt;'.'<br />';
                    }
                    $arrInsertData['receiver_name'] = ltrim($arrInsertData['receiver_name'],',');
                    $arrInsertData['receiver_email'] = ltrim($arrInsertData['receiver_email'],',');
                    $arrInsertData['receiver_name_email'] = rtrim($arrInsertData['receiver_name_email'],'<br />');
                    // PAC_5-1664:回覧破棄をすると削除ステータスになり、利用者からは見えないくなる    期待した結果  完了一覧にはいる
                    DB::table('circular_user')->whereIn('id',$arrIDs)->update($arrInsertData);

                }
                // PAC_5-2011   1438
                DB::table('circular_user')
                    ->where('circular_id', $id)
                    ->where("stamp_flg",1)
                    ->whereRaw("CONCAT(LPAD(parent_send_order, 3, '0'),LPAD(child_send_order, 3, '0')) < "
                        .str_pad($sendbackUser->parent_send_order,3,'0', STR_PAD_LEFT).str_pad($sendbackUser->child_send_order,3,'0', STR_PAD_LEFT))
                    ->update([
                        'circular_status' => CircularUserUtils::APPROVED_WITH_STAMP_STATUS,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $user->email,
                    ]);
            }

            DB::table('circular_document')
                ->where('circular_id', $id)
                ->where('parent_send_order', 0)
                ->where('confidential_flg', 0)
                ->update([
                    'origin_document_id' => 0,
                    'update_at'=> Carbon::now(),
                    'update_user'=> $user->email
                ]);

            $check=DB::table('eps_t_app')
                    ->where('circular_id',$id)
                    ->first();
            if(!($check==null)){

                DB::table('eps_t_app')
                    ->where('mst_company_id', $user->mst_company_id)
                    ->where('circular_id', $id)
                    ->update([
                        'circular_id' =>null,
                        'status' => ExpenseUtils::EXPENSE_CIRCULAR_STATUS,
                        'update_user' => $user->email,
                        'update_at' => Carbon::now(),
                    ]);
            }

            DB::commit();
            //TODO update transfer circular
            return $this->sendResponse(true, "回覧破棄の処理に成功しました。");
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function storeCircular($circular_id, Request $request)
    {
        $isCurrentEnv = true;
        $folderId = $request->get('folderId');
        if(isset($request['usingHash']) && $request['usingHash']) {
            $user = $request->get('user');
            $edition_flg = $request['current_edition_flg'];
            $env_flg = $request['current_env_flg'];
            $server_flg = $request['current_server_flg'];
            if ($edition_flg == config('app.edition_flg') && $env_flg == config('app.server_env') && $server_flg == config('app.server_flg') ) {
                if (isset($request['current_circular_user']) && $request['current_circular_user'] != null) {
                    $company = DB::table('mst_company')->where('id', $request['current_circular_user']->mst_company_id)->first();
                } else {
                    $company = DB::table('mst_company')->where('id', $request['current_viewing_user']->mst_company_id)->first();
                }
            }else{
                $isCurrentEnv = false;
                if (isset($request['current_circular_user']) && $request['current_circular_user'] != null) {
                    $company_id = $request['current_circular_user']->mst_company_id;
                } else {
                    $company_id = $request['current_viewing_user']->mst_company_id;
                }

                $envClient = EnvApiUtils::getAuthorizeClient($request['current_env_flg'], $request['current_server_flg']);
                if (!$envClient) throw new \Exception('Cannot connect to Env Api');

                $response = $envClient->get("getCompany/$company_id", []);
                if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                    //check stamps flg company
                    $company = json_decode($response->getBody())->data;
                }
            }
        }else {
        $user = Auth::user();
            $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
            $edition_flg = $user->edition_flg;
            $env_flg = $user->env_flg;
            $server_flg = $user->server_flg;
        }
        $keyword = $request->get('keyword','');
        
        //PAC_5-2070対応
        if(isset($request['keyword_flg'])){
            $keyword_flg = $request->get('keyword_flg');
        }else{
            $keyword_flg = NULL;
        }
        
        // 完了一覧
        if (isset($request['finishedDate'])) {
            // 回覧完了日時
            $finishedDateKey = $request->get('finishedDate');
        } else {    // 完了一覧以外
            $finishedDateKey = '';
        }

        if (!$company || !$company->long_term_storage_flg){
            $this->sendError("Cannot store Circular", \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }

        if($company->long_term_folder_flg && $folderId !== '' && $folderId !== null){
            $hasPermission = DB::table('long_term_folder_auth')
                ->where('long_term_folder_id',$folderId)
                ->where('auth_kbn', AppUtils::LONG_TERM_FOLDER_AUTH_PERSON)
                ->where('auth_link_id', $user->id)
                ->first();
            if (!$hasPermission) return $this->sendError(__('message.false.long_term_folder_permission'));
        }
        $folderId = $folderId ?:0;


        // check max_usable_capacity
        $storage_size = DB::table('long_term_document')
            ->where('mst_company_id', $company->id)
            ->select(DB::raw('sum(file_size) as storage_size'))
            ->value('storage_size');
        if($storage_size >= $company->max_usable_capacity * 1024 * 1024 * 1024){
            return $this->sendError("データ容量($company->max_usable_capacity GB)を超えています。", \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }

        $longTermDocument = DB::table('long_term_document')
            ->where('circular_id', $circular_id)
            ->where('mst_company_id', $company->id)
            ->first();
        if (!$finishedDateKey) {
            $finishedDate = '';
        } else {
            $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
        }
        $fileNames  = [];
        $circular_docs  =   DB::table("circular_document$finishedDate")
            ->where('circular_id', $circular_id)
            ->where(function($query) use ($company, $edition_flg, $env_flg, $server_flg){
                $query->where('confidential_flg', 0);
                $query->orWhere(function($query1) use ($company,$edition_flg, $env_flg, $server_flg){
                    $query1->where('confidential_flg', 1);
                    $query1->where('create_company_id', $company->id);
                    $query1->where('origin_edition_flg', $edition_flg);
                    $query1->where('origin_env_flg', $env_flg);
                    $query1->where('origin_server_flg', $server_flg);
                });
            })
            ->select('file_name','circular_id')
            ->get();

        if(count($circular_docs)){
            foreach($circular_docs as $circular_doc){
                $cids[]         = $circular_doc->circular_id;
                $fileNames[]    = $circular_doc->file_name;
            }
            Session::flash('fileNames', $fileNames);
        }
        if (!$longTermDocument){
            if ($isCurrentEnv){
                \Artisan::call("circular:storeS3", ['circular_id' => $circular_id, 'company_id' => $company->id, '--keyword' => $keyword, 'finishedDate' => $finishedDateKey, '--keyword_flg' => $keyword_flg, '--folder_id' => $folderId]);
            $returnMsg =  str_replace(array("\r", "\n"), '', \Artisan::output());
            if($returnMsg == "1"){
                    return $this->sendError(__('message.false.long_term_save'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            } else if($returnMsg == "0"){
                    return Response::json(['status' => true, 'message' => __('message.success.long_term_save')]);
            }else{
                return $this->sendError($returnMsg, \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }else{
                // 他環境処理を呼び出し
                $envClient = EnvApiUtils::getAuthorizeClient($env_flg,$server_flg);
                if (!$envClient) {
                    Log::error('回覧の長期保管:Cannot connect to other Env Api Client');
                    return $this->sendError(['status' => false, 'data' => null], StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
                }

                $response = $envClient->post('longTermStoreCircular', [
                    RequestOptions::JSON => ['circular_id' => $circular_id, 'company_id' => $company->id, 'keyword' => $keyword,
                        'finishedDate' => $finishedDateKey, 'keyword_flg' => $keyword_flg , 'folder_id' => $folderId]
                ]);
                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                    Log::error('回覧の長期保管:other Env Api longStoreCircular failed');
                    Log::error($response->getBody());
                    return $this->sendError(__('message.false.long_term_save'), StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
                }
                return Response::json(['status' => true, 'message' => __('message.success.long_term_save')]);
            }
        }else{
            DB::table('long_term_document')
                ->where('circular_id', $circular_id)
                ->where('mst_company_id', $company->id)
                ->update([
                    'keyword' => $keyword,
                    'is_del' => 0,
                ]);

            return Response::json(['status' => true, 'message' => __('message.success.long_term_save')]);
        }
    }

    /**
     * その他環境文書の長期保存
     * @param Request $request
     * @return mixed
     */
    public function longTermStoreCircular(Request $request){
        $origin_circular_id = $request->input('circular_id');
        $company_id = $request->input('company_id');
        $keyword = $request->input('keyword');
        $finishedDate = $request->input('finishedDate');
        $keyword_flg = $request->input('keyword_flg');
        $folder_id = $request->input('folder_id');

        $circular = DB::table("circular$finishedDate")
            ->where('origin_circular_id',$origin_circular_id)
            ->first();

        $longTermDocument = DB::table('long_term_document')
            ->where('circular_id', $circular->id)
            ->where('mst_company_id', $company_id)
            ->first();

        if (!$longTermDocument){
            \Artisan::call("circular:storeS3", ['circular_id' => $circular->id, 'company_id' => $company_id, '--keyword' => $keyword, 'finishedDate' => $finishedDate, '--keyword_flg' => $keyword_flg, '--folder_id' => $folder_id]);

            $returnMsg =  str_replace(array("\r", "\n"), '', \Artisan::output());
            if($returnMsg == "1"){
                return Response::json(['status' => false, 'message' => __('message.false.long_term_save')], StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
            } else if($returnMsg == "0"){
                return Response::json(['status' => true, 'message' => __('message.success.long_term_save')]);
            }else{
                return Response::json(['status' => false, 'message' => $returnMsg], StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
            }
        }else{
            DB::table('long_term_document')
                ->where('circular_id', $circular->id)
                ->where('mst_company_id', $company_id)
                ->update(['keyword' => $keyword,'is_del' => 0]);

            return Response::json(['status' => true, 'message' => __('message.success.long_term_save')]);
        }

    }

    public function storeMultipleCircular($request)
    {
        $user = Auth::user();
        $cids = $request->get('cids',[]);
        $folderId = $request->get('folderId');
        // 完了一覧
        if (isset($request['finishedDate'])) {
            // 回覧完了日時
            $finishedDateKey = $request->get('finishedDate');
        } else {    // 完了一覧以外
            $finishedDateKey = '';
        }
        if(count($cids)){
            $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
            if (!$company || !$company->long_term_storage_flg){
                $this->sendError("Cannot store Circular", \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            if($company->long_term_folder_flg && $folderId !== ''){
                $hasPermission = DB::table('long_term_folder_auth')
                    ->where('long_term_folder_id',$folderId)
                    ->where('auth_kbn', AppUtils::LONG_TERM_FOLDER_AUTH_PERSON)
                    ->where('auth_link_id', $user->id)
                    ->first();
                if (!$hasPermission) return $this->sendError(__('message.false.long_term_folder_permission'));
            }
            $folderId = $folderId ?:0;

            // check max_usable_capacity
            $storage_size = DB::table('long_term_document')
                ->where('mst_company_id', $user->mst_company_id)
                ->select(DB::raw('sum(file_size) as storage_size'))
                ->value('storage_size');
            if($storage_size >= $company->max_usable_capacity * 1024 * 1024 * 1024){
                return $this->sendError("データ容量($company->max_usable_capacity GB)を超えています。", \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }
            if (!$finishedDateKey) {
                $finishedDate = '';
            } else {
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            }

            $circular_docs = DB::table("circular_document$finishedDate")
                ->whereIn('circular_id', $cids)
                ->where(function ($query) use ($user) {
                    $query->where('confidential_flg', 0);
                    $query->orWhere(function ($query1) use ($user) {
                        $query1->where('confidential_flg', 1);
                        $query1->where('create_company_id', $user->mst_company_id);
                        $query1->where('origin_edition_flg', config('app.edition_flg'));
                        $query1->where('origin_env_flg', config('app.server_env'));
                        $query1->where('origin_server_flg', config('app.server_flg'));
                    });
                })
                ->select('file_name')
                ->get();
            $fileNames = [];
            foreach ($circular_docs as $circular_doc){
                $fileNames[] = $circular_doc->file_name;
            }
            Session::flash('fileNames', $fileNames);

            $longTermDocument = DB::table('long_term_document')
                ->whereIn('circular_id', $cids)
                ->where('mst_company_id', $user->mst_company_id)
                ->pluck('circular_id')
                ->toArray();

            if (count($cids) == 1){
                $keyword = $request->get('keyword','');
                if (count($longTermDocument) == 1){
                    DB::table('long_term_document')
                        ->where('circular_id', $longTermDocument[0])
                        ->where('mst_company_id', $user->mst_company_id)
                        ->update([
                            'keyword' => $keyword,
                            'is_del' => 0,
                        ]);
                }
            }else{
                $keyword = '';
            }
            $cids = array_diff($cids, $longTermDocument);
            
            //PAC_5-2070対応
            if(isset($request['keyword_flg'])){
                $keyword_flg = $request->get('keyword_flg');
            }else{
                $keyword_flg = NULL;
            }

            if (count($cids)){
                $returnMsg = "0";
                foreach ($cids as $cid){
                    \Artisan::call("circular:storeS3", ['circular_id' => $cid, 'company_id' => $user->mst_company_id, '--keyword' => $keyword, 'finishedDate' => $finishedDateKey, '--keyword_flg' => $keyword_flg, '--folder_id' => $folderId]);
                    if($returnMsg == "0"){
                        $returnMsg =  str_replace(array("\r", "\n"), '', \Artisan::output());
                    }
                }
                if($returnMsg == "1"){
                    return $this->sendError('回覧の長期保管をできませんでした。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                } else if($returnMsg == "0"){
                    return Response::json(['status' => true, 'message' => '回覧の長期保管をできました。']);
                }else{
                    return $this->sendError($returnMsg, \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }else{
                return Response::json(['status' => true, 'message' => '回覧の長期保管をできました。']);
            }
        }
    }

    /**
     * @Description そのた環境のCircularを削除
     * @param Request $request 必要なパラメータ
     */
    public function deleteOtherCircular(Request $request)
    {
        DB::beginTransaction();
        try {
            $finishedDate = $request['finishedDate'];
            Log::debug("回覧状態変更パラメータ：circular_id:".$request['circular_id']
                .",circular_env_flg:".$request['circular_env_flg']
                .",circular_edition_flg".$request['circular_edition_flg']
                .",circular_server_flg".$request['circular_server_flg']);

            // 回覧削除処理
            DB::table("circular$finishedDate")
                ->where('origin_circular_id', $request['circular_id'])
                ->where('env_flg', $request['circular_env_flg'])
                ->where('edition_flg', $request['circular_edition_flg'])
                ->where('server_flg', $request['circular_server_flg'])
                ->update(['circular_status' => 9,
                    'update_at' => Carbon::now(),
                    'update_user' => $request['user_email'],
                    'final_updated_date' => Carbon::now(),
                    ]);

            // 回覧ユーザ削除処理
            DB::update("UPDATE circular$finishedDate AS C, circular_user$finishedDate AS CU"
                .' SET CU.del_flg = '.CircularUserUtils::DELETED
                ." ,CU.update_at = '".Carbon::now()."'"
                ." ,CU.update_user = '".$request['user_email']."'"
                .' WHERE C.id = CU.circular_id '
                .' AND C.origin_circular_id = '.$request['circular_id']
            );

            // 閲覧ユーザ削除処理
            DB::update("UPDATE circular$finishedDate AS C, viewing_user AS V"
                .' SET V.del_flg = '.CircularUserUtils::DELETED
                ." ,V.update_at = '".Carbon::now()."'"
                ." ,V.update_user = '".$request['user_email']."'"
                .' WHERE C.id = V.circular_id '
                .' AND C.origin_circular_id = '.$request['circular_id']
            );
            DB::commit();
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError('そのた環境に回覧削除処理が失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function storeCircularUser($circular_id, $templateId, $login_user, $users) {
        try {
            Log::debug("storeCircularUser param: " . print_r($users, true));

            $system_env_flg     = config('app.server_env');
            $system_edition_flg = config('app.edition_flg');
            $system_server_flg = config('app.server_flg');

            $last_circular_user = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->orderBy('parent_send_order', 'desc')
                ->orderBy('child_send_order', 'desc')
                ->first();
            $company=DB::table('mst_company')
                ->where('id','=',$login_user->mst_company_id)
                ->first();
            DB::beginTransaction();

            $old_company_key = null;
            $parent_send_order = 0;
            $child_send_order = 0;

            $emails = [];
            $env_flg        = isset($user['env_flg'])?$user['env_flg']:$system_env_flg;
            $edition_flg    = isset($user['edition_flg'])?$user['edition_flg']:$system_edition_flg;
            $server_flg     = isset($user['server_flg'])?$user['server_flg']:$system_server_flg;
            foreach ($users as $user) {
                if ($edition_flg == $system_edition_flg && $env_flg == $system_env_flg && $server_flg == $system_server_flg){
                    $emails[] = $user['email'];
                }
            }
            $dbUsers = DB::table('mst_user')->whereIn('email', $emails)->where('state_flg', AppUtils::STATE_VALID)->select('email', 'id')->get();
            $mapDBUsers = [];
            foreach ($dbUsers as $user) {
                $mapDBUsers[$user->email] = $user;
            }

            // 合議のroute id
            $old_template_route_id = -1;
            $plan_id = 0;
            foreach ($users as $user) {
                $user_company_id = isset($user['company_id'])?$user['company_id']:-1;
                $user_company_key   = "$user_company_id-$env_flg-$edition_flg-$server_flg";
                $template_route_id = isset($user['template_rotes_id'])?$user['template_rotes_id']:-1;

                if (!$old_company_key){
                    $old_company_key   = "$user_company_id-$env_flg-$edition_flg-$server_flg";
                }

                $received_date = null;
                if(isset($user['is_maker']) && $user['is_maker']) {
                    $parent_send_order = 0;
                    $child_send_order = 0;
                }else{
                    if($user_company_id == -1 || $user_company_key != $old_company_key) {
                        $parent_send_order += 1;
                        $child_send_order   = 1;
                    }else{
                        // $template_route_id == -1 非合議
                        if($template_route_id == -1 || $template_route_id != $old_template_route_id){
                            $child_send_order += 1;
                        }
                    }
                    $old_company_key = $user_company_key;
                    $old_template_route_id = $template_route_id;
                }
                if ($parent_send_order === 0 && $child_send_order === 0){
                    $received_date = Carbon::now();
                }
                $mst_user_id = null;
                if ($edition_flg == $system_edition_flg && $env_flg == $system_env_flg && $server_flg == $system_server_flg && isset($mapDBUsers[$user['email']])){
                    $mst_user_id = $mapDBUsers[$user['email']]->id;
                }else {
                    //新エディション側の回覧ユーザー
                    if($edition_flg == $system_edition_flg){
                        //本環境の文書データを取得する
                        $envClient = EnvApiUtils::getAuthorizeClient($env_flg, $server_flg);
                        if (!$envClient) throw new \Exception('Cannot connect to Env Api');

                        $response = $envClient->get("getUserInfo/" . $user['email'], []);
                        if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                            $envUserInfo = json_decode($response->getBody())->data;
                            if ($envUserInfo) {
                                $mst_user_id = $envUserInfo->mst_user_id;
                            }
                        } else {
                            Log::warning('Cannot get Env UserInfo from other env');
                            Log::warning($response->getBody());
                        }
                    }else{
                        //現行エディション側の無効回覧ユーザー制約
                        $client = IdAppApiUtils::getAuthorizeClient();
                        if (!$client){
                            //TODO message
                            return response()->json(['status' => false,
                                'message' => ['Cannot connect to ID App']
                            ]);
                        }
                        $response = $client->post("users/checkEmail",[
                            RequestOptions::JSON => ['email' => $user['email'] ,'contract_app' => $edition_flg ,'app_env' => $env_flg]
                        ]);
                        if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK) {
                            $result = json_decode((string) $response->getBody());
                            if($result->data == []){
                                DB::rollBack();
                                return $this->sendError('無効なパソコン決裁Cloud利用者がルートに含まれています。お気に入りを再度作成し直してください。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                            }
                        } else {
                            DB::rollBack();
                            return $this->sendError('回覧ユーザー登録処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    }
                }
                Log::debug("Insert circular user for circular $circular_id: email - ".$user['email'].", parent_send_order - $parent_send_order, child_send_order - $child_send_order");

                $circular_user_params = [
                    'circular_id'=> $circular_id,
                    'parent_send_order'=> $parent_send_order,
                    'child_send_order'=> $child_send_order,
                    'env_flg'=> $env_flg,
                    'edition_flg'=> $edition_flg,
                    'server_flg'=> $server_flg,
                    'mst_company_id'=> $user_company_id==-1?null:$user_company_id,
                    'mst_company_name'=> $user_company_id==-1?null:(isset($company->company_name)?$company->company_name:null),
                    'mst_user_id' => $mst_user_id,
                    'name'=> isset($user['name'])?$user['name']:($user['family_name'].' '.$user['given_name']),
                    'email'=> $user['email'],
                    'title'=> $last_circular_user ? $last_circular_user->title: '',
                    'del_flg'=> CircularUserUtils::NOT_DELETE,
                    'return_flg' => 1,
                    'circular_status'=> CircularUserUtils::NOT_NOTIFY_STATUS,
                    'create_at' => Carbon::now(),
                    'create_user' => $login_user->email,
                    'update_at' => Carbon::now(),
                    'update_user' => $login_user->email,
                    'received_date' => $received_date,
                    'plan_id' => $plan_id,
                ];
                DB::table('circular_user')->insert($circular_user_params);

                // 合議の場合
                if($template_route_id !== -1){
                    $mode = $user['template_mode'];
                    $wait = $user['template_wait'];
                    $score = $user['template_score'];
                    $detail = $user['template_detail'];

                    $circular_user_routes = CircularUserRoutes::where('circular_id', $circular_id)->where('child_send_order', $child_send_order)->first();
                    if(!$circular_user_routes){
                        $circular_user_routes = new CircularUserRoutes();
                        $circular_user_routes->circular_id = $circular_id;
                        $circular_user_routes->child_send_order = $child_send_order;
                        $circular_user_routes->mode = $mode;
                        $circular_user_routes->wait = $wait;
                        $circular_user_routes->score = $score;
                        $circular_user_routes->detail = $detail;
                        $circular_user_routes->state = 1;
                        $circular_user_routes->create_at = Carbon::now();
                        $circular_user_routes->create_user = $login_user->email;
                        $circular_user_routes->update_at = Carbon::now();
                        $circular_user_routes->update_user = $login_user->email;
                        $circular_user_routes->template_id = $templateId;
                        $circular_user_routes->save();
                    }
                }
            }
            DB::commit();
            return true;
        }catch (\Exception $ex) {
            DB::rollBack();
            return false;
        }
    }
}
