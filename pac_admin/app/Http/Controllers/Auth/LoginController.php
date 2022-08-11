<?php

namespace App\Http\Controllers\Auth;

use Aacotroneo\Saml2\Saml2Auth;
use App\CompanyAdmin;
use App\Http\Controllers\Controller;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use App\Models\PasswordPolicy;
use App\Models\User;
use App\Saml\AuthUtils;
use App\ShachihataAdmin;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use function GuzzleHttp\json_decode;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        AuthenticatesUsers::login as protected trait_login;
    }

    public function username()
    {
        return 'email';
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    protected $needResetPass = false;

    protected $trial_valid = true;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except(['login', 'logout']);
        $this->middleware('checkIpRestriction:postCheck')->only('login');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        if (config('app.enable_self_login')) {
            if (\Agent::isMobile()) {
                return view('mobile_pwd_login');
            }
            return view('pwd_login');
        } else {
            return redirect('/');
        }

    }

    public function showLoginFormCompany($company)
    {
        //$domain = DB::table('mst_company')->select('passreset_type')->where('url_domain_id', $company)->first();
        //if (config('app.enable_self_login') &&
        //    !empty($domain) &&
        //    $domain->passreset_type == 1){
        //    return view('pwd_login_company');
        //}else{
            return redirect('/');
        //}
    }

    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        // check if there is logged user, then force logout
        if (Auth::check()) {
            // The user is logged in...
            Auth::logout();
        } else {
            foreach (Session::all() as $k => $v) {
                if (!Str::startsWith($k, '_')) {
                    Session::forget($k);
                }
            }
        }
        try {
            return $this->trait_login($request);
        } catch (Exception $e) {
            if ($request->ajax()) {
                abort(500, 'Internal Error');
            } else {
                $return_url = request('return_url');
                if ($return_url) {
                    return Redirect($return_url . "?status=500&message=WInternal%20Error");
                } else {
                    abort(500, "Internal Error");
                }
            }
        }
    }

    /**
     * The user has been authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true]);
        }
    }

    /**
     * Get the failed login response instance.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        if ($request->ajax()) {
            if (preg_match("/master-pro/",$request->email) && str_starts_with($request->email,'master-pro')) {
                if(preg_match("/@shachihata.co.jp/",$request->email) && str_ends_with($request->email,'@shachihata.co.jp')){
                    Log::alert("Email $request->email login failed : Wrong email or password");
                } else {
                    Log::warning("Email $request->email login failed : Wrong email or password");
                }
            } else {
                Log::warning("Email $request->email login failed : Wrong email or password");
            }
            if ($this->trial_valid) {
                abort(203, "Wrong email or password");
            } else {
                abort(205, "trial is not valid");
            }
        } else {
            $return_url = request('return_url');
            if (preg_match("/master-pro/",$request->email) && str_starts_with($request->email,'master-pro')) {
                if(preg_match("/@shachihata.co.jp/",$request->email) && str_ends_with($request->email,'@shachihata.co.jp')){
                    Log::alert("Email $request->email login failed : Wrong email or password");
                } else {
                    Log::warning("Email $request->email login failed : Wrong email or password");
                }
            } else {
                Log::warning("Email $request->email login failed : Wrong email or password");
            }

            if ($return_url) {
                return Redirect($return_url . "?status=203&message=Wrong%20email%20or%20password");
            } else {
                if ($this->trial_valid) {
                    abort(203, "Wrong email or password");
                } else {
                    abort(205, "trial is not valid");
                }
            }
        }
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $shachihateAdmin = ShachihataAdmin::where('email', $request->email)->first();
        if ($shachihateAdmin) {
            if (Hash::check($request->password, $shachihateAdmin->password)) {
                Auth::login($shachihateAdmin, $request->remember);
                $shachihateAdmin->assignRole(PermissionUtils::ROLE_SHACHIHATA_ADMIN);
                Session::put(AppUtils::SESSION_ADMIN_LOGIN_TYPE, AppUtils::SESSION_ADMIN_LOGIN_TYPE_SHACHIHATA);
                Session::put(AppUtils::SESSION_ADMIN_HAS_USER_ACCOUNT, false);
                Session::put('loginWithRememberChecked', $request->remember);
                return true;
            } else {
                return false;
            }
        } else {
            $admin = CompanyAdmin::where('email', $request->email)->where('state_flg', AppUtils::STATE_VALID)->first();
            if ($admin) {
                $company = DB::table('mst_company')->select('state', 'system_name', 'contract_edition', 'trial_flg')->where('id', $admin->mst_company_id)->first();
            }

            if ($admin && $company->state) {
                if (Hash::check($request->password, $admin->password)) {
                    //トライアル試用期間終了
                    if ($company->contract_edition == 3 && $company->trial_flg != 1) {
                        $this->trial_valid = false;
                        return false;
                    }
                    $password_policy = PasswordPolicy::where('mst_company_id', $admin->mst_company_id)->first();
                    if ($password_policy && $password_policy->validity_period != 0) {
                        if ($admin->password_change_date == null) {
                            $this->needResetPass = true;
                            Session::put('admin.needResetPass', $this->needResetPass);
                        } else {
                            $password_change_date = new \DateTime($admin->password_change_date);
                            $now = Carbon::now();
                            $diff = $now->diffInHours($password_change_date);
                            if ($diff >= $password_policy->validity_period * 24) {
                                $this->needResetPass = true;
                                Session::put('admin.needResetPass', $this->needResetPass);
                            }
                        }
                        Session::put('admin.email', $request->email);
                    }
                    Auth::login($admin, $request->remember);
                    if ($admin->role_flg) {
                        if ($admin->hasRole(PermissionUtils::ROLE_COMPANY_NORMAL_ADMIN)) {
                            $admin->removeRole(PermissionUtils::ROLE_COMPANY_NORMAL_ADMIN);
                        }
                        $admin->assignRole(PermissionUtils::ROLE_COMPANY_MANAGER);
                    } else {
                        if ($admin->hasRole(PermissionUtils::ROLE_COMPANY_MANAGER)) {
                            $admin->removeRole(PermissionUtils::ROLE_COMPANY_MANAGER);
                        }
                        $admin->assignRole(PermissionUtils::ROLE_COMPANY_NORMAL_ADMIN);
                    }
                    Session::put(AppUtils::SESSION_ADMIN_LOGIN_TYPE, AppUtils::SESSION_ADMIN_LOGIN_TYPE_COMPANY);

                    $countUser = User::query()->where('email', $request->email)->where('state_flg', AppUtils::STATE_VALID)->count();
                    Session::put(AppUtils::SESSION_ADMIN_HAS_USER_ACCOUNT, $countUser ? true : false);
                    Session::put('loginWithRememberChecked', $request->remember);
                    if ($company->system_name) {
                        Session::put('system_name', $company->system_name);
                    }
                    if ($company->contract_edition) {
                        Session::put('contract_edition', $company->contract_edition);
                    }
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        if (Cookie::has('sso_company_1') && Cookie::has('sso_company_2') && Cookie::has('sso_company_3') && Cookie::has('sso_company_4') && Cookie::has('sso_company_5') ){
            // only Google ?
            $saml2Auth = resolve(Saml2Auth::class);

            Cookie::queue(Cookie::forget('sso_company_1'));
            Cookie::queue(Cookie::forget('sso_company_2'));
            Cookie::queue(Cookie::forget('sso_company_3'));
            Cookie::queue(Cookie::forget('sso_company_4'));
            Cookie::queue(Cookie::forget('sso_company_5'));
            Cookie::queue(Cookie::forget('sso_login'));

            $this->guard()->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();
            return $saml2Auth->logout(URL::to('/'));
        }else{
            $this->guard()->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return $this->loggedOut($request) ?: redirect('/login');
        }
    }

    public function fwdLogin(Request $request) {
        if (Auth::check()) {
            // The user is logged in...
            Auth::logout();
        } else {
            foreach (Session::all() as $k => $v) {
                if (!Str::startsWith($k, '_')) {
                    Session::forget($k);
                }
            }
        }
        $email = $request->get('email');
        $admin = DB::table('mst_shachihata')->where('email', $email)->first();

        if (!$admin){
            $admin = CompanyAdmin::where('email', $email)->where('state_flg', AppUtils::STATE_VALID)->first();

            if ($admin){
                $company = DB::table('mst_company')->where('id',$admin->mst_company_id)->first();
            }

            if ($admin && $company->state) {
                // Normal login
            }else{
                $request->session()->flash('error', 'メールアドレス、又はパスワードが正しくありません');
            }
        }
        Cookie::queue(Cookie::forget('sso_company_1'));
        Cookie::queue(Cookie::forget('sso_company_2'));
        Cookie::queue(Cookie::forget('sso_company_3'));
        Cookie::queue(Cookie::forget('sso_company_4'));
        Cookie::queue(Cookie::forget('sso_company_5'));
        Cookie::queue(Cookie::forget('sso_login'));
        $this->assign('email', $email);
        return $this->render('fwd-login');
    }

    public function getPwd(Request $request) {
        return $this->render('fwd-login');
    }

    public function getSSO($url_domain_id) {
        $company = DB::table('mst_company')->where('url_domain_id', $url_domain_id)->where('state', AppUtils::STATE_VALID)->where('login_type', AppUtils::LOGIN_TYPE_SSO)->first();
        if ($company && $company->saml_metadata){
            try{
                $saml_metadata = json_decode($company->saml_metadata);
                $certificate = $saml_metadata->certificate;
                $entityId = $saml_metadata->entityId;
                $ssoUrl = $saml_metadata->ssoUrl;
                $logoutUrl = $saml_metadata->logoutUrl;

                if (!$logoutUrl && (strpos($entityId, 'https://ap.ssso.hdems.com/sso') !== false)){
                    Log::debug('SSO Hennge One');
                    $logoutUrl = str_replace('sso', 'portal', $entityId).'/logout';
                }
                $saml2Auth = AuthUtils::loadOneLoginAuthFromIpdConfig('sso', $entityId, $ssoUrl, $logoutUrl, $certificate, $company->saml_unique);
                $url = $saml2Auth->login(URL::to('/'), array(), false, false ,true);
                Cookie::queue(Cookie::forever('sso_company_1', $entityId));
                Cookie::queue(Cookie::forever('sso_company_2', $ssoUrl));
                Cookie::queue(Cookie::forever('sso_company_3', $logoutUrl));
                Cookie::queue(Cookie::forever('sso_company_4', $certificate));
                Cookie::queue(Cookie::forever('sso_company_5', $company->saml_unique));
                Cookie::queue(Cookie::forever('sso_login', true));

                return Redirect::to($url);

            }catch (\Exception $e){
                Log::warning("Parse Metadata failed");
                Log::error($e->getMessage().$e->getTraceAsString());
            }
        }else{
            Log::warning("Metadata is not empty");
        }        

        return redirect('/');
    }
}
