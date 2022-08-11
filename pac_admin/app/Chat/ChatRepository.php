<?php

namespace App\Chat;

use App\Chat\Consts\ChatCallbackStatusToContractSite;
use App\Chat\Consts\ChatServiceStatus;
use App\Chat\Consts\ChatStatus;
use App\Chat\Consts\ChatUserStatus;
use App\Chat\Properties\AbstractProperties;
use App\Chat\Properties\ChatAwsProperties;
use App\Chat\Properties\ChatServerInfoProperties;
use App\Chat\Properties\ChatTaskEnvironmentValues;
use DateTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ChatRepository
{
    use ChatTrait;

    /**
     *
     * @return ChatAwsProperties
     */
    public function getAwsProperties()
    {
        $ret = new ChatAwsProperties();
        $ret->access_key_id(config("chat.aws_key"));
        $ret->secret_access_key(config("chat.aws_secret"));
        $ret->region(config("chat.aws_region"));
        return $ret;
    }

    /**
     * タスク定義用に設定するデフォルトの環境変数を取得します
     *
     * @return ChatTaskEnvironmentValues
     *
     */
    public function getDefaultTaskEnvironmentValues()
    {
        $ret = new ChatTaskEnvironmentValues();
        $ret->timezone("Asia/Tokyo");
        $ret->virtual_port(3000);
        $ret->fileupload_storage_type("AmazonS3");
        $ret->fileupload_s3_awsaccesskeyid(config("chat.aws_s3_key"));
        $ret->fileupload_s3_awssecretaccesskey(config("chat.aws_s3_secret"));
        $ret->fileupload_s3_region(config("chat.aws_s3_region"));
        $ret->admin_email(config("chat.default_admin_email"));
        $ret->admin_password(config("chat.default_admin_password"));
        $ret->admin_username(config("chat.default_admin_username"));
        return $ret;
    }

    /* ============ */

    /* 未テスト */
    public function getCompany($id)
    {
        return DB::table('mst_company')->where('id', $id)->first();
    }

    /* ================================== */

    const MST_CHAT = "mst_chat";
    const CHAT_SERVER_TEMP = "chat_server_onetime_token";
    const CHAT_SERVER_USERS = "chat_server_users";
    const CONTRACTED_SITES_CALLBACK_URL = "contracted_sites_callback_url";

    /* ✔ */
    /**
     *
     * @return int ID(PK)
     */
    public function createServerInfo(ChatServerInfoProperties $props)
    {
        return DB::table(self::MST_CHAT)->insertGetId([
            'mst_company_id' => $props->company_id(),
            'trial_start_date' => $props->trial_start_date(),
            'trial_end_date' => $props->trial_end_date(),
            'contract_start_date' => $props->contract_start_date(),
            'contract_end_date' => $props->contract_end_date(),
            'user_max_limit' => $props->user_max(),
            'storage_max_limit' => $props->storage_max_mega(),
            'domain' => $props->server_name(),
            'contract_type' => $props->plan(),
            'status' => ChatStatus::INVALID, // 0:無効
            'create_at' => Carbon::now(),
            'create_user' => $props->operation_user(),
            'service_status' => ChatServiceStatus::NONE
        ]);
    }

    /**
     * 企業マスタのフラグを変更します
     */
    public function updateCompanyFlags($operation_user, $company_id, bool $trial_flag, bool $chat_flag = true)
    {
        return DB::table('mst_company')
            ->where('id', $company_id)
            ->update([
                'chat_flg' => $chat_flag,
                'chat_trial_flg' => $trial_flag,
                'update_at' => Carbon::now(),
                'update_user' => $operation_user,
            ]);
    }

    /**
     * サーバー作成前に、取得したサーバーURLとテナントキーを保存します
     *
     * @return int
     */
    public function updateServerInfoForPreCreate(
        int $id,
        string $server_url,
        string $tenant_key,
        string $mongo_url,
        string $operation_user
    ) {
        return DB::table(self::MST_CHAT)
            ->where("id", $id)
            ->update([
                'url' => $server_url,
                'tenant_key' => $tenant_key,
                'mongo_url' => $mongo_url,
                'update_at' => Carbon::now(),
                'update_user' => $operation_user,
                'version' => DB::raw('version + 1'),
            ]);
    }

    /**
     * サービス状況の更新
     */
    public function updateServerInfoServerStatus(int $id, int $status, string $operation_user)
    {
        return DB::table(self::MST_CHAT)
            ->where("id", $id)
            ->update([
                'service_status' => $status, // 0:未作成 1:起動待ち 2:初期化中 3:実行中
                'service_status_at' => Carbon::now(),
                'update_at' => Carbon::now(),
                'update_user' => $operation_user,
                'version' => DB::raw('version + 1'),
            ]);
    }

    /**
     * サーバー情報の更新
     */
    public function updateServerInfo($id, ChatServerInfoProperties $props, $operation_user)
    {
        $names = [
            "trial_start_date",
            "trial_end_date",
            "contract_start_date",
            "contract_end_date",
            "user_max"            => "user_max_limit",
            "storage_max_mega"    => "storage_max_limit",
            "plan"                => "contract_type",
        ];
        $sets = $this->toArrayExistsParams($props, $names);
        if (empty($sets)) {
            return false;
        }

        $sets['update_at'] = Carbon::now();
        $sets['update_user'] = $operation_user;
        $sets['version'] = DB::raw('version + 1');

        return DB::table(self::MST_CHAT)
            ->where("id", $id)
            ->where("mst_company_id", $props->company_id())
            ->where("version", $props->version())
            ->update($sets);
    }


    private function toArrayExistsParams(
        AbstractProperties $props,
        array $names
    ) {
        $ret = [];
        foreach ($names as $propkey => $tokey) {
            $key = $propkey;
            if (!is_string($key)) {
                $key = $tokey;
            }
            if ($props->has($key)) {
                $ret[$tokey] = $props->property($key);
            }
        }
        return $ret;
    }

    public function selectForUpdateByCompanyAndServerName($company_id, $server_name = null)
    {

        $query = DB::table(self::MST_CHAT . " AS s")
            ->where("s.mst_company_id", $company_id)
            ->select(
                's.id',
                's.version'
            )
            ->orderBy("s.id")
            ->lockForUpdate();

        if ($server_name !== null) {
            $query->where("s.domain", $server_name);
        }

        return $query->first();
    }


    /**
     *
     * @return bool
     */
    public function registerServerOnetimeToken(
        int $subdomain_id,
        string $onetime_token,
        string $operation_user
    ) {
        return DB::table(self::CHAT_SERVER_TEMP)->insert([
            'sub_domain_id' => $subdomain_id,
            'onetime_token' => $onetime_token,
            'create_at' => Carbon::now(),
            'create_user' => $operation_user,
        ]);
    }

    /* ✔ */
    /**
     *
     *
     */
    public function getServerIdFromOnetimeKey(string $onetime_key)
    {
        $res = DB::table(self::CHAT_SERVER_TEMP)
            ->where("onetime_token", $onetime_key)
            ->first("sub_domain_id");
        $ret = null;
        if ($res !== null) {
            $ret = $res->sub_domain_id;
        }
        return $ret;
    }

    /**
     *
     */
    public function deleteServerOnetimeToken($subdomain_id, $onetime_key)
    {
        return DB::table(self::CHAT_SERVER_TEMP)
            ->where("sub_domain_id", $subdomain_id)
            ->where("onetime_token", $onetime_key)
            ->delete();
    }

    /* ✔ */
    /**
     *
     */
    public function getServerInfoPreOpenByIdAndKey($subdomain_id, $tenant_key)
    {
        $rs = DB::table(self::MST_CHAT . " AS s")
            ->where("s.id", $subdomain_id)
            ->where("s.tenant_key", $tenant_key)
            ->whereNull("s.admin_id")
            ->whereNull("s.admin_token")
            ->select(
                's.mst_company_id',
                's.domain',
                's.url',
                's.tenant_key',
                's.version'
            )
            ->first();

        $ret = null;
        if ($rs !== null) {
            $ret = new ChatServerInfoProperties();
            $ret->company_id($rs->mst_company_id);
            $ret->server_name($rs->domain);
            $ret->server_url($rs->url);
            $ret->tenant_key($rs->tenant_key);
            $ret->version($rs->version);
            $ret->admin_username(config("chat.default_admin_username"));
            $ret->admin_password(config("chat.default_admin_password"));
        }

        return $ret;
    }

    private static function copyProps(
        object $sourceObject,
        AbstractProperties $destProps,
        array $datecols = null
    ) {
        foreach ($sourceObject as $col => $val) {
            if (is_array($datecols) && in_array($col, $datecols) && !empty($val)) {
                $val = new DateTime($val);
            }
            $destProps->$col($val);
        }
        return $destProps;
    }

    public function countChatUsers($subdomain_id)
    {
        return DB::table(self::CHAT_SERVER_USERS . " AS u")
            ->where("u.mst_chat_id", $subdomain_id)
            ->groupBy("u.status")
            ->select(
                "u.status",
                DB::raw("count(u.id) as user_count")
            )
            ->get();
    }


    /* ✔ */
    public function getServerInfoByCompanyId($company_id, $server_name = null)
    {
        return $this->_getServerInfoByCompanyId($company_id, $server_name, ChatStatus::VALID); // 1:有効
    }



    public function registerContractCallback($subdomain_id, $url, $operation_user)
    {
        return DB::table(self::CONTRACTED_SITES_CALLBACK_URL)->insert([
            "mst_chat_id" => $subdomain_id,
            "call_back_url" => $url,
            "status" => ChatCallbackStatusToContractSite::WAITING,
            "create_at" => Carbon::now(),
            "create_user" => $operation_user,
        ]);
    }

    public function getContractCallbackUrl($subdomain_id)
    {
        return DB::table(self::CONTRACTED_SITES_CALLBACK_URL)
            ->where("mst_chat_id", $subdomain_id)
            ->value("call_back_url");
    }

    public function updateContractCallbackUrlStatus($subdomain_id, $status, $operation_user)
    {
        return DB::table(self::CONTRACTED_SITES_CALLBACK_URL)
            ->where("mst_chat_id", $subdomain_id)
            ->update([
                "status" => $status,
                "update_at" => Carbon::now(),
                "update_user" => $operation_user,
            ]);
    }

    /**
     * @return ChatServerInfoProperties
     */
    private function _getServerInfoByCompanyId($company_id, $server_name = null, $status = null)
    {
        $q = $this->_getServerInfoBase($server_name, $status);
        $q->orderBy("s.id");
        $q->where("c.id", $company_id);
        return $this->_makeChatServerInfo($q->first());
    }

    private function _makeChatServerInfo($result)
    {
        $ret = null;
        if ($result !== null) {
            $datecols = [
                'id',
                'trial_start_date',
                'trial_end_date',
                'contract_start_date',
                'contract_end_date',
                'create_at',
                'update_at',
                'service_status_at',
            ];
            $ret = new ChatServerInfoProperties();
            self::copyProps($result, $ret, $datecols);
        }
        return $ret;
    }


    private function _getServerInfoBase($server_name = null, $status = null)
    {
        return DB::table("mst_company AS c")
            ->leftJoin(self::MST_CHAT . " AS s", function ($join) use ($server_name, $status) {
                $join->on('c.id', '=', 's.mst_company_id');
                if ($server_name !== null) {
                    $join->where("s.domain", '=', $server_name);
                }
                if ($status !== null) {
                    $join->where("s.status", '=', $status);
                }
            })
            ->select(
                'c.id as company_id',
                's.trial_start_date',
                's.trial_end_date',
                's.contract_start_date',
                's.contract_end_date',
                's.user_max_limit as user_max',
                's.storage_max_limit as storage_max_mega',
                's.domain as server_name',
                's.contract_type as plan',
                's.status',
                's.url as server_url',
                's.tenant_key',
                's.admin_id',
                's.admin_token',
                's.create_at',
                's.create_user',
                's.update_at',
                's.update_user',
                's.service_status',
                's.service_status_at',
                's.version',
                'c.chat_flg as is_contract',
                'c.chat_trial_flg as is_trial'
            );
    }

    /**
     *
     * @return ChatServerInfoProperties
     */
    public function getServerInfoById($id)
    {
        $q = $this->_getServerInfoBase();
        $q->where("s.id", $id);
        return $this->_makeChatServerInfo($q->first());
    }

    /**
     *
     */
    public function updateServerAdminToken($id, $admin_id, $admin_token, $operation_user)
    {
        return DB::table(self::MST_CHAT)
            ->where("id", $id)
            ->where("status", ChatStatus::INVALID)
            ->update([
                'admin_id' => $admin_id,
                'admin_token' => $admin_token,
                'update_at' => Carbon::now(),
                'update_user' => $operation_user,
                'version' => DB::raw("version + 1"),
            ]);
    }

    /**
     *
     */
    public function updateServerOpen($id, $operation_user)
    {
        return DB::table(self::MST_CHAT)
            ->where("id", $id)
            ->where("status", 0)
            ->update([
                'status' => ChatStatus::VALID, // 1:有効
                'service_status' => ChatServiceStatus::RUNNING, // 3:実行中
                'service_status_at' => Carbon::now(),
                'update_at' => Carbon::now(),
                'update_user' => $operation_user,
                'version' => DB::raw("version + 1"),
            ]);
    }

    /* ✔ */
    /**
     * @return int
     */
    public function getLoginRetries()
    {
        return config("chat.login_retries");
    }

    /* ✔ */
    /**
     * @return int
     */
    public function getLoginSleepSeconds()
    {
        return config("chat.login_sleep_seconds");
    }

    /**
     * @deprecated getAwaitingUsersを使用してください
     */
    public function getAwaitingRegistUsers($subdomain_id)
    {
        return $this->getAwaitingUsers($subdomain_id, ChatUserStatus::WAITING_TO_REGISTER);
    }

    /**
     * 登録・削除・停止・停止解除の取得部分を共通化
     *
     * @param int $subdomain_id 更新対象企業のmst_chat.id
     * @param int $user_status 更新条件のstatus
     *
     */
    public function getAwaitingUsers($subdomain_id, $user_status)
    {
        return DB::table(self::CHAT_SERVER_USERS . " AS s")
            ->where("s.mst_chat_id", $subdomain_id)
            ->where("s.status", $user_status) // 更新条件のstatus
            ->select([
                's.id',
                's.chat_user_id',
                's.chat_personal_name',
                's.chat_user_name',
                's.chat_email',
                's.chat_role_flg',
                's.chat_user_id',
                's.system_remark',
                's.status',
                's.create_user',
                's.update_user',
            ])
            ->get();
    }

    public function updateRegistUsers($users)
    {
        // TODO
        $ret = 0;
        foreach ($users as $user) {
            $ret += DB::table(self::CHAT_SERVER_USERS)
                ->where("id", $user->id)
                ->where("status", ChatUserStatus::WAITING_TO_REGISTER)
                ->update(
                    [
                        "chat_user_id" => $user->chat_user_id,
                        "status" => $user->status,
                        "system_remark" => $user->system_remark,
                        "update_at" => Carbon::now(),
                        "update_user" =>  $user->operation_user,
                    ]
                );
        }
        return $ret;
    }

    /**
     * @deprecated getAwaiintUsersを使用してください
     */
    public function getAwaitingDeleteUsers($subdomain_id)
    {
        return $this->getAwaitingUsers($subdomain_id, ChatUserStatus::WAITING_TO_DELETE);
    }

    /**
     * @deprecated updateUsersStatusを使用してください。
     */
    public function updateDeleteUsers($users)
    {
        return $this->updateUsersStatus($users, ChatUserStatus::WAITING_TO_DELETE);
    }

    /**
     * 削除・停止・停止解除の最終更新部分を共通化
     *
     * @param array $users chat_server_users.id・chat_user_id・status・system_remark・update_user
     * @param int $user_status 更新条件のstatus
     *
     */
    public function updateUsers($users, $user_status)
    {
        return $this->updateUsersStatus($users, $user_status);
    }

    /**
     * 削除・停止・停止解除の最終更新部分を共通化
     *
     * @param array $users chat_server_users.id・chat_user_id・status・system_remark・update_user
     * @param int $user_status 更新条件のstatus
     *
     */
    public function updateUsersStatus($users, $user_status)
    {
        $ret = 0;
        foreach ($users as $user) {
            $ret += DB::table(self::CHAT_SERVER_USERS)
                ->where("id", $user->id)
                ->where("chat_user_id", $user->chat_user_id)
                ->where("status", $user_status) // 更新条件のstatus
                ->update(
                    [
                        "status" => $user->status,
                        "system_remark" => $user->system_remark,
                        "update_at" => Carbon::now(),
                        "update_user" => $user->operation_user,
                    ]
                );
        }
        return $ret;
    }

}
