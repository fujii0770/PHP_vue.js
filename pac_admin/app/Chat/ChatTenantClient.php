<?php

namespace App\Chat;

use App\Chat\Exceptions\FailedHttpAccessException;
use App\Chat\Exceptions\MultiException;
use App\Chat\Properties\ChatUserProperties;
use App\Chat\Properties\ChatUserDataProperties;
use App\Chat\Properties\ChatOrganizationProperties;
use App\Chat\Properties\PlainProperties;
use App\Chat\Properties\ChatServerProperties;
use App\Chat\Properties\ChatSmtpProperties;
use App\Chat\Consts\ChatUserStatus;
use Closure;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/* No Singleton */

class ChatTenantClient
{

    private $props = null;


    /**
     * 認証ID
     *
     * @param string
     */
    public function auth_id(string $value = null)
    {
        return $this->props->getset(__FUNCTION__, func_get_args());
    }

    /**
     * 認証トークン
     *
     * @param string
     */
    public function auth_token(string $value = null)
    {
        return $this->props->getset(__FUNCTION__, func_get_args());
    }

    /**
     * パスワード
     *
     * @param string $value
     */
    public function password(string $value = null)
    {
        return $this->props->getset(__FUNCTION__, func_get_args());
    }

    /**
     * サーバーのURL
     *
     * @param string $value /apiの前までのURL
     */
    public function root_url(string $value = null)
    {
        return $this->props->getset(__FUNCTION__, func_get_args());
    }

    /**
     * コンストラクタ
     */
    public function __construct($root_url, $auth_id = null, $auth_token = null)
    {
        $this->props = new PlainProperties();
        $this->root_url($root_url);
        $this->auth_id($auth_id);
        $this->auth_token($auth_token);
    }

    /**
     * HTTPクライアントの作成
     */
    private static function makeClient()
    {
        return ChatUtils::makeHttpClient();
    }

    /**
     *
     */
    private function client()
    {
        return $this->props->getIfNullSet(__FUNCTION__, function () {
            return self::makeClient();
        });
    }


    /**
     * usernameとパスワードを使いログイン
     *
     * @param string $user
     * @param string $password
     * @param int $retry
     * @param int $sleep_seconds
     *
     * @throws FailedHttpAccessException
     * @throws MultiException
     */
    public function loginServer($user, $password, $retry = 3, $sleep_seconds = 5)
    {
        $this->_loginServer($user, $password, $retry, $sleep_seconds);
    }

    /**
     *
     * @throws FailedHttpAccessException
     * @throws MultiException
     */
    private function _loginServer($user, $password, $retry, $sleep_seconds, MultiException $exceptions = null)
    {
        $url = $this->makeApiUrl("login");
        try {
            $obj = $this->postForm($url, ["user" => $user, "password" => $password]);
            $data = $obj->data;
            $this->auth_id($data->userId);
            $this->auth_token($data->authToken);
            $this->password($password);
        } catch (FailedHttpAccessException $e) {
            Log::warning($e);
            if ($retry < 1) {
                if ($exceptions === null) {
                    throw $e;
                }
                $exceptions->add($e);
                throw $exceptions;
            }
            if ($exceptions === null) {
                $exceptions = new MultiException();
            }
            $exceptions->add($e);
            sleep($sleep_seconds);
            $this->_loginServer($user, $password, --$retry, $sleep_seconds, $exceptions);
        }
        return $this;
    }

