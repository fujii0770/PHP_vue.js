<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use App\Http\Utils\MailUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class BatchHistoryMailSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:batchHistoryMailSend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send batch history email';

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
        Log::channel('cron-daily')->debug('Run to batchHistoryMailSend');
        try {
            $now_date = Carbon::now()->format('Y/m/d');
            $batch_histories = DB::table('batch_history')
                ->where('execution_date', $now_date)
                ->where('batch_name','<>','login:layoutchange')//バッチ「login:layoutchange」除外
                ->where('batch_name','<>','notifications:toDoListTask')//バッチ「notifications:toDoListTask」除外
                ->orderBy('id')
                ->get()->toArray();
            $adminEmail = config('mail.admin_email');
            $param = [];
            $str = '';
            foreach ($batch_histories as $history) {
                $timediff =  AppUtils::calcDiffInMilliseconds($history->created_at,$history->updated_at);
                $history->timediff = $timediff.'ms';
                $str = $str.'<tr>';
                $str = $str.'<td>'.$history->batch_name.'</td>';
                $str = $str.'<td>'.$history->execution_date.'</td>';
                $str = $str.'<td>'.AppUtils::BATCH_HISTORY_EMAIL[$history->status].'</td>';
                $str = $str.'<td>'.$history->created_at.'</td>';
                $str = $str.'<td>'.$history->updated_at.'</td>';
                $str = $str.'<td>'.$history->timediff.'</td>';
                $str = $str.'</tr>';
            }
            $env = AppUtils::getEnvStr();
            $data = array('batch_date' => $now_date,'batch_histories' => $batch_histories,'env' => $env);
            $param['batch_date'] = $now_date;
            $param['batch_histories'] = $str;
            $param['env'] = $env;

            MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                $adminEmail,
                // メールテンプレート
                MailUtils::MAIL_DICTIONARY['BATCH_HISTORY_MAIL_SEND']['CODE'],
                // パラメータ
                json_encode($data,JSON_UNESCAPED_UNICODE),
                // タイプ
                AppUtils::MAIL_TYPE_ADMIN,
                // 件名
                config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.batchHistoryMailSend.subject', ['env' => $env]),
                // メールボディ
                trans('mail.batchHistoryMailSend.body',$param)
            );
            Log::channel('cron-daily')->debug('Run to batchHistoryMailSend finished');
        } catch (\Exception $e) {
            Log::channel('cron-daily')->error('Run to batchHistoryMailSend failed');
            Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
        }
    }
}
