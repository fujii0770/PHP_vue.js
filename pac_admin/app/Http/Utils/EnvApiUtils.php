<?php

/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/3/19
 * Time: 10:22
 */

namespace App\Http\Utils;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class EnvApiUtils
{
    const ENV_FLG_AWS = 0;
    const ENV_FLG_K5 = 1;

    public static function getAuthorizeClient($envId,$serverFlg,$connect_timeout=true)
    {
        $accessToken = null;
        $sessionKey = $envId.$serverFlg."_env_api_access_token";
        $serverEnvApi = config('app.server_env_api');
        $api_host = rtrim($serverEnvApi[$envId.$serverFlg]['host'], "/");
        $api_base = trim($serverEnvApi[$envId.$serverFlg]['base_url'],"/");
        if (Session::has($sessionKey)){
            $accessToken = Session::get($sessionKey);
        }else{
            if($connect_timeout){
                $client = new Client(['base_uri' => $api_host.'/', 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
                    'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']
                ]);
            }else{
                $client = new Client(['base_uri' => $api_host.'/', 'http_errors' => false, 'verify' => false,
                    'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']
                ]);
            }
            // Try to get an access token using the client credentials grant.

            Log::debug("Call ENV Api to get access token for env $envId, server $serverFlg, host $api_host, base url $api_base");

            $result = $client->post("oauth/token",array(
                'form_params' => array(
                    'grant_type' => 'client_credentials',
                    'client_id' => trim($serverEnvApi[$envId.$serverFlg]['client_id']),
                    'client_secret' => trim($serverEnvApi[$envId.$serverFlg]['client_secret'])
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
        $client = new Client(['base_uri' => $api_host."/".$api_base."/", 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest', 'Authorization' => 'Bearer ' . $accessToken]
        ]);
        return $client;
    }

    public static function getUnauthorizeClient($envId,$serverFlg)
    {
        $serverEnvApi = config('app.server_env_api');
        $api_host = rtrim($serverEnvApi[$envId.$serverFlg]['host'], "/");
        $api_base = trim($serverEnvApi[$envId.$serverFlg]['base_url'],"/");
        $client = new Client(['base_uri' => $api_host."/".$api_base."/", 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']
        ]);
        return $client;
    }

    public static function getEditionAuthorizeClient($envId, $circular, $currentCircularUser)
    {
        $accessToken = "$currentCircularUser->email#$circular->id#".Carbon::now()->getTimestamp();
        $serverEnvApi = config('app.current_edition_api');
        $api_host = rtrim($serverEnvApi[$envId]['host'], "/");
        $api_base = trim($serverEnvApi[$envId]['base_url'],"/");

        $client = new Client(['base_uri' => $api_host."/".$api_base."/", 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']
        ]);
        // Try to get an access token using the client credentials grant.
        Log::debug("Call current edition Api to register access token for env $envId, host $api_host, base url $api_base");

        $result = $client->post("addToken",array(
            RequestOptions::JSON => array(
                'email' => $currentCircularUser->email,
                'access_id' => trim($serverEnvApi[$envId]['client_id']),
                'access_code' => trim($serverEnvApi[$envId]['client_secret']),
                'document_id' => $circular->id,
                'edition_flg' => $circular->edition_flg,
                'env_flg' => $circular->env_flg,
                'server_flg' => $circular->server_flg,
                'key' => $accessToken
            )
        ));

        Log::debug("Access token: $accessToken for current edition and env $envId");
        $ret = ['status' => true, 'token' =>$accessToken, 'client'=> null];
        if($result->getStatusCode() == 200 || $result->getStatusCode() == 201) {
            $response = json_decode((string) $result->getBody());
            if ($response->status == 200 || $response->status == 201){
                $ret['client'] = new Client(['base_uri' => $api_host."/".$api_base."/", 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
                    'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']
                ]);
            }else{
                $ret['status'] = false;
                $ret['message'] = $response->message;
            }
        }else{
            Log::warning("Call current edition Api to register access token failed. Response Body ".$result->getBody());
            $ret['status'] = false;
            $ret['message'] = $result->getBody();
        }

        return $ret;
    }
}
