<?php

namespace App\Http\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GwAppApiUtils
{
    const COMPANY_SETTING_API             = 'api/v1/admin-master/mst-application-companies/auth/';//アプリ利用権限一覧
    const COMPANY_STORM_API               = 'api/v1/admin-master/mst-company/';//会社マスタ登録,更新
    const COMPANY_STORM_LIMIT_API         = 'api/v1/admin-master/app-limit/';//アプリ制約追加,編集,取得
    const COMPANY_STORM_SETTING_API       = 'api/v1/admin-master/mst-application-companies/';//アプリ企業マスタ利用権限の登録,削除,取得
    const COMPANY_APP_SEARCH              = 'api/v1/admin/mst-application-companies/auth/';//アプリ利用権限取得
    const COMPANY_APP_ROLES_SEARCH        = 'api/v1/admin/app-role-detail/search/';//アプリロール一覧取得
    const COMPANY_APP_ROLE_USERS_SEARCH   = 'api/v1/admin/app-role/search/app-role-users/';//ユーザーのアプリに対するロール一覧検索
    const COMPANY_APP_ROLE_DETAIL         = 'api/v1/admin/app-role-detail/detail/';//ロール詳細表示
    const COMPANY_APP_ROLE_UPDATE         = 'api/v1/admin/app-role-detail/users/';//アプリロールユーザ更新
    const COMPANY_APP_ROLE_DETAIL_STORE   = 'api/v1/admin/app-role-detail/';//アプリロール登録,更新,削除
    const COMPANY_SCHEDULE_GET            = 'api/v1/admin/app-restrict/';//会社のスケジュール重複状態表示
    const COMPANY_SCHEDULE_UPDATE_UPDATE  = 'api/v1/admin/app-restrict/update/';//会社のスケジュール重複フラグを更新する
    const COMPANY_FACILITY_SEARCH         = 'api/v1/admin/mst-facility/search/';//設備情報レコード取得
    const COMPANY_FACILITY_DELETE         = 'api/v1/admin/mst-facility/';//設備情報レコード削除
    const COMPANY_APP_USERS_SEARCH        = 'api/v1/admin/mst-application-users/search/users/';//アプリ利用ユーザ検索
    const COMPANY_APP_USERS_UPDATE       = 'api/v1/admin/mst-application-users/';//アプリ利用ユーザ登録,削除
    const COMPANY_COUNT_SCHEDULE_GET      = 'api/v1/batch/schedule/count-company-events';//スケジュールレコード数取得
    const SCHEDULE_FLG = 1;
    const COMPANY_APP_USERS_STATE         = 'api/v1/admin/mst-application-users/search/users/application';//アプリ有効利用ユーザ検索
    const COMPANY_APP_USER_STATE_UPDATE   = 'api/v1/admin/mst-user'; // ユーザーステータスの変更


