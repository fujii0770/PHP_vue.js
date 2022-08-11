<?php

namespace App\Console\Commands;

use App\Http\Utils\GwAppApiUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Matrix\Exception;

class syncPACCompanyInfoToGw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:syncCompanyToGw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'syncCompanyToGw';

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
        Log::channel('sync-company-to-gw')->info("★★★企業情報の移行を開始します。★★★");
        $allCompany = 0;
        $successCompany = 0;
        try {
            $gw_use = config('app.gw_use');
            $gw_domin = config('app.gw_domain');
            if ($gw_use == 1 && $gw_domin) {
                $companyList = DB::table('mst_company')
                    ->get();
                foreach ($companyList as $company) {
                    $allCompany++;
                    Log::channel('sync-company-to-gw')->info("GW登録開始:企業ID" . $company->id . "  企業名:" . $company->company_name);
                    if ($this->hasCompany($company->id)) {
                        Log::channel('sync-company-to-gw')->info("GW側に該当企業が存在している:" . $company->id);
                        continue;
                    }
                    //会社情報登録API呼び出し
                    $storeCompanyResult = GwAppApiUtils::storeCompany($company->id, $company->company_name, $company->state);
                    if (!$storeCompanyResult) {
                        Log::channel('sync-company-to-gw')->info($company->company_name . " が登録に失敗しました。");
                    } else {
                        Log::channel('sync-company-to-gw')->info($company->company_name . " が登録成功しました。");
                    }
                    //アプリ利用制限登録API呼び出し
                    $app_limit_id = GwAppApiUtils::storeCompanyLimit($company->id);
                    if (!$app_limit_id) {
                        Log::channel('sync-company-to-gw')->info($company->company_name . " アプリ利用制限登録に失敗しました。");
                    } else {
                        Log::channel('sync-company-to-gw')->info($company->company_name . " アプリ利用制限登録");
                    }
                    if ($app_limit_id && $storeCompanyResult) {
                        $successCompany++;
                    }
                    Log::channel('sync-company-to-gw')->info("<<<<<GW登録完了: " . $company->id);
                    usleep(10*1000);
                }
            }
        } catch (\Exception $e) {
            Log::channel('sync-company-to-gw')->error($e->getMessage() . $e->getTraceAsString());
        }
        Log::channel('sync-company-to-gw')->info('★★★企業情報の移行が完了しました。合計: ' . $allCompany . '  ' . $successCompany . '個の企業を登録しました。');
    }

    /**
     * @throws \Exception
     */
    public function hasCompany($company_id)
    {
        $client = GwAppApiUtils::getAuthorizeClient();
        $masterUser = DB::table('mst_shachihata')->select('email')->first();
        $response = $client->post(GwAppApiUtils::COMPANY_STORM_SETTING_API . $company_id,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $masterUser->email,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg" => config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == 200) {
            return true;
        } elseif ($response->getStatusCode() == 404) {
            return false;
        } else {
            Log::error('get appcompany portalId:' . $company_id);
            Log::error($response_decode);
            throw new \Exception('get appcompany portalId:' . $company_id);
        }
    }
}
