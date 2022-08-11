<?php

namespace App\Http\Controllers;

use Aacotroneo\Saml2\Saml2Auth;
use App\Saml\AuthUtils;
use App\Utils\UserApiUtils;
use App\Utils\UserLoginUtils;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use function GuzzleHttp\json_decode;

class AuthController extends Controller {

    public function __construct()
    {
        
    }

    public function login() {
        if (Session::has('accessToken')){
            $token = Session::get('accessToken');
            $this->logoutWithToken($token);
        } else {
            foreach (Session::all() as $k => $v) {
                if (!Str::startsWith($k, '_')) {
                    Session::forget($k);
                }
            }
        }
        $return_url = request('return_url');
        $client = UserApiUtils::getApiClient();
        $cookies = new CookieJar();

        $result = $client->post("login?checkLoginWeb=1",[
            'cookies' => $cookies,
            RequestOptions::JSON =>  ['username' => request('username'), 'password' => request('password'), 'remember' => request('remember'),
                'clientIp' => request()->getClientIp(), 'userAgent' => request()->userAgent(),'from_admin' => !empty(request('from_admin')) ? 1 : 0]
        ]);
        $resultLogin = json_decode((string) $result->getBody());

        if($result->getStatusCode() == 200){
            //トライアル試用期間終了
            if($resultLogin->user->contract_edition == 3 && $resultLogin->user->trial_flg != 1 ){
                abort(205, "trial is not valid");
            }
            // メールから文書を開いたときにログイン必須になっている動作の改善  PAC_5-2283 大文字を小文字に変換
            $return_url_arr = explode('?', $return_url);
            if (count($return_url_arr) > 1) {
                if(!Hash::check(mb_strtolower(request('username')), $return_url_arr[1])){//mb_strtolower() PAC_5-2283 大文字を小文字に
                    abort(406, "URL無効");
                }
            }
            if (isset($resultLogin->token)){
                $resultLogin->user->isAuditUser = $resultLogin->is_audit_user;
                if ($resultLogin->is_audit_user){
                    $resultLogin->user->family_name = $resultLogin->user->account_name;
                    $resultLogin->user->given_name = '';
                }

                Session::put('resultLogin', $resultLogin);
                Session::put('return_url', $return_url);
                Session::put($resultLogin->token, $resultLogin->user);
                Session::put('accessToken',$resultLogin->token);
                Session::put('limit',$resultLogin->limit);
                Session::put('admin',$resultLogin->admin);
                Session::put('mfa',$resultLogin->mfa);
                if($resultLogin->needResetPass){
                    Session::put('needResetPass',$resultLogin->needResetPass);
                }
                Session::put('tokenResetPass',$resultLogin->token);
                Session::put('username', mb_strtolower(request('username')));//mb_strtolower() PAC_5-2283 大文字を小文字に
                Session::put('is_audit_user', $resultLogin->is_audit_user);

                if (request('remember')) {
                    $cookie = $cookies->toArray()[0] ?? null;
                    if ($cookie) {
                        Cookie::queue(Cookie::forever($cookie['Name'], $cookie['Value']));
                    }
                }
                $redirectionUrl = rtrim(config('app.url'), '/');
                if ($resultLogin->is_audit_user){
                    $redirectionUrl = $redirectionUrl.'/'.ltrim(config('app.audit_home_screen'), '/');
                }else {
                    if ($resultLogin->pageDisplayFirst === "下書き一覧") {
                        $redirectionUrl = $redirectionUrl . '/saved';
                    } elseif ($resultLogin->pageDisplayFirst === "受信一覧") {
                        $redirectionUrl = $redirectionUrl . '/received';
                    } elseif ($resultLogin->pageDisplayFirst === "送信一覧") {
                        $redirectionUrl = $redirectionUrl . '/sent';
                    } elseif ($resultLogin->pageDisplayFirst === "完了一覧") {
                        $redirectionUrl = $redirectionUrl . '/completed';
                    } elseif ($resultLogin->pageDisplayFirst === "ポータル") {
                        $redirectionUrl = $redirectionUrl . '/portal';
                    }else{
                        $redirectionUrl = $redirectionUrl . '/';
                    }
                }
                //clear groupware
                $arrCookieGroupWare = ['accessToken','userRoles','userProfile','dateGetToken','refreshToken','emailGroupwareAccessToken'];
                foreach($arrCookieGroupWare as $cookie){
                    Cookie::queue(Cookie::forget($cookie, '/', config('app.gw_domain')));
                }


                if (request('from_admin') == 'true' || \Illuminate\Support\Facades\Request::ajax()) {
                    return Response::json(['redirectUrl' => $return_url ?$return_url:$redirectionUrl],200);
                }else{
                    return redirect($redirectionUrl);
                }
            }
        }else{
            Log::debug("Login response body ".$result->getBody());
            if (request()->ajax()) {
                if (isset($resultLogin->status)){
                    abort($resultLogin->status, $resultLogin->message);
                }else{
                    abort($result->getStatusCode(), $resultLogin->message);
                }
            }else{
                return redirect()->route('login')->with(['error' => isset($resultLogin->message)?$resultLogin->message:'メールアドレス、又はパスワードが正しくありません', 'username' => request('username')]);
            }
        }
    }

