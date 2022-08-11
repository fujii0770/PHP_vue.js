<?php

namespace App\Console;

use App\Http\Utils\AppUtils;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [

    ];

    protected $bath_id = [];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $this->commandsWithHistory($schedule, 'delete:fileExpired', '5 0 * * *', true);

        $this->commandsWithHistory($schedule, 'audit:expire', '0 3 * * *', true);

        $this->commandsWithHistory($schedule, 'emails:alertFileExpired', '0 8 * * *', true);

        $this->commandsWithHistory($schedule, 'circular:reNotify', '0 8 * * *', true);

        $this->commandsWithHistory($schedule, 'emails:alertDiskQuota', '5 8 * * *', true);

        $this->commandsWithHistory($schedule, 'save:circularCompleted', '30 0 * * *', true);

        $this->commandsWithHistory($schedule, 'update:timestampAutomatic', config('app.add_timestamp_start_time'), true);

        // PAC_5-857 logstashファイルで7日以前の履歴を削除する
        $this->commandsWithHistory($schedule, 'logStash:clean', '0 1 * * *', false);

        $this->commandsWithHistory($schedule, 'clean:directories', '0 0 * * *', false);

        $this->commandsWithHistory($schedule, 'clean:directoryTrees', '5 0 * * *', false);

        // PAC_5-1680 長期保存 - 完了一覧内にある文書を自動で長期保存
        $this->commandsWithHistory($schedule, 'autoSave:circularCompleted', '0 1 * * *', true);

        // PAC_5-2669 cacheをDBへ保存する＋要らない対象を削除
        $this->commandsWithHistory($schedule, 'clearCurrentFileCache:clearAll2669DBCache', '0 23 * * *', true);

        $schedule->command('fileMail:clean')->hourly();

        // 特設サイト文書作成
        $schedule->command('specialSite:createDocumentData')->everyMinute()->withoutOverlapping()->onOneServer()->when(function () {
            if (env('SPECIAL_SITE_RECEIVER', 0) == 1) {
                return true;
            }
        });
    
        //ToDoリスト通知を送信
        $this->commandsWithHistoryOnFailure($schedule, 'notifications:toDoListTask', '*/5 * * * *', true);

        //PAC_5-3485 バッチ処理で自動的にBOX自動保管失敗の対象を再処理する
        $this->commandsWithHistory($schedule, 'boxAutoStorage:retry', '15 1 * * *', true);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    /**
     * @param Schedule $schedule
     * @param string $command
     * @param string $cron
     * @param bool $onOneServer
     * @return \Illuminate\Console\Scheduling\Event
     */
    protected function commandsWithHistory(Schedule $schedule, string $command, string $cron, bool $onOneServer = false, $execution_date = '')
    {
        $schedule = $schedule->command($command)->cron($cron)
            ->before(function () use ($command, $execution_date) {
                //
                $id = DB::table('batch_history')->insertGetId([
                    'execution_date' => $execution_date ?: Carbon::now()->format('Y/m/d'),
                    'batch_name' => $command,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
                    'status' => AppUtils::BATCH_RUNNING,
                ]);
                $this->bath_id[$command] = $id;
            })
            ->onSuccess(function () use ($command) {
                // The task succeeded...
                DB::table('batch_history')->where('id', $this->bath_id[$command])->update([
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
                    'status' => AppUtils::BATCH_SUCCESS
                ]);
            })
            ->onFailure(function () use ($command) {
                // The task failed...
                DB::table('batch_history')->where('id', $this->bath_id[$command])->update([
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
                    'status' => AppUtils::BATCH_FAIL
                ]);
            });

        if ($onOneServer) {
            $schedule = $schedule->onOneServer();
        }
        return $schedule;
    }

    /**
     * 失敗のみの場合は、バッチ履歴テーブルに新規登録する
     * @param $schedule Schedule スケジュール
     * @param $command string 実行のコマンド
     * @param $cron string クロン  フォーマット:「'minute,hour,day of month,month,day of week'」
     * @param $onOneServer bool 単一サーバでのタスクの実行
     * @option $execution_date string 実行時間
     * @return schedule Schedule スケジュール
     */
    protected function commandsWithHistoryOnFailure(Schedule $schedule, string $command, string $cron, bool $onOneServer = false, $execution_date = '')
    {
        $schedule = $schedule->command($command)->cron($cron)
            ->onFailure(function () use ($command, $execution_date) {
                // バッチ実行失敗の場合
                DB::table('batch_history')->insert([
                    'execution_date' => $execution_date ?: Carbon::now()->format('Y/m/d'),
                    'batch_name' => $command,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
                    'status' => AppUtils::BATCH_FAIL
                ]);
            });

        if ($onOneServer) {
            $schedule = $schedule->onOneServer();
        }
        return $schedule;
    }
}
