<?php


namespace App\Chat;

use App\Chat\ChatRepository;
use App\Chat\Consts\ChatCallbackStatusToContractSite;
use App\Chat\Consts\ChatServiceStatus;
use App\Chat\Consts\ChatStatus;
use App\Chat\Consts\ChatUserStatus;
use App\Chat\Exceptions\CompanyNotFoundException;
use App\Chat\Exceptions\DataAccessException;
use App\Chat\Exceptions\DataNotFoundException;
use App\Chat\Exceptions\ExclusiveException;
use App\Chat\Exceptions\FailedHttpAccessException;
use App\Chat\Properties\ChatOrganizationProperties;
use App\Chat\Properties\ChatServerInfoProperties;
use App\Chat\Properties\ChatServerProperties;
use App\Chat\Properties\ChatSmtpProperties;
use App\Chat\Properties\ChatTenantProperties;
use App\Chat\Properties\ChatUserDataProperties;
use App\Chat\Properties\PlainProperties;
use App\Http\Utils\AppUtils;
use Closure;
use Exception;
use Illuminate\Support\Facades\Log;

class ChatService
{
    private $_props = null;

    public function __construct()
    {
        $this->_props = new PlainProperties();
    }

    /**
     *
     * @return ChatRepository
     */
    private function chat_repository()
    {
        return $this->_props->getIfNullSet(__FUNCTION__, function () {
            return new ChatRepository();
        });
    }

    /**
     *
     * @return ChatIdServerClient
     */
    private function idclient()
    {
        return $this->_props->getIfNullSet(__FUNCTION__, function () {
            return new ChatIdServerClient();
        });
    }

    /* ✔ */
    /**
     * サブドメイン（サーバー）の存在を確認します。
     *
     * @param string $server_name サブドメイン名
     * @return boolean
     */
    public function checkAvailableServerName($server_name)
    {
        $idc = $this->idclient();
        // サーバー名の存在確認
        return $idc->checkAvailableServerName($server_name);
    }

    /* ✔ */
    /**
     * サーバー（コンテナ）を作成します
     *
     * @param string $company_id 企業ID
     * @param string $server_name サーバー名（サブドメイン）
     *
     * @throws DataAccessException
     * @throws CompanyNotFoundException
     */
    public function createServer(ChatServerInfoProperties $props)
    {
        $co = $this->getCompany($props->company_id());
        $props->company_name($co->company_name);
        $creator = new ChatServerCreator($props);
        $creator->createServer();
    }

    private function getCompany($company_id)
    {
        $repo = $this->chat_repository();
        $company = $repo->getCompany($company_id);
        if (empty($company)) {
            throw new CompanyNotFoundException($company_id);
        }
        return $company;
    }

    protected function getOperationUser()
    {
        return "system"; // TODO
    }


    /**
     * @throws DataNotFoundException
     * @throws DataAccessException
     */
    public function initServer($tenant_key, $onetime_key)
    {
        $repo = $this->chat_repository();
        $opuser = $this->getOperationUser();

        // ワンタイムキーからサーバーIDの取得
        $id = $repo->getServerIdFromOnetimeKey($onetime_key);
        if (empty($id)) {
            throw new DataNotFoundException("Not found subdomain_id $id");
        }
        // ワンタイムキーの削除とステータスを初期化中に
        ChatUtils::transaction(function () use ($repo, $id, $onetime_key, $opuser) {
            $repo->updateServerInfoServerStatus($id, ChatServiceStatus::INITIALIZING, $opuser); //初期化中に更新
            return $repo->deleteServerOnetimeToken($id, $onetime_key);
        });

        // サーバー情報の取得
        $info = $repo->getServerInfoPreOpenByIdAndKey($id, $tenant_key);

        // チャットサーバーの初期化
        [$aid, $token] = $this->initTenant($repo, $info, $id, $opuser);

        // サーバー情報を更新
        $tenant = new ChatTenantProperties();
        $tenant->company_id($info->company_id());
        $tenant->server_name($info->server_name());
        $tenant->admin_id($aid);
        $tenant->admin_token($token);
        $tenant->status(ChatStatus::VALID);

        $idc = $this->idclient();
        $idc->updateServerInfo($tenant);

        // コールバック
        $this->callback($id, $opuser);
    }

    /**
     * 
     */
    private function initTenant($repo, $info, $id, $opuser)
    {
        $url = $info->server_url();
        $uname = $info->admin_username();
        $psswd = $info->admin_password();
        $retry = $repo->getLoginRetries();
        $sleep = $repo->getLoginSleepSeconds();
        $server_name = $info->server_name();
        $company_id = $info->company_id();

        // サーバーにログイン
        $client = new ChatTenantClient($url);
        $client->loginServer($uname, $psswd, $retry, $sleep);
        // アクセストークンの取得
        $token = $client->getPersonalAccessToken();
        $aid = $client->auth_id();

        // アクセストークンの登録
        ChatUtils::transaction(function () use ($repo, $id, $aid, $token, $opuser) {
            return $repo->updateServerAdminToken($id, $aid, $token, $opuser);
        });

        $company = $repo->getCompany($company_id);
        // サーバーの設定
        $this->_initServer($client, $server_name, $company->company_name);

        ChatUtils::transaction(function () use ($repo, $id, $opuser) {
            return $repo->updateServerOpen($id, $opuser);
        });

        return [$aid, $token];
    }