    private function logoutWithToken($token){

        $result = [];
        $client = UserApiUtils::getAuthorizedApiClient($token);
        if (Cookie::has('sso_company_1') && Cookie::has('sso_company_2') && Cookie::has('sso_company_3') && Cookie::has('sso_company_4_1') && Cookie::has('sso_company_4_2')  && Cookie::has('sso_company_5') ){
            // only Google ?
            $saml2Auth = resolve(Saml2Auth::class);

            Cookie::queue(Cookie::forget('sso_company_1'));
            Cookie::queue(Cookie::forget('sso_company_2'));
            Cookie::queue(Cookie::forget('sso_company_3'));
            Cookie::queue(Cookie::forget('sso_company_4_1'));
            Cookie::queue(Cookie::forget('sso_company_4_2'));
            Cookie::queue(Cookie::forget('sso_company_5'));
            Cookie::queue(Cookie::forget('sso_company_6'));
            Cookie::queue(Cookie::forget('sso_login'));

            if (config('app.enable_sso_slo')){
                $redirectUrl = $saml2Auth->logout(URL::to('/'), null,  null, null, true);
                $result['redirectUrl'] = $redirectUrl;
            }else{
                if($client){
                    $response = $client->get('setting/getMyCompany');
                    if($response->getStatusCode() == 200) {
                        $company = json_decode($response->getBody())->data;
                        if ($company){
                            if(isset($company->url_domain_id) && $company->url_domain_id) {
                                $result['redirectUrl'] = URL::to("/sso/$company->url_domain_id/login");
                            }else{
                                Log::warning("logoutWithToken: call ENV Api to get company, without url_domain_id. Response Body ".$response->getBody());
                            }
                        }else{
                            Log::warning("logoutWithToken: call ENV Api to get company, without company. Response Body ".$response->getBody());
                        }
                    }else{
                        Log::warning("logoutWithToken: call ENV Api to get company failed. Response Body ".$response->getBody());
                    }
                }else{
                    Log::warning("logoutWithToken: cannot get ENV Api to get company");
                }
            }
        }
        $client->get("logout",[]);
        Session::flush();

        return Response::json($result, 200);
    }

    public function logout(Request $request) {
        $token = $request->bearerToken();
        if (!$token && Session::has('accessToken')){
            $token = Session::get('accessToken');
        }
        return $this->logoutWithToken($token);
    }

    public function getSSO($url_domain_id) {
        Cookie::queue(Cookie::forget('sso_company_1'));
        Cookie::queue(Cookie::forget('sso_company_2'));
        Cookie::queue(Cookie::forget('sso_company_3'));
        Cookie::queue(Cookie::forget('sso_company_4_1'));
        Cookie::queue(Cookie::forget('sso_company_4_2'));
        Cookie::queue(Cookie::forget('sso_company_5'));
        Cookie::queue(Cookie::forget('sso_company_6'));
        Cookie::queue(Cookie::forget('sso_login'));

        if(config('app.enable_sso_login')){
            $client = UserApiUtils::getAuthorizeClient();
            if($client){
                $result = $client->get('getCompanyByDomain/'.$url_domain_id);
                if($result->getStatusCode() == 200) {
                    $company = json_decode($result->getBody())->data;
                    if ($company){
                        if($company->login_type == UserLoginUtils::LOGIN_TYPE_SSO) {
                            if ($company->saml_metadata){
                                try{
                                    $saml_metadata = json_decode($company->saml_metadata);
                                    $certificate = $saml_metadata->certificate;
                                    $entityId = $saml_metadata->entityId;
                                    $ssoUrl = $saml_metadata->ssoUrl;
                                    $logoutUrl = $saml_metadata->logoutUrl;
                                    if (!$logoutUrl && (strpos($entityId, 'https://ap.ssso.hdems.com/sso') !== false)){
                                        Log::debug('SSO Hennge One');
                                        $logoutUrl = str_replace('https://ap.ssso.hdems.com/sso/', 'https://ap.ssso.hdems.com/portal/', $entityId).'/logout/';
                                    }
                                    Cookie::queue(Cookie::forever('sso_company_1', $entityId));
                                    Cookie::queue(Cookie::forever('sso_company_2', $ssoUrl));
                                    Cookie::queue(Cookie::forever('sso_company_3', $logoutUrl));
                                    Cookie::queue(Cookie::forever('sso_company_4_1', substr($certificate,0,floor(strlen($certificate)/2))));
                                    Cookie::queue(Cookie::forever('sso_company_4_2', substr($certificate,floor(strlen($certificate)/2))));
                                    Cookie::queue(Cookie::forever('sso_company_5', $company->saml_unique));
                                    Cookie::queue(Cookie::forever('sso_company_6', $url_domain_id));
                                    Cookie::queue(Cookie::forever('sso_login', true));

                                    $saml2Auth = AuthUtils::loadOneLoginAuthFromIpdConfig('sso', $entityId, $ssoUrl, $logoutUrl, $certificate, $company->saml_unique);

                                    $redirectUrl = request('redirectUrl');
                                    if (!$redirectUrl){
                                        $redirectUrl = URL::to('/sso_home');
                                    }
                                    $url = $saml2Auth->login($redirectUrl, array(), false, false ,true);

                                    return redirect($url);

                                }catch (\Exception $e){
                                    Log::warning("Parse Metadata failed");
                                    Log::error($e->getMessage().$e->getTraceAsString());
                                }
                            }else{
                                Log::warning("Metadata is empty");
                            }
                        }else{
                            Log::warning("Call ENV Api to get company, without sso company. Response Body ".$result->getBody());
                        }
                    }else{
                        Log::warning("Call ENV Api to get company, without company. Response Body ".$result->getBody());
                    }
                }else{
                    Log::error("Call ENV Api to get company failed. Response Body ".$result->getBody());
                }
            }else{
                Log::error("Cannot get ENV Api to get company");
            }
        }

        return redirect('/');
    }

