<?php

/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/3/19
 * Time: 10:22
 */

namespace App\Http\Utils;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SpecialAppApiUtils
{
    public static function getAuthorizeClient()
    {
        $accessToken = null;

        $api_host = rtrim(config('app.special_app_api_host'), "/");
        $api_base = trim(config('app.special_app_api_base_url'),"/");
//        if (Session::has('special_app_api_access_token')){
//            $accessToken = Session::get('special_app_api_access_token');
//        }else{
            $client = new Client(['base_uri' => $api_host, 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
                'headers' => ['Content-Type' => 'multipart/form-data', 'X-Requested-With' => 'XMLHttpRequest']
            ]);
//            // Try to get an access token using the client credentials grant.
//
//            Log::debug("Call Special App Api to get access token for client ".config('app.special_app_api_client_secret'));
//
//            $result = $client->post("/oauth/token",array(
//                'form_params' => array(
//                    'grant_type' => 'client_credentials',
//                    'client_id' => trim(config('app.special_app_api_client_id')),
//                    'client_secret' => trim(config('app.special_app_api_client_secret')),
//                )
//            ));
//            if($result->getStatusCode() == 200) {
//                Log::debug("Status 200");
//                $response = json_decode((string) $result->getBody());
//                $accessToken = $response->access_token;
//                Session::put('special_app_api_access_token', $accessToken);
//            }else{
//                Log::warning("Call Special App Api to get access token failed. Response Body ".$result->getBody());
//                return false;
//            }
//
//        }
//        $client = new Client(['base_uri' => $api_host."/".$api_base."/", 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
//            'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest', 'Authorization' => 'Bearer ' . $accessToken]
//        ]);
        return $client;
    }
}
