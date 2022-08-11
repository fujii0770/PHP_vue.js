<?php
namespace App\Utils;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Session;
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 10/26/2019
 * Time: 3:19 PM
 */
class UserApiUtils
{
    const STATE_VALID = 1; // �Є�
    public static function getApiClient(){
        $api_host = rtrim(config('app.api_host'), "/");
        $api_base = ltrim(config('app.api_base'),"/");
        $client = new Client(['base_uri' => $api_host."/".$api_base, 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']
        ]);
        return $client;
    }

    public static function getStampApiClient(){
        $api_host = rtrim(config('app.stamp_api_host'), "/");
        $api_base = ltrim(config('app.stamp_api_base'),"/");
        $client = new Client(['base_uri' => $api_host."/".$api_base, 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']
        ]);
        return $client;
    }

    public static function getPdfApiClient(): Client
    {
        $api_host = rtrim(config('app.stamp_api_host'), "/");
        $api_base = ltrim(config('app.stamp_api_base'),"/");
        $client = new Client(['base_uri' => $api_host."/".$api_base, 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'multipart/form-data;']
        ]);
        return $client;
    }

    public static function getAuthorizedApiClient($access_token){
        $api_host = rtrim(config('app.api_host'), "/");
        $api_base = trim(config('app.api_base'),"/");
        $client = new Client(['base_uri' => $api_host."/".$api_base."/", 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest', 'Authorization' => 'Bearer ' . $access_token]
        ]);
        return $client;
    }

    public static function getAuthorizedPublicApiClient($access_token){
        $api_host = rtrim(config('app.api_host'), "/");
        $api_base = trim(config('app.api_base'),"/");
        $client = new Client(['base_uri' => $api_host."/".$api_base."/public/", 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest', 'Authorization' => 'Bearer ' . $access_token]
        ]);
        return $client;
    }

    public static function getAuthorizedApiClientWithRequest($request){
        $token = $request->bearerToken();
        // PAC_5-1488 クラウドストレージを追加する
        if(isset($request['usingHash']) && ((string)$request['usingHash'] === 'true' || (string)$request['usingHash'] === '1')) {
            $client = self::getAuthorizedPublicApiClient($token);
        }else{
            $client = self::getAuthorizedApiClient($token);
        }
        return $client;
    }

    public static function getAuthorizeClient()
    {
        $accessToken = null;
        $sessionKey = "user_api_access_token";
        $serverApi = config('app.server_api');
        $api_host = rtrim($serverApi['host'], "/");
        $api_base = trim($serverApi['base_url'],"/");
        if (Session::has($sessionKey)){
            $accessToken = Session::get($sessionKey);
        }else{
            $client = new Client(['base_uri' => $api_host.'/', 'http_errors' => false, 'verify' => false,
                'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']
            ]);
            // Try to get an access token using the client credentials grant.

            Log::debug("Call User Api to get access token");

            $result = $client->post("oauth/token",array(
                'form_params' => array(
                    'grant_type' => 'client_credentials',
                    'client_id' => trim($serverApi['client_id']),
                    'client_secret' => trim($serverApi['client_secret'])
                )
            ));
            if($result->getStatusCode() == 200) {
                $response = json_decode((string) $result->getBody());
                $accessToken = $response->access_token;
                Session::put($sessionKey, $accessToken);
            }else{
                Log::warning("Call ENV Api to get access token failed. Response Body ".$result->getBody());
                return false;
            }

        }
        $client = new Client(['base_uri' => $api_host."/".$api_base."/", 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest', 'Authorization' => 'Bearer ' . $accessToken]
        ]);
        return $client;
    }
}