<?php

namespace App\Http\Utils;

use Carbon\Traits\Creator;
use DB;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use App\Jobs\MakeDownloadData;
use Illuminate\Support\Facades\Storage;

/**
 * ダウンロード要求処理
 * Class DownloadRequestUtils
 * @package App\Http\Utils
 */
class DownloadRequestUtils
{
    // 削除
    const DELETE_STATE = 9;
    // 期限切れ
    const EXPIRED_STATE = 10;

    /* download_request状態 */
    const REQUEST_PROCESS_WAIT = 0;                     //  0：処理待ち
    const REQUEST_CREATING = 1;                         //  1：作成中
    const REQUEST_DOWNLOAD_WAIT = 2;                    //  2：ダウンロード待ち
    const REQUEST_REQUEST_DOWNLOAD_COMPLETE = 3;        //  3：ダウンロード完了
    const REQUEST_DOWNLOAD_END = 4;                     //  4：ダウンロード済み
    const REQUEST_DELETED = 9;                          //  9：削除
    const REQUEST_EXPIRED = 10;                         // 10：期限切れ
    const REQUEST_SANITIZING_WAIT = 11;                 // 11：無害化待ち
    const REQUEST_SANITIZING_PROC = 12;                 // 12：無害化中
    const REQUEST_SANITIZING_GETTING = 13;              // 13：データ取得中

    /* download_proc_wait_data状態 */
    const PROC_PROCESS_WAIT = 0;                        //０：処理待ち
    const PROC_PROCESS_END = 1;                         //１：処理済み

    /**
     * ダウンロード予約処理
     *
     * @param $cids
     * @param $reqFileName
     * @param $finishedDate
     * @return \Illuminate\Http\JsonResponse
     */
    public static function reserveLongTermDocumentDownload($cids, $reqFileName)
    {
        try {
            $user = \Auth::user();

            // 長期保存の場合
            $doc_size = array_sum(
                array_column(
                    DB::table('long_term_document')
                        ->whereIn('circular_id', $cids)
                        ->where('mst_company_id', $user->mst_company_id)
                        ->select(DB::raw('file_size'))->get()->toArray(),
                    'file_size'
                )
            );
            // mysql最長クエリ
            $max_allowed_packet = DB::select("show variables like 'max_allowed_packet'")[0]->Value;
            if ($doc_size * DownloadUtils::MULTIPLE_SIZE > $max_allowed_packet) {
                return response()->json(['status' => false,
                    'message' => __('message.warning.download_request.order_size_max')]);
            }

            // long_term_document 情報取得
            $long_term_document_datas = DB::table('long_term_document')
                ->whereIn('circular_id', $cids)
                ->where('mst_company_id', $user->mst_company_id)
                ->select('id', 'circular_id', 'file_name', 'file_size')
                ->get();
            // 0件の場合、取得失敗
            if (count($long_term_document_datas) === 0) {
                return response()->json(['status' => false,
                    'message' => __('message.false.download_request.file_detail_get')]);
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

            // If InputData, FileName is changed
            if ($reqFileName != '') {
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                // No Extension
                $ext = $ext == "" ? "" : '.' . $ext;
                $fileName = $reqFileName . $ext;
            }

            // ダウンロード処理登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadRequestUtils', 'getLongTermDocumentDownloadData', $fileName,
                $cids
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                'message' => __('message.false.download_request.download_ordered', ['attribute' => $result])]);
            }

