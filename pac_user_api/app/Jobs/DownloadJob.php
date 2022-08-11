<?php

namespace App\Jobs;

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

use App\Models\User;
use App\Models\Constraint;
use App\Models\DownloadProcWaitData;
use App\Models\DownloadRequest;
use App\Models\DownloadWaitData;

class DownloadJob implements ShouldQueue
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
     * @param User $user 利用者情報
     * @param string $class_path クラスのフルパス
     * @param string $function_name 戻り値がダウンロードデータである関数名
     * @param string $file_name ファイル名
     * @param bool $is_sanitizing 無害化の有無
     * @return void
     */
    public function __construct($user, $class_path, $function_name, $file_name, $is_sanitizing)
    {
        $this->user             = $user;
        $this->is_sanitizing    = $is_sanitizing;
        $this->file_name        = $file_name;
        $this->class_path       = $class_path;
        $this->function_name    = $function_name;
        $this->arguments        = array_slice(func_get_args(), 5)[0];

        Log::debug(  "[DL_JOB] user_auth: "  .AppUtils::AUTH_FLG_USER. 
                    ", user_id: "           .$user->id. 
                    ", class_path: "        .$class_path. 
                    ", function_name: "     .$function_name. 
                    ", is_sanitizing: "     .$is_sanitizing. 
                    ", file_name: "         .$file_name
                );

        DB::beginTransaction();

        // ダウンロード期限
        $dl_max_keep_days = Constraint::where('mst_company_id', $user->mst_company_id)
                                        ->select('dl_max_keep_days')
                                        ->first()->dl_max_keep_days;

        //PAC_5-2912 S
        $mst_sanitizing_line_id = DB::table('mst_company')->where('id', $user->mst_company_id)->select('mst_sanitizing_line_id')->first()->mst_sanitizing_line_id;
        //PAC_5-2912 E

        if(isset($user->auditUser)){
            $user_auth = $user->auditUser ? AppUtils::AUTH_FLG_AUDIT : AppUtils::AUTH_FLG_USER;
        }else{
            $user_auth = $user->isAuditUser() ? AppUtils::AUTH_FLG_AUDIT : AppUtils::AUTH_FLG_USER;
        }

        $dl_request                     = new DownloadRequest;
        $dl_request->mst_company_id     = $user->mst_company_id;
        $dl_request->mst_user_id        = $user->id;
        $dl_request->user_auth          = $user_auth;
        $dl_request->file_name          = $file_name;
        $dl_request->request_date       = Carbon::now();
        $dl_request->contents_create_at = Carbon::now();
        $dl_request->download_period    = Carbon::now()->copy()->addDay($dl_max_keep_days);
        $dl_request->state              = DownloadUtils::REQUEST_PROCESS_WAIT;
        $dl_request->class_path         = $this->class_path;
        $dl_request->function_name      = $this->function_name;
        $dl_request->arguments          = serialize($this->arguments);
        //PAC_5-2912 S
        if($mst_sanitizing_line_id){
            $dl_request->mst_sanitizing_line_id = $mst_sanitizing_line_id;
        }
        //PAC_5-2912 E
        $dl_request->save();

        $dl_wait_data                       = new DownloadWaitData;
        $dl_wait_data->download_request_id  = $dl_request->id;
        $dl_wait_data->create_at            = Carbon::now();
        $dl_wait_data->save();

        $this->dl_request_id = $dl_request->id;
        array_push($this->arguments, $dl_request->id);

        if(count($this->arguments) > 0 && isset($this->arguments[1]['frm_id'])) {
            DB::table('frm_imp_mgr')
                ->where('id', $this->arguments[1]['frm_id'])
                ->update([
                    'download_request_id' => $dl_request->id
                ]);
        }
        DB::commit();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DownloadRequest::where('id', $this->dl_request_id)
            ->update([
                'state' => DownloadUtils::REQUEST_CREATING
            ]);

        // ダウンロードデータ取得
        $data = call_user_func_array(array($this->class_path, $this->function_name), $this->arguments);
        if(!isset($data)){
            $message = "'download_wait_data.data'に保存する値の取得に失敗しました.";
            throw new \Exception($message);
            return;
        }

        // ダウンロード要求更新
        DownloadUtils::updateDownloadData($this->dl_request_id, $data, $this->is_sanitizing);
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
