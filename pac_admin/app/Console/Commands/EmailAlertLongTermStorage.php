<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Utils\MailUtils;
use Carbon\Carbon;

class EmailAlertLongTermStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:alertLongTermStorage';

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
        Log::channel('cron-daily')->debug('Run to alert long term storage percent');

        try {
            $settingConstraints = DB::table('mst_constraints')->get();

            // total used capacity
            $long_term_storage_sizes = DB::table('long_term_document')
                ->groupBy('mst_company_id')
                ->select(DB::raw('mst_company_id, sum(file_size) as long_term_storage_size'))
                ->pluck('long_term_storage_size','mst_company_id');

            //max capacity
            $max_usable_capacities = DB::table('mst_company')
                ->where('long_term_storage_flg', 1)
                ->select(['id', 'max_usable_capacity'])
                ->pluck('max_usable_capacity','id');

            if(count($settingConstraints)) {
                $listOverStore = [];
                $listCompanyOverStore = [];
                foreach ($settingConstraints as $settingConstraint) {
                    if(isset($long_term_storage_sizes[$settingConstraint->mst_company_id]) && isset($max_usable_capacities[$settingConstraint->mst_company_id])) {
                        $long_term_storage_percent_alert = $settingConstraint->long_term_storage_percent;
                        $long_term_storage_size_alert = $max_usable_capacities[$settingConstraint->mst_company_id]*1024*1024*1024*$long_term_storage_percent_alert/100;
                        $current_long_term_storage_size = $long_term_storage_sizes[$settingConstraint->mst_company_id];

                        if($current_long_term_storage_size >= $long_term_storage_size_alert){
                            $listOverStore[] = [
                                'mst_company_id' => $settingConstraint->mst_company_id,
                                'current_long_term_storage_size' => $current_long_term_storage_size <= 1024*1024*1024 ? round($current_long_term_storage_size/(1024*1024), 2).'MB' : round($current_long_term_storage_size/(1024*1024*1024),2).'GB',
                                'current_long_term_storage_percent' => ($max_usable_capacities[$settingConstraint->mst_company_id]?(round($current_long_term_storage_size*100/($max_usable_capacities[$settingConstraint->mst_company_id]*1024*1024*1024))):100).'%'
                            ];
                            $listCompanyOverStore[] = $settingConstraint->mst_company_id;
                        }
                    }
                }
                if (count($listCompanyOverStore)){
                    $users_admin = DB::table('mst_admin')
                        ->whereIn('mst_company_id', $listCompanyOverStore)
                        ->where('role_flg', AppUtils::ADMIN_MANAGER_ROLE_FLG)
                        ->select('given_name', 'family_name', 'email', 'mst_company_id')
                        ->get()->keyBy('mst_company_id');
                    foreach($listOverStore as $infoOverStore){
                        if(isset($users_admin[$infoOverStore['mst_company_id']])){
                            $userAdmin = $users_admin[$infoOverStore['mst_company_id']];
                            $data = [
                                'given_name' => $userAdmin->given_name,
                                'family_name' => $userAdmin->family_name,
                                'current_long_term_storage_size' => $infoOverStore['current_long_term_storage_size'],
                                'current_long_term_storage_percent' => $infoOverStore['current_long_term_storage_percent'],
                            ];

                            Log::channel('cron-daily')->error('Send alert long term storage percent to email '.$userAdmin->email);

                            // 管理者:長期保管ディスク容量通知
                            MailUtils::InsertMailSendResume(
                                // 送信先メールアドレス
                                $userAdmin->email,
                                // メールテンプレート
                                MailUtils::MAIL_DICTIONARY['LONG_TERM_STORAGE_ALERT']['CODE'],
                                // パラメータ
                                json_encode($data,JSON_UNESCAPED_UNICODE),
                                // タイプ
                                AppUtils::MAIL_TYPE_ADMIN,
                                // 件名
                                config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.SendMailAlertLongTermStorage.subject'),
                                // メールボディ
                                trans('mail.SendMailAlertLongTermStorage',$data)
                            );
                        }

                    }
                }
            }
            Log::channel('cron-daily')->debug('Run to alert long term storage percent finished');
        } catch(\Exception $e){
            Log::channel('cron-daily')->error('Run to alert long term storage percent failed');
            Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
    }
}
