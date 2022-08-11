<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use App\Http\Utils\GwAppApiUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ChangeTrialCompanyStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trial:changeCompanyStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'トライル企業契約状態変更バッチ';

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
        Log::channel('cron-daily')->debug("トライル企業契約状態変更バッチ開始");

        DB::beginTransaction();
        try {
            $update_at = Carbon::now();
            //トライアル期間（デフォルト30日）過ぎたらトライアル状態を無効にする
            DB::table('mst_company')->where('contract_edition', AppUtils::CONTRACT_EDITION_TRIAL)
                ->where('trial_flg', AppUtils::COMPANY_STATE_VALID)
                ->where(DB::raw('TO_DAYS(NOW()) - TO_DAYS(create_at)'), '>', DB::raw('trial_time'))
                ->update(['trial_flg' => AppUtils::COMPANY_STATE_INVALID,
                    'update_at' => $update_at,
                    'update_user' => 'Shachihata',
                    'long_term_storage_flg' => 0,
                    'long_term_storage_option_flg' => 0,
                    'max_usable_capacity' => 0,
                ]);

            // 会社無効時、rememberToken削除
            $company_id = DB::table('mst_company')
                ->where('contract_edition', AppUtils::CONTRACT_EDITION_TRIAL)
                ->where('trial_flg', AppUtils::COMPANY_STATE_INVALID)
                ->where('update_at', $update_at)
                ->pluck('id')
                ->toArray();
            $gw_use=config('app.gw_use');
            $gw_domin=config('app.gw_domain');
            if (count($company_id) > 0) {
                DB::table('mst_admin')->whereIn('mst_company_id', $company_id)
                    ->where('remember_token', '!=', '')
                    ->update(['remember_token' => '']);

                DB::table('mst_user')->whereIn('mst_company_id', $company_id)
                    ->where('remember_token', '!=', '')
                    ->update(['remember_token' => '']);

                DB::table('mst_audit')->whereIn('mst_company_id', $company_id)
                    ->where('remember_token', '!=', '')
                    ->update(['remember_token' => '']);
                if($gw_use==1 && $gw_domin) {
                    foreach ($company_id as $companyId) {
                        $company = DB::table('mst_company')->where('id', $companyId)->first();
                        $setting  = GwAppApiUtils::getCompanySetting($company->id);
                        $schedule_flg = $setting['scheduler_flg'];
                        $caldav_flg = $setting['caldav_flg'];
                        if ($schedule_flg){
                            $settingCompanyIds = GwAppApiUtils::getCompanySettingId($company->id, $company->company_name, $company->state);
                            if ($settingCompanyIds) {
                                $gw_app_schedule_id = $settingCompanyIds['schedule_id'];
                                $gw_app_caldav_id = $settingCompanyIds['caldav_id'];
                                if ($gw_app_schedule_id) {
                                    $del_scheduler_result = GwAppApiUtils::deleteCompanySetting($gw_app_schedule_id);
                                    if (!$del_scheduler_result) {
                                        Log::channel('cron-daily')->warning('Cannot connect to GwApp Api');
                                    }
                                    if($caldav_flg){
                                        if ($gw_app_caldav_id) {
                                            $del_caldav_result = GwAppApiUtils::deleteCompanySetting($gw_app_caldav_id);
                                            if (!$del_caldav_result) {
                                                Log::channel('cron-daily')->warning('Cannot connect to GwApp Api');
                                            }
                                        }
                                    }
                                }
                            } else {
                                Log::channel('cron-daily')->warning('Cannot connect to GwApp Api');
                            }
                        }
                    }
                }
            }

            //トライアル終了後30日過ぎたら契約状態を無効にする、トライアル状態を無効（非活性）にする
            DB::table('mst_company')->where('contract_edition', AppUtils::CONTRACT_EDITION_TRIAL)
                ->where('trial_flg', AppUtils::COMPANY_STATE_INVALID)
                ->where('state', AppUtils::COMPANY_STATE_VALID)
                ->where(DB::raw('TO_DAYS(NOW()) - TO_DAYS( ADDDATE(create_at, INTERVAL 30 DAY))'), '>', DB::raw('trial_time'))
                ->update(['state' => AppUtils::COMPANY_STATE_INVALID,
                    'update_at' => Carbon::now(),
                    'update_user' => 'Shachihata',
                ]);
            DB::commit();
            Log::channel('cron-daily')->debug("トライル企業契約状態変更バッチ終了");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('cron-daily')->error('トライル企業契約状態変更失敗');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }
    }
}
