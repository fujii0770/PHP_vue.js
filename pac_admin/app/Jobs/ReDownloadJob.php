<?php

namespace App\Jobs;

use App\Http\Utils\MailUtils;
use DB;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Http\Utils\AppUtils;
use App\Http\Utils\DownloadUtils;

use App\CompanyAdmin;
use App\Models\Constraint;
use App\Models\DownloadProcWaitData;
use App\Models\DownloadRequest;
use App\Models\DownloadWaitData;

class ReDownloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;                  // ログイン管理者情報
    private $file_name;             // ファイル名
    private $is_sanitizing;         // 無害化処理フラグ (mst_company.sanitizing_flg)
    private $class_path;            // ダウンロードファイル生成処理が宣言されているクラスのフルパス
    private $function_name;         // ダウンロードファイル生成処理関数名
    private $arguments;             // ダウンロードファイル生成処理関数の引数

    private $dl_request_id;         // ダウンロード要求ID

    /**
     * Create a new job instance.
     * @param CompanyAdmin $user 管理者情報
     * @param string $dl_request_id ダウンロード要求ID
     * @param bool $is_sanitizing 無害化の有無
     * @return void
     */
    public function __construct(CompanyAdmin $user, $dl_request_id, $is_sanitizing)
    {
        $this->user             = $user;
        $this->dl_request_id    = $dl_request_id;
        $this->is_sanitizing    = $is_sanitizing;

        $dl_request = DownloadRequest::where('id', $dl_request_id)
                                    ->where('mst_user_id', $user->id)
                                    ->first();

        if(!$dl_request){
            throw new Exception("指定したダウンロード要求データは存在しません");
        }

        $this->class_path       = $dl_request->class_path;
        $this->function_name    = $dl_request->function_name;
        $this->file_name        = $dl_request->file_name;
        $this->arguments        = unserialize($dl_request->arguments);

        Log::debug(  "[REDL_JOB] user_auth: "  .AppUtils::AUTH_FLG_ADMIN. 
                    ", user_id: "           .$user->id. 
                    ", class_path: "        .$dl_request->class_path. 
                    ", function_name: "     .$dl_request->function_name. 
                    ", is_sanitizing: "     .$is_sanitizing. 
                    ", file_name: "         .$dl_request->file_name
                );

        DB::beginTransaction();

        // ダウンロード期限
        $dl_max_keep_days = Constraint::where('mst_company_id', $user->mst_company_id)
                                        ->select('dl_max_keep_days')
                                        ->first()->dl_max_keep_days;

        $dl_request->request_date       = Carbon::now();
        $dl_request->contents_create_at = Carbon::now();
        $dl_request->download_period    = Carbon::now()->copy()->addDay($dl_max_keep_days);
        $dl_request->state              = DownloadUtils::REQUEST_PROCESS_WAIT;
        $dl_request->save();

        $dl_wait_data = DownloadWaitData::where('download_request_id', $dl_request_id)
                                    ->first();
        $dl_wait_data->create_at = Carbon::now();
        $dl_wait_data->save();

        $this->dl_request_id = $dl_request->id;
        array_push($this->arguments, $dl_request->id);

        DB::commit();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // ダウンロードデータ取得
        $data = call_user_func_array(array($this->class_path, $this->function_name), $this->arguments);
        if(!isset($data)){
            $message = "'download_wait_data.data'に保存する値の取得に失敗しました.";
            throw new \Exception($message);
            return;
        }

        // ダウンロード要求更新
        DownloadUtils::updateDownloadData($this->dl_request_id, $data, $this->is_sanitizing);

        // 無害化サーバ経由時はここでは通知無し
        if($this->is_sanitizing != 1) {
            // ダウンロード要求情報取得
            $dl_req = DB::table('download_request')
                ->where('id', $this->dl_request_id)->first();
            if($dl_req && isset($dl_req->file_name) && stripos($dl_req->file_name,'.csv')){
                $email_data = [
                    'file_name' => $dl_req->file_name,
                    'dl_period' => $dl_req->download_period
                ];
                MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                    $this->user->email,
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
        }
    }

    /**
     * Failed to Execute the job.
     *
     * @return void
     */
    public function failed($e)
    {
        Log::error('ダウンロードjobs処理失敗しました。download_request_id:' . $this->dl_request_id);
        Log::error($e->getMessage() . $e->getTraceAsString());

        DB::beginTransaction();

        DownloadRequest::where('id', $this->dl_request_id)
            ->update([
                'state' => DownloadUtils::REQUEST_FAILED
            ]);
        DownloadProcWaitData::where('download_request_id', $this->dl_request_id)
            ->update([
                'state' => DownloadUtils::PROC_PROCESS_WAIT,
            ]);

        DB::commit();
    }
}
