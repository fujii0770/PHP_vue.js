<?php


namespace App\Chat;

use App\Chat\ChatRepository;
use App\Chat\Consts\ChatServiceStatus;
use App\Chat\Exceptions\DataAccessException;
use App\Chat\Properties\ChatRegisteredTaskDefinitionProperties;
use App\Chat\Properties\ChatServerInfoProperties;
use App\Chat\Properties\ChatTaskRegisterProperties;
use App\Chat\Properties\ChatTenantProperties;
use App\Chat\Properties\ChatEcsServiceProperties;

class ChatServerCreator
{
    private $repo = null;
    private $idclient = null;
    private $ecs = null;
    private $props = null;

    public function __construct(ChatServerInfoProperties $props)
    {
        $opuser = $props->operation_user();
        if (empty($opuser)) {
            $opuser = "system";
        }
        $props->operation_user($opuser);
        $this->props = $props;


        $this->repo = new ChatRepository();
        $this->idclient = new ChatIdServerClient();
        $this->ecs = new ChatEcsClient($this->repo->getAwsProperties());
    }


    /**
     * サーバー（コンテナ）を作成します
     *
     * @throws DataAccessException
     */
    public function createServer()
    {
        $repo = $this->repo;
        $props = $this->props;

        // [統合ID] サーバー予約
        $rrs = $this->reserveServer();
        $tenant_key = $rrs->tenant_key();
        $url = $rrs->server_url();
        $carn = $rrs->cluster_arn();
        $mongo = $rrs->mongo_url();
        $opuser = $props->operation_user();

        // マスタ情報の登録
        $id = ChatUtils::transaction(function() use($repo, $props, $opuser) {
            $repo->updateCompanyFlags($opuser, $props->company_id(), $props->is_trial());
            $ret = $repo->createServerInfo($props);

            $callback_url = $props->callback_url();
            if (!empty($callback_url)) {
                $repo->registerContractCallback($ret, $callback_url, $props->operation_user());
            }
            return $ret;
        });


        // 予約情報の保存
        ChatUtils::transaction(function() use($repo, $id, $url, $tenant_key, $mongo, $opuser) {
            return $repo->updateServerInfoForPreCreate($id, $url, $tenant_key, $mongo, $opuser);
        });
        // [ECS] タスク定義の登録
        $onekey = $this->makeOnetimeKey($tenant_key);
        $tdprops = $this->createTaskDefinition($rrs, $onekey);
        $tdef = $tdprops->task_definition_arn();

        // [ECS] ECSサービスの作成
        $regd_svr = $this->createEcsService($tenant_key, $carn, $tdef);
        // 渡したワンタイムトークンの保存 & 起動待ち
        ChatUtils::transaction(function() use($repo, $id, $onekey, $opuser) {
            $repo->updateServerInfoServerStatus($id, ChatServiceStatus::WAITING_FOR_STARTUP, $opuser);
            return $repo->registerServerOnetimeToken($id, $onekey, $opuser);
        });
        // [統合ID] サーバー登録情報の更新
        $this->updateServerCreated($regd_svr->service_arn(), $tdef);

    }


    /**
     * サーバー予約
     * @return ChatTenantProperties
     */
    private function reserveServer() {
        $co_id = $this->props->company_id();
        $co_name = $this->props->company_name();
        $server_name = $this->props->server_name();
        $plan = $this->props->plan;

        $idc = $this->idclient;
        return $idc->reserveServerName($server_name, $co_id, $co_name, $plan);
    }

