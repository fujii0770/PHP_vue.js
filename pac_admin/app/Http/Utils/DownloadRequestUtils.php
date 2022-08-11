<?php

namespace App\Http\Utils;

use Carbon\Traits\Creator;
use DB;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Matrix\Exception;
use Response;
use Session;
use Carbon\Carbon;
use App\CompanyAdmin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\RequestOptions;
use App\Http\Utils\DownloadUtils;
use App\Models\DownloadProcWaitData;

use App\Models\Company;

/**
 * ダウンロード要求処理
 * Class DownloadRequestUtils
 * @package App\Http\Utils
 */
class DownloadRequestUtils
{

    /**
     * ダウンロード予約処理
     *
     * @param $cids
     * @param $reqFileName
     * @param $finishedDate
     * @param $longtermFlg
     * @return \Illuminate\Http\JsonResponse
     */
    public static function reserveDownload($cids, $reqFileName, $finishedDate = '', $checkHistory = false, $longtermFlg = '0',$ids=[]){
        try{
            $user = \Auth::user();
            // 無害化するかを確認
            $is_sanitizing = Company::where('id', $user->mst_company_id)->first()->sanitizing_flg;// PAC_5-2853
            // 長期保存以外の場合
            if ($longtermFlg == '0') {
                $circular_docs = DB::table("circular_document$finishedDate")
                    ->where(function ($query) use ($user) {
                        $query->where('confidential_flg', DB::raw('1'))
                            ->where('create_company_id', $user->mst_company_id)
                            ->orWhere('confidential_flg', DB::raw('0'));
                    })
                    ->whereIn('circular_id', $cids)
                    ->whereIn('origin_document_id', ['-1', '0'])
                    ->select('id', 'circular_id', 'file_name')
                    ->get()->keyBy('id');

                $document_datas = array();
                $circular_ids_all = $circular_docs->keys()->toArray();

                // mysql最長クエリ
                $max_allowed_packet = DB::select("show variables like 'max_allowed_packet'")[0]->Value;

                // 選択文書総容量
                $doc_size = DB::table("circular_document$finishedDate")->select(DB::raw('sum(file_size) as file_size'))
                    ->whereIn('id',$circular_ids_all)->value('file_size');

                if ($doc_size * DownloadUtils::MULTIPLE_SIZE > $max_allowed_packet) {
                    return response()->json(['status' => false,
                        'message' => [__('message.warning.download_request.order_size_max')]]);
                }

                $document_data = DB::table("document_data$finishedDate")
                    ->whereIn('circular_document_id', $circular_ids_all)
                    ->select('id', 'circular_document_id')
                    ->get()
                    ->keyBy('circular_document_id')
                    ->toArray();
                foreach ($document_data as $key => $item) {
                    $document_datas[$key] = $item;
                }

                // デフォルトファイル名設定
                if (count($document_datas) == 1) {
                    $fileName = $reqFileName . $circular_docs[reset($document_datas)->circular_document_id]->file_name;
                } elseif (count($document_datas) > 1) {
                    $fileName = Carbon::now()->copy()->format('YmdHis') . ".zip";
                }

                // PAC_5-2853 S
                // 無害化するかを確認
                if($is_sanitizing){
                    foreach ($circular_docs as $key=>$val){
                        $fileName = $val->file_name;
                        $cid = $val->circular_id;
                        $did = $val->id;
                        // Job登録
                        $result = DownloadUtils::downloadRequest(
                            $user, 'App\Http\Utils\DownloadRequestUtils', 'getCircularDownloadData', $fileName,
                            $user, $ids,$cids, $reqFileName, $finishedDate, $checkHistory, $longtermFlg, $cid, $did, $is_sanitizing, $fileName
                        );

                        if(!($result === true)){
                            return response()->json(['status' => false,
                                'message' => [__('message.false.download_request.download_ordered_sanitizing', ['attribute' => $result])]]);
                        }
                    }
                    return response()->json([
                        'status' => true,
                        'message' =>    [__("message.success.download_request.download_ordered_sanitizing")]
                    ]);
                }
            }else{

                // long_term_document 情報取得
                $long_term_document_datas = DB::table('long_term_document')
                    ->whereIn('id', $ids)
                    ->where('mst_company_id', $user->mst_company_id)
                    ->select('id', 'circular_id', 'file_name', 'file_size')
                    ->get();
                // 0件の場合、取得失敗
                if (count($long_term_document_datas) === 0) {
                    return response()->json(['status' => false,
                        'message' => [__('message.false.download_request.file_detail_get')]]);
                }
                // デフォルトファイル名設定
                $fileName = Carbon::now()->copy()->format('YmdHis') . ".zip";
                if (count($long_term_document_datas) == 1) {
                    // 一つcircular_idの場合
                    $documentFileName = $long_term_document_datas[0]->file_name;
                    $arr = explode(".", $documentFileName);
                    // 一つファイルの場合
                    if (count($arr) <= 2 || !strpos($documentFileName,", ")){
                        $fileName = $reqFileName . $documentFileName;
                    }
                }

                // PAC_5-2853 S
                // 無害化するかを確認
                if($is_sanitizing){
                    foreach ($long_term_document_datas as $key=>$val){
                        $fileNames = explode(', ', $val->file_name);
                        foreach ($fileNames as $fileName){
                            $cid = $val->circular_id;
                            $did = $val->id;
                            // Job登録
                            $result = DownloadUtils::downloadRequest(
                                $user, 'App\Http\Utils\DownloadRequestUtils', 'getCircularDownloadData', $fileName,
                                $user, $ids,$cids, $reqFileName, $finishedDate, $checkHistory, $longtermFlg, $cid, $did, $is_sanitizing, $fileName
                            );

                            if(!($result === true)){
                                return response()->json(['status' => false,
                                    'message' => [__('message.false.download_request.download_ordered_sanitizing', ['attribute' => $result])]]);
                            }
                        }
                    }
                    return response()->json([
                        'status' => true,
                        'message' =>    [__("message.success.download_request.download_ordered_sanitizing")]
                    ]);
                }
            }
            if(!$is_sanitizing){
                // If InputData, FileName is changed
                if ($reqFileName != '') {
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    // No Extension
                    $ext = $ext == "" ? "" : '.' . $ext;
                    $fileName = $reqFileName . $ext;
                }

                // Job登録
                $result = DownloadUtils::downloadRequest(
                    $user, 'App\Http\Utils\DownloadRequestUtils', 'getCircularDownloadData', $fileName,
                    $user, $ids,$cids, $reqFileName, $finishedDate, $checkHistory, $longtermFlg, 0, 0, 0, $fileName
                );

                if(!($result === true)){
                    return response()->json(['status' => false,
                        'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
                }
                Session::put('file_name',$fileName);
                return response()->json([
                    'status' => true,
                    'message' =>    [__("message.success.download_request.download_ordered",
                        ['attribute' => $fileName])]
                ]);
            }

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * 非同期ダウンロード用ダウンロードファイル取得
     *
     * @param $user
     * @param $cids
     * @param $reqFileName
     * @param $finishedDate
     * @param $longtermFlg
     * @param $download_req_id
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getCircularDownloadData($user, $ids, $cids, $reqFileName, $finishedDate = '', $checkHistory = false, $longtermFlg = '0', $cid = 0, $did = 0, $is_sanitizing = 0, $fileName = '', $download_req_id){
        $result = DownloadRequestUtils::setDownloadWaitData($user, $ids,$cids, $finishedDate, $checkHistory, $longtermFlg, $download_req_id, $cid, $did, $is_sanitizing, $fileName);
        if($result == false){
            return null;
        }
        return DownloadRequestUtils::getDownloadDataFromDataProcWaitData($download_req_id, $finishedDate);
    }

    /**
     * ダウンロード待ちデータテーブルからダウンロードファイルを生成し取得
     *
     * @param $download_req_id
     * @return string
     */
    public static function getDownloadDataFromDataProcWaitData($download_req_id, $finishedDate = ''){
        ini_set('memory_limit','2048M');
        // ダウンロード要求情報取得
        $dl_req = DB::table('download_request')
            ->where('id', $download_req_id)->first();

        if (!$dl_req) {
            Log::debug('この申請データは存在しない。download_request_id:'.$download_req_id);
            return;
        }

        // 状態更新 ( 処理待ち:0 => 作成中:1)
        DB::table('download_request')
            ->where('id', $download_req_id)
            ->update([
                'state' => DownloadUtils::REQUEST_CREATING
            ]);

        // 回覧文書ID
        $dl_proc_wait_datas = DB::table('download_proc_wait_data')
            ->where('download_request_id', $download_req_id)
            ->select('circular_document_id', 'circular_id', 'file_name', 'document_data','title','id','circular_update_at')
            ->orderBy('circular_id', 'asc')
            ->orderBy('circular_document_id', 'asc')
            ->get();
        //ダウンロード件数
        $dl_count = $dl_proc_wait_datas->count();

        // 回覧情報
        $circular_info = DB::table('download_proc_wait_data')
            ->where('download_request_id', $download_req_id)
            ->select('circular_id', 'title', 'circular_update_at')
            ->groupBy('circular_id', 'title', 'circular_update_at')
            ->get()->keyBy('circular_id')->toArray();
        // 申請者情報
        $user_info = DB::table('mst_admin')
            ->where('id', $dl_req->mst_user_id)
            ->select(['id', 'email', 'mst_company_id'])
            ->first();

        $path = '';
        try {
            if ($dl_count === 1) {
                // 回覧一件and文書一件の場合
                $data = AppUtils::decrypt($dl_proc_wait_datas->first()->document_data);
            } else {
                // 回覧or文書複数件の場合
                $path = sys_get_temp_dir()."/download-circular-" . AppUtils::getUniqueName(config('app.edition_flg'), config('app.server_env'), config('app.server_flg'), $dl_req->mst_company_id, $dl_req->id) . ".zip";
                $zip = new \ZipArchive();
                if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) == false) {
                    throw new \Exception(__('message.false.download_request.zip_create'));
                }
                $arrayFolderName = [];//key="回覧ID(ID==false時0)"、value="決定されたフォルダ名"
                foreach ($dl_proc_wait_datas as $dl_proc_wait_data){
                    //フォルダ生成
                    $circularIDKey = $dl_proc_wait_data->circular_id ?: 0;//回覧ID判別用(ID==false時0)
                    if (!key_exists($circularIDKey, $arrayFolderName)){
                        //初出の回覧IDkey
                        $title = preg_replace('/[\t]/', '', $dl_proc_wait_data->title);
                        $title = CommonUtils::changeSymbols(trim($title, ' ') ? $title : $dl_proc_wait_data->file_name);
                        $title = mb_substr($title, 0, AppUtils::MAX_TITLE_LETTERS);
                        $updateName = $title;
                        $loopCount = 0;
                        while(in_array($updateName, $arrayFolderName, true)){
                            //フォルダ名重複時
                            $loopCount++;
                            $updateName = $title . ' (' . $loopCount . ') ';
                        }
                        $arrayFolderName[$circularIDKey] = $updateName;
                        $zip->addEmptyDir($updateName);
                    }
                }
                $arrayFileName = [];//value="ファイルパス(フォルダ名)/決定されたファイル名(拡張子除く)"
                foreach ($dl_proc_wait_datas as $dl_proc_wait_data){
                    //ファイル保存
                    $circularIDKey = $dl_proc_wait_data->circular_id ?: 0;//回覧ID判別用(ID==false時0)
                    $title = mb_substr($dl_proc_wait_data->file_name, mb_strrpos($dl_proc_wait_data->file_name, '/'));
                    $title = mb_substr($title, 0, mb_strrpos($dl_proc_wait_data->file_name, '.'));
                    $updateName = $arrayFolderName[$circularIDKey] . '/' . $title;
                    $loopCount = 0;
                    while(in_array($updateName, $arrayFileName, true)){
                        //ファイル名重複時
                        $loopCount++;
                        $updateName = $arrayFolderName[$circularIDKey] . '/' . $title . ' (' . $loopCount . ') ';
                    }
                    $arrayFileName[] = $updateName;
                    $documentData = AppUtils::decrypt($dl_proc_wait_data->document_data);
                    $zip->addFromString($updateName . '.pdf', base64_decode($documentData));
                }
                if (!$zip->close()) {
                    throw new \Exception(__('message.false.download_request.zip_create'));
                }
            }

            if ($dl_count != 1 && !file_exists($path)) {
                if (file_exists($path)) {
                    throw new \Exception(__('message.false.download_request.compress_e', ['attribute' => $dl_count, 'path' => $path]));
                } else {
                    throw new \Exception(__('message.false.download_request.compress_n', ['attribute' => $dl_count, 'path' => $path]));
                }
            }

            DB::table('download_proc_wait_data')
                ->where('download_request_id', $download_req_id)
                ->update([
                    'state' => DownloadUtils::PROC_PROCESS_END,
                ]);

            // ZIPファイルはそのまま、ファイル単体はデコード
            if ($dl_count > 1) {
                $data = \file_get_contents($path);
            }else{
                $data = base64_decode($data);
            }
            // notify
            $user = DB::table('mst_admin')
                ->where('id', $dl_req->mst_user_id)
                ->first();
            $admin = CompanyAdmin::find($user->id);
            $email_data = [
                'file_name' => $dl_req->file_name,
                'dl_period' => $dl_req->download_period
            ];

            // 無害化サーバで無害化処理するか
            $isSanitizing = DB::table('mst_company')->where('id', $dl_req->mst_company_id)->first()->sanitizing_flg;
            // 無害化サーバ経由時はここでは通知無し
            if($isSanitizing != 1) {
                MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                    $admin->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['SEND_DOWNLOAD_RESERVE_COMPLETED']['CODE'],
                    // パラメータ
                    json_encode($email_data,JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_ADMIN,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.SendDownloadReserveCompletedMail.subject'),
                    // メールボディ
                    trans('mail.SendDownloadReserveCompletedMail.body', $email_data)
                );
            }

            return $data;

        } catch (\Exception $e) {
            // キューリトライ
            DB::table('download_request')
                ->where('id', $download_req_id)
                ->update([
                    'state' => DownloadUtils::REQUEST_PROCESS_WAIT
                ]);
            Log::error($e->getMessage());
            return null;
        }
    }

    /**
     * ダウンロードファイル生成用のデータをダウンロード待ちデータテーブルに保存
     *
     * @param $user
     * @param $cids
     * @param $reqFileName
     * @param $finishedDate
     * @param $longtermFlg
     * @param $download_req_id
     */
    public static function setDownloadWaitData($user, $ids, $cids, $finishedDate, $checkHistory, $longtermFlg, $download_req_id, $cid, $did, $is_sanitizing, $fileName){
        try {
            ini_set('memory_limit','1024M');
            // 再ダウンロード処理対策
            DownloadProcWaitData::where('download_request_id', $download_req_id)->delete();

            $now_db_timezone = DB::select("SELECT CURRENT_TIMESTAMP")[0]->CURRENT_TIMESTAMP;
            $limit = DB::table('mst_constraints')
                        ->where('mst_company_id', $user->mst_company_id)
                        ->select('dl_max_keep_days', 'dl_request_limit', 'dl_file_total_size_limit')
                        ->first();
            // 長期保存以外の場合
            if ($longtermFlg == '0') {
                $circular_docs = DB::table("circular_document$finishedDate")
                    ->where(function ($query) use ($user) {
                        $query->where('confidential_flg', DB::raw('1'))
                            ->where('create_company_id', $user->mst_company_id)
                            ->orWhere('confidential_flg', DB::raw('0'));
                    })
                    // PAC_5-2853 S
                    ->where(function ($query) use ($is_sanitizing, $cids, $cid, $did) {
                        if($is_sanitizing && $cid && $did){
                            $query->where('circular_id', '=', $cid)
                                ->where('id', '=', $did);
                        }else{
                            $query->whereIn('circular_id', $cids);
                        }
                    })
                    // PAC_5-2853 E
                    ->whereIn('origin_document_id', ['-1', '0'])
                    ->select('id', 'circular_id', 'file_name')
                    ->get()->keyBy('id');

            // mysql最長クエリ
            $max_allowed_packet = DB::select("show variables like 'max_allowed_packet'")[0]->Value;

            $document_datas = array();
            // 選択文書総容量
             $doc_size = DB::table("circular_document$finishedDate")->select(DB::raw('sum(file_size) as file_size'))
                 ->whereIn('id',$circular_docs->keys())->value('file_size');

             if ($doc_size * DownloadUtils::MULTIPLE_SIZE > $max_allowed_packet) {
                 return response()->json(['status' => false,
                     'message' => [__('message.warning.download_request.order_size_max')]]);
             }
             $document_data = DB::table("document_data$finishedDate")
                 ->whereIn('circular_document_id', $circular_docs->keys())
                 ->select('id', 'circular_document_id', 'file_data')
                 ->get()
                 ->keyBy('circular_document_id')
                 ->toArray();

             foreach ($document_data as $key => $item) {
                $document_datas[$key] = $item;
             }
             unset($item);
             unset($document_data);

            if (count($document_datas) === 0) {
                return response()->json(['status' => false,
                    'message' => [__('message.false.download_request.file_detail_get')]]);
            }
            // 件名，最終更新日
            $circulars = DB::table("circular$finishedDate as c")
                ->leftJoin("circular_user$finishedDate as cu", function ($join) {
                    $join->on('c.id', 'cu.circular_id')
                        ->on('parent_send_order', DB::raw('0'))
                        ->on('child_send_order', DB::raw('0'));
                })
                ->whereIn('c.id', $cids)
                ->select('c.id', 'c.update_at', 'cu.title','c.circular_status','c.completed_date')
                ->get()
                ->keyBy('id');

            // 履歴取得
            if ($checkHistory) {
                $stampApiClient = UserApiUtils::getStampApiClient();
                $circular_edition_flg = config('app.pac_contract_app');
                $circular_env_flg = config('app.pac_app_env');
                $circular_server_flg = config('app.pac_contract_server');
                foreach ($circular_docs as $circular_doc) {
                    $resultBody = CircularDocumentUtils::getHistory($circular_doc->circular_id, $circular_doc->id, $user->mst_company_id, $circular_edition_flg, $circular_env_flg, $circular_server_flg, $finishedDate, $checkHistory);
                    if ($resultBody['status']) {
                        $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
                        $hasSignature = $company->esigned_flg == 1;

                        $resultC = $stampApiClient->post("signatureAndImpress", [
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
                                        'usingTas' => 0
                                    ],
                                ],
                            ]
                        ]);

                        $resData = json_decode((string)$resultC->getBody());
                        if ($resData->data) {
                            $document_datas[$circular_doc->id]->file_data = AppUtils::encrypt($resData->data[0]->pdf_data);
                        }
                        unset($resData);
                        unset($resultBody);
                    } else {
                        Log::error('Log getCircularDoc: ' . $circular_doc->file_name);
                        return Response::json(['status' => false, 'message' => $circular_doc->file_name . "の履歴を取得することが失敗です。", 'data' => null], 500);
                    }
                }
            }

            DB::beginTransaction();

                $count = 1;
                // Download Process Wait Data
                foreach ($document_datas as $document_data) {
                    $cir_doc_id = $document_data->circular_document_id;
                    if (!isset($circular_docs[$cir_doc_id])) continue;
                    $circular_document = $circular_docs[$cir_doc_id];
                    $size = AppUtils::getFileSize($document_data->file_data);
                    $time=$circulars[$circular_document->circular_id]->update_at;
                    if(in_array($circulars[$circular_document->circular_id]->circular_status,[CircularUtils::CIRCULAR_COMPLETED_STATUS,CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS])){
                        $time=$circulars[$circular_document->circular_id]->completed_date;
                    }
                    DB::table('download_proc_wait_data')->insert([
                        'state' => 0,
                        'download_request_id' => $download_req_id,
                        'num' => $count,
                        'circular_document_id' => $circular_document->id,
                        'document_data_id' => $document_data->id,
                        'document_data' => $document_data->file_data,
                        'create_at' => Carbon::now(),
                        'create_user' => $user->id,
                        'circular_id' => $circular_document->circular_id,
                        'file_name' => $circular_document->file_name,
                        'title' => $circulars[$circular_document->circular_id]->title,
                        'circular_update_at' => $time,
                        'file_size' => $size,
                    ]);
                    $count++;
                }
                unset($document_data);
                unset($document_datas);
            }else{
                // 長期保存の場合
                $doc_size = array_sum(
                    array_column(
                        DB::table('long_term_document')
                            ->whereIn('id', $ids)
                            ->where('mst_company_id', $user->mst_company_id)
                            ->select(DB::raw('file_size'))->get()->toArray(),
                        'file_size'
                    )
                );
                // mysql最長クエリ
                $max_allowed_packet = DB::select("show variables like 'max_allowed_packet'")[0]->Value;
                if ($doc_size * DownloadUtils::MULTIPLE_SIZE > $max_allowed_packet) {
                    return response()->json(['status' => false,
                        'message' => [__('message.warning.download_request.order_size_max')]]);
                }

                // long_term_document 情報取得
                $long_term_document_datas = DB::table('long_term_document')
                    // PAC_5-2853 S
                    ->where(function ($query) use ($is_sanitizing, $ids, $cid, $did) {
                        if($is_sanitizing && $cid && $did){
                            $query->where('circular_id', '=', $cid)
                                ->where('id', '=', $did);
                        }else{
                            $query->whereIn('id', $ids);
                        }
                    })
                    // PAC_5-2853 E
                    ->where('mst_company_id', $user->mst_company_id)
                    ->select('id', 'circular_id', 'file_name', 'file_size','upload_id','upload_status','completed_at','title','create_at')
                    ->get()
                    ->keyBy('id');
                // 0件の場合、取得失敗
                if (count($long_term_document_datas) === 0) {
                    return response()->json(['status' => false,
                        'message' => [__('message.false.download_request.file_detail_get')]]);
                }

                DB::beginTransaction();
                $type=Storage::disk('s3');
                if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_K5){
                    $type= Storage::disk('k5');
                }
                $count = 0;
                $input=[];
                $newDocument=[];
                foreach ($long_term_document_datas as $k=>$circular){
//                    $longTermDocumentData = DB::table('long_term_document')
//                        ->where('circular_id', $circular->circular_id)
//                        ->where('mst_company_id', $user->mst_company_id)
//                        ->where('upload_id',$circular->upload_id)
//                        ->select(['id','circular_id','mst_company_id','file_name','file_size','completed_at','title','create_at'])
//                        ->first();
                    $s3path=config('filesystems.prefix_path') . '/' .config('app.s3_storage_root_folder') . '/' . config('app.pac_app_env') . '/' . config('app.pac_contract_app')
                        . '/' . config('app.pac_contract_server') . '/' . $user->mst_company_id.'/';
                    $path =  $s3path . (($circular->upload_status==1)?('upload_'.$circular->upload_id):$circular->circular_id);
                    // S3サーバのフォルダパス編集
                    $arrAllCurrentDocumentData = DB::table("long_term_circular_operation_history")
                        ->where("circular_document_id", '>', 0)
                        ->where('circular_id', $circular->circular_id)
                        ->groupBy("circular_document_id", 'file_name', 'file_size')
                        ->select("circular_document_id", 'file_name', 'file_size')
                        ->get();
                    $arrFileNameCount = [];
                    if(($arrAllCurrentDocumentData->isEmpty()) && isset($circular->upload_id) && $circular->upload_id > 0){
                        $objFindUpData = DB::table('upload_data')->where("id",$circular->upload_id)->first();

                        if(empty($objFindUpData)){
                            continue;
                        }
                        $newDocument[$count] = [
                            'file_name' => $circular->file_name,
                            'circular_id' => $circular->circular_id,
                            'num' => $count,
                            'title' => $circular->title,
                            'create_at' => $circular->create_at,
                            'file_size' => $circular->file_size,
                            'file_data' => AppUtils::decrypt($objFindUpData->upload_data),
                            'id' => $k,
                            'document_id' => 0,
                        ];
                        $checkHistory = false;
                        $count++;
                    }
                    foreach ($arrAllCurrentDocumentData as $aacddKey => $aacddVal) {
                        $arrFileNameCount[$aacddVal->file_name] = isset($arrFileNameCount[$aacddVal->file_name]) ? $arrFileNameCount[$aacddVal->file_name] + 1 : 0;
                        if($arrFileNameCount[$aacddVal->file_name] > 0){
                            $aacddVal->file_name = basename($aacddVal->file_name,'.pdf') .' ('.$arrFileNameCount[$aacddVal->file_name].') .pdf';
                        }
                        $strFilePath = ($path . '/' . $aacddVal->file_name);
                        if ($type->exists($strFilePath)) {
                            $file_content = $type->get($strFilePath);
                            $newDocument[$count] = [
                                'file_name' => $aacddVal->file_name,
                                'circular_id' => $circular->circular_id,
                                'num' => $count,
                                'title' => $circular->title,
                                'create_at' => $circular->create_at,
                                'file_size' => $aacddVal->file_size,
                                'file_data' => base64_encode($file_content),
                                'id' => $k,
                                'document_id' => $aacddVal->circular_document_id,
                            ];
                            $count++;
                        }
                    }
                }
                if ($checkHistory) {
                    $stampApiClient = UserApiUtils::getStampApiClient();
                    $circular_edition_flg = config('app.pac_contract_app');
                    $circular_env_flg = config('app.pac_app_env');
                    $circular_server_flg = config('app.pac_contract_server');
                    foreach ($newDocument as $key => $long_term_document) {
                        $resultBody = CircularDocumentUtils::getLongTermHistory(
                            $long_term_document['id'],
                            $user->mst_company_id,
                            $circular_edition_flg,
                            $circular_env_flg,
                            $circular_server_flg,
                            $checkHistory,
                            $long_term_document['file_name'],
                            $long_term_document,
                            $finishedDate
                        );
                        if ($resultBody['status']) {
                            $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
                            $hasSignature = $company->esigned_flg == 1;
                            $resultC = $stampApiClient->post("signatureAndImpress", [
                                RequestOptions::JSON => [
                                    'signature' => $hasSignature,
                                    'signatureKeyFile' => null,
                                    'signatureKeyPassword' => null,
                                    'data' => [
                                        [
                                            'circular_document_id' => $key,
                                            'pdf_data' => base64_encode($resultBody['circular_document']->file_data),
                                            'append_pdf_data' => $resultBody['circular_document']->append_pdf,
                                            'stamps' => [],
                                            'texts' => [],
                                            'usingTas' => 0
                                        ],
                                    ],
                                ]
                            ]);
                            $resData = json_decode((string)$resultC->getBody());
                            if ($resData->data) {
                                $newDocument[$key]['file_data'] =  $resData->data[0]->pdf_data;
                            }
                            unset($resData);
                            unset($resultBody);
                        } else {
                            Log::error('Log getCircularDoc: ' . $long_term_document['file_name']);
                            return Response::json(['status' => false, 'message' => $long_term_document['file_name'] . "の履歴を取得することが失敗です。", 'data' => null], 500);
                        }
                    }
                }
                $newCount=1;
                foreach ($newDocument as $document){
                    $input[]=[
                        'state' => 0,
                        'download_request_id' => $download_req_id,
                        'num' => $newCount,
                        'circular_document_id' => $newCount,
                        'document_data_id' => $newCount,
                        'document_data' =>AppUtils::encrypt($document['file_data']),
                        'create_at' => Carbon::now(),
                        'create_user' => $user->id,
                        'circular_id' => $document['circular_id'],
                        'file_name' => $document['file_name'],
                        'title' => $document['title'],
                        'circular_update_at' => $document['create_at'],
                        'file_size' => $document['file_size'],
                    ];
                    $newCount++;
                }
                DB::table('download_proc_wait_data')->insert($input);
                unset($newDocument);
                unset($input);
            }