    /**
     *
     * @throws FailedHttpAccessException
     */
    private function request($method, $url, array $options = [], $auth = true)
    {
        $headers = [];
        if ($auth === true) {
            $uid = $this->auth_id();
            $token = $this->auth_token();
            $pwd = $this->password();
            if (is_string($uid)) {
                $headers["X-User-Id"] = $uid;
            }
            if (is_string($token)) {
                $headers["X-Auth-Token"] = $token;
            }
            if (is_string($pwd)) {
                $fa = hash("sha256", $pwd);
                $headers["x-2fa-code"] = $fa;
                $headers["x-2fa-method"] = "password";
            }
        }
        if (array_key_exists("headers", $options)) {
            $oh = $options["headers"];
            if (is_array($oh)) {
                $headers = array_merge($headers, $oh);
            }
        }
        $options = $this->mergeHeaders($options, $headers);

        $client = $this->client();
        $res = null;
        try {
            Log::debug("Request : [ $method ] $url : " . var_export($options, true));
            $res = $client->request($method, $url, $options);
        } catch (Exception $e) {
            throw new FailedHttpAccessException($e->getMessage(), 0, $e);
        }
        return self::getResponseObject($res, $url);
    }

    private function mergeHeaders(array $options, array $headers)
    {
        $hs = $headers;
        if (array_key_exists("headers", $options)) {
            $oh = $options["headers"];
            if (is_array($oh)) {
                $hs = array_merge($oh, $hs);
            }
        }
        $options["headers"] = $hs;
        return $options;
    }

    /**
     *
     * @throws FailedHttpAccessException
     */
    private function get($url, array $options = [], $auth = true)
    {
        return $this->request("GET", $url, $options, $auth);
    }

    /**
     *
     * @throws FailedHttpAccessException
     */
    private function post($url, $content_type, array $options = [], $auth = true)
    {
        $options = $this->mergeHeaders($options, ['Content-Type' => $content_type]);
        return $this->request("POST", $url, $options, $auth);
    }

    /**
     *
     * @throws FailedHttpAccessException
     */
    private function postForm($url, array $values)
    {
        return $this->post($url, "application/x-www-form-urlencoded", ['form_params' => $values]);
    }

    /**
     *
     * @throws FailedHttpAccessException
     */
    private function postJson($url, array $values)
    {
        return $this->post($url, 'application/json', ['json' => $values]);
    }

    /**
     * @return object
     * @throws FailedHttpAccessException
     */
    private static function getResponseObject($response, $url)
    {
        $code = $response->getStatusCode();
        if ($code != 200) {
            $msg = __("api.fail.chat.exservice_api_call", ["service" => __("api.columns.chat_server_api"), "uri" => $url, "code" => $code]);
            throw new FailedHttpAccessException($msg, $code);
        }
        $json = $response->getBody();
        return json_decode($json, false);
    }

    private function makeApiUrl($method)
    {
        return $this->root_url() . "/api/v1/" . $method;
    }

    /**
     * 管理用パーソナルアクセストークンの取得
     *
     * @return string アクセストークン
     * @throws FailedHttpAccessException
     */
    public function getPersonalAccessToken($tokenname = null)
    {
        if (empty($tokenname)) {
            $tokenname = "forsystem_" . date("YmdHis");
        }
        $url = $this->makeApiUrl("users.generatePersonalAccessToken");
        $vals = [
            "tokenName" => $tokenname,
            "bypassTwoFactor" => true
        ];
        $json = $this->postJson($url, $vals);
        $ret = $json->token;
        $this->auth_token($ret);
        return $ret;
    }



    /**
     * 設定情報の設定
     *
     * @throws FailedHttpAccessException
     *
     * @see https://developer.rocket.chat/reference/api/rest-api/endpoints/team-collaboration-endpoints/settings-endpoints/update-settings
     */
    public function setSetting($setting, $value)
    {
        $url = $this->makeApiUrl("settings/" . $setting);
        $vals = [
            "value" => $value
        ];
        return $this->postJson($url, $vals);
    }

    /**
     * 設定情報の取得
     *
     * @throws FailedHttpAccessException
     *
     * @see https://developer.rocket.chat/reference/api/rest-api/endpoints/team-collaboration-endpoints/settings-endpoints/get-settings-by-id
     *
     */
    public function getSetting($setting)
    {
        $url = $this->makeApiUrl("settings/" . $setting);
        return $this->get($url);
    }

