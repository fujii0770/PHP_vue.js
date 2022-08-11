<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 操作履歴データ削除
 * Class StampClarity
 * @package App\Console\Commands
 */
class ClearOperationHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'operationHistory:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '2か月以上経過した操作ログを削除';

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
        Log::channel('cron-daily')->debug("操作履歴データ削除処理開始");

        try {

            // 月が替わった日の午前4時に実施
            // 当月より2ヶ月以上前の移行前の操作ログを削除する
            // 例：6月1日午前4時にバッジ起動　→ 2021年4月1日 ～ 2021年4月30日の操作ログを削除する（コピーフラグが1のもの）

            // 1月以前の時間取得
            // 例：6月1日午前4時にバッジ起動　→ 2021年5月1日取得　→ 2021年5月1日より小さいもの削除
            $last_month = date("Y/m/d 00:00:00", strtotime("-1 month"));

            Log::channel('cron-daily')->debug('1月以前の時間取得:'.$last_month);

            DB::table('operation_history')
                ->where('create_at', '<', $last_month)
                ->where('copy_flg', '1')
                ->delete();

            Log::channel('cron-daily')->debug("操作履歴データ削除処理完了");
        } catch (\Exception $e) {
            Log::channel('cron-daily')->error('操作履歴データ削除処理エラー発生');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }
    }
}
