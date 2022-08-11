<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use App\Http\Utils\MailUtils;
use App\Http\Utils\StatusCodeUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\EnvApiUtils;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise;


class MailSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:mailSend {second}';

    /**
     * The console command description.
     *
     * @var string
     */
    // 本バッチはPHPサーバのSuperVisorにて管理し、３０秒毎に処理を行う。
    protected $description = 'send email';
    
    protected $intMSec = 3000; 

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
        Log::channel('cron-daily')->debug("send email start");
        try {
            $second = $this->argument('second'); // ミリセカンド
            while (true) {
                $this->doRun();
                $this->wait($second);

                // ファイル存在時、退出(/pac_admin/public/mailStop)
                if (file_exists(public_path() . '/mailStop')) {
                    Log::channel('cron-daily')->debug('send email stop');
                    break;
                }
            }

        } catch (\Exception $e) {
            Log::channel('cron-daily')->error('Run to send email  failed');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
        }
        Log::channel('cron-daily')->debug('send email finished');
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function doRun()
    {
        // 「未送信」が「送信中」状態に変更する。
        $count = config('mail.MailSendCounts');

        Log::channel('cron-daily')->debug("送信処理開始：".$this->msectime());
        //メールファイル便遅延送信
        DB::table('mail_send_resume')->where('state', AppUtils::MAIL_STATE_DELAY)
            ->where('create_at', '<', Carbon::now()->addMinutes(-10))
            ->update(['state' => AppUtils::MAIL_STATE_WAIT]);

        DB::table('mail_send_resume')
            ->where('state', AppUtils::MAIL_STATE_FAILED)
            ->where('update_at','>=',Carbon::now()->subHour())
            ->where('send_times','<',AppUtils::MAX_MAIL_SEND_DEFAULT_TIMES)
            ->update(['state' => AppUtils::MAIL_STATE_WAIT]);

        Log::channel('cron-daily')->debug("データ更新完了 ：".$this->msectime());
        $cids = DB::table('mail_send_resume')
            ->select('mail_send_resume.id')
            ->where('mail_send_resume.state', AppUtils::MAIL_STATE_WAIT)
            ->orderBy('mail_send_resume.id', 'asc')
            ->limit($count)
            ->pluck('mail_send_resume.id')->toArray();

        //認証コードのメールの優先度上
        DB::table('mail_send_resume')
            ->where('state',AppUtils::MAIL_STATE_WAIT)
            ->where(function ($query){
                $query->where('template',MailUtils::ADMIN_MFA_CODE_RELEASE)
                    ->orWhere('template',MailUtils::USER_MFA_CODE_RELEASE);
            })->update(['state' => AppUtils::MAIL_STATE_RUNNING]);

        DB::table('mail_send_resume')->whereIn('id', $cids)->update(['state' => AppUtils::MAIL_STATE_RUNNING]);

        // 「送信中」レコード取得する
        $mail_resume = DB::table('mail_send_resume')->select('id')->where('state', AppUtils::MAIL_STATE_RUNNING)->orderBy('create_at', 'asc')->get();

        Log::channel('cron-daily')->debug("【{$mail_resume->count()}】件データを取得しました。 ".$this->msectime());
        if ($mail_resume->count()) {
            Log::channel('cron-daily')->debug("送信開始：" . $this->msectime());
            $promises = array();
            $app_env = config('app.pac_app_env');
            $app_server = config('app.pac_contract_server');
            $envClient = EnvApiUtils::getAuthorizeClient($app_env, $app_server);
            $arrSendMailTime = [];
            // 送信パラメーター作成
            $requests = function ($total) use ($envClient, $mail_resume,&$arrSendMailTime) {
                foreach ($mail_resume as $mail) {
                    yield function () use ($envClient, $mail,&$arrSendMailTime) {
                        $arrSendMailTime[$mail->id] = [
                            'start' => ['time'=> $this->msectime(true),'data'=>"【{$mail->id}】送信開始：" . $this->msectime()]
                        ];
                        return $envClient->requestAsync('POST', "send-mail", [RequestOptions::JSON => ['id' => $mail->id,]]);
                    };
                }
            };

            // 送信結果
            $pool = new Pool($envClient, $requests($mail_resume->count()), [
                'concurrency' => 50,
                'fulfilled' => function ($response, $index) use ($mail_resume,&$arrSendMailTime) {
                    $arrSendMailTime[$mail_resume->get($index)->id]['end'] = ['time'=>$this->msectime(true),'data'=>"【{$mail_resume->get($index)->id}】送信完了：" . $this->msectime()];
                    if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK) {
                        DB::table('mail_send_resume')
                            ->where('id', $mail_resume->get($index)->id)
                            ->update([
                                'param' => '',
                                'send_times' => DB::raw('send_times + 1'),
                                'state' => AppUtils::MAIL_STATE_SUCCESS,
                                'update_at' => Carbon::now()
                            ]);
                    } else {
                        Log::channel('cron-daily')->alert('send email failed :id=' . $mail_resume->get($index)->id . ' reason=' . $response->getBody());
                        DB::table('mail_send_resume')
                            ->where('id', $mail_resume->get($index)->id)
                            ->update([
                                'send_times' => DB::raw('send_times + 1'),
                                'state' => AppUtils::MAIL_STATE_FAILED,
                                'update_at' => Carbon::now()
                            ]);
                    }
                },
                'rejected' => function ($reason, $index) use ($mail_resume,&$arrSendMailTime) {
                    $arrSendMailTime[$mail_resume->get($index)->id]['end'] = ['time'=>$this->msectime(true),'data'=>"【{$mail_resume->get($index)->id}】送信完了：" . $this->msectime()];
                    Log::channel('cron-daily')->alert('send email failed :id=' . $mail_resume->get($index)->id . ' reason=' . $reason);
                    DB::table('mail_send_resume')
                        ->where('id', $mail_resume->get($index)->id)
                        ->update([
                            'send_times' => DB::raw('send_times + 1'),
                            'state' => AppUtils::MAIL_STATE_FAILED,
                            'update_at' => Carbon::now()
                        ]);
                },
            ]);

            $promise = $pool->promise();
            $promise->wait();

            Promise\settle($promises)->wait();
            
            if(!empty($arrSendMailTime)){
                foreach($arrSendMailTime as $key=>$val){
                    if($val['end']['time'] - $val['start']['time'] > $this->intMSec){
                        Log::channel('cron-daily')->debug("「{$key}」の送信時間 ".($val['end']['time'] - $val['start']['time']).'(millisecond)');
                    }       
                }
            }
            Log::channel('cron-daily')->debug("送信完了 " . $this->msectime());
        }
    }

    /**
     * ミリセカンド
     * @param string $time
     */
    protected function wait($time)
    {
        $wait = $time * 1000 * 1000;
        usleep($wait);
    }

    // return StringDateTime OR millisecond
    function msectime($boolIsString = false) {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec)) * 1000);
        if($boolIsString == true){
            return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        }
        return date("Y-m-d H:i:s")." millisecond: ".$msectime;
    }
}
