<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use App\Http\Utils\EnvApiUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\MailUtils;


class SendTimestampsNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timestamps:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send timestamps notify';

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
        Log::channel('cron-daily')->debug('Send Timestamps Notify');
        $now = Carbon::now();
        $targetMonth = $now->format('Ym');
        try {
            $mst_company_list = DB::table('mst_company')
                ->where('guest_company_flg', DB::raw('0'))
                ->where('state', AppUtils::STATE_VALID)
                ->where('stamp_flg', DB::raw(1))
                ->where('timestamps_count', '>', DB::raw('0'))
                ->where('contract_edition', '<>', AppUtils::CONTRACT_EDITION_TRIAL)
                ->select('id', 'company_name', 'timestamps_count', 'stamp_flg', 'timestamp_notified_flg', 'state')
                ->get()
                ->keyBy('id');
            $companyList = [];
            $timestampsNotifyCompanyIds = [];
            foreach ($mst_company_list as $company) {
                $companyList[$company->id] = clone $company;
                $companyList[$company->id]->total_time_stamp = 0;
                $timestampsNotifyCompanyIds[] = $company->id;
            }
            // 本環境のタイムスタンプ情報を計算
            $totalAllTimestamps = DB::table('time_stamp_info')
                ->select(['mst_company_id', DB::raw('COUNT(id) as count_timestamp')])
                ->where(DB::raw("DATE_FORMAT(create_at, '%Y%m')"), '=', $targetMonth)
                ->where('app_env', '=', config('app.pac_app_env'))
                ->where('contract_server', '=', config('app.pac_contract_server'))
                ->whereIn('mst_company_id', $timestampsNotifyCompanyIds)
                ->groupBy('mst_company_id')
                ->get();

            foreach ($totalAllTimestamps as $totalTimestamp){
                // ホスト企業
                if (array_key_exists($totalTimestamp->mst_company_id, $companyList)){
                    $companyList[$totalTimestamp->mst_company_id]->total_time_stamp = $totalTimestamp->count_timestamp;
                }
            }
    
            foreach (explode(',', config('app.server_list')) as $key){
//            foreach ($serverEnvApi as $key => $value){
                $env = substr($key,0,1);
                $server = substr($key,1,strlen($key)-1);
                $local_env = config('app.pac_app_env');
                $local_server = config('app.pac_contract_server');
        
                // 本環境以外
                if ($env != $local_env || $server != $local_server){
                    $envClient = EnvApiUtils::getAuthorizeClient($env,$server,false);
                    if ($envClient){
                        $response = $envClient->get("timestamp/countByMonthAndEnv?appEnv=$local_env&contractServer=$local_server"."&targetMonth=$targetMonth",[]);
                        if($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                            $totalTimestampOtherEnvs = json_decode($response->getBody())->data;
                            foreach ($totalTimestampOtherEnvs as $totalTimestampOtherEnv){
                                if (array_key_exists($totalTimestampOtherEnv->mst_company_id, $companyList)){
                                    $companyList[$totalTimestampOtherEnv->mst_company_id]->total_time_stamp += $totalTimestampOtherEnv->count_timestamp;
                                }
                            }
                        }else{
                            Log::channel('cron-daily')->warning('Cannot countByMonthAndEnv');
                            Log::channel('cron-daily')->warning($response->getBody());
                        }
                    }else{
                        Log::channel('cron-daily')->warning('Cannot connect to Env Api');
                    }
                }
            }
            $mail_data = [];
            $company_ids = [];
            foreach ($companyList as $company) {
                $used_timestamp = (int)$company->total_time_stamp;
                $timestamps_count = (int)$company->timestamps_count;
                if ($used_timestamp > 0){
                    $data = [
                        'mst_company_id' => $company->id,
                        'company_name' => $company->company_name,
                        'timestamps_count' => $timestamps_count - $used_timestamp,
                        'cloud_link' => config('app.timestamp_order_url')
                    ];
                    if ($used_timestamp === 0 || $used_timestamp > $timestamps_count) {
                        $data['type'] = 'upper_limit';
                        $company_ids[] = $company->id;
                    } else {
                        continue;
                    }
                    $mail_data[] = $data;
                }
            }
            unset($company);
            unset($data);
            if (!empty($mail_data)) {
                $admin_list = DB::table('mst_admin')->select('mst_company_id', 'family_name', 'given_name', 'email')
                    ->whereIn('mst_company_id', $company_ids)
                    ->where('role_flg', DB::raw(1))
                    ->where('state_flg', AppUtils::STATE_VALID)
                    ->get()
                    ->toArray();
                
                foreach ($admin_list as $admin_user) {
                    if (isset($companyList[$admin_user->mst_company_id])) {
                        $companyList[$admin_user->mst_company_id]->admin_user = $admin_user;
                    }
                }
                
                foreach ($mail_data as $data) {
                    $company = clone $companyList[$data['mst_company_id']];
                    if (!isset($company->admin_user) || empty($company->admin_user) || !$company->state) {
                        continue;
                    }
                    $data['admin_name'] = $company->admin_user->family_name . $company->admin_user->given_name;
                    if ($data['type'] === 'upper_limit') {
                        MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                            $company->admin_user->email,
                            // メールテンプレート
                            MailUtils::MAIL_DICTIONARY['SEND_TIMESTAMPS_COUNT_UPPER_LIMIT_REMIND_MAIL']['CODE'],
                            // パラメータ
                            json_encode($data, JSON_UNESCAPED_UNICODE),
                            // タイプ
                            AppUtils::MAIL_TYPE_ADMIN,
                            // 件名
                            config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.send_timestamps_count_upper_limit_remind_mail.subject'),
                            // メールボディ
                            trans('mail.send_timestamps_count_upper_limit_remind_mail.body', $data)
                        );

                        // シヤチハタの管理 送信
                        MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                            config('mail.shachihata_management_email'),
                            // メールテンプレート
                            MailUtils::MAIL_DICTIONARY['SEND_TIMESTAMPS_COUNT_UPPER_LIMIT_REMIND_MAIL']['CODE'],
                            // パラメータ
                            json_encode($data, JSON_UNESCAPED_UNICODE),
                            // タイプ
                            AppUtils::MAIL_TYPE_ADMIN,
                            // 件名
                            config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.send_timestamps_count_upper_limit_remind_mail.subject'),
                            // メールボディ
                            trans('mail.send_timestamps_count_upper_limit_remind_mail.body', $data)
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            Log::channel('cron-daily')->error('Send Timestamps Notify failed');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }
        Log::channel('cron-daily')->debug('Send Timestamps Notify finished');
    }
}
