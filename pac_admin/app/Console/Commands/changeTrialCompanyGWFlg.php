<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use App\Http\Utils\GwAppApiUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class changeTrialCompanyGWFlg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:changeTrialCompanyGWFlg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'changeTrialCompanyGWFlg';

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
        Log::channel('sync-company-to-gw')->info("★★★TRIAL企業スケジューラFLGをONにします。★★★");
        $allCompany = 0;
        $successCompany = 0;
        try {
            $gw_use = config('app.gw_use');
            $gw_domin = config('app.gw_domain');
            if ($gw_use == 1 && $gw_domin) {
                $companyList = DB::table('mst_company')
                    ->where('contract_edition', AppUtils::CONTRACT_EDITION_TRIAL)
                    ->get();
                foreach ($companyList as $company) {
                    $allCompany++;
                    Log::channel('sync-company-to-gw')->info("TRIAL企業スケジューラFLGの変更開始:企業ID" . $company->id . "  企業名:" . $company->company_name);
                    $storeCompanySettingResult = GwAppApiUtils::storeCompanySetting($company->id, AppUtils::GW_APPLICATION_ID_CALDAV, 1, 0);
                    if (!$storeCompanySettingResult) {
                        Log::channel('sync-company-to-gw')->info($company->company_name . " が登録に失敗しました。");
                    } else {
                        $successCompany++;
                        Log::channel('sync-company-to-gw')->info($company->company_name . " が登録成功しました。");
                    }
                    Log::channel('sync-company-to-gw')->info("<<<<<企業スケジューラFLGの変更完了: " . $company->id);
                    usleep(10 * 1000);
                }
            }
        } catch (\Exception $e) {
            Log::channel('sync-company-to-gw')->error($e->getMessage() . $e->getTraceAsString());
        }
        Log::channel('sync-company-to-gw')->info('★★★TRIAL企業スケジューラFLGの変更が完了しました。合計: ' . $allCompany . '  ' . $successCompany . '個のTRIAL企業のスケジューラFLGを変更しました。');

    }
}