    /**
     * @return Client
     */
    public static function getAuthorizeClient(): Client
    {
        $client = new Client(['base_uri' => 'https://' . config('app.gw_domain') . '/','timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest', 'Authorization' => 'Bearer XXX']
        ]);
        return $client;
    }
    /**
     * @return Client
     */
    public static function getBatchAuthorizeClient(): Client
    {
        $batchToken = config('app.gw_batch_token');
        $client = new Client(['base_uri' => 'https://' . config('app.gw_domain') . '/','timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest', 'Authorization' => 'Bearer ' . $batchToken]
        ]);
        return $client;
    }

    /**
     * 登録GW会社
     * @param $company_id
     * @param $company_name
     * @param $state int 会社の状態　1: 有効
     * @return bool
     */
    public static function storeCompany($company_id, $company_name, $state): bool
    {
        $client = self::getAuthorizeClient();
        $masterUser = DB::table('mst_shachihata')->select('email')->first();
        $response_app = $client->post( self::COMPANY_STORM_API,
            [
                RequestOptions::JSON => [
                    "adminMasterRequest" => [
                        "portalEmail" => $masterUser->email,
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg"=> config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server')
                    ],
                    "block" => "-",
                    "building" => "-",
                    "city" => "-",
                    "companyName" => $company_name,
                    "faxNumber" => "00-0000-0000",
                    "mstPrefectureId" => "1",
                    "phoneNumber" => "00-0000-0000",
                    "portalId" => $company_id,
                    "postalCode" => "000-0000",
                    "stateFlg" => $state
                ]
            ]);
        $response_app_encode = json_decode($response_app->getBody(),true);
        if ($response_app->getStatusCode() == 200 && $response_app_encode){
            return true;
        }else{
            Log::error('Api storeCompany portalId:' . $company_id);
            Log::error($response_app_encode);
            return false;
        }
    }

    /**
     * 会社情報更新
     * @param $company_id
     * @param $company_name
     * @param $state int 会社の状態　1: 有効
     * @return bool
     */
    public static function updateCompany($company_id, $company_name, $state): bool
    {
        $client = self::getAuthorizeClient();
        $masterUser = DB::table('mst_shachihata')->select('email')->first();
        $response = $client->put( self::COMPANY_STORM_API . $company_id,
            [
                RequestOptions::JSON => [
                    "adminMasterRequest" => [
                        "portalEmail" => $masterUser->email,
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg" => config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server')
                    ],
                    "block" => "-",
                    "building" => "-",
                    "city" => "-",
                    "companyName" => $company_name,
                    "faxNumber" => "00-0000-0000",
                    "mstPrefectureId" => "1",
                    "phoneNumber" => "00-0000-0000",
                    "portalId" => $company_id,
                    "postalCode" => "000-0000",
                    "stateFlg" => $state
                ]
            ]);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == 200 && $response_decode){
            return true;
        }elseif ($response->getStatusCode() == 404){
            //会社情報登録
            $store_company_result = self::storeCompany($company_id, $company_name, $state);
            if (!$store_company_result){
                return false;
            }
            //アプリ利用制限登録
            $store_company_limit = self::storeCompanyLimit($company_id);
            if (!$store_company_limit){
                return false;
            }
        }else{
            Log::error('Update company portalId:' . $company_id);
            Log::error($response_decode);
            return false;
        }
    }

    /**
     * アプリ利用制限登録
     * @param $company_id
     * @return false|int
     * if store company limit success return 'company_limit_id'(アプリ利用制限のID),
     * else return false.
     */
    public static function storeCompanyLimit($company_id)
    {
        $client = self::getAuthorizeClient();
        $masterUser = DB::table('mst_shachihata')->select('email')->first();
        $response = $client->post(self::COMPANY_STORM_LIMIT_API,
            [
                RequestOptions::JSON => [
                    "adminMasterRequest" => [
                        "portalEmail" => $masterUser->email,
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg"=> config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server')
                    ],
                    "maxBbsCount" => AppUtils::MAX_BBS_COUNT,
                    "maxScheduleCount" => AppUtils::MAX_SCHEDULE_COUNT,
                    "mstCompanyId" => $company_id
                ]
            ]);
        $response_app_encode = json_decode($response->getBody(),true);
        if ($response->getStatusCode() == 200 && $response_app_encode) {
            $limit_id = $response_app_encode['id'];
        } else {
            Log::error('Api storeLimit companyId:' . $company_id);
            Log::error($response_app_encode);
            return false;
        }
        return $limit_id;
    }

    /**
     * アプリ利用制限更新
     * @param $app_limit_id int アプリ利用制限のID
     * @param $company_id
     * @param $constraint array アプリ利用制限　['maxBbsCount'(掲示板容量),'maxScheduleCount'(スケジューラー容量)]
     * @return bool
     */
    public static function updateCompanyLimit($app_limit_id, $company_id, $constraint): bool
    {
        $client = self::getAuthorizeClient();
        $masterUser = DB::table('mst_shachihata')->select('email')->first();
        $response = $client->put(self::COMPANY_STORM_LIMIT_API . $app_limit_id,
            [
                RequestOptions::JSON => [
                    "adminMasterRequest" => [
                        "portalEmail" => $masterUser->email,
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg" => config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server')
                    ],
                    "maxBbsCount" => $constraint['maxBbsCount'],
                    "maxScheduleCount" => $constraint['maxScheduleCount'],
                    "mstCompanyId" => $company_id
                ]
            ]);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == 200 && $response_decode){
            return true;
        }else {
            Log::error('Update limit app_limit_id:' . $app_limit_id);
            Log::error($response_decode);
            return false;
        }
    }

    /**
     * アプリ利用制限取得
     * @param $company_id
     * @return array | bool
     * if statusCode equals 200,return ['app_limit_id'(アプリ利用制限のID),'maxBbsCount'(掲示板容量),'maxScheduleCount'(スケジューラー容量)],
     * else if statusCode equals 404 ,return default array,
     * else return false.
     */
    public static function getCompanyLimit($company_id){
        $settings = [];
        $client = self::getAuthorizeClient();
        $masterUser = DB::table('mst_shachihata')->select('email')->first();
        $response_app = $client->post(self::COMPANY_STORM_LIMIT_API . $company_id,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $masterUser->email,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg"=> config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        $response_app_encode = json_decode($response_app->getBody(),true);
        if ($response_app->getStatusCode() == 200 && $response_app_encode){
            $settings['app_limit_id']     = $response_app_encode[0]["id"];
            $settings["maxBbsCount"]      = $response_app_encode[0]["maxBbsCount"];
            $settings["maxScheduleCount"] = $response_app_encode[0]["maxScheduleCount"];
        }elseif($response_app->getStatusCode() == 404){
            $settings['app_limit_id']     = '';
            $settings["maxBbsCount"]      = AppUtils::MAX_BBS_COUNT;
            $settings["maxScheduleCount"] = AppUtils::MAX_SCHEDULE_COUNT;
        }else{
            Log::error('Search appLimit portalCompanyId:' . $company_id);
            Log::error($response_app_encode);
            return false;
        }
        return $settings;
    }

    /**
     * グループウェア機能登録
     * @param $company_id
     * @param $gw_application_id int グループウェア機能: 1:掲示板 2:スケジューラー　3:CalDAV
     * @return bool
     */
    public static function storeCompanySetting($company_id, $gw_application_id, $limit_flg, $buy_count): bool
    {
        $client = self::getAuthorizeClient();
        $masterUser = DB::table('mst_shachihata')->select('email')->first();
        $response = $client->post(self::COMPANY_STORM_SETTING_API,
            [
                RequestOptions::JSON => [
                    "adminMasterRequest"=>[
                        "portalEmail" => $masterUser->email,
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg"=> config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server')
                    ],
                    "mstApplicationId" => $gw_application_id,
                    "mstCompanyId" => $company_id,
                    "isInfinite" => $limit_flg,
                    "purchaseCount" => $buy_count,
                ]
            ]);
        $response_decode = json_decode($response->getBody(),true);

        if ($response->getStatusCode() == 200 && $response_decode) {
            return true;
        } else {
            Log::error('Api storeCompanySetting gw_application_id:' .$gw_application_id. ' companyId:' . $company_id);
            Log::error($response_decode);
            return false;
        }
    }

    /**
     * アプリ企業マスタ参照API グループウェア側のcompany_idを取得するため
     * @param $company_id
     * @param $company_name
     * @param $state  int 会社の状態　1: 有効
     * @return array | bool
     *  if get success return ['schedule_id'(会社のスゲジュウルID),'caldav_id'(CalDAVのID)],
     *  else return false.
     */
    public static function getCompanySettingId($company_id, $company_name, $state)
    {
        $settings = [];
        $client = self::getAuthorizeClient();
        $masterUser = DB::table('mst_shachihata')->select('email')->first();
        $response = $client->post(self::COMPANY_STORM_SETTING_API . $company_id,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $masterUser->email,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg" => config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        $response_decode = json_decode($response->getBody(), true);
        $settings['google_id'] = "";
        $settings['outlook_id'] = "";
        $settings['apple_id'] = "";
        $settings['shared_scheduler_id'] = "";
        if ($response->getStatusCode() == 200 && $response_decode){
            //グループウェア側の企業アプリマスタのスケジュールのid
            $settings['schedule_id'] = $response_decode[array_search(AppUtils::GW_APPLICATION_ID_SCHEDULE, array_column($response_decode, 'mstApplicationId'))]['id'];
            $settings['caldav_id']   = $response_decode[array_search(AppUtils::GW_APPLICATION_ID_CALDAV, array_column($response_decode, 'mstApplicationId'))]['id'];
            $google = array_search(AppUtils::GW_APPLICATION_ID_GOOGLE, array_column($response_decode, 'mstApplicationId'));
            $outlook = array_search(AppUtils::GW_APPLICATION_ID_OUTLOOK, array_column($response_decode, 'mstApplicationId'));
            $apple = array_search(AppUtils::GW_APPLICATION_ID_APPLE, array_column($response_decode, 'mstApplicationId'));
            $shared_scheduler = array_search(AppUtils::GW_APPLICATION_ID_SHARED_SCHEDULE, array_column($response_decode, 'mstApplicationId'));
            if ($google) {
                $settings['google_id']   = $response_decode[$google]['id'];
            }
            if ($outlook) {
                $settings['outlook_id']   = $response_decode[$outlook]['id'];
            }
            if ($apple) {
                $settings['apple_id']   = $response_decode[$apple]['id'];
            }
            if ($shared_scheduler){
                $settings['shared_scheduler_id']   = $response_decode[$shared_scheduler]['id'];
            }
            $settings['file_mail_id']   = $response_decode[array_search(AppUtils::GW_APPLICATION_ID_FILE_MAIL, array_column($response_decode, 'mstApplicationId'))]['id'];
            /*PAC_5-2246 S*/
            $settings['time_card_id']   = $response_decode[array_search(AppUtils::GW_APPLICATION_ID_TIME_CARD, array_column($response_decode, 'mstApplicationId'))]['id'];
            /*PAC_5-2246 E*/
        }elseif ($response->getStatusCode() == 404){
            //会社情報登録
            $store_company_result = self::storeCompany($company_id, $company_name, $state);
            if (!$store_company_result){
                return false;
            }
            //アプリ利用制限登録
            $store_company_limit = self::storeCompanyLimit($company_id);
            if (!$store_company_limit){
                return false;
            }
            //スケジュールのID取得
            $response = $client->post( self::COMPANY_STORM_SETTING_API . $company_id,
                [
                    RequestOptions::JSON => [
                        "portalEmail" => $masterUser->email,
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg" => config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server')
                    ]
                ]);
            $response_decode = json_decode($response->getBody(), true);
            if ($response->getStatusCode() !== 200 || !$response_decode){
                Log::error('get appcompany portalId:' . $company_id);
                Log::error($response_decode);
                return false;
            }

            $settings['schedule_id'] = $response_decode[array_search(AppUtils::GW_APPLICATION_ID_SCHEDULE, array_column($response_decode, 'mstApplicationId'))]['id'];
            $settings['caldav_id']   = $response_decode[array_search(AppUtils::GW_APPLICATION_ID_CALDAV, array_column($response_decode, 'mstApplicationId'))]['id'];
            $google = array_search(AppUtils::GW_APPLICATION_ID_GOOGLE, array_column($response_decode, 'mstApplicationId'));
            $outlook = array_search(AppUtils::GW_APPLICATION_ID_OUTLOOK, array_column($response_decode, 'mstApplicationId'));
            $apple = array_search(AppUtils::GW_APPLICATION_ID_APPLE, array_column($response_decode, 'mstApplicationId'));
            $shared_scheduler = array_search(AppUtils::GW_APPLICATION_ID_SHARED_SCHEDULE, array_column($response_decode, 'mstApplicationId'));
            if ($google) {
                $settings['google_id']   = $response_decode[$google]['id'];
            }
            if ($outlook) {
                $settings['outlook_id']   = $response_decode[$outlook]['id'];
            }
            if ($apple) {
                $settings['apple_id']   = $response_decode[$apple]['id'];
            }
            $settings['file_mail_id']   = $response_decode[array_search(AppUtils::GW_APPLICATION_ID_FILE_MAIL, array_column($response_decode, 'mstApplicationId'))]['id'];
            /*PAC_5-2246 S*/
            $settings['time_card_id']   = $response_decode[array_search(AppUtils::GW_APPLICATION_ID_TIME_CARD, array_column($response_decode, 'mstApplicationId'))]['id'];
            /*PAC_5-2246 E*/
            if ($shared_scheduler){
                $settings['shared_scheduler_id']   = $response_decode[$shared_scheduler]['id'];
            }
        }else{
            Log::error('get appcompany portalId:' . $company_id);
            Log::error($response_decode);
            return false;
        }
        return $settings;
    }

    /**
     * アプリ企業マスタ登録　
     * @param $company_id
     * @param $gw_application_id  int グループウェア機能: 1:掲示板 2:スケジューラー　3:CalDAV
     * @return bool
     */
    public static function updateCompanySetting($company_id, $gw_application_id, $limit_flg, $buy_count): bool
    {
        $client = self::getAuthorizeClient();
        $masterUser = DB::table('mst_shachihata')->select('email')->first();
        $response = $client->post(self::COMPANY_STORM_SETTING_API,
            [
                RequestOptions::JSON => [
                    "adminMasterRequest" => [
                        "portalEmail" => $masterUser->email,
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg" => config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server')
                    ],
                    "mstApplicationId" => $gw_application_id,
                    "mstCompanyId" => $company_id,
                    "isInfinite" => $limit_flg,
                    "purchaseCount" => $buy_count,
                ]
            ]);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == 200 && $response_decode){
            return true;
        }else{
            Log::error('Api storeSchedule companyId:' . $company_id);
            Log::error($response_decode);
            return false;
        }
    }

    /**
     * アプリ企業マスタ削除
     * @param $gw_app_application_id  int 会社のスゲジュウルID
     * @return bool
     */
    public static function deleteCompanySetting($gw_app_application_id): bool
    {
        $client = self::getAuthorizeClient();
        $masterUser = DB::table('mst_shachihata')->select('email')->first();
        $response = $client->delete(self::COMPANY_STORM_SETTING_API . $gw_app_application_id,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $masterUser->email,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg" => config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == 200 ){
            return true;
        }else{
            Log::error('Api deleteCompanySetting gw_app_application_id:' . $gw_app_application_id);
            Log::error($response_decode);
            return false;
        }
    }

    /**
     * グループウェア機能 スケジュール
     * @param $company_id
     * @return array
     * if get success return ['scheduler_flg'(スケジュールフラグ),'caldav_flg'(CalDAV)],
     * else return default value scheduler_flg=0.
     */
    public static function getCompanySetting($company_id): array
    {
        $settings = [];
        $client = self::getAuthorizeClient();
        $masterUser = DB::table('mst_shachihata')->select('email')->first();
        //アプリ企業マスタ参照(アプリ名,使用の有無)API呼び出し
        $response_app = $client->post(self::COMPANY_SETTING_API . $company_id,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $masterUser->email,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg" => config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        $response_app_encode = json_decode($response_app->getBody(), true);
        if ($response_app->getStatusCode() == 200 && $response_app_encode) {
            $settings['scheduler_flg'] = $response_app_encode[1]["isAuth"] ? 1 : 0;
            $settings["caldav_flg"]    = $response_app_encode[2]["isAuth"] ? 1 : 0;
            $settings["attendance_flg"]    = $response_app_encode[6]["isAuth"] ? 1 : 0;
        } elseif ($response_app->getStatusCode() == 404) {
            $settings['scheduler_flg'] = 0;
            $settings["caldav_flg"]    = 0;
            $settings["attendance_flg"]    = 0;
        } else {
            $settings['scheduler_flg'] = 0;
            $settings["caldav_flg"]    = 0;
            $settings["attendance_flg"]    = 0;
            Log::error('Search app portalCompanyId:' . $company_id);
            Log::error($response_app_encode);
        }
        return $settings;
    }

    /**
     * アプリ一覧API呼び出し　セレクトボックスのリスト
     * @param $admin_email
     * @param $company_id
     * @return array|false
     * if get success return array(アプリ利用権限取得),
     * [{"appName":"掲示板","id":1,"isAuth":true}]
     * else return false.
     */
    public static function getCompanyAppSearch($admin_email, $company_id){
        $client = self::getAuthorizeClient();
        $response = $client->post(self::COMPANY_APP_SEARCH ,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $admin_email,
                    "portalCompanyId" => $company_id,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg"=> config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == 200){
            $app_search_results = $response_decode;
        }else{
            Log::error('getCompanyAppSearch response getStatusCode ' . $response->getStatusCode());
            Log::error('getCompanyAppSearch response body ' . $response->getBody());
            return false;
        }
        return $app_search_results;
    }

    /**
     * ロール一覧API呼び出し　セレクトボックスのリスト
     * @param $admin_email
     * @param $company_id
     * @param $first_app_id int 一覧の先頭のapp_id
     * @return array|false
     * if get success return array(ロール一覧),
     * [[{"code":0,"id":0,"isDefault":true,"name":"string"}],['id1'=> 'ロールの名前1','id2'=> 'ロールの名前2',..]]
     * else return false.
     */
    public static function getCompanyAppRoleSearch($admin_email, $company_id, $first_app_id){
        $roleList = array();
        $listRole = array();
        $client = self::getAuthorizeClient();
        $response = $client->post(self::COMPANY_APP_ROLES_SEARCH,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $admin_email,
                    "portalCompanyId" => $company_id,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg"=> config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == 200 && $response_decode){
            //アプリSELECT入力と同じmstApplicationIdを探す
            foreach ($response_decode as $value){
                if ($value['mstApplicationId'] == $first_app_id)
                    $roleList = $value['appRoleList'];
            }
            foreach ($roleList as $value){
                $listRole[$value['id']] = $value['name'];
            }
        }else{
            Log::error('getCompanyAppRoleSearch response getStatusCode ' . $response->getStatusCode());
            Log::error('getCompanyAppRoleSearch response body ' . $response->getBody());
            return false;
        }
        return [$roleList,$listRole];
    }

    /**
     * アプリロール設定 ユーザー一覧API呼び出し
     * @param $admin_email string 管理者のメールアドレス
     * @param $company_id
     * @param $mst_application_id int 一覧の先頭のapp_id
     * @param string|null $app_role_id ロールリストのID defaultValue = null
     * @param string|null $user_email ユーザのメールアドレス defaultValue = null
     * @param string|null $mst_department_id 部署のID　defaultValue = null
     * @param string|null $mst_position_id 役職のID　defaultValue = null
     * @param string|null $username ユーザの名前　defaultValue = null
     * @return false|array
     * if get success return array(ユーザーのアプリに対するロール一覧検索),
     * {"appRoleId":1,"appRoleName":"掲示板基本ロール","appRoleUsersId":1,"mstUser":{}}
     * else return false.
     */
    public static function getCompanyAppUsersSearch($admin_email, $company_id, $mst_application_id, string $app_role_id = null, string $user_email = null,
                                                    string $mst_department_id = null, string $mst_position_id = null, string $username = null){
        $client = self::getAuthorizeClient();
        $response = $client->post( self::COMPANY_APP_ROLE_USERS_SEARCH,
            [
                RequestOptions::JSON => [
                    "adminRequest"=>[
                        "portalEmail" => $admin_email,
                        "portalCompanyId" => $company_id,
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg"=> config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server')
                    ],
                    "appRoleId" => $app_role_id,
                    "email" => $user_email,
                    "mstApplicationId" => $mst_application_id,
                    "mstDepartmentId" => $mst_department_id,
                    "mstPositionId" => $mst_position_id,
                    "name" => $username
                ]
            ]);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == 200 ){
            $response_users = $response_decode;
        }else{
            Log::error('getCompanyAppUsersSearch response StatusCode ' . $response->getStatusCode());
            Log::error('getCompanyAppUsersSearch response body ' . $response->getBody());
            return false;
        }
        return $response_users;
    }

    /**
     * ロール更新API呼び出し
     * @param $admin_email
     * @param $company_id
     * @param $app_role_id int ロールID
     * @param $role_user_ids array チェックボックスの配列
     * @return bool
     */
    public static function updateCompanyAppUser($admin_email, $company_id, $app_role_id, $role_user_ids): bool
    {
        $client = self::getAuthorizeClient();
        $response = $client->put( self::COMPANY_APP_ROLE_UPDATE,
            [
                RequestOptions::JSON => [
                    "adminRequest"=>[
                        "portalEmail" => $admin_email,
                        "portalCompanyId" => $company_id,
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg"=> config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server')
                    ],
                    "appRoleId"=> $app_role_id,
                    "appRoleUsersIds"=> $role_user_ids
                ]
            ]);

        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == 200 ){
            return true;
        }else{
            Log::error('updateCompanyAppUser response StatusCode ' . $response->getStatusCode());
            Log::error('updateCompanyAppUser response body ' . $response->getBody());
            return false;
        }
    }

    /**
     * ロール詳細API呼び出し
     * @param $app_role_id int ロールID
     * @param $admin_email string 管理者のメールアドレス
     * @param $company_id
     * @return false|array
     * if get success return array(ロール詳細表示),
     * {"id":1,"memo":"掲示板カテゴリ 管理者","mstAppFunctionList":[{"functionName":"掲示板カテゴリ 操作","id":1,"mstAccessPrivilegesList":[{}]}],"mstApplicationId":1,"mstCompanyId":1,"name":"掲示板（カテゴリ追加） 管理者"}
     * else return false.
     */
    public static function getCompanyAppUserDetail($app_role_id, $admin_email, $company_id){
        $client = self::getAuthorizeClient();
        $response = $client->post( self::COMPANY_APP_ROLE_DETAIL . $app_role_id,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $admin_email,
                    "portalCompanyId" => $company_id,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg"=> config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == 200){
            $response_roles = $response_decode;
        }else{
            Log::error('getCompanyAppUserDetail response StatusCode ' . $response->getStatusCode());
            Log::error('getCompanyAppUserDetail response body ' . $response->getBody());
            return false;
        }
        return $response_roles;
    }


    /**
     * ロール詳細登録API呼び出し
     * @param $admin_email
     * @param $company_id
     * @param $memo string メモ
     * @param $mst_application_id int application_id
     * @param $mst_access_privilege_ids array アクセス権限のID
     * @param $name string ロール名
     * @return int statusCode  200:success | 400:同じ名前のロールが既に存在します | others: failed
     */
    public static function storeCompanyAppDetail($admin_email, $company_id, $memo, $mst_application_id, $mst_access_privilege_ids, $name): int
    {
        $client = self::getAuthorizeClient();
        $response = $client->post(self::COMPANY_APP_ROLE_DETAIL_STORE,
            [
                RequestOptions::JSON => [
                    "adminRequest"=>[
                        "portalEmail" => $admin_email,
                        "portalCompanyId" => $company_id,
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg"=> config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server')
                    ],
                    "memo"=> $memo,
                    "mstApplicationId"=> $mst_application_id,
                    "mstAccessPrivilegeIds"=> $mst_access_privilege_ids,
                    "name"=> $name
                ]
            ]);
        return $response->getStatusCode();
    }

    /**
     * ロール詳細更新PI呼び出し
     * @param $admin_email
     * @param $company_id
     * @param $app_role_id int ロールID
     * @param $memo string メモ
     * @param $mst_application_id int application_id
     * @param $mst_access_privilege_ids array アクセス権限のID
     * @param $name string ロール名
     * @return  int statusCode  200:success | 400:同じ名前のロールが既に存在します | others: failed
     */
    public static function updateCompanyAppDetail($admin_email, $company_id, $app_role_id, $memo, $mst_application_id, $mst_access_privilege_ids, $name): int
    {
        $client = self::getAuthorizeClient();
        $response = $client->put(self::COMPANY_APP_ROLE_DETAIL_STORE . $app_role_id,
            [
                RequestOptions::JSON => [
                    "adminRequest"=>[
                        "portalEmail" => $admin_email,
                        "portalCompanyId" => $company_id,
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg"=> config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server')
                    ],
                    "memo"=> $memo,
                    "mstApplicationId"=> $mst_application_id,
                    "mstAccessPrivilegeIds"=> $mst_access_privilege_ids,
                    "name"=> $name
                ]
            ]);
        return $response->getStatusCode();
    }

    /**
     * ロール詳細削除PI呼び出し
     * @param $admin_email
     * @param $company_id
     * @param $app_role_id int ロールID
     * @return bool
     */
    public static function deleteCompanyAppDetail($admin_email, $company_id, $app_role_id): bool
    {
        $client = self::getAuthorizeClient();
        $response = $client->delete( self::COMPANY_APP_ROLE_DETAIL_STORE . $app_role_id,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $admin_email,
                    "portalCompanyId" => $company_id,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg"=> config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        if($response->getStatusCode() == 200 ){
            return true;
        }else{
            Log::error('deleteCompanyAppDetail response getStatusCode ' . $response->getStatusCode());
            Log::error('deleteCompanyAppDetail response body ' . $response->getBody());
            return false;
        }
    }

    /**
     * 取得スケジュールの重複予約の配置
     * @param $admin_email
     * @param $company_id
     * @return int
     * if get success return スケジュールの重複予約フラグ( 0 不可 | 1 可能)
     * else return false
     */
    public static function getApplicationSchedule($admin_email, $company_id): int
    {
        $res_flg = 0;
        $client = self::getAuthorizeClient();
        $response = $client->post(self::COMPANY_SCHEDULE_GET,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $admin_email,
                    "portalCompanyId" => $company_id,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg"=> config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        $response_decode = json_decode((string)$response->getBody(), true);
        if ($response->getStatusCode() == 200){
            if ($response_decode == ''){
                $res_flg = 1;
            }else{
                $res_flg = $response_decode['repeatFlg'];
            }
        }else{
            Log::error('getApplicationSchedule response getStatusCode ' . $response->getStatusCode());
            Log::error('getApplicationSchedule response body ' . $response->getBody());
            return false;
        }
        return $res_flg;
    }

    /**
     * 更新スケジュールの重複予約
     * @param $admin_email
     * @param $company_id
     * @param $repeat_flg int スケジュールの重複予約フラグ( 0 不可 | 1 可能)
     * @return bool
     */
    public static function updateApplicationSchedule($admin_email, $company_id, $repeat_flg): bool
    {
        $client = self::getAuthorizeClient();
        $response = $client->post(self::COMPANY_SCHEDULE_UPDATE_UPDATE,
            [
                RequestOptions::JSON => [
                    "adminRequest"=>[
                        "portalEmail" => $admin_email,
                        "portalCompanyId" => $company_id,
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg"=> config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server')
                    ],
                    "repeatFlg" => $repeat_flg
                ]
            ]);
        if ($response->getStatusCode() == 200){
            return true;
        }else{
            Log::error('updateApplicationSchedule response getStatusCode ' . $response->getStatusCode());
            Log::error('updateApplicationSchedule response body ' . $response->getBody());
            return false;
        }
    }

    /**
     * 設備情報が取得
     * @param $admin_email
     * @param $company_id
     * @return false|mixed
     * if get success return array [{"id": 0(設備のID),"mstCompany": {},"name": "string"(設備の名前) }],
     * else return false.
     */
    public static function getFacility($admin_email, $company_id){
        $client = self::getAuthorizeClient();
        $response = $client->post(self::COMPANY_FACILITY_SEARCH,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $admin_email,
                    "portalCompanyId" => $company_id,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg" => config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        $response_decode = json_decode((string)$response->getBody(), true);
        if ($response->getStatusCode() == 200){
            return $response_decode;
        }else{
            Log::error('Get Facility response getStatusCode ' . $response->getStatusCode());
            Log::error('Get Facility response body ' . $response->getBody());
            return false;
        }
    }

    /**
     * 設備を削除
     * @param $facility_id int 設備のID
     * @param $admin_email
     * @param $company_id
     * @return bool
     */
    public static function deleteFacility($facility_id, $admin_email, $company_id): bool
    {
        $client = self::getAuthorizeClient();
        $response = $client->delete(self::COMPANY_FACILITY_DELETE . $facility_id,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $admin_email,
                    "portalCompanyId" => $company_id,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg"=> config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        $response_decode = json_decode((string)$response->getBody(), true);
        if ($response->getStatusCode() == 200){
            return true;
        }else{
            Log::error('deleteFacility response getStatusCode ' . $response->getStatusCode());
            Log::error('deleteFacility response body ' . $response->getBody());
            return false;
        }
    }

    /**
     * アプリ利用ユーザ検索
     * @param $admin_email string 管理者のメールアドレス
     * @param $company_id int
     * @param $mstApplicationId int アプリロールid
     * @param string|null $user_email string ユーザのメールアドレス
     * @param string|null $mst_department_id string 部署のID
     * @param string|null $mst_position_id string 役職のID
     * @param string|null $username string ユーザの名前
     * @param int $isValid int 0（無効）、1（有効）、-1（検索条件に含めない）
     * @return false|mixed
     * if get success return array{"appName":"ロール名","id":"1","mstApplicationUsersStateLists":[{"appUserId":"1","enabled":true,"mstUser":{"block":"","building":"","city":"",...}}]}
     * else return false.
     */
    public static function appUsersSearch($admin_email, $company_id, $mstApplicationId, string $user_email = null, string $mst_department_id = null,
                                          string $mst_position_id = null, string $username = null ,int $isValid = -1)
    {
        $client = self::getAuthorizeClient();
        $response = $client->post(self::COMPANY_APP_USERS_SEARCH,
            [
                RequestOptions::JSON => [
                    "adminRequest"=>[
                        "portalEmail" => $admin_email,
                        "portalCompanyId" => $company_id,
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg"=> config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server')
                    ],
                    "email" => $user_email,
                    "name" => $username,
                    "mstDepartmentId" => $mst_department_id,
                    "mstPositionId" => $mst_position_id,
                    "isValid" => $isValid,
                    "mstApplicationId" => $mstApplicationId
                ]
            ]);
        $response_decode = json_decode((string)$response->getBody(), true);
        if ($response->getStatusCode() == 200 ){
            $response_users = $response_decode;
        }else{
            Log::error('appUsersSearch response StatusCode ' . $response->getStatusCode());
            Log::error('appUsersSearch response body ' . $response->getBody());
            return false;
        }

        return $response_users;
    }

    /**
     * アプリ利用ユーザ更新
     * @param $admin_email string 管理者のメールアドレス
     * @param $company_id
     * @param $mst_application_id int アプリロールid
     * @param $app_user_id int アプリユーザid
     * if update success return true ,else return false.
     * @return false|mixed
     * if update success return array{"createdAt":"","id":1,"mstApplicationId":1,"mstUser":{"block":"","building":"","city":"","email":"","employeeCode":"","mstColor":{},"mstCompany"{}...}},
     * else return false.
     */
    public static function appUserUpdate($admin_email, $company_id, $mst_application_id, $app_user_id)
    {
        $client = self::getAuthorizeClient();
        $response = $client->post(self::COMPANY_APP_USERS_UPDATE,
            [
                RequestOptions::JSON => [
                    "adminRequest"=>[
                        "portalEmail" => $admin_email,
                        "portalCompanyId" => $company_id,
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg"=> config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server')
                    ],
                    "mstApplicationId"=> $mst_application_id,
                    "mstUserId"=> $app_user_id
                ]
            ]);
        if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK){
            $responseUser = json_decode((string)$response->getBody(), true);
        }else{
            Log::error('appUserUpdate response StatusCode ' . $response->getStatusCode());
            Log::error('appUserUpdate response body ' . $response->getBody());
            return false;
        }

        return $responseUser;
    }

    /**
     * アプリ利用ユーザ削除
     * @param $app_user_id int アプリユーザid
     * @param $admin_email string 管理者のメールアドレス
     * @param $company_id
     * @return bool
     * if delete success return true,else return false.
     */
    public static function appUserDelete($app_user_id, $admin_email, $company_id): bool
    {
        $client = self::getAuthorizeClient();
        $response = $client->delete(self::COMPANY_APP_USERS_UPDATE . $app_user_id,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $admin_email,
                    "portalCompanyId" => $company_id,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg"=> config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        if ($response->getStatusCode() != StatusCodeUtils::HTTP_OK){
            Log::error('appUserDelete response StatusCode ' . $response->getStatusCode());
            Log::error('appUserDelete response body ' . $response->getBody());
            return false;
        }

        return true;
    }

    /**
     * スケジュールレコード数が取得
     * @return false|mixed
     * if get success return array [{"company_id": int(企業ID),"count_schedule": int(企業内のスケジュールレコード数) }],
     * else return false.
     */
    public static function getCountSchedule(){
        $client = self::getBatchAuthorizeClient();
        $response = $client->post(self::COMPANY_COUNT_SCHEDULE_GET,
            [
                RequestOptions::JSON => [
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg" => config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        $response_decode = json_decode((string)$response->getBody(), true);
        if ($response->getStatusCode() == 200){
            return $response_decode;
        }else{
            Log::error('Get Count Schedule response getStatusCode ' . $response->getStatusCode());
            Log::error('Get Count Schedule response body ' . $response->getBody());
            return false;
        }
    }

    /**
     * アプリ一覧API呼び出し　セレクトボックスのリスト
     * @param $admin_email
     * @param $company_id
     * @return array|false
     * if get success return array(アプリ利用権限取得),
     * [{"appName":"掲示板","id":1,"isAuth":true}]
     * else return false.
     */
    public static function getAppUsersStateSearch($admin_email, $company_id, $ids){
        $client = self::getAuthorizeClient();
        $response = $client->post(self::COMPANY_APP_USERS_STATE ,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $admin_email,
                    "portalCompanyId" => $company_id,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg"=> config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server'),
                    "mstUserIds" => $ids,
                ]
            ]);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == 200){
            $app_search_results = $response_decode;
        }else{
            Log::error('getAppUsersStateSearch response getStatusCode ' . $response->getStatusCode());
            Log::error('getAppUsersStateSearch response body ' . $response->getBody());
            return false;
        }
        return $app_search_results;
    }
    
    /**
     * GW・CalDAV側の利用者情報も削除する
     * @param $user_id
     * @param $user_email
     * @param $company_id
     * @return bool
     */
    public static function userDelete($user_id, $user_email, $company_id)
    {
        $client = self::getAuthorizeClient();
        $response = $client->delete(self::COMPANY_APP_USER_STATE_UPDATE . '/' . $user_id,
            [
                RequestOptions::JSON => [
                    "portalEmail" => $user_email,
                    "portalCompanyId" => $company_id,
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg"=> config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server')
                ]
            ]);
        if ($response->getStatusCode() != StatusCodeUtils::HTTP_OK){
            Log::error('Gw Api user delete response StatusCode ' . $response->getStatusCode());
            Log::error('Gw Api user delete response body ' . $response->getBody());
            return false;
        }
        return true;
    }
}