    /**
     * タスク定義の登録
     *
     * @return ChatRegisteredTaskDefinitionProperties
     */
    private function createTaskDefinition(ChatTenantProperties $props, $onetime_key) {
        $tkey = $props->tenant_key();
        $mongo = $props->mongo_url();
        $surl = $props->server_url();
        $image = $props->image();

        $repo = $this->repo;
        $tenv = $repo->getDefaultTaskEnvironmentValues();
        $tenv->mongo_oplog_url($this->getMongoOplogUrl($mongo));
        $tenv->mongo_url($this->getMongoUrl($mongo, $tkey));
        $tenv->root_url($surl);
        $tenv->virtual_host(parse_url($surl)["host"]);
        $tenv->opening_callback_url($this->makeCallbackUrl($tkey, $onetime_key));
        $tenv->fileupload_s3_bucket($this->makeS3Bucket($tkey));

        $treg = new ChatTaskRegisterProperties();
        $treg->environments($tenv);
        $treg->image($image);
        $treg->container_name($tkey);
        $treg->awslog_driver(config("chat.aws_tenant_log_driver"));
        $treg->awslogs_group($this->getAwsLogGroup($tkey));
        $treg->awslogs_region(config("chat.aws_tenant_logs_region"));
        $treg->awslogs_stream_prefix(config("chat.aws_tenant_logs_stream_prefix"));
        $treg->memory_reservation(config("chat.aws_tenant_memory_reservation"));
        $treg->execution_role_arn(config("chat.aws_execution_role_arn"));
        $treg->task_role_arn(config("chat.aws_task_role_arn"));
        $treg->family($tkey);

        // タスク定義の登録
        $ecs = $this->ecs;
        $ret = $ecs->registerTaskDefinition($treg);
        return $ret;
    }

    private function getAwsLogGroup($tkey) {
        $ret = config("chat.aws_tenant_logs_group", "/ecs/{key}");
        $ret = str_replace("{key}", $tkey, $ret);
        return $ret;
    }

    private function getMongoOplogUrl($mongobase) {
        $ret = trim($mongobase,"/");
        $op = config("chat.mongo_oplog_db", "local");
        $ret .= "/".trim($op, "/");
        return $ret;
    }

    private function getMongoUrl($mongobase, $tenant_key) {
        $ret = trim($mongobase,"/");
        $ret .= "/".$tenant_key;
        $opt = config("chat.mongo_option", "");
        if ($opt !== "") {
            $ret .= "?".trim($opt, "?");
        }
        return $ret;
    }

    /**
     * Ecsサービスの登録
     * @return ChatEcsServiceProperties
     */
    private function createEcsService($tenant_key, $cluster, $task_definition) {

        $svrp = new ChatEcsServiceProperties();
        $svrp->task_definition($task_definition);
        $svrp->cluster($cluster);
        $svrp->service_name($tenant_key);

        $ecs = $this->ecs;
        return $ecs->craeteService($svrp);
    }

    /**
     * サーバー登録情報を「作成済み」に更新
     */
    private function updateServerCreated($service_arn, $task_definition) {
        $company_id = $this->props->company_id;
        $server_name = $this->props->server_name;

        // $status = "created";

        $uppp = new ChatTenantProperties();
        $uppp->company_id($company_id);
        $uppp->server_name($server_name);
        // $uppp->status($status);
        $uppp->service_arn($service_arn);
        $uppp->task_definition($task_definition);

        $idc = $this->idclient;
        $idc->updateServerInfo($uppp);
    }

    /**
     * チャットサーバーに渡すコールバック用URLを作成します。
     *
     * @return string
     */
    protected function makeCallbackUrl($tenant_key, $onetime_key) {
        $uri = config("chat.rest_path_for_init");
        $ret = url($uri."?a=".urlencode($onetime_key)."&b=".urlencode($tenant_key));
        return $ret;
    }

    protected function makeOnetimeKey($tenant_key) {
        $ret = trim(base64_encode(bin2hex(random_bytes(32))),"=");
        $ret = substr_replace($ret, $tenant_key, 5, 0);
        return $ret;
    }

    /**
     * チャットサーバーに設定するS3のバケットのパスを作成します。
     *
     * @return string
     */
    protected function makeS3Bucket($tenant_key) {
        $ret = config("chat.aws_s3_bucket_root");
        $ret .= "/".$tenant_key;
        return $ret;
    }
}
