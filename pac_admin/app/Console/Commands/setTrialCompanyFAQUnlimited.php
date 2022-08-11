<?php

namespace App\Console\Commands;

use App\Http\Utils\ApplicationAuthUtils;
use App\Http\Utils\AppUtils;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class setTrialCompanyFAQUnlimited extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:setTrialCompanyFAQUnlimited';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'setTrialCompanyFAQUnlimited';

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
        DB::beginTransaction();
        try {
            $companyIds = DB::table("mst_company")
                ->leftJoin('mst_application_companies','mst_company.id','=','mst_application_companies.mst_company_id')
                ->where('mst_company.contract_edition', AppUtils::CONTRACT_EDITION_TRIAL)
                ->where('mst_company.trial_flg', 1)
                ->where('mst_application_companies.mst_application_id',AppUtils::GW_APPLICATION_ID_FAQ_BOARD)
                ->WhereNull('mst_application_companies.is_infinite')
                ->pluck("mst_company.id");
            foreach ($companyIds as $companyId) {
                $userTotal = 0;
                ApplicationAuthUtils::getCompanyAppSearch($companyId)->each(function ($item) use (&$userTotal, $companyId) {
                    if ($item->app_code == AppUtils::GW_APPLICATION_ID_FAQ_BOARD && $item->is_auth == 1 && $item->is_infinite != 1) {
                        ApplicationAuthUtils::storeCompanySetting($companyId, AppUtils::GW_APPLICATION_ID_FAQ_BOARD, 1, 0);
                        DB::table("mst_user")
                            ->where('mst_company_id', $companyId)
                            ->where('state_flg', AppUtils::STATE_VALID)
                            ->whereIn('option_flg', [AppUtils::USER_OPTION, AppUtils::USER_NORMAL])
                            ->pluck('id')
                            ->each(
                                function ($userId) use (&$userTotal, $companyId) {
                                    ApplicationAuthUtils::appUserUpdate($companyId, AppUtils::GW_APPLICATION_ID_FAQ_BOARD, $userId);
                                    $userTotal++;
                                }
                            );
                    }
                });
                
            }
            DB::commit();
            Log::info("setTrialCompanyFAQUnlimited success. company total:".count($companyIds));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info("setTrialCompanyFAQUnlimited failed. companyId:", $companyIds->toArray());
        }

    }
}
