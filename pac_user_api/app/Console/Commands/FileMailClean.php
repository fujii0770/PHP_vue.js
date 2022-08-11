<?php

namespace App\Console\Commands;

use App\Http\Utils\EnvApiUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 *  ファイルメール便削除
 * Class StampClarity
 * @package App\Console\Commands
 */
class FileMailClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fileMail:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'file mail clean';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::channel('cron-daily')->debug("ファイルメール便削除処理開始");

        try {
            //レコード削除
            //期限切れ、上限越え時はS3からファイルの削除だけをお願いしたい。
            $disk_mail_valid_ids = DB::table('disk_mail')
                ->where('status', AppUtils::DISK_MAIL_VALID_STATUS)
                ->whereNotExists(function($query){
                    $query->select(DB::raw(1))
                        ->from('mst_application_companies')
                        ->leftJoin('mst_user','mst_application_companies.mst_company_id','=','mst_user.mst_company_id')
                        ->where('mst_application_companies.mst_application_id',AppUtils::GW_APPLICATION_ID_FILE_MAIL_EXTEND)
                        ->whereRaw('disk_mail.mst_user_id = mst_user.id');
                })
                ->where(function ($query) {
                    $query->where('expiration_date', '<', Carbon::now());
                    $query->orWhere(function ($sql){
                        $sql->whereRaw('download_limit<=download_count');
                        $sql->where('download_limit','!=',-1);
                    });
                })->pluck('id')->toArray();

            if (count($disk_mail_valid_ids) > 0) {
                $disk_files = DB::table('disk_mail_file')->select('file_url')->whereIn('disk_mail_id', $disk_mail_valid_ids)->get();
                foreach ($disk_files as $disk_file) {
                    if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS) {
                        Storage::disk('s3')->delete($disk_file->file_url);
                    } elseif (config('app.server_env') == EnvApiUtils::ENV_FLG_K5) {
                        Storage::disk('k5')->delete($disk_file->file_url);
                    }
                }
                DB::table('disk_mail_file')->whereIn('disk_mail_id', $disk_mail_valid_ids)->update([
                    'status' => AppUtils::DISK_MAIL_FILE_DELETE_STATUS
                ]);
                DB::table('disk_mail')->whereIn('id', $disk_mail_valid_ids)->update([
                    'status' => AppUtils::DISK_MAIL_FILE_DELETE_STATUS
                ]);
            }

            // 削除ボタン、または夜間バッチにてレコードは削除
            //デフォルトは期限切れ、上限越え日含めて、2日とする。
            $disk_mail_ids = DB::table('disk_mail')->where(function ($query) {
                $query->where('status', AppUtils::DISK_MAIL_TEMP_STATUS)
                    ->where('create_at', '<=', Carbon::now()->addDays(-1));
            })->orWhere(function ($query) {
                $query->where('status', AppUtils::DISK_MAIL_FILE_DELETE_STATUS)
                    ->where('update_at', '<=', Carbon::now()->addDays(-2));
            })->pluck('id')->toArray();
            Log::channel('cron-daily')->debug($disk_mail_ids);
            DB::table('disk_mail_file')->whereIn('disk_mail_id', $disk_mail_ids)->delete();
            DB::table('disk_mail')->whereIn('id', $disk_mail_ids)->delete();

            Log::channel('cron-daily')->debug("ファイルメール便削除処理完了");
        } catch (\Exception $e) {
            Log::channel('cron-daily')->error('ファイルメール便削除処理エラー発生');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }
    }
}
