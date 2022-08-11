<?php namespace App\Utils;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Created by PhpStorm.
 * User: hp
 * Date: 5/27/2019
 * Time: 12:12 PM
 */

class BoxUtils
{
    const BOX_REFERER = 'https://app.box.com/';
    const BOX_PARAM_AUTH_CODE = 'auth_code';
    const BOX_PARAM_USER_ID = 'user_id';
    const BOX_PARAM_FILE_ID = 'file_id';
    const BOX_PARAM_FILE_NAME = 'file_name';
    const BOX_PARAM_FILE_SHA = 'file_sha';
    const BOX_PARAM_FILE_ETAG = 'etag';
    const BOX_PARAM_REDIRECT_FROM_BOX = 'box_param_redirect_from_box';

    const BOX_API_TOKEN = 'box_cloud_api_token';
    const BOX_API_TOKEN_EXPIRE_TIME = 'box_api_token_expire_time';
    const BOX_API_REFRESH_TOKEN = 'box_api_refresh_token';

    public static function getApiClient(){
        Log::debug('--- base_url = '.Config::get('oauth.box.base_url'));
        $client = new Client(['base_uri' => Config::get('oauth.box.base_url'), 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,]);
        return $client;
    }

    public static function getAuthorizedApiClient($isUpload = false,$customHeaders = null){
        $access_token = Session::get(BoxUtils::BOX_API_TOKEN);
        if(!$access_token) return false;

        $baseUrl = Config::get('oauth.box.base_url');

        if($isUpload) {
            $baseUrl = Config::get('oauth.box.upload_url');
        }

        Log::debug('--- base_url = '.$baseUrl);

        if ($customHeaders && is_array($customHeaders)){
            $customHeaders['Authorization'] = 'Bearer ' . $access_token;
        }else{
            $customHeaders = ['Authorization' => 'Bearer ' . $access_token];
        }
        $client = new Client(['base_uri' => $baseUrl, 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,'allow_redirects' => ['track_redirects' => true],
            'headers' => $customHeaders]);

        Log::info($customHeaders);

        return $client;
    }

    public static function getAccessTokenClient(){
        $client = new Client(['base_uri' => Config::get('oauth.box.urlAccessToken'), 'http_errors' => false, 'verify' => false,]);
        return $client;
    }

    public static function refreshAccessToken($code, $isRefreshToken = false){
        $client = BoxUtils::getAccessTokenClient();
        if ($isRefreshToken){
            $params = ['form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $code,
                'client_id' =>  Config::get('oauth.box.clientId'),
                'client_secret' =>  Config::get('oauth.box.clientSecret'),
            ]];
        }else{
            $params = ['form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' =>  Config::get('oauth.box.clientId'),
                'client_secret' =>  Config::get('oauth.box.clientSecret'),
            ]];
        }
        $result = $client->post('', $params);
        $statusCode = $result->getStatusCode();
        if ($statusCode == 200){
            $token = json_decode((string) $result->getBody(), true);
            if (isset($token['access_token']) && $token['access_token']){
                Session::put(BoxUtils::BOX_API_TOKEN, $token['access_token']);
                Session::put(BoxUtils::BOX_API_REFRESH_TOKEN, isset($token['refresh_token'])?$token['refresh_token']:'');

                Log::debug('Setup file name, sha, etag');
                if (isset($token['restricted_to']) && is_array($token['restricted_to']) && count($token['restricted_to']) && isset($token['restricted_to'][0]['object'])){
                    $object = $token['restricted_to'][0]['object'];
                    if (isset($object['name'])){
                        Log::debug('Box file name: '.$object['name']);
                        Session::put(BoxUtils::BOX_PARAM_FILE_NAME, $object['name']);
                    }
                    if (isset($object['sha1'])){
                        Log::debug('Box file sha: '.$object['sha1']);
                        Session::put(BoxUtils::BOX_PARAM_FILE_SHA, $object['sha1']);
                    }
                    if (isset($object['etag'])){
                        Log::debug('Box file etag: '.$object['etag']);
                        Session::put(BoxUtils::BOX_PARAM_FILE_ETAG, $object['etag']);
                    }
                }

                $expiresIn = (isset($token['expires_in']) && is_numeric($token['expires_in']))?(int)$token['expires_in']:3600;
                if ($expiresIn > 600){
                    $expiresIn -= 600;
                }

                $now = Carbon::now();
                $now->addSeconds($expiresIn);
                Session::put(BoxUtils::BOX_API_TOKEN_EXPIRE_TIME, $now->getTimestamp());

                Log::debug("Box integration: set ACCCESS_TOKEN to session");
                return true;
            }else{
                Log::error("Cannot got access token from Box: http code $statusCode");
                Log::error("Response Body: ".$result->getBody());

                return false;
            }
        }else{
            Log::error("Cannot got access token from Box: http code $statusCode");
            Log::error("Response Body: ".$result->getBody());

            return false;
        }
    }
}