            return response()->json(['status' => true,
                'message' => __("message.success.download_request.download_ordered", ['attribute' => $fileName])]);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => __('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]);
        }
    }

    /**
     * 非同期ダウンロード用長期保存済み文書ダウンロードファイル取得
     *
     * @param $cids
     * @param $download_req_id
     * @return string
     */
    public static function getLongTermDocumentDownloadData($cids, $dl_request_id){

        DownloadRequestUtild::setLongTermDocumentDataForDownloadProcWaitData($cids, $dl_request_id);

        // ダウンロード要求情報取得
        $dl_req = DB::table('download_request')
            ->where('id', $dl_request_id)->first();

        if (!$dl_req) {
            Log::error('この申請データは存在しない。download_request_id:'.$dl_request_id);
            return;
        }

        // 状態更新 ( 処理待ち:0 => 作成中:1)
        DB::table('download_request')
            ->where('id', $dl_request_id)
            ->update([
                'state' => DownloadRequestUtils::REQUEST_CREATING
            ]);

        // 回覧文書ID 
        $dl_proc_wait_datas = DB::table('download_proc_wait_data')
            ->where('download_request_id', $dl_request_id)
            ->select('circular_document_id', 'circular_id', 'file_name', 'document_data')->get();

        // 回覧情報
        $circular_info = DB::table('download_proc_wait_data')
            ->where('download_request_id', $dl_request_id)
            ->select('circular_id', 'title', 'circular_update_at')
            ->groupBy('circular_id', 'title', 'circular_update_at')
            ->get()->keyBy('circular_id')->toArray();


        // 申請者情報
        $user_info = DB::table('mst_user')
            ->where('id', $dl_req->mst_user_id)
            ->select(['id', 'email', 'mst_company_id'])
            ->first();

        foreach ($dl_proc_wait_datas as $dl_proc_wait_data) {
            $cir_doc_ids_array[] = $dl_proc_wait_data->circular_document_id;
        }

        $path = '';
        try {
            // 文書一件の場合
            if ($dl_proc_wait_datas->count() === 1) {
                $data = AppUtils::decrypt($dl_proc_wait_datas->first()->document_data);
            } else {  // 文書複数件の場合
                $cirs = array();
                // 同一回覧内文書集合
                foreach ($dl_proc_wait_datas as $dl_proc_wait_data) {
                    $circular_id = $dl_proc_wait_data->circular_id;

                    $cirs[$circular_id][] = ['fileName' => $dl_proc_wait_data->file_name,
                        'data' => AppUtils::decrypt($dl_proc_wait_data->document_data)];
                }

                $path = sys_get_temp_dir()."/download-circular-" . AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $dl_req->mst_company_id, $dl_req->id) . ".zip";

                $zip = new \ZipArchive();
                if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) == false) {
                    throw new \Exception(__('message.false.download_request.zip_create'));
                }

                $cir_ids = array_keys($cirs);
                $countFolderName = [];
                foreach ($cir_ids as $cir_id) {
                    // フォルダ名設定
                    // PAC_5-1973 Start
                    $circular_info[$cir_id]->title = preg_replace('/[\t]/', '', $circular_info[$cir_id]->title);
                    // PAC_5-1973 End
                    $title = CommonUtils::changeSymbols(trim($circular_info[$cir_id]->title, ' ') ? $circular_info[$cir_id]->title : reset($cirs[$cir_id])['fileName']);

                    $circular_data = DB::table('circular')
                        ->where('id', $circular_info[$cir_id]->circular_id)
                        ->first();

                    $folder_name = $title . '_' . date("YmdHis", strtotime($circular_data->completed_date));
                    // フォルダ名重複の場合
                    if (key_exists($folder_name, $countFolderName)) {
                        // フォルダ名重複数を統計
                        $countFolderName[$folder_name]++;
                        $folder_name .= $folder_name . ' (' . $countFolderName[$folder_name] . ') ';
                    } else {
                        $countFolderName[$folder_name] = 0;
                    }
                    $zip->addEmptyDir($folder_name);

                    $countFilename = [];
                    foreach ($cirs[$cir_id] as $doc) {

                        $filename = mb_substr($doc['fileName'], mb_strrpos($doc['fileName'], '/'));
                        $filename = mb_substr($filename, 0, mb_strrpos($doc['fileName'], '.'));
                        // ファイル名重複の場合
                        if (key_exists($filename, $countFilename)) {
                            $countFilename[$filename]++;
                            $filename = $filename . ' (' . $countFilename[$filename] . ') ';
                        } else {
                            $countFilename[$filename] = 0;
                        }
                        $zip->addFromString($folder_name . '/' . $filename . '.pdf', base64_decode($doc['data']));
                    }
                }
                if (!$zip->close()) {
                    throw new \Exception(__('message.false.download_request.zip_create'));
                }
            }

            if ($dl_proc_wait_datas->count() != 1 && !file_exists($path)) {
                if (file_exists($path)) {
                    throw new \Exception(__('message.false.download_request.compress_e', ['attribute' => $dl_proc_wait_datas->count(), 'path' => $path]));
                } else {
                    throw new \Exception(__('message.false.download_request.compress_n', ['attribute' => $dl_proc_wait_datas->count(), 'path' => $path]));
                }
            }

            DB::table('download_proc_wait_data')
                ->where('download_request_id', $dl_request_id)
                ->update([
                    'state' => DownloadRequestUtils::PROC_PROCESS_END,
                ]);

            if ($dl_proc_wait_datas->count() > 1) {
                $data = \file_get_contents($path);
            }else{
                $data = base64_decode($data);
            }

            // 無害化サーバで無害化処理するか
            $isSanitizing = DB::table('mst_company')
                        ->where('id', $dl_req->mst_company_id)->first()
                        ->sanitizing_flg;
            // 完了お知らせ
            // 無害化サーバ経由時はここでは通知無し
            if($isSanitizing != 1) {
                $email_data = [
                    'file_name' => $dl_req->file_name,
                    'dl_period' => $dl_req->download_period
                ];
                
                MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                    $user_info->email,
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

        } catch (\Exception $e) {
            // リトライ
            DB::table('download_request')
                ->where('id', $dl_request_id)
                ->update([
                    'state' => DownloadRequestUtils::REQUEST_PROCESS_WAIT
                ]);
            throw $e;
        }
    }

    /**
     * 非同期ダウンロード用長期保存済み文書ダウンロードファイル用データをダウンロード待ちデータテーブルに保存
     *
     * @param $cids
     * @param $download_req_id
     */
    public static function setLongTermDocumentDataForDownloadProcWaitData($cids, $dl_request_id){
        $user = \Auth::user();

        // 長期保存の場合
        $doc_size = array_sum(
            array_column(
                DB::table('long_term_document')
                    ->whereIn('circular_id', $cids)
                    ->where('mst_company_id', $user->mst_company_id)
                    ->select(DB::raw('file_size'))->get()->toArray(),
                'file_size'
            )
        );
        // mysql最長クエリ
        $max_allowed_packet = DB::select("show variables like 'max_allowed_packet'")[0]->Value;
        if ($doc_size * DownloadUtils::MULTIPLE_SIZE > $max_allowed_packet) {
            return response()->json(['status' => false,
                'message' => __('message.warning.download_request.order_size_max')]);
        }

        // long_term_document 情報取得
        $long_term_document_datas = DB::table('long_term_document')
            ->whereIn('circular_id', $cids)
            ->where('mst_company_id', $user->mst_company_id)
            ->select('id', 'circular_id', 'file_name', 'file_size')
            ->get();
        // 0件の場合、取得失敗
        if (count($long_term_document_datas) === 0) {
            return response()->json(['status' => false,
                'message' => __('message.false.download_request.file_detail_get')]);
        }

        DB::beginTransaction();

        try {
            $count = 1;
            foreach ($cids as $circularId){

                $longTermDocumentData = DB::table('long_term_document')
                    ->where('circular_id', $circularId)
                    ->where('mst_company_id', $user->mst_company_id)
                    ->select(['id','circular_id','mst_company_id','file_name','file_size','completed_at','title','create_at'])
                    ->first();

                // DBに存在する場合、
                if ($longTermDocumentData){
                    // S3サーバのフォルダパス編集
                    $path = config('filesystems.prefix_path') . '/' . config('app.s3_storage_root_folder') . '/' . config('app.server_env') . '/' . config('app.edition_flg')
                        . '/' . config('app.server_flg') . '/'. $user->mst_company_id . '/' . $circularId;
                    // 存在する場合
                    if ( Storage::disk('s3')->exists($path)){
                        // フォルダパス下のファイルを取得して、
                        $file_names = Storage::disk('s3')->files($path);
                        foreach($file_names as $file_name){

                            $file_content = Storage::disk('s3')->get($file_name);
                            $startIndex = strrchr($file_name,'/');
                            $single_file_name = str_replace('/','',$startIndex);

                            DB::table('download_proc_wait_data')->insert([
                                'state' => 0,
                                'download_request_id' => $dl_request_id,
                                'num' => $count,
                                'circular_document_id' => $count,//long_term_documnetにがない、かつnullが設定できないため、仮設定（影響なし）
                                'document_data_id' => $count,//同上
                                'document_data' => AppUtils::encrypt(base64_encode($file_content)),
                                'create_at' => Carbon::now(),
                                'create_user' => $user->id,
                                'circular_id' => $circularId,
                                'file_name' => $single_file_name,
                                'title' => $longTermDocumentData->title,
                                'circular_update_at' => $longTermDocumentData->create_at,
                            ]);
                            $count++;

                        }
                    }
                }
            }

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage() . $th->getTraceAsString());
            return response()->json(['status' => false,
                'message' => __('message.false.download_request.download_ordered', ['attribute' => $th->getMessage()])]);
        }
    }

    /**
     * ダウンロード要求に付随するデータのみを削除
     */
    public static function RemoveRequestData($id, $user)
    {
        DB::beginTransaction();
        try {
            DB::table('download_request')
                ->where('mst_user_id', $user->id)
                ->where('user_auth', $user->isAuditUser() ? AppUtils::AUTH_FLG_AUDIT : AppUtils::AUTH_FLG_USER)
                ->where('id', $id)
                ->update([
                    'state' => DownloadRequestUtils::DELETE_STATE,
                ]);
            DB::table('download_proc_wait_data')
                ->where('download_request_id', $id)
                ->update([
                    'document_data' => null,
                    'file_size' => 0,
                ]);
            DB::table('download_wait_data')
                ->where('download_request_id', $id)
                ->update([
                    'data' => null,
                    'update_at' => Carbon::now(),
                    'file_size' => 0,
                ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e);
        }
    }

    /**
     * 要求の中から期限切れのものを更新
     * 要求自体は削除しない
     */
    public static function RemoveExpiredRequestData($user)
    {
        $period_request = DB::table('download_request')
            ->where('mst_user_id', $user->id)
            ->where('user_auth', $user->isAuditUser() ? AppUtils::AUTH_FLG_AUDIT : AppUtils::AUTH_FLG_USER)
            ->where('download_period', '<=', Carbon::now())
            ->where('state', '!=', DownloadRequestUtils::DELETE_STATE);

        if (!$period_request) {
            return;
        }

        DB::beginTransaction();
        try {
            $period_request
                ->update([
                    'state' => DownloadRequestUtils::EXPIRED_STATE,
                ]);

            $download_req_ids = [];
            foreach ($period_request->get() as $period_request_row) {
                $download_req_ids[] = $period_request_row->id;
            }
            DB::table('download_proc_wait_data')
                ->wherein('download_request_id', $download_req_ids)
                ->update([
                    'document_data' => null,
                    'file_size' => 0,
                ]);
            DB::table('download_wait_data')
                ->wherein('download_request_id', $download_req_ids)
                ->update([
                    'data' => null,
                    'update_at' => Carbon::now(),
                    'file_size' => 0,
                ]);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw new \Exception($ex);
        }
    }

    /**
     * ステータスが削除 且つ 要求が一時間以上過ぎた ダウンロード要求を削除
     */
    public static function RemoveRequestAnHourAgoAndDeleteState($user)
    {
        $now_db_timezone = DB::select("SELECT CURRENT_TIMESTAMP")[0]->CURRENT_TIMESTAMP;

        $remove_request = DB::table('download_request')
            ->where('mst_user_id', $user->id)
            ->where('user_auth', $user->isAuditUser() ? AppUtils::AUTH_FLG_AUDIT : AppUtils::AUTH_FLG_USER)
            ->where('state', DownloadRequestUtils::DELETE_STATE)
            ->where('request_date', '<', (new Carbon($now_db_timezone))->subHour());

        if (!$remove_request) {
            return;
        }

        $download_req_ids = [];
        foreach ($remove_request->get() as $remove_request_row) {
            $download_req_ids[] = $remove_request_row->id;
        }

        DB::beginTransaction();
        try {
            // ダウンロード要求
            DB::table('download_request')
                ->wherein('id', $download_req_ids)
                ->delete();
            // 圧縮用データ保管
            DB::table('download_proc_wait_data')
                ->wherein('download_request_id', $download_req_ids)
                ->delete();
            // ダウンロード待ちデータ
            DB::table('download_wait_data')
                ->wherein('download_request_id', $download_req_ids)
                ->delete();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw new \Exception($ex);
        }
    }
}