    /**
     * 
     */
    private function callback($id, $opuser)
    {
        $repo = $this->chat_repository();
        $url = $repo->getContractCallbackUrl($id);
        if (empty($url)) {
            return;
        }

        $client = ChatUtils::makeHttpClient();
        $status = ChatCallbackStatusToContractSite::CALLBACKED;
        try {
            Log::debug("Callback Request : [ GET ] $url : ");
            $r = $client->request("GET", $url);
            if ($r->getStatusCode() !== 200) {
                $status = ChatCallbackStatusToContractSite::FAILED;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $status = ChatCallbackStatusToContractSite::FAILED;
        }
        ChatUtils::transaction(function () use ($repo, $id, $status, $opuser) {
            return $repo->updateContractCallbackUrlStatus($id, $status, $opuser);
        });
    }

    /**
     * サーバー（コンテナ）起動後の初期処理を行います。
     *
     * @param string $server_name サーバー名（サブドメイン）
     * @param string $company_name 企業名
     */
    private function _initServer(ChatTenantClient $client, $server_name, $company_name)
    {
        $orgp = new ChatOrganizationProperties();
        $orgp->name($company_name);

        $svrp = new ChatServerProperties();
        $svrp->site_name(
            __("api.columns.chat.site_name", ["tenant" => $server_name])
        );

        $smtp = new ChatSmtpProperties();
        $smtp->protocol(config("chat.smtp_protocol"));
        $smtp->host(config("chat.smtp_host"));
        $smtp->port(config("chat.smtp_port"));
        $smtp->ignore_tls(config("chat.smtp_ignore_tls"));
        $smtp->pool(config("chat.smtp_pool"));
        $smtp->username(config("chat.smtp_username"));
        $smtp->password(config("chat.smtp_password"));
        $smtp->from(config("chat.smtp_from"));

        $client->setOrganization($orgp);
        $client->setServer($svrp);
        // $client->setSiteUrl($client->root_url());
        $client->setSmtp($smtp);
        $client->initSetting();
        $client->initSettingFromFiles();
        $client->completeSetupWizard();
        $client->restartServer();
    }

    /**
     * サーバーの情報を取得します。
     *
     */
    public function getServerInfo($company_id, $server_name = null)
    {
        $this->getCompany($company_id);
        $repo = $this->chat_repository();
        $info = $repo->getServerInfoByCompanyId($company_id, $server_name);

        return $info;
    }

    /**
     * サーバーの設定を変更します。
     *
     */
    public function updateServerInfo(ChatServerInfoProperties $props)
    {
        $company = $this->getCompany($props->company_id());
        $opuser = $this->getOperationUser();
        return ChatUtils::transaction(function () use ($company, $props, $opuser) {
            $repo = $this->chat_repository();
            $company_id = $props->company_id();

            $company_chat_flg = self::conv_flag($company->chat_flg);
            $company_chat_trial_flg = self::conv_flag($company->chat_trial_flg);
            $is_contract =  ChatUtils::nullvalue($props->is_contract(), $company_chat_flg);
            $is_trial =  ChatUtils::nullvalue($props->is_trial(), $company_chat_trial_flg);

            if ($is_contract !== $company_chat_flg || $is_trial !== $company_chat_trial_flg) {
                $repo->updateCompanyFlags($opuser, $company_id, $is_trial, $is_contract);
            }

            $idv = $repo->selectForUpdateByCompanyAndServerName($company_id, $props->server_name());
            if (empty($idv)) {
                throw new DataNotFoundException();
            } else {
                if ($idv->version !== $props->version()) {
                    throw new ExclusiveException();
                }
                if ($repo->updateServerInfo($idv->id, $props, $opuser) === 0) {
                    throw new ExclusiveException();
                }
            }
            return 1;
        });

    }

    private static function conv_flag($value) {
        return $value == 1 ? true : false;
    }


    /**
     * ユーザーを追加します。
     *
     * @param subdomain_id mst_chat.id
     *
     * @return array ["target"=>処理件数, "success"=>成功件数, "failure"=>失敗件数]
     *
     */
    public function addUser($subdomain_id)
    {
        return $this->procUser(
            $subdomain_id,
            ChatUserStatus::WAITING_TO_REGISTER,
            function ($client, $user) {
                $u = new ChatUserDataProperties();
                $u->name($user->chat_personal_name);
                $u->username($user->chat_user_name);
                $u->email($user->chat_email);
                $u->roles([$this->getChatRole($user->chat_role_flg)]);
                $u->joinDefaultChannels(true);
                $u->password($this->generateChatTempPassword());
                $u->requirePasswordChange(true);
                $u->sendWelcomeEmail(false);
                $u->setRandomPassword(true);
                $u->verified(false);

                $cu = $client->createUser($u);
                $user->chat_user_id = $cu->_id;
            },
            ChatUserStatus::VALID,
            ChatUserStatus::PROCESSED_REGISTER_FAIL,
            function ($repo, $rusers) {
                $repo->updateRegistUsers($rusers);
            }
        );
    }

    protected function getChatRole($role_flg)
    {
        $ret = config("chat.tenant_user_role_name");
        if ($role_flg === 0) {
            $ret = config("chat.tenant_admin_role_name");
        }
        return $ret;
    }

    protected function generateChatTempPassword()
    {
        $len = 8; // 適当な長さ
        return "s2a3t3och" . substr(bin2hex(random_bytes($len)), 0, $len); // 適当な値
    }


    /* ✔ */
    /**
     * ユーザーを削除します。
     *
     */
    public function deleteUser($subdomain_id)
    {
        return $this->procUser(
            $subdomain_id,
            ChatUserStatus::WAITING_TO_DELETE,
            function ($client, $user) {
                $cuid = $user->chat_user_id;
                $client->deleteUser($cuid);
            },
            ChatUserStatus::DELETED,
            ChatUserStatus::PROCESSED_DELETE_FAIL
        );
    }

    /**
     * ユーザー情報を停止します。
     *
     */
    public function stopUser($subdomain_id)
    {
        return $this->procUser(
            $subdomain_id,
            ChatUserStatus::WAITING_TO_STOP,
            function ($client, $user) {
                $cuid = $user->chat_user_id;
                $client->stopUser($cuid);
            },
            ChatUserStatus::STOPPED,
            ChatUserStatus::PROCESSED_TO_STOP
        );
    }

    /**
     * ユーザー情報を停止解除します。
     *
     */
    public function unstopUser($subdomain_id)
    {
        return $this->procUser(
            $subdomain_id,
            ChatUserStatus::WAITING_TO_UNSTOP,
            function ($client, $user) {
                $cuid = $user->chat_user_id;
                $client->unStopUser($cuid);
            },
            ChatUserStatus::VALID,
            ChatUserStatus::PROCESSED_TO_UNSTOP
        );
    }


    /**
     *
     * @param string $subdomain_id サブドメインID
     * @param \Illuminate\Support\Collection $users 該当シャチクラユーザー
     * @param Closure $oneUserFunction １ユーザーに関する処理
     *     （引数: ChatTenantClientインスタンス, ユーザーデータ）
     * @param int $okStatus 成功時に設定するステータス
     * @param int $ngStatus 失敗時に設定するステータス
     * @param Closure $updateUsersStatus 最後にユーザー情報を更新する処理
     *     （引数：Repositoryインスタンス, ユーザーコレクション）
     */
    private function procUser(
        string $subdomain_id,
        int $awaiting_status,
        Closure $oneUserFunction,
        int $okStatus,
        int $ngStatus,
        Closure $updateUsersStatus = null
    ) {
        $repo = $this->chat_repository();
        // サーバー情報の取得
        $info = $this->getServerInfoById($subdomain_id);
        $url = $info->server_url();
        $aid = $info->admin_id();
        $token = $info->admin_token();

        // カウント変数の初期化
        $total = 0;
        $success = 0;
        $failure = 0;

        $client = new ChatTenantClient($url, $aid, $token);
        $rusers = [];

        $users = $repo->getAwaitingUsers($subdomain_id, $awaiting_status);
        if ($users == null) {
            throw new DataNotFoundException();
        }

        foreach ($users as $user) {
            $total++;
            try {
                $oneUserFunction($client, $user);
                $success++;
                $user->status = $okStatus;
                $user->system_remark = null;
            } catch (Exception $e) {
                Log::error($e->getMessage() . $e->getTraceAsString());
                $failure++;
                $user->status = $ngStatus; // 91
                $user->system_remark = self::generateSystemRemark($e);
            }
            $user->operation_user = self::choiseOpeUserId($user);
            $rusers[] = $user;
        }

        if ($updateUsersStatus !== null) {
            $updateUsersStatus($repo, $rusers);
        } else {
            $repo->updateUsersStatus($rusers, $awaiting_status);
        }

        return ["target" => $total, "success" => $success, "failure" => $failure];
    }


    private static function generateSystemRemark($exception)
    {
        $msg = null;
        if ($exception instanceof FailedHttpAccessException) {
            $msg = $exception->getMessage();
            if ($msg === null || (is_string($msg) && trim($msg) === "")) {
                $msg = "Response code : " . $exception->getCode();
            }
        }
        if (empty($msg)) {
            $msg = __('message.false.api.system_error');
        }
        return $msg;
    }

    private static function choiseOpeUserId($user)
    {
        $opuser = $user->update_user;
        if ($opuser === null) {
            $opuser = $user->create_user;
        }
        return $opuser;
    }

    /**
     * @return ChatServerInfoProperties
     */
    private function getServerInfoById($id)
    {
        $repo = $this->chat_repository();

        // サーバー情報の取得
        $info = $repo->getServerInfoById($id);
        if (empty($info)) {
            throw new DataNotFoundException("Server information not found. ($id)");
        }
        return $info;
    }
}