    /**
     * ユーザーの作成
     *
     * @throws FailedHttpAccessException
     *
     * @see https://developer.rocket.chat/reference/api/rest-api/endpoints/team-collaboration-endpoints/users-endpoints/create-user-endpoint
     */
    public function createUser(ChatUserDataProperties $user)
    {
        $res = $this->postApi("users.create", $user->toArray());
        return $res->user;
    }

    /**
     * ユーザーの削除
     *
     * @throws FailedHttpAccessException
     *
     * @see https://developer.rocket.chat/reference/api/rest-api/endpoints/team-collaboration-endpoints/users-endpoints/delete
     */
    public function deleteUser($user_id)
    {
        return $this->postApi("users.delete", ["userId" => $user_id, "confirmRelinquish" => true]);
    }

    /**
     * ユーザー情報の停止
     *
     * @throws FailedHttpAccessException
     *
     * @see https://developer.rocket.chat/reference/api/rest-api/endpoints/team-collaboration-endpoints/users-endpoints/update-user
     */
    public function stopUser($user_id)
    {
        return $this->changeUserActive($user_id, ChatUserStatus::PROCESSED_TO_STOP_STATUS);
    }

    /**
     * ユーザー情報の停止解除
     *
     * @throws FailedHttpAccessException
     *
     * @see https://developer.rocket.chat/reference/api/rest-api/endpoints/team-collaboration-endpoints/users-endpoints/update-user
     */
    public function unStopUser($user_id)
    {
        return $this->changeUserActive($user_id, ChatUserStatus::PROCESSED_TO_UNSTOP_STATUS);
    }

    /**
     * ユーザーのアクティブまたはインアクティブに設定します。
     *
     * @param string $user_id ユーザーID
     * @param bool $active true:アクティブ, false:インアクティブ
     *
     * @return object 実行結果
     */
    public function changeUserActive($user_id, bool $active = true)
    {
        $data = new ChatUserDataProperties();
        $data->active($active);

        $props = new ChatUserProperties();
        $props->userId($user_id);
        $props->data($data);

        return $this->updateUser($props);
    }

    /**
     * ユーザー情報の更新
     *
     * @param ChatUserProperties $user プロパティ
     *
     * @return
     * @throws FailedHttpAccessException
     *
     * @see https://developer.rocket.chat/reference/api/rest-api/endpoints/team-collaboration-endpoints/users-endpoints/update-user
     */
    public function updateUser(ChatUserProperties $user)
    {
        return $this->postApi("users.update", $user->toArray());
    }

    /**
     *
     * @throws FailedHttpAccessException
     */
    private function postApi($apiid, $param)
    {
        $url = $this->makeApiUrl($apiid);
        return $this->postJson($url, $param);
    }

    /**
     * ここのサイトURL
     *
     */
    public function setSiteUrl()
    {
        $this->setSetting("Site_Url", $this->root_url());
    }


    /**
     * 組織情報の設定
     *
     * @throws FailedHttpAccessException
     */
    public function setOrganization(ChatOrganizationProperties $org)
    {
        $this->setSetting(ChatOrganizationProperties::API_ID_TYPE, $org->type_code());
        $this->setSetting(ChatOrganizationProperties::API_ID_NAME, $org->name());
        $this->setSetting(ChatOrganizationProperties::API_ID_INDUSTRY, $org->industry());
        $this->setSetting(ChatOrganizationProperties::API_ID_SIZE, $org->size());
        $this->setSetting(ChatOrganizationProperties::API_ID_COUNTRY, $org->country());
        $this->setSetting(ChatOrganizationProperties::API_ID_WEBSITE, $org->website());
    }

    /**
     * サーバー情報の設定
     *
     * @throws FailedHttpAccessException
     */
    public function setServer(ChatServerProperties $svr)
    {
        $this->setSetting(ChatServerProperties::API_ID_SERVER_TYPE, $svr->server_type());
        $this->setSetting(ChatServerProperties::API_ID_SITE_NAME, $svr->site_name());
        $this->setSetting(ChatServerProperties::API_ID_LANGUAGE, $svr->language());
        $this->setSetting(ChatServerProperties::API_ID_2FA_EMAIL_AUTO, $svr->acconts_2fa_email_auto());
        $this->setSetting(ChatServerProperties::API_ID_REGISTER_SERVER, $svr->register_server());
    }