            DB::commit();



        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
            Log::error($e->getPrevious());
            return false;
        }
        return true;
    }


    public static  function getLongTermData($ids) :array
    {
        $long_term_document_datas=DB::table('long_term_document')
            ->whereIn('id',$ids)
            ->select('id','file_name','circular_id','upload_status','upload_id')
            ->get();
        $cids=[];
        $upload_id=[];
        foreach ($long_term_document_datas as $long_term_document_data){
            if(!$long_term_document_data->upload_status){
                array_push($cids,$long_term_document_data->circular_id);
            }else{
                array_push($upload_id,$long_term_document_data->upload_id);
            }
        }
        return [$long_term_document_datas,$cids,$upload_id];
    }
    public static function getDownloadAttachment($user, $param, $dl_request_id)
    {
        try {
            $document = $param['detail'];
            $result = self::setDownloadAttachment($user,$param,$dl_request_id);
            if($result==false){
                return null;
            }
            // ダウンロード要求情報取得
            $dl_req = DB::table('download_request')
                ->where('id', $dl_request_id)->first();

            if (!$dl_req) {
                Log::error('この申請データは存在しない。download_request_id:'.$dl_request_id);
                return;
            }

            // 回覧文書ID
            $dl_proc_wait_datas = DB::table('download_proc_wait_data')
                ->where('download_request_id', $dl_request_id)
                ->select('circular_document_id', 'circular_id', 'file_name', 'document_data')->get();
            // 申請者情報
            $user_info = DB::table('mst_user')
                ->where('id', $dl_req->mst_user_id)
                ->select(['id', 'email', 'mst_company_id'])
                ->first();
            $zipPath = storage_path("app")."/download-" . AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) . ".zip";
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) == false) {
                throw new \Exception(__('message.false.download_request.zip_create'));
            }
            foreach ($dl_proc_wait_datas as $val){
                $zip->addEmptyDir($document['circular_id']);
                $zip->addFromString($document['circular_id'] . '/' . $val->file_name, AppUtils::decrypt($val->document_data));

            }

            if (!$zip->close()) {
                throw new \Exception(__('message.false.download_request.zip_create'));
            }
            if ($dl_proc_wait_datas->count() != 1 && !file_exists($zipPath)) {
                if (file_exists($zipPath)) {
                    throw new \Exception(__('message.false.download_request.compress_e', ['attribute' => $dl_proc_wait_datas->count(), 'path' => $zipPath]));
                } else {
                    throw new \Exception(__('message.false.download_request.compress_n', ['attribute' => $dl_proc_wait_datas->count(), 'path' => $zipPath]));
                }
            }
            // 無害化サーバで無害化処理するか
            $isSanitizing = DB::table('mst_company')
                ->where('id', $dl_req->mst_company_id)->first()
                ->sanitizing_flg;

            DB::table('download_proc_wait_data')
                ->where('download_request_id', $dl_request_id)
                ->update([
                    'state' => DownloadUtils::PROC_PROCESS_END,
                ]);
            $admin = CompanyAdmin::find($user->id);
            $data = file_get_contents($zipPath);
            // 完了お知らせ
            // 無害化サーバ経由時はここでは通知無し
            if($isSanitizing != 1) {
                $email_data = [
                    'file_name' => $dl_req->file_name,
                    'dl_period' => $dl_req->download_period
                ];
                MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                    $admin->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['USER_SEND_DOWNLOAD_RESERVE_COMPLETED']['CODE'],
                    // パラメータ
                    json_encode($email_data,JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_USER,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.SendDownloadReserveCompletedMail.subject'),
                    // メールボディ
                    trans('mail.SendDownloadReserveCompletedMail.body', $email_data)
                );
            }
            return $data;
        }catch (\Exception $e){
            DB::table('download_request')
                ->where('id', $dl_request_id)
                ->update([
                    'state' => DownloadUtils::REQUEST_PROCESS_WAIT
                ]);
            Log::error($e->getMessage() . $e->getTraceAsString());
            return null;
        }
    }

    public static function setDownloadAttachment($user, $param, $dl_request_id)
    {
        try {
            $document = $param['detail'];
            $circularIds = DB::table('long_term_document')
                ->where('id', $document['id'])
                ->where('mst_company_id', $user->mst_company_id)
                ->pluck('circular_attachment_json','circular_id')
                ->toArray();
            $intCircularID = DB::table('long_term_document')
                ->where('id', $document['id'])
                ->where('mst_company_id', $user->mst_company_id)
                ->value("circular_id");
            $count = 1;
            $input=[];
            foreach ($circularIds as $k=>$v){
                $v=json_decode($v);
                if(!empty($v)){
                    array_walk_recursive($v,function($val, $key) use(&$input,$user,$v,$k,$dl_request_id,&$count){
                        if((isset($val->company_id,$val->confidential_flg)) && !(($val->company_id == $user->mst_company_id )||($val->confidential_flg == 0))){unset($v[$key]);}

                        if(isset($val->type)){
                            $file_content = Storage::disk($val->type)->get($val->server_url);
                        }else{
                            if(!self::handlerAttachment($k,$user->mst_company_id,$val->server_url)){
                                return ;
                            }
                            if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_AWS){
                                $file_content =Storage::disk('s3')->get($val->server_url);
                            }else if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_K5){
                                $file_content = Storage::disk('k5')->get($val->server_url);
                            }
                        }
                        $val->file_content = $file_content;
                        $size = $val->file_size;
                        $input[]=[
                            'state' => 0,
                            'download_request_id' => $dl_request_id,
                            'num' => $count,
                            'circular_document_id' => $k,
                            'document_data_id' => $k,
                            'document_data' => AppUtils::encrypt($file_content),
                            'create_at' => Carbon::now(),
                            'create_user' => $user->id,
                            'circular_id' => $k,
                            'file_name' => $val->file_name,
                            'title' => '',
                            'circular_update_at' =>Carbon::now(),
                            'file_size' => $size
                        ];
                        $count++;
                    });
                }
            }
            DB::table('download_proc_wait_data')->insert($input);
            return true;
        }catch (\Exception $e){
            Log::error($e->getMessage() . $e->getTraceAsString());
            return  false;
        }
    }
    public static function handlerAttachment($intCircularID,$intCompanyID,$server_url)
    {
        $objAttachment = DB::table("circular_attachment")->where("circular_id",$intCircularID)->where("server_url",$server_url)->where("status",1)->first();
        if(empty($objAttachment)){
            return false;
        }
        if($objAttachment->confidential_flg == 1  && $objAttachment->create_company_id != $intCompanyID){
            return false;
        }
        return true;
    }

}
