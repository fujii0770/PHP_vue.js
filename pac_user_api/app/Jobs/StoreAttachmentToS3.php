<?php

namespace App\Jobs;

use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\EnvApiUtils;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StoreAttachmentToS3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $id;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * Create a new job instance.
     *
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $circular_attachment_id = $this->id;

        $attachment = DB::table('circular_attachment')
            ->where('id', $circular_attachment_id)
            ->where('status', CircularAttachmentUtils::ATTACHMENT_NOT_CHECK_STATUS)
            ->first();

        if (!$attachment) {
            Log::warning(__('message.warning.attachment_request.not_exist'));
            return;
        }

        $edition_flg = $attachment->edition_flg;
        $env_flg = $attachment->env_flg;
        $server_flg = $attachment->server_flg;

        try {

            //申請者会社ID
            $mst_company_id = DB::table('mst_user')
                ->where('id', $attachment->apply_user_id)
                ->select('mst_company_id')
                ->value('mst_company_id');

            $folderPath = config('app.server_env') ? config('app.k5_storage_attachment_root_folder') : config('app.s3_storage_attachment_root_folder');
            $server_url = config('filesystems.prefix_path') . '/' . $folderPath . '/' . $edition_flg . $env_flg . $server_flg . '/' . $mst_company_id . '/' . $attachment->circular_id;
            $file_name = $circular_attachment_id . '_' . substr(md5(time()), 0, 8) . '.' . substr(strrchr($attachment->file_name, '.'), 1);

            if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS){
                Storage::disk('s3')->putfileAs($server_url, new File($attachment->server_url), $file_name, 'pub');
            }else if (config('app.server_env') == EnvApiUtils::ENV_FLG_K5){
                Storage::disk('k5')->putfileAs($server_url, new File($attachment->server_url), $file_name);
            }

            $server_file_url = $server_url . '/' . $file_name;

            //添付ファイル最終更新日時チェック
            $attachment_updated = DB::table('circular_attachment')->where('id', $circular_attachment_id)->first();
            if ($attachment_updated && $attachment_updated->status == CircularAttachmentUtils::ATTACHMENT_NOT_CHECK_STATUS) {
                DB::table('circular_attachment')
                    ->where('id', $circular_attachment_id)
                    ->update([
                        'server_url' => $server_file_url,
                        'status' => CircularAttachmentUtils::ATTACHMENT_CHECK_SUCCESS_STATUS,
                        'update_at' => Carbon::now(),
                    ]);
            } else if ($attachment_updated && $attachment_updated->status == CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS) {
                //添付ファイル既に削除の場合
                if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS){
                    Storage::disk('s3')->delete($server_file_url);
                }else if (config('app.server_env') == EnvApiUtils::ENV_FLG_K5){
                    Storage::disk('k5')->delete($server_file_url);
                }

            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            throw $ex;
        }
    }


    /**
     * The job failed to process.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::error('添付ファイルダ保存jobs処理失敗しました。circular_attachment_id:' . $this->id);
        Log::error($exception->getMessage() . $exception->getTraceAsString());
        DB::table('circular_attachment')
            ->where('id', $this->id)
            ->update([
                'status' => CircularAttachmentUtils::ATTACHMENT_CHECK_FAIL_STATUS,
            ]);
    }
}
