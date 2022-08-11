<?php


namespace App\Utils;


use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;use PHPUnit\Exception;

class AutoLoginUtils
{
    public static function check()
    {
        $cookies = request()->cookie();
        $jar = new CookieJar();
        foreach ($cookies as $k => $v) {
            if (Str::startsWith($k, 'remember_web')) {
                $c = new SetCookie();
                $c->setName($k);
                $c->setValue($v);
                $c->setDomain(parse_url(config('app.api_host'), PHP_URL_HOST));
                $jar->setCookie($c);
                break;
            }
        }
        if ($jar->count() == 0) {
            return false;
        }

        $encrypter = app(\Illuminate\Contracts\Encryption\Encrypter::class);
        $sso_login = false;
        if (Cookie::has('sso_login')){
            try{
                $cookie = $encrypter->decrypt(Cookie::get('sso_login'), false);
                $cookie = explode('|', $cookie);
                if (count($cookie) > 1){
                    $sso_login = $cookie[1];
                }
            }catch(\Exception $e){
                Log::warning($e->getMessage().$e->getTraceAsString());
            }
            
        }
        $client = UserApiUtils::getApiClient();
        $result = $client->post("recall",[
            'cookies' => $jar,
            RequestOptions::JSON =>  ['clientIp' => request()->getClientIp(), 'userAgent' => request()->userAgent(), 'sso_login' => $sso_login]
        ]);

        $return_url = request('return_url');
        $resultLogin = json_decode((string) $result->getBody());
        if($result->getStatusCode() == 200 && isset($resultLogin->token)) {
            foreach (Session::all() as $k => $v) {
                if (!Str::startsWith($k, '_')) {
                    Session::forget($k);
                }
            }
            Session::put('resultLogin', $resultLogin);
            Session::put('return_url', $return_url);
            Session::put($resultLogin->token, $resultLogin->user);
            Session::put('accessToken', $resultLogin->token);
            Session::put('limit', $resultLogin->limit);
            Session::put('admin',$resultLogin->admin);
            Session::put('mfa', $resultLogin->mfa);
            if($resultLogin->needResetPass){
                Session::put('needResetPass',$resultLogin->needResetPass);
                setcookie($resultLogin->recallerName);
            }
            Session::put('tokenResetPass',$resultLogin->token);
            Session::put('username', $resultLogin->user->email);
            Session::put('viaRemember', true);
            
            return true;
        } elseif ($result->getStatusCode() == 401 && isset($resultLogin->recallerName)) {
            setcookie($resultLogin->recallerName);
        }

        return false;
    }
    
}