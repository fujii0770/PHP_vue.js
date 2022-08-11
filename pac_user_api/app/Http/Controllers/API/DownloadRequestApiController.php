<?php

namespace App\Http\Controllers\API;

use App\AuditUser;
use App\Http\Requests\API\SearchCircularUserAPIRequest;
use App\Http\Controllers\AppBaseController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\CircularDocumentUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\DownloadRequestApiControllerUtils;
use App\Http\Utils\DownloadRequestUtils;
use App\Http\Utils\DownloadUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\OperationsHistoryUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Http\Utils\UserApiUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use App\Jobs\MakeDownloadData;
use GuzzleHttp\RequestOptions;
use App\Http\Utils\CommonUtils;
use Session;
use Response;

use App\Models\Company;

class DownloadRequestApiController extends AppBaseController
{

    public function index(SearchCircularUserAPIRequest $request)
    {
        try {
            $user = $request->user();

            //PAC_5-2874 S
            // 無害化するかを確認
            $company = Company::where('id', $user->mst_company_id)->first();
            $is_sanitizing = $company ? $company->sanitizing_flg : 0;
            $is_private = config('app.app_lgwan_flg');
            //PAC_5-2874 E

            $limit = AppUtils::normalizeLimit($request->get('limit', 10), 10);
            $orderDir = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));

            $orderBy = $request->get('orderBy', "update_at");
            $arrOrder = ['title' => 'title', 'R.contents_create_at' => 'R.contents_create_at',
                'R.download_period' => 'R.download_period', 'R.state' => 'R.state'];
            $orderBy = isset($arrOrder[$orderBy]) ? $arrOrder[$orderBy] : 'contents_create_at';

