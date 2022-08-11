<?php

namespace App\Listeners;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use App\CompanyAdmin;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SamlLoginListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Saml2LoginEvent  $event
     * @return void
     */
    public function handle(Saml2LoginEvent $event)
    {
        $user = $event->getSaml2User();
        $email = $user->getAttribute('EmailAddress');

        $admin = CompanyAdmin::where('email', $email[0])->where('state_flg', AppUtils::STATE_VALID)->first();

        if ($admin){
            $company = DB::table('mst_company')->select('state','system_name')->where('id',$admin->mst_company_id)->first();
        }

        if ($admin && $company->state) {
            \Auth::login($admin, true);
            if ($admin->role_flg) {
                $admin->assignRole(PermissionUtils::ROLE_COMPANY_MANAGER);
            } else {
                $admin->assignRole(PermissionUtils::ROLE_COMPANY_NORMAL_ADMIN);
            }
            Session::put(AppUtils::SESSION_ADMIN_LOGIN_TYPE, AppUtils::SESSION_ADMIN_LOGIN_TYPE_COMPANY);

            $countUser = User::query()->where('email', $email)->where('state_flg', AppUtils::STATE_VALID)->count();
            Session::put(AppUtils::SESSION_ADMIN_HAS_USER_ACCOUNT, $countUser?true:false);
            if($company->system_name){
                Session::put('system_name', $company->system_name);
            }
            Session::put('loginWithRememberChecked', true);
        } else{
            return redirect()->route('getPwd')->with(['error' => 'メールアドレス、又はパスワードが正しくありません', 'email' => $email]);
        }
    }
}