    public function getSSOLogin($url_domain_id) {
        Cookie::queue(Cookie::forget('sso_company_1'));
        Cookie::queue(Cookie::forget('sso_company_2'));
        Cookie::queue(Cookie::forget('sso_company_3'));
        Cookie::queue(Cookie::forget('sso_company_4_1'));
        Cookie::queue(Cookie::forget('sso_company_4_2'));
        Cookie::queue(Cookie::forget('sso_company_5'));
        Cookie::queue(Cookie::forget('sso_company_6'));
        Cookie::queue(Cookie::forget('sso_login'));

        if(config('app.enable_sso_login')){
            $client = UserApiUtils::getAuthorizeClient();
            if($client){
                $result = $client->get('getCompanyByDomain/'.$url_domain_id);
                if($result->getStatusCode() == 200) {
                    $company = json_decode($result->getBody())->data;
                    if ($company){
                        if($company->login_type == UserLoginUtils::LOGIN_TYPE_SSO) {
                            $this->assign('background_color', $company->background_color);
                            $this->assign('logo_file_data', $company->logo_file_data);
                            $this->assign('color', $company->color);
                            $this->assign('url_domain_id', $url_domain_id);
                            return $this->render('login-sso');
                        }else{
                            Log::warning("Call ENV Api to get company, without sso company. Response Body ".$result->getBody());
                        }
                    }else{
                        Log::warning("Call ENV Api to get company, without company. Response Body ".$result->getBody());
                    }
                }else{
                    Log::error("Call ENV Api to get company failed. Response Body ".$result->getBody());
                }
            }else{
                Log::error("Cannot get ENV Api to get company");
            }
        }

        return redirect('/');
    }

    public function getSSOHome(){
        $resultLogin = Session::get('resultLogin');
        $redirectionUrl = rtrim(config('app.url'), '/');
        if ($resultLogin){
            if ($resultLogin->is_audit_user){
                $redirectionUrl = $redirectionUrl.'/'.ltrim(config('app.audit_home_screen'), '/');
            }else {
                if ($resultLogin->pageDisplayFirst === "下書き一覧") {
                    $redirectionUrl = $redirectionUrl . '/saved';
                } elseif ($resultLogin->pageDisplayFirst === "受信一覧") {
                    $redirectionUrl = $redirectionUrl . '/received';
                } elseif ($resultLogin->pageDisplayFirst === "送信一覧") {
                    $redirectionUrl = $redirectionUrl . '/sent';
                } elseif ($resultLogin->pageDisplayFirst === "完了一覧") {
                    $redirectionUrl = $redirectionUrl . '/completed';
                } elseif ($resultLogin->pageDisplayFirst === "ポータル") {
                    $redirectionUrl = $redirectionUrl . '/portal';
                } else{
                    $redirectionUrl = $redirectionUrl . '/';
                }
            }
        }
        Log::debug("SSO Home Redirection Url:  ".$redirectionUrl);
        return redirect($redirectionUrl);
    }

    public function device_check(){

        if (config('app.enable_self_login')){
            if (Agent::isMobile()) {
                return view('mobile_pwd_login');
            }
            return view('pwd_login');
        }else{
            return redirect('/received/');
        }


    }
}
