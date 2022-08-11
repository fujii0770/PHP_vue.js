<?php

namespace App\Listeners;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use App\Utils\AppUtils;
use App\Utils\UserApiUtils;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Response;
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
        $url_domain_id = null;
        if (Cookie::has('sso_company_6')){
            $url_domain_id = Cookie::get('sso_company_6');
        }
        if (!$url_domain_id){
            Log::alert("Call ENV Api to get sso login for $email[0]. Not found url domain");
            return redirect()->route('login')->with(['error' => 'メールアドレス、又はパスワードが正しくありません', 'email' => $email]);
        }

        $client = UserApiUtils::getAuthorizeClient();
        $cookies = new CookieJar();
        $result = $client->post("sso-login",[
            'cookies' => $cookies,
            RequestOptions::JSON =>  ['username' => $email[0], 'remember' => true, 'urlDomainId' => $url_domain_id,
                'clientIp' => request()->getClientIp(), 'userAgent' => request()->userAgent()]
        ]);
        $resultLogin = json_decode((string) $result->getBody());

        if($result->getStatusCode() == 200){
            if (isset($resultLogin->token)){
                $resultLogin->user->isAuditUser = $resultLogin->is_audit_user;
                if ($resultLogin->is_audit_user){
                    $resultLogin->user->family_name = $resultLogin->user->account_name;
                    $resultLogin->user->given_name = '';
                }
                Session::put('resultLogin', $resultLogin);
                Session::put($resultLogin->token, $resultLogin->user);
                Session::put('accessToken',$resultLogin->token);
                Session::put('limit',$resultLogin->limit);
                Session::put('admin',$resultLogin->admin);
                Session::put('mfa',$resultLogin->mfa);
                if($resultLogin->needResetPass){
                    Session::put('needResetPass',$resultLogin->needResetPass);
                }
                Session::put('tokenResetPass',$resultLogin->token);
                Session::put('username', request('username'));
                Session::put('is_audit_user', $resultLogin->is_audit_user);

                $cookie = $cookies->toArray()[0] ?? null;
                if ($cookie) {
                    Cookie::queue(Cookie::forever($cookie['Name'], $cookie['Value']));
                }
                $redirectionUrl = request()->url();
                Log::debug("SAML Redirection Url:  ".$redirectionUrl);
                if (request('from_admin') == 'true' || \Illuminate\Support\Facades\Request::ajax()) {
                    return Response::json(['redirectUrl' => $redirectionUrl],200);
                }else{
                    return redirect($redirectionUrl);
                }
            }
        }else{
            Log::alert("Call ENV Api to get sso login for $email[0]. Response Body ".$result->getBody());
            return redirect()->route('login')->with(['error' => 'メールアドレス、又はパスワードが正しくありません', 'email' => $email]);
        }

    }
}
