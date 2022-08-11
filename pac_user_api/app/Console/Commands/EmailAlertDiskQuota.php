<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Utils\MailUtils;
use App\Http\Utils\AppUtils;
use Carbon\Carbon;

class EmailAlertDiskQuota extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:alertDiskQuota';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        Log::channel('cron-daily')->debug('Run to alertDiskSpaceUse');

        try{
            $notice_over_storage_percent = config('app.notice_over_storage_percent'); // 90％になったタイミングでメール通知

            $usageSituationDetails = DB::table('usage_situation_detail')
                ->join('mst_admin', function($join){
                    $join->on('mst_admin.mst_company_id', 'usage_situation_detail.mst_company_id');
                    $join->on('mst_admin.role_flg', DB::raw('1'));
                })
                ->where('target_date', Carbon::yesterday()->format('Y-m-d'))
                ->whereNull('guest_company_id')
                ->where('storage_rate_re','>=',$notice_over_storage_percent)
                ->select([
                    'usage_situation_detail.mst_company_id'
                    , 'usage_situation_detail.storage_rate_re'
                    , 'usage_situation_detail.storage_sum_re'
                    , 'mst_admin.email'
                    , 'mst_admin.given_name'
                    , 'mst_admin.family_name'])
                ->get();

            if(count($usageSituationDetails)){
                foreach($usageSituationDetails as $usageSituationDetail){
                    $data = [
                        'given_name' => $usageSituationDetail->given_name,
                        'family_name' => $usageSituationDetail->family_name,
                        'current_storage_size' => $usageSituationDetail->storage_sum_re,
                        'current_storage_percent' => $usageSituationDetail->storage_rate_re,
                    ];

                    Log::channel('cron-daily')->debug('Send alertDiskSpaceUse to email '.$usageSituationDetail->email);

                    // 管理者:ディスク容量使用通知
                    MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                        $usageSituationDetail->email,
                        // メールテンプレート
                        MailUtils::MAIL_DICTIONARY['DISK_QUOTA_ALERT']['CODE'],
                        // パラメータ
                        json_encode($data,JSON_UNESCAPED_UNICODE),
                        // タイプ
                        AppUtils::MAIL_TYPE_ADMIN,
                        // 件名
                        config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.SendMailAlertDiskQuota.subject'),
                        // メールボディ
                        trans('mail.SendMailAlertDiskQuota.body', $data)
                    );
                }
            }

            $batch_status = AppUtils::BATCH_SUCCESS;
        }catch(\Exception $e){
            Log::channel('cron-daily')->error('Run to alertDiskSpaceUse failed');
            Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
        Log::channel('cron-daily')->debug('Run to alertDiskSpaceUse finished');
    }
}
