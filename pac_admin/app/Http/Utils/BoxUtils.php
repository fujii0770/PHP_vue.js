<?php

/**
 * Created by PhpStorm.
 * User: lul
 * Date: 1/15/2021
 * Time: 12:12 PM
 */
namespace App\Http\Utils;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class BoxUtils
{
    const BOX_API_TOKEN = 'box_cloud_api_token_auto_storage';
    const BOX_API_REFRESH_TOKEN = 'box_api_refresh_token_auto_storage';

    // 外部連携Box(1:有効 0:無効)
    const BOX_ENABLED = 1;
    const BOX_NOT_ENABLED = 0;

    // 自動保管(1:有効 0:無効)
    const BOX_ENABLED_AUTO_STORAGE = 1;
    const BOX_NOT_ENABLED_AUTO_STORAGE = 0;

    // 保管後の自動削除(1:有効 0:無効)
    const BOX_ENABLED_AUTO_DELETE = 1;
    const BOX_NOT_ENABLED_AUTO_DELETE = 0;

    // 自動保管結果(0:デフォルト 1:成功 2:失敗)
    const BOX_AUTOMATIC_STORAGE_RESULT_DEFAULT = 0;
    const BOX_AUTOMATIC_STORAGE_RESULT_SUCCESS = 1;
    const BOX_AUTOMATIC_STORAGE_RESULT_FAIL = 2;

    const STATE_BOX_AUTO_STORAGE_LABEL = [1 => '成功', 2 => '失敗',];
    const STATE_BOX_AUTO_DELETE_LABEL = [1 => '済', 0 => '未削除',];

    /**
     * @param null $customHeaders
     * @return bool|Client
     */
    public static function getAuthorizedApiClient($customHeaders = null)
    {
        $access_token = Session::get(BoxUtils::BOX_API_TOKEN);
        if (!$access_token) return false;

        $baseUrl = Config::get('oauth.box.base_url');

        if ($customHeaders && is_array($customHeaders)) {
            $customHeaders['Authorization'] = 'Bearer ' . $access_token;
        } else {
            $customHeaders = ['Authorization' => 'Bearer ' . $access_token];
        }
        $client = new Client(['base_uri' => $baseUrl, 'http_errors' => false, 'verify' => false, 'allow_redirects' => ['track_redirects' => true],
            'headers' => $customHeaders]);

        return $client;
    }

    /**
     * Boxリフレッシュトークン更新
     * @param $code
     * @return false|mixed
     */
    public static function refreshToken($code)
    {
        $client = new Client(['base_uri' => Config::get('oauth.box.urlAccessToken'), 'http_errors' => false, 'verify' => false,]);

        $params = ['form_params' => [
            'grant_type' => 'refresh_token',
            'refresh_token' => $code,
            'client_id' => Config::get('oauth.box.clientId'),
            'client_secret' => Config::get('oauth.box.clientSecret'),
        ]];

        $result = $client->post('', $params);
        if ($result->getStatusCode() == StatusCodeUtils::HTTP_OK) {
            $result_body = json_decode((string)$result->getBody(), true);
            if (isset($result_body['refresh_token']) && $result_body['refresh_token']) {
                return $result_body['refresh_token'];
            }
        }

        return false;
    }
}