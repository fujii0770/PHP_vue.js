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

class OneDriveUtils
{

    const ONEDRIVE_API_TOKEN = 'one_drive_api_token';

    public static function getApiClient(){
        Log::debug('--- base_url = '.Config::get('oauth.onedrive.base_url'));
        $client = new Client(['base_uri' => Config::get('oauth.onedrive.base_url'), 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,]);
        return $client;
    }

    public static function getSimpleClient(){
        $client = new Client(['http_errors' => false, 'verify' => false, 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'),]);
        return $client;
    }

    public static function getAuthorizedApiClient($isUpload = false,$customHeaders = null){
        $access_token = Session::get(OneDriveUtils::ONEDRIVE_API_TOKEN);
        if(!$access_token) return false;

        $baseUrl = Config::get('oauth.onedrive.base_url');

        if($isUpload) {
            $baseUrl = Config::get('oauth.onedrive.upload_url');
        }

        Log::debug('--- base_url = '.$baseUrl);
        // check if token is expired
        /*$now = Carbon::now();
        $expiresIn = Session::get(BoxUtils::BOX_API_TOKEN_EXPIRE_TIME, $now->getTimestamp());
        if ($expiresIn <= $now->getTimestamp()){
            Log::debug("Box integration: ACCCESS_TOKEN is expired. Call to get new token");
            $result = BoxUtils::setupAccessToken(Session::get(BoxUtils::BOX_API_REFRESH_TOKEN), true);
            if ($result){
                $access_token = Session::get(BoxUtils::BOX_API_TOKEN);
            }else{
                Log::debug("Box integration: Cannot refresh to get new token");
            }
        }*/
        // $access_token = '2FkN9xyETkYhy2l7TzKqGCM04Z5eDCju';
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
}