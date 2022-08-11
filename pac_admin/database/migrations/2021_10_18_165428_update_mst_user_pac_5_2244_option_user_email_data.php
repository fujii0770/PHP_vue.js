<?php

use App\Http\Utils\AppUtils;
use App\Http\Utils\IdAppApiUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UpdateMstUserPac52244OptionUserEmailData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $companys = DB::table('mst_company')->select('domain','id','company_name','system_name')->get()->keyBy('id');

        foreach ($companys  as $company_id => $company){

            $upd_domain = explode("\r\n", $company->domain);
            $suffix_domain = '@' . substr(ltrim($upd_domain[0],"@"),0,strpos(ltrim($upd_domain[0],"@"),'.'));

            $option_users = DB::table('mst_user')
                ->select('email','id','given_name','family_name','state_flg')
                ->where('mst_company_id',$company_id)
                ->where('option_flg',AppUtils::USER_OPTION)
                ->where('state_flg','!=',AppUtils::STATE_DELETE)
                ->get()->keyBy('id');

            foreach ($option_users as $user_id => $option_user){
                if (!str_contains($option_user->email,'@')){
                    DB::beginTransaction();
                    DB::table('mst_user')->where('id',$user_id)
                        ->update([
                            'email' => $option_user->email . $suffix_domain
                        ]);

                    $apiUser = [
                        "user_email" => $option_user->email,
                        "email" => strtolower($option_user->email . $suffix_domain),
                        "contract_app" => config('app.pac_contract_app'),
                        "app_env" => config('app.pac_app_env'),
                        "contract_server" => config('app.pac_contract_server'),
                        "user_auth" => AppUtils::AUTH_FLG_OPTION,
                        "user_first_name" => $option_user->given_name,
                        "user_last_name" => $option_user->family_name,
                        "company_name" => $company ? $company->company_name : '',
                        "company_id" => $company_id,
                        "status" => AppUtils::convertState($option_user->state_flg),
                        "system_name" => $company ? $company->system_name : '',
                        'update_user_email' => null
                    ];

                    $client = IdAppApiUtils::getAuthorizeClient();
                    if (!$client) {
                        Log::error('Connect ID API failed');
                    }
                    $result = $client->put("users", [
                        RequestOptions::JSON => $apiUser
                    ]);
                    if ($result->getStatusCode() == 200) {
                        DB::commit();
                    }else{
                        DB::rollBack();
                        Log::error("Call ID App Api to update company user failed. Response Body " . $result->getBody());
                    }
                }
            }

        }


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