            $que = DB::table('download_request as R')
                ->select(DB::raw('R.id, R.user_auth, R.file_name, R.contents_create_at, R.download_period,
                 IF(R.download_period<=CURRENT_TIMESTAMP,'. DownloadUtils::EXPIRED_STATE.',state) AS state, R.sanitizing_state'))// PAC_5-2874
                ->where('R.mst_user_id', $user->id)
                ->where('R.user_auth', $user->isAuditUser() ? AppUtils::AUTH_FLG_AUDIT : AppUtils::AUTH_FLG_USER)
                ->where('R.state', '!=', DownloadRequestUtils::DELETE_STATE)
                ->orderBy($orderBy, $orderDir)
                ->paginate($limit)->appends(request()->input());

            //　PAC_5-2874 S
            foreach ($que as $item){
                // 無害化無効　又は　LGWAN public環境
                if(!$is_sanitizing || !$is_private){
                    $item->sanitizing_state = DOwnloadUtils::SANITIZING_UNNEEDED;
                }
            }
            //PAC_5-2874 E

            return $this->sendResponse($que, __('message.success.download_request.get_data'));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ダウンロード完了削除処理
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        try {
            $rid = $request->rid;
            $isCloud = $request->get('risCloud', false);
            $user = Auth::user();

            if (!$isCloud) {
                Log::debug("delete not cloud");
            DownloadRequestUtils::RemoveRequestData($rid, $user);
            } else {
                Log::debug("delete on cloud");
                $this->checkConstraintsAndDelete($rid,$user);
            }

            return $this->sendSuccess(__('message.success.download_request.file_delete'));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.download_request.file_delete'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ダウンロード画面ダウンロード操作
     *
     * @param Request $request
     * @return mixed
     */
    public function download(Request $request)
    {
        try {
            ini_set('memory_limit','2048M');
            $user = $request->user();
            $rid = $request->get('rid');
            $isCloud = $request->get('risCloud', false);
            $ip_address = $request->server->get('HTTP_X_FORWARDED_FOR') ? $request->server->get('HTTP_X_FORWARDED_FOR'):$request->getClientIp();
            $req_data = DB::table('download_request')
                ->where('id', $rid)
                ->select('id', 'file_name')
                ->first();

            $file_name = $req_data ? $req_data->file_name : '';
            $doc_data = DB::table('download_wait_data')
                ->where('download_request_id', $rid)
                ->first();

            if (!$doc_data) {
                if ($file_name){
                    OperationsHistoryUtils::storeOperationLog($user->id, false, $ip_address,OperationsHistoryUtils::DOWNLOAD_FILE, $file_name);
                }
                return $this->sendError(__('message.false.download_request.get_data'));
            }

            $data = AppUtils::decrypt($doc_data->data);

            if (!$isCloud) {
                $this->checkConstraintsAndDelete($rid,$user);
            }
            OperationsHistoryUtils::storeOperationLog($user->id, true, $ip_address,OperationsHistoryUtils::DOWNLOAD_FILE, $file_name);
            // ファイル名に/が入っているとエラーになるので置換してから送る
            return $this->sendResponse(['fileName' => AppUtils::fileNameReplace($req_data->file_name), 'file_data' => $data], __('message.success.download_request.download_file'));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR] . ($request->query('id'))[0], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ダウンロード予約処理
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function reserve(Request $request){
        try {
            $user                       = $request->user();
            $reqFileName                = $request->get('fileName', '');
            $cids                       = $request->get('cids', []);
            $finishedDateKey            = $request->get('finishedDate');
            $finishedDate               = !$finishedDateKey ? '' : Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            $check_add_stamp_history    = $request->get('stampHistory', false);
            $download                   = $request->get('download',false);
            $ip_address                 = $request->server->get('HTTP_X_FORWARDED_FOR') ? $request->server->get('HTTP_X_FORWARDED_FOR'):$request->getClientIp();
            $request_type               = $request->get('download_type');
            $frmFlg                     = $request->get('frmFlg', '');
            $upload_id                 =$request->get('upload_id',[]);

            // PAC_5-2853 S

            // 無害化するかを確認
            $is_sanitizing = Company::where('id', $user->mst_company_id)->first()->sanitizing_flg;
            if ($is_sanitizing){
                $cntCheck = true;
                if(count($cids) > 0){
                    $docs = DB::table("circular$finishedDate as C")
                        ->join("circular_document$finishedDate as D", 'C.id', '=', 'D.circular_id')
                        ->select(DB::raw("C.id, D.id as did, D.file_name"))
                        ->whereIn('C.id', $cids)
                        ->get();
                    foreach ($docs as $key=>$val){
                        $fileName = $reqFileName ? $reqFileName . '.pdf' : $val->file_name;
                        $request->merge(['cid' => $val->id,
                            'did' => $val->did,
                            'is_sanitizing' => $is_sanitizing,
                            'cntCheck' => $cntCheck,
                            'cids' => [],
                            'upload_id' => []]);
                        // ダウンロードJob登録
                        $result = DownloadUtils::downloadRequest(
                            $user, 'App\Http\Utils\DownloadRequestApiControllerUtils', 'getCircularsDownloadData', $fileName,
                            $user, $request->all()
                        );
                        //PAC_5-2527 利用者が一括ダウンロードしたときの操作履歴を取りたい
                        if ($request_type){
                            OperationsHistoryUtils::storeOperationLog( $user->id, $result, $ip_address, $request_type, $fileName);
                        }
                        if(!($result === true)){
                            return $this->sendError(__('message.false.download_request.download_ordered_sanitizing', ['attribute' => $result]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                        }else{
                            $cntCheck = false;
                        }
                    }
                }
                if(count($upload_id) > 0){
                    $ups = DB::table("long_term_document")
                        ->select(DB::raw("upload_id, file_name"))
                        ->whereIn('upload_id', $upload_id)
                        ->get();
                    foreach ($ups as $key=>$val){
                        unset($upload_id);
                        $fileName = $val->file_name;
                        $upload_id[] = $val->upload_id;
                        $request->merge(['upload_id' => $upload_id,
                            'is_sanitizing' => $is_sanitizing,
                            'cntCheck' => $cntCheck,
                            'cids' => []]);
                        // ダウンロードJob登録
                        $result = DownloadUtils::downloadRequest(
                            $user, 'App\Http\Utils\DownloadRequestApiControllerUtils', 'getCircularsDownloadData', $fileName,
                            $user, $request->all()
                        );
                        //PAC_5-2527 利用者が一括ダウンロードしたときの操作履歴を取りたい
                        if ($request_type){
                            OperationsHistoryUtils::storeOperationLog( $user->id, $result, $ip_address, $request_type, $fileName);
                        }
                        if(!($result === true)){
                            return $this->sendError(__('message.false.download_request.download_ordered_sanitizing', ['attribute' => $result]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                        }else{
                            $cntCheck = false;
                        }
                    }
                }
                return response()->json(['status' => true, 'message' => __('message.success.download_request.download_ordered_sanitizing')]);
            }else{
                // デフォルトのファイル名取得
                $fileName = DownloadRequestApiControllerUtils::getDefaultFileName($user, $cids, $finishedDate, $finishedDateKey, $check_add_stamp_history, $frmFlg,$upload_id);

                // ファイル名の入力有り
                if ($reqFileName != '') {
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    // No Extension
                    $ext = $ext == "" ? "" : '.' . $ext;
                    $fileName = $reqFileName . $ext;
                }

                if($download){
                    $fileName = "download-" . time() . ".zip";
                }
                // ダウンロードJob登録
                $result = DownloadUtils::downloadRequest(
                    $user, 'App\Http\Utils\DownloadRequestApiControllerUtils', 'getCircularsDownloadData', $fileName,
                    $user, $request->all()
                );
                //PAC_5-2527 利用者が一括ダウンロードしたときの操作履歴を取りたい
                if ($request_type){
                    OperationsHistoryUtils::storeOperationLog( $user->id, $result, $ip_address, $request_type, $fileName);
                }
                if(!($result === true)){
                    return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $result]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                return response()->json(['status' => true, 'message' => __('message.success.download_request.download_ordered', ['attribute' => $fileName])]);
            }
            // PAC_5-2853 E

        } catch (\Throwable $th) {
            Log::error($th->getMessage() . $th->getTraceAsString());
            return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $th->getMessage()]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function downloadLongTerm(Request $request){
        $user                       = $request->user();
        $reqFileName                = $request->get('fileName', '');
        $cids                       = $request->get('cids', []);
        $finishedDateKey            = $request->get('finishedDate');
        $finishedDate               = !$finishedDateKey ? '' : Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
        $check_add_stamp_history    = $request->get('stampHistory', false);
        $download                   = $request->get('download',false);
        $ip_address                 = $request->server->get('HTTP_X_FORWARDED_FOR') ? $request->server->get('HTTP_X_FORWARDED_FOR'):$request->getClientIp();
        $request_type               = $request->get('download_type');
        $frmFlg                     = $request->get('frmFlg', '');
        $upload_id                 =$request->get('upload_id',[]);

        try {

            // 無害化するかを確認
            $is_sanitizing = Company::where('id', $user->mst_company_id)->first()->sanitizing_flg;

            if($is_sanitizing){
                $arrFileName = [];
                $strFirstName = null;
                DB::table('long_term_document')->whereIn('id',$cids)->get()->each(function($item) use (&$arrFileName,&$strFirstName){
                    $arrFile = explode(".pdf,",$item->file_name);
                    $strFirstName = $strFirstName ?? $arrFile[0];
                    $arrFileName[$item->id] = isset($arrFileName[$item->id]) ? array_merge($arrFileName[$item->id],$arrFile) : $arrFile;
                });

                foreach($arrFileName as $fileKey => $fileVal){
                    foreach($fileVal as $key => $item){
                        // ダウンロードJob登録
                        $request->cids = [$fileKey];
                        $request->dName = $item;
                        $result = DownloadUtils::downloadRequest(
                            $user, 'App\Http\Utils\DownloadRequestApiControllerUtils', 'getLongTermDocumentDownloadData', $item,
                            $user, $request->all()
                        );
                        //PAC_5-2527 利用者が一括ダウンロードしたときの操作履歴を取りたい
                        if ($request_type){
                            OperationsHistoryUtils::storeOperationLog( $user->id, $result, $ip_address, $request_type, $item , $request->user() instanceof AuditUser);
                        }
                        if(!($result === true)){
                            return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $result]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    }
                }
                return response()->json(['status' => true, 'message' => __('message.success.download_request.download_ordered', ['attribute' => $strFirstName])]);
            }
            $fileName = Carbon::now()->copy()->format('YmdHis') . ".zip";
            if(count($cids) <= 1){
                // デフォルトのファイル名取得
                $strFileName = DB::table('long_term_document')->whereIn('id',$cids)->value('file_name');
                $arrFile=explode(".pdf,",$strFileName);
                if(count($arrFile) <= 1){
                    $fileName = $strFileName;
                }
            }

            // ファイル名の入力有り
            if ($reqFileName != '') {
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                // No Extension
                $ext = $ext == "" ? "" : '.' . $ext;
                $fileName = $reqFileName . $ext;
            }

            // ダウンロードJob登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadRequestApiControllerUtils', 'getLongTermDocumentDownloadData', $fileName,
                $user, $request->all()
            );
            //PAC_5-2527 利用者が一括ダウンロードしたときの操作履歴を取りたい
            if ($request_type){
                OperationsHistoryUtils::storeOperationLog( $user->id, $result, $ip_address, $request_type, $fileName,$request->user() instanceof AuditUser);
            }
            if(!($result === true)){
                return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $result]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json(['status' => true, 'message' => __('message.success.download_request.download_ordered', ['attribute' => $fileName])]);


        } catch (\Throwable $th) {
            Log::error($th->getMessage() . $th->getTraceAsString());
            return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $th->getMessage()]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 再申請
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rerequest(Request $request)
    {
        try {
            $dl_request_id = $request->get('rid');
            $user = $request->user();

            DownloadUtils::reDownloadRequest($user, $dl_request_id);

            return response()->json(['status' => true, 'message' => __('message.success.download_request.re_order')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
            return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //PAC_5-2874 S
    /**
     * LGWAN privateの場合、未無害化のダウンロード内容は、無害化待ちに更新する
     *
     * @param Request $request
     * @return mixed
     */
    public function sanitizingUpdate(Request $request)
    {
        try {
            $rid = $request->rid;
            $user = Auth::user();

            //1：無害化要⇒2：無害化待ち
            DB::table('download_request')
                ->where('mst_user_id', $user->id)
                ->where('user_auth', $user->isAuditUser() ? AppUtils::AUTH_FLG_AUDIT : AppUtils::AUTH_FLG_USER)
                ->where('id', $rid)
                ->update([
                    'sanitizing_state' => DownloadUtils::SANITIZING_WAIT,
                ]);

            return $this->sendSuccess(__('message.success.download_request.sanitizing_update'));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.download_request.sanitizing_update'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    //PAC_5-2874 E

    /**
     * 制約条件確認＆削除or更新処理 PAC_5-1315
     *
     * @param mixed $user
     * @param Integer $id
     * @return void
     */
    public function checkConstraintsAndDelete($rid,$user){

        $constraints = DB::table('mst_constraints')
            ->where('mst_company_id', $user->mst_company_id)
            ->select('dl_after_keep_days', 'dl_after_proc')
            ->first();

        // 0の場合はダウンロード実行日中
        if ($constraints->dl_after_keep_days === 0) {
            $dl_period = Carbon::today()->copy()->addDay(1)->subSecond();
        } else {
            $dl_period = Carbon::now()->copy()->addDay($constraints->dl_after_keep_days);
        }

        if ($constraints->dl_after_proc === 0) {
            DownloadRequestUtils::RemoveRequestData($rid, $user);
        } else {
            DB::beginTransaction();
            DB::table('download_request')
            ->where('id', $rid)
            ->where('state', DownloadRequestUtils::REQUEST_DOWNLOAD_WAIT)
            ->update([
                'state' => DownloadRequestUtils::REQUEST_DOWNLOAD_END,
                'download_period' => $dl_period
            ]);
            DB::commit();
        }
    }

    /**
     * 回覧状態は回覧完了(保存済み)に更新
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCircularStatus(Request $request)
    {
        try {
            $user = $request->user();
            $rid = $request->get('id');
            // 回覧IDを取得
            $req_circulars = DB::table('download_proc_wait_data as P')
                ->leftJoin('circular_finished_month as F', 'F.circular_id', 'P.circular_id')
                ->select(['P.circular_id', 'F.month'])
                ->where('P.download_request_id', $rid)
                ->where('P.circular_id','<>','0')//回覧ID=0(長期保管新規)的データ、取得しない
                ->groupBy('P.circular_id', 'F.month')
                ->get()->toArray();

            // 帳票回覧判定
            $cids = [];
            foreach ($req_circulars as $req_circular) {
                array_push($cids, $req_circular->circular_id);
            }
            // 請求書
            $frm_invoice = DB::table("frm_invoice_data")
                ->selectRaw('id')
                ->whereIn('circular_id', $cids)
                ->get()
                ->toArray();
            // その他
            $frm_others = DB::table("frm_others_data")
                ->selectRaw('id')
                ->whereIn('circular_id', $cids)
                ->get()
                ->toArray();

            // 請求書　もしくは　その他　データあれば、帳票回覧です。
            // 帳票回覧は、回覧状態を変更できません。
            if(count($frm_invoice) >0 || count($frm_others) > 0) {
                return $this->sendResponse(['request_id' => $rid], __('message.success.update_circular_status'));
            }

            DB::beginTransaction();
            foreach ($req_circulars as $req_circular) {
                $circular_id = $req_circular->circular_id;
                // 回覧完了日時
                $finished_date = $req_circular->month;
                $req_finished_circular = DB::table("circular$finished_date")->select('circular_status')->where('id', $circular_id)->first();
                if ($req_finished_circular->circular_status != CircularUtils::CIRCULAR_COMPLETED_STATUS){
                    DB::rollBack();
                    return $this->sendResponse(['request_id' => $rid], __('message.success.update_circular_status'));
                }
                DB::table("circular$finished_date")->where('id', $circular_id)->update([
                    'circular_status' => CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS,
                    'update_at' => Carbon::now(),
                    'update_user' => $user->email,
                    'final_updated_date' => Carbon::now(),
                ]);
                $circulars = DB::table("circular$finished_date as C")
                    ->join("circular_user$finished_date as U", 'C.id', '=', 'U.circular_id')
                    ->select('C.id', 'U.edition_flg', 'U.env_flg', 'U.server_flg', 'C.origin_circular_id', 'C.edition_flg as origin_edition_flg', 'C.env_flg as origin_env_flg', 'C.server_flg as origin_server_flg','C.completed_date')
                    ->where('C.id', $circular_id)
                    ->groupBy('C.id', 'U.edition_flg', 'U.env_flg', 'U.server_flg', 'C.origin_circular_id')
                    ->get();
                // 回覧状態を更新
                foreach ($circulars as $circular) {
                    // クロス環境ファイルかどうかを判別します
                    if ($circular->edition_flg == config('app.edition_flg') && ($circular->env_flg != config('app.server_env') || $circular->server_flg != config('app.server_flg'))) {
                        // クロス環境
                        $envClient = EnvApiUtils::getAuthorizeClient($circular->env_flg, $circular->server_flg);
                        if (!$envClient) {
                            throw new \Exception('Cannot connect to Env Api');
                        }
                        // 回覧完了日時  当月:0 1ヶ月前:1
                        $finished_date = Carbon::create(substr($req_circular->month,0,4),substr($req_circular->month,4,6))->diff(Carbon::now())->m;
                        // この環境ファイルかどうかを判断します
                        if ($circular->origin_circular_id) {
                            $circular_status = [
                                RequestOptions::JSON => ['origin_circular_id' => $circular->origin_circular_id, 'status' => CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS, 'origin_env_flg' => $circular->origin_env_flg,
                                    'origin_edition_flg' => $circular->origin_edition_flg, 'origin_server_flg' => $circular->origin_server_flg, 'user' => $user, 'finishedDate' => $finished_date,'completed_date' => $circular->completed_date]
                            ];
                        } else {
                            $circular_status = [
                                RequestOptions::JSON => ['origin_circular_id' => $circular->id, 'status' => CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS, 'origin_env_flg' => $circular->origin_env_flg,
                                    'origin_edition_flg' => $circular->origin_edition_flg, 'origin_server_flg' => $circular->origin_server_flg, 'user' => $user, 'finishedDate' => $finished_date,'completed_date' => $circular->completed_date]
                            ];
                        }
                        // ファイルステータスの変更
                        $response = $envClient->post("updateEnvStatus", $circular_status);
                        if (!$response) {
                            DB::rollBack();
                            return $this->sendError(__('message.success.update_circular_status'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    }
                }
            }
            DB::commit();
            return $this->sendResponse(['request_id' => $rid], __('message.success.update_circular_status'));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.update_circular_status'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 掲示板 添付ファイルダウンロード予約
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bbsAttachmentReserve(Request $request){
        try {
            $user = $request->user();
            $file_name = $request->get('file_name'); //ダウンロード予約のファイル名
            $bbs_id = $request->get('bbs_id');

            $attachment = DB::table('bbs')->where('id',$bbs_id)->first();

            if (!$attachment){
                return $this->sendError(__('message.false.attachment_request.download_attachment'), StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
            }

            if ($attachment->state == AppUtils::BBS_STATE_SAVED){
                return $this->sendError(__('message.false.attachment_request.download_attachment'), StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
            }

            // ダウンロードJob登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadRequestApiControllerUtils', 'getBbsAttachmentData', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $result]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json(['status' => true, 'message' => __('message.success.download_request.download_ordered', ['attribute' => $file_name])]);
        }catch (\Exception $ex){
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $ex->getMessage()]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 文書プレビューダウンロード時の無害化
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function previewFileReserve(Request $request){
        try {
            $user = $request->user();
            $reserveFileName      = $request->get('reserve_file_name',''); //ダウンロード予約のファイル名


            // ダウンロードJob登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadRequestApiControllerUtils', 'getPreviewFileDownloadData', $reserveFileName,
                $user, $request->all()
            );

            if(!($result === true)){
                return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $result]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json(['status' => true, 'message' => __('message.success.download_request.download_ordered', ['attribute' => $reserveFileName]),'data' => []]);

        }catch (\Exception $ex){
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $ex->getMessage()]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 添付ファイルダウンロード予約
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachmentReserve(Request $request){
        try {
            $user = $request->user();
            $file_name      = $request->get('file_name'); //ダウンロード予約のファイル名
            $circular_attachment_id = $request->get('circular_attachment_id');

            $attachment = DB::table('circular_attachment')->where('id',$circular_attachment_id)->first();

            if (!$attachment){
                return $this->sendError(__('message.false.attachment_request.download_attachment'), StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
            }

            if ($attachment->status == CircularAttachmentUtils::ATTACHMENT_NOT_CHECK_STATUS){
                return $this->sendError(__('message.false.attachment_request.is_checking'), StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
            }else if ($attachment->status == CircularAttachmentUtils::ATTACHMENT_CHECK_FAIL_STATUS){
                return $this->sendError(__('message.false.attachment_request.check_fail'), StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
            }

            // ダウンロードJob登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadRequestApiControllerUtils', 'getAttachmentData', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $result]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json(['status' => true, 'message' => __('message.success.download_request.download_ordered', ['attribute' => $file_name])]);
        }catch (\Exception $ex){
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $ex->getMessage()]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