    /**
     * SMTP情報の設定
     */
    public function setSmtp(ChatSmtpProperties $smtp)
    {
        $this->setSetting(ChatSmtpProperties::PROTOCOL, $smtp->protocol());
        $this->setSetting(ChatSmtpProperties::HOST, $smtp->host());
        $this->setSetting(ChatSmtpProperties::PORT, $smtp->port());
        $this->setSetting(ChatSmtpProperties::IGNORE_TLS, $smtp->ignore_tls());
        $this->setSetting(ChatSmtpProperties::POOL, $smtp->pool());
        $this->setSetting(ChatSmtpProperties::USERNAME, $smtp->username());
        $this->setSetting(ChatSmtpProperties::PASSWORD, $smtp->password());
        $this->setSetting(ChatSmtpProperties::FROM, $smtp->from());
    }

    /**
     * サイトURLを設定
     *
     * @throws FailedHttpAccessException
     */
    public function completeSetupWizard()
    {
        $this->setSetting("Show_Setup_Wizard", "complete");
    }

    /**
     * 初期設定
     */
    public function initSetting()
    {
        // ログインフォームからのユーザー登録を無効
        $this->setSetting('Accounts_RegistrationForm', false);
        // ユーザー名（アカウント名）の変更は禁止
        // $this->setSetting('Accounts_AllowUsernameChange', false);
        // メールアドレスの変更は禁止
        $this->setSetting('Accounts_AllowEmailChange', false);
        // ユーザー名のルール
        $this->setSetting('UTF8_User_Names_Validation', '[0-9a-zA-Z-+_.]+');  // ^[a-zA-Z0-9][a-zA-Z0-9_+.\-]*$
        // ルーム名のルール
        $this->setSetting('UTF8_Channel_Names_Validation', '^[^/@#!\^][^/@#]*$');

        // Ctrl＋Enterで送信
        $this->setSetting('Accounts_Default_User_Preferences_sendOnEnter', 'alternative');
        // タイムゾーン
        $this->setSetting('Default_Timezone_For_Reporting', 'custom');
        $this->setSetting('Default_Custom_Timezone', 'Asia/Tokyo');

        // 統計情報をロケ茶に送信OFF
        $this->setSetting('Statistics_reporting', false);
        // 検索に常に正規表現を使用する
        $this->setSetting('Message_AlwaysSearchRegExp', true);
        // 2ファクタ認証を無効にする
        $this->setSetting('Accounts_TwoFactorAuthentication_Enabled', false);

    }

    /**
     * 初期設定 ID.setting のファイルから設定
     */
    public function initSettingFromFiles()
    {
        $storage = Storage::disk("chat_settings");
        $files = $storage->files();

        if (is_array($files)) {
            $list = [];
            $this->fileext_filter($files, "setting.local", function($key, $file) use (&$list){
                $list[$key] = $file;
            });
            $this->fileext_filter($files, "setting", function($key, $file) use (&$list){
                if (!array_key_exists($key, $list)) {
                    $list[$key] = $file;
                }
            });

            foreach($list as $id => $file) {
                $value = $storage->get($file);
                $this->setSetting($id, $value);
            }
        }
    }

    /**
     * チャットサーバーを再起動します
     */
    public function restartServer() {
        $v = ["message" => "{\"msg\":\"method\",\"id\":\"21\",\"method\":\"restart_server\",\"params\":[]}"];
        return $this->postApi("method.call/restart_server", $v);
    }

    private function fileext_filter(array $files, string $ext, Closure $function) {
        $pattern = '/^(.+)'. preg_quote("." . $ext)."$/";
        foreach($files as $file) {
            $matches = [];
            if (preg_match($pattern, $file, $matches) === 1) {
                $key = $matches[1];
                $function($key, $file);
            }
        }
    }
}
