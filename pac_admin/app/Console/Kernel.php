<?php

namespace App\Console;

use App\Http\Utils\AppUtils;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Utils\EnvApiUtils;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
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
        if (config('app.pac_app_env')) {
            $this->commandsWithHistory($schedule, 'usage_situation:insert', '55 23 * * *', true, Carbon::tomorrow()->format('Y/m/d'));
        } else {
            $this->commandsWithHistory($schedule, 'usage_situation:insert', '45 23 * * *', true, Carbon::tomorrow()->format('Y/m/d'));
        }
        // 利用状況再計算(PAC_3-234)
        $this->commandsWithHistory($schedule, 'usage_situation_detail:insert', '50 23 * * *', true);

        $this->commandsWithHistory($schedule, 'emails:alertLongTermStorage', '5 8 * * *', true);

        $this->commandsWithHistory($schedule, 'email:lowercase', '10 0 * * *', true);

        // トライアル期間が切れた企業のStateを9（無効）に変更する
        $this->commandsWithHistory($schedule, 'trial:changeCompanyStatus', '30 4 * * *', true);

        // PAC_5-821 部署名入り日付印の登録タイミングを、現行と同様に10時まで待って登録にする
        $this->commandsWithHistory($schedule, 'stampStatus:activate', '0 10 * * *', true);

        // PAC_5-944 バッチ処理で1月以前の履歴を削除する
        $this->commandsWithHistory($schedule, 'import_csv_history:clear', '30 0 * * *', true);

        // PAC_5-857 logstashファイルで7日以前の履歴を削除する
        $this->commandsWithHistory($schedule, 'logStash:clean', '0 1 * * *', false);

        // PAC_5-972 保存期限終了の回覧を削除します
        $this->commandsWithHistory($schedule, 'expired_circular:delete', '20 0 * * *', true);

        // PAC_5-1180 新エディションのバッチリストの作成と監視
        $schedule->command('mail:batchHistoryMailSend')->dailyAt('11:00')->onOneServer();

        // PAC_5-1313-2 操作ログ分割用コピーバッチ実行
        $this->commandsWithHistory($schedule, 'release:operationHistory', '30 1 * * *', true);

        // PAC_5-1493 2か月以上経過した操作ログ削除
        $this->commandsWithHistory($schedule, 'operationHistory:clear', '0 4 1 * *', true);

        // PAC_5-1265 完了一覧で表示する文書を月ごとに表示し、完了一覧に保存されてから一定期間経過した文書ファイルは参照先を変更する
        $this->commandsWithHistory($schedule, 'copy:completedCircular', '2 23 * * *', true, Carbon::tomorrow()->format('Y/m/d'));

        // PAC_5-1265 毎一日の時、上月コピーなしのデータコピー実施
        $this->commandsWithHistory($schedule, 'copy:completedCircular', '0 2 1 * *', true);

        // PAC_5-1542 template ファイルサイズ取得バッチ
        $this->commandsWithHistory($schedule, 'SizeVerification:Template', '30 2 * * *', true);
        // BOX自動保管の期限切れ対応
        $this->commandsWithHistory($schedule, 'update:BoxRefreshToken', '0 8 * * *', true);

        // GW掲示板掲示終了日が過ぎた掲示物を削除します(PAC_5-1742)
        $this->commandsWithHistory($schedule, 'bbs_timeout:delete', '0 1 * * *', true);

        // PAC_5-2089 マスター画面からログイン画面の画像・テキスト変換の実行バッチ
        if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_AWS) {
        $this->commandsWithHistoryOnFailure($schedule, 'login:layoutchange', '35 * * * *', false);
        }
        
        // PAC_5-1006 タイムスタンプの超過アラート機能
        $this->commandsWithHistory($schedule, 'timestamps:notify', '0 9 * * *', true);

        //PAC_5-2033 回覧一覧から大量の一括ダウンロードをされたときの対策
        $this->commandsWithHistory($schedule,'download_request:clear','0 5 * * *',true);

        $this->commandsWithHistory($schedule, 'delete:token_Access_Remember', '0 1 * * *', true);

        //PAC_5-1454 コピーが完了した文書レコード削除バッチの作成
        $this->commandsWithHistory($schedule, 'delete:expiredCompletedCircular', '5 2 * * *', true);
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
