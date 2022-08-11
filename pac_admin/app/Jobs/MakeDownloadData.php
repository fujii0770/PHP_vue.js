<?php

namespace App\Jobs;

use App\Http\Utils\CommonUtils;
use App\Http\Utils\DownloadUtils;
use App\Http\Utils\MailUtils;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use DB;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Carbon;
use App\CompanyAdmin;

class MakeDownloadData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $download_req_id = $this->id;
        Log::info('Run Make Download Data ( ' . $download_req_id . ' )');

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
            ->select('circular_document_id', 'circular_id', 'file_name', 'document_data')
            ->orderBy('circular_document_id')
            ->get();

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
            // 文書一件の場合
            if ($dl_proc_wait_datas->count() === 1) {
                $data = $dl_proc_wait_datas->first()->document_data;
                $size = AppUtils::getFileSize($data);
            } else {    // 文書複数件の場合
                $cirs = array();
                // 同一回覧内文書集合
                foreach ($dl_proc_wait_datas as $dl_proc_wait_data) {
                    $cirs[$dl_proc_wait_data->circular_id][] = ['fileName' => $dl_proc_wait_data->file_name,
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

                    $folder_name = $title . '_' . date("YmdHis", strtotime($circular_data->final_updated_date));
                    // フォルダ名重複の場合
                    if (key_exists($folder_name, $countFolderName)) {
                        // フォルダ名重複数を統計
                        $countFolderName[$folder_name]++;
                        $folder_name .= $folder_name . ' (' . $countFolderName[$folder_name] . ') ';
                    } else {
                        $countFolderName[$folder_name] = 0;
                    }
                    // フォルダを新規
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

            // 無害化サーバで無害化処理するか
            $isSanitizing = DB::table('mst_company')->where('id', $dl_req->mst_company_id)->first()
                                ->sanitizing_flg;
            if($isSanitizing == 1){
                // 状態更新 ( 作成中:1 => 無害化待ち:11)
                $state = DownloadUtils::REQUEST_SANITIZING_WAIT;
            }else{
                // 状態更新 ( 作成中:1 => ダウンロード待ち:2)
                $state = DownloadUtils::REQUEST_DOWNLOAD_WAIT;
            }

            DB::table('download_request')
                ->where('id', $download_req_id)
                ->update([
                    'state' => $state,
                    'contents_create_at' => Carbon::now()
                ]);

            DB::table('download_proc_wait_data')
                ->where('download_request_id', $download_req_id)
                ->update([
                    'state' => DownloadUtils::PROC_PROCESS_END,
                ]);

            if ($dl_proc_wait_datas->count() > 1) {
                $data = base64_encode(\file_get_contents($path));
                $size = filesize($path);
            }

            DB::table('download_wait_data')
                ->where('download_request_id', $this->id)
                ->update([
                    'data' => $data,
                    'update_at' => Carbon::now(),
                    'file_size' => $size,
                ]);

            // 完了お知らせ
            $data = [
                'file_name' => $dl_req->file_name,
                'dl_period' => $dl_req->download_period
            ];
            // notify
            $user = DB::table('mst_admin')
                ->where('id', $dl_req->mst_user_id)
                ->first();
            $admin = CompanyAdmin::find($user->id);
            $email_data = [
                'file_name' => $dl_req->file_name,
                'dl_period' => $dl_req->download_period
            ];
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

        } catch (\Exception $e) {
            // キューリトライ
            DB::table('download_request')
                ->where('id', $download_req_id)
                ->update([
                    'state' => DownloadUtils::REQUEST_PROCESS_WAIT
                ]);
            throw $e;
        }
    }

    public function failed(Exception $exception)
    {
        Log::error('ダウンロードjobs処理失敗しました。download_request_id:' . $this->id);
        Log::error($exception->getMessage() . $exception->getTraceAsString());
        DB::table('download_request')
            ->where('id', $this->id)
            ->update([
                'state' => -1
            ]);
        DB::table('download_proc_wait_data')
            ->where('download_request_id', $this->id)
            ->update([
                'state' => DownloadUtils::PROC_PROCESS_WAIT,
            ]);
    }
}
