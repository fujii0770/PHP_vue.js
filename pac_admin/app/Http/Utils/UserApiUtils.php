<?php

namespace App\Http\Utils;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Created by PhpStorm.
 * User: lul
 * Date: 2/5/2021
 * Time: 18:54 PM
 */
class UserApiUtils
{
    public static function getStampApiClient()
    {
        $api_host = rtrim(config('app.stamp_api_host'), "/");
        $api_base = ltrim(config('app.stamp_api_base'), "/");
        $client = new Client(['base_uri' => $api_host . "/" . $api_base, 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']
        ]);
        return $client;
    }

    /**
     * ユーザ有効チェック
     *      true :有効
     *      false:無効
     *
     * @param $email メール
     * @param $mst_company_id 会社ID
     * @param $env env_flg
     * @param $server server_flg
     * @param $edition_flg edition_flg
     * @return bool true：有効；false：無効
     */
    public static function checkUser($email, $mst_company_id, $env, $server, $edition_flg)
    {
        // 本環境の場合
        if ($env == config('app.server_env') && $server == config('app.server_flg') && $edition_flg == config('app.edition_flg')) {
            return self::checkUserValid($email, $mst_company_id);
        }

        $client = EnvApiUtils::getUnauthorizeClient($env, $server);
        $result = $client->get('checkUser?email=' . $email . '&mst_company_id=' . $mst_company_id);

        if ($result->getStatusCode() != StatusCodeUtils::HTTP_OK) {
            Log::error(__('message.warning.userCheck', ['email' => $email, 'env' => $env, 'server' => $server]));
            Log::error($result->getBody());
            return false;
        }
        return json_decode((string)$result->getBody())->data->userValid;
    }

    /**
     * ユーザ有効チェック：ユーザ会社又はトライアル企業が有効、もユーザが有効
     *
     * @param $email
     * @param $mst_company_id
     * @return bool true：有効；false：無効
     */
    public static function checkUserValid($email, $mst_company_id)
    {
        $user = DB::table('mst_user as u')
            ->select(['u.*'])
            ->join('mst_user_info as ui', function ($join) {
                $join->on('u.id', 'ui.mst_user_id')
                    ->on('ui.approval_request_flg', DB::raw(AppUtils::APPROVE_REQUEST_VALID));
            })
            ->join('mst_company as c', function ($join) {
                $join->on('u.mst_company_id', 'c.id')
                    ->on('c.state', DB::raw(AppUtils::COMPANY_STATE_VALID))
                    ->on(function ($condition) {
                        $condition->on('c.contract_edition', '!=', DB::raw(AppUtils::COMPANY_STATE_VALID))
                            ->orOn(function ($condition1) {
                                $condition1->on('c.contract_edition', DB::raw(AppUtils::CONTRACT_EDITION_TRIAL))
                                    ->on('c.trial_flg', DB::raw(AppUtils::COMPANY_STATE_VALID));
                            });
                    });
            })
            ->where('u.email', $email)
            ->where('u.mst_company_id', $mst_company_id)
            ->where('u.state_flg', AppUtils::STATE_VALID)
            ->first();
        return $user ? true : false;
    }
}