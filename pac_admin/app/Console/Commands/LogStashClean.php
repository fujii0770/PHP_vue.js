<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 操作ログファイル削除
 * Class StampClarity
 * @package App\Console\Commands
 */
class LogStashClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logStash:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'logStash file clean';

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
        Log::channel('cron-daily')->debug("操作ログファイル削除処理開始");

        try {
            // logstashファイル名を取得
            foreach (glob(storage_path() . "/logs/logstash-*.log") as $filename) {
                // ファイル最後操作日時は7日前の時、ファイルを削除する
                if (file_exists($filename) && filemtime($filename) < mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"))) {
                    unlink($filename);
                }
            }
            clearstatcache();
            Log::channel('cron-daily')->debug("操作ログファイル削除処理完了");
        } catch (\Exception $e) {
            Log::channel('cron-daily')->error('操作ログファイル削除処理エラー発生');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }

        /***  @021/12/02 追加 ***/
        Log::channel('cron-daily')->debug("ダウンロード用回覧ZIPファイル削除処理開始");

        try {
            // logstashファイル名を取得
            foreach (glob("/tmp/*.zip") as $filename) {
                // ファイル最後操作日時は7日経過したファイルを削除する
                if (file_exists($filename) && filemtime($filename) < mktime(0, 0, 0, date("m"), date("d") -31, date("Y"))) {
                    unlink($filename);
                }
            }
            clearstatcache();
            Log::channel('cron-daily')->debug("ダウンロード用回覧ZIPファイル削除処理完了");
        } catch (\Exception $e) {
            Log::channel('cron-daily')->error('ダウンロード用回覧ZIPファイル削除処理エラー発生');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }

        Log::channel('cron-daily')->debug("ダウンロード用回覧CSVファイル削除処理開始");

        try {
            // logstashファイル名を取得
            foreach (glob("/tmp/*csv") as $filename) {
                // ファイル最後操作日時は7日経過したファイルを削除する
                if (file_exists($filename) && filemtime($filename) < mktime(0, 0, 0, date("m"), date("d") -31, date("Y"))) {
                    unlink($filename);
                }
            }
            clearstatcache();
            Log::channel('cron-daily')->debug("ダウンロード用回覧CSVファイル削除処理完了");
        } catch (\Exception $e) {
            Log::channel('cron-daily')->error('ダウンロード用回覧CSVファイル削除処理エラー発生');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }

    }
}
