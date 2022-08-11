<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 10/24/2019
 * Time: 10:23 AM
 */
namespace App\Auth;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use App\Models\Company;
use App\Models\PasswordPolicy;
use App\ShachihataAdmin;
use Carbon\Carbon;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class UserProvider extends EloquentUserProvider implements UserProviderContract
{
    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $loginType = Session::get(AppUtils::SESSION_ADMIN_LOGIN_TYPE);

        if ($loginType == AppUtils::SESSION_ADMIN_LOGIN_TYPE_SHACHIHATA){
            return ShachihataAdmin::where('id', $identifier)->first();
        }else{
            return parent::retrieveById($identifier);
        }
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        foreach (Session::all() as $k => $v) {
            if (!Str::startsWith($k, '_')) {
                Session::forget($k);
            }
        }
        $user = parent::retrieveByToken($identifier, $token);
        if (is_null($user)) {
            $modelTmp = $this->getModel();
            $this->setModel('App\ShachihataAdmin');
            $user = parent::retrieveByToken($identifier, $token);
            if (is_null($user)) {
                $this->setModel($modelTmp);
            } else {
                $user->assignRole(PermissionUtils::ROLE_SHACHIHATA_ADMIN);
                Session::put(AppUtils::SESSION_ADMIN_LOGIN_TYPE, AppUtils::SESSION_ADMIN_LOGIN_TYPE_SHACHIHATA);
                Session::put(AppUtils::SESSION_ADMIN_HAS_USER_ACCOUNT, false);
            }
        } else {
            $company = Company::where('id',  $user->mst_company_id)->first();
            $encrypter = app(\Illuminate\Contracts\Encryption\Encrypter::class);
            $sso_login = false;
            if (Cookie::has('sso_login')){
                $cookie = $encrypter->decrypt(Cookie::get('sso_login'), false);
                $cookie = explode('|', $cookie);
                if (count($cookie) > 1){
                    $sso_login = $cookie[1];
                }
            }
            if (!config('app.enable_sso_login') || !$sso_login){
                $password_policy = PasswordPolicy::where('mst_company_id', $user->mst_company_id)->first();
                if($password_policy && $password_policy->validity_period != 0) {
                    if ($user->password_change_date == null) {
                        Session::put('admin.needResetPass', true);
                    } else {
                        $password_change_date = new \DateTime($user->password_change_date );
                        $now = Carbon::now();
                        $diff = $now->diffInHours($password_change_date);
                        if($diff >= $password_policy->validity_period*24) {
                            Session::put('admin.needResetPass', true);
                        }
                    }
                    Session::put('admin.email', $user->email);
                }
            }
        }
        
        return $user;
    }
}