<?php

namespace App\Console\Commands;

use App\Http\Utils\DownloadUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ClearCircularDownloadRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download_request:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ダウンロード状況確認ステータスが削除';

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
        try {
            Log::channel('cron-daily')->debug("ダウンロード状況確認ステータスが削除バッチ実行開始");

            DownloadUtils::removeExpiredRequestData();
            DownloadUtils::removeRequestAnHourAgoAndDeleteState();

            Log::channel('cron-daily')->debug("ダウンロード状況確認ステータスが削除バッチ実行終了");
        }catch (\Exception $ex){
            Log::channel('cron-daily')->error('ダウンロード状況確認ステータスが削除バッチ実行エラーが発生');
            Log::channel('cron-daily')->error($ex->getMessage() . $ex->getTraceAsString());
            throw $ex;
        }
    }
}
