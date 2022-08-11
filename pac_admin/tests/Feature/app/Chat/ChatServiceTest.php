<?php

namespace Tests\Feature\app\Chat;

use App\Chat\ChatEcsClient;
use App\Chat\ChatIdServerClient;
use Tests\TestCase;

use App\Chat\ChatRepository;
use App\Chat\ChatService;
use App\Chat\ChatTenantClient;
use App\Chat\Consts\ChatCallbackStatusToContractSite;
use App\Chat\Consts\ChatPlan;
use App\Chat\Consts\ChatServiceStatus;
use App\Chat\Consts\ChatStatus;
use App\Chat\Consts\ChatUserStatus;
use App\Chat\Properties\ChatEcsServiceProperties;
use App\Chat\Properties\ChatRegisteredTaskDefinitionProperties;
use App\Chat\Properties\ChatServerInfoProperties;
use App\Chat\Properties\ChatTenantProperties;
use App\Chat\Properties\ChatUserDataProperties;
use App\Chat\Properties\ChatUserProperties;
use App\Http\Controllers\API\Chat\ChatCompanyAPIController;
use App\Models\Company;
use Carbon\Carbon;
use DateTime;
use Exception;
use Facade\FlareClient\Http\Client;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;

class ChatServiceTest extends TestCase
{

    private $url = null;
    private $admin_name = null;
    private $admin_pass = null;
    private $auth_id = null;
    private $auth_token = null;


    // /**
    //  * @runInSeparateProcess
    //  * @preserveGlobalState disabled
    //  */
    protected function setUp(): void
    {
        parent::setUp();

        $this->url = env("CHAT_TEST_CHAT_SERVER_URL");
        $this->admin_name = env("CHAT_TEST_CHAT_ADMIN_NAME");
        $this->admin_pass = env("CHAT_TEST_CHAT_ADMIN_PASS");
        $this->auth_id = env("CHAT_TEST_CHAT_AUTH_ID");
        $this->auth_token = env("CHAT_TEST_CHAT_AUTH_TOKEN");

        // DB::beginTransaction();
        // DB::table("mst_chat")->delete();
        // DB::table("chat_server_onetime_token")->delete();
        // DB::table("chat_server_users")->delete();
        // DB::table("contracted_sites_callback_url")->delete();

        // DB::table("mst_company")->delete();
        // DB::table("mst_company")->insert(
        //     [
        //         "id"=>101,
        //         "company_name"=>"testcompany",
        //         "company_name_kana"=>"テストガイシャ",
        //         "create_user"=>"test",
        //         "department_stamp_flg"=>0,
        //         "domain"=>"testdomain",
        //         "dstamp_style"=>"test",
        //         "esigned_flg"=>0,
        //         "host_app_env"=>0,
        //         "host_company_name"=>"ホスト会社",
        //         "login_type"=>0,
        //         "long_term_storage_flg"=>0,
        //         "max_usable_capacity"=>0,
        //         "stamp_flg"=>0,
        //         "state"=>0,
        //         "use_api_flg"=>0,
        //         "upper_limit"=>0,
        //     ]
        // );
        // DB::commit();
    }

    public function test_createServer_mock_idserver() {

        $sid = date("mdHis");
        $tenantkey = "testtenant$sid";
        $servername = "test-$sid";

        $mock9 = Mockery::mock("overload:" . ChatIdServerClient::class);
        $mock9->shouldReceive("reserveServerName")
            ->withArgs(function ($a1, $a2, $a3, $a4) use($servername) {
                $ok = true;
                if ($ok) $ok = $a1 == $servername;
                if ($ok) $ok = $a2 == 101;
                if ($ok) $ok = $a3 == "testcompany";
                if ($ok) $ok = $a4 == 1;
                return $ok;
            })
            ->andReturnUsing(function($a1, $a2, $a3, $a4) use($tenantkey) {
                $ret = new ChatTenantProperties();
                $ret->cluster_arn(env("CHAT_TEST_ECS_CLUSTER_ARN"));
                $ret->image(env("CHAT_TEST_ECR_IMG_URI"));
                $ret->mongo_url(env("CHAT_TEST_MONGO_BASE"));
                $ret->tenant_key($tenantkey);
                $ret->server_url(str_replace("{servername}", $a1, env("CHAT_TEST_SERVER_URL")));
                $ret->company_id($a2);
                $ret->server_name($a1);
                return $ret;
            });
        $mock9->shouldReceive("updateServerInfo");

        $props = new ChatServerInfoProperties();
        $props->company_id(101);
        $props->server_name($servername);
        $props->trial_start_date(new DateTime("2022-01-01"));
        $props->trial_end_date(new DateTime("2022-01-31"));
        $props->contract_start_date(new DateTime("2022-02-01"));
        $props->contract_end_date(new DateTime("2023-02-01"));
        $props->user_max(100);
        $props->storage_max_mega(500);
        $props->is_trial(true);
        $props->is_contract(true);
        $props->plan(1);
        $props->callback_url("https://c.example.com/api/v1/a?id=2");

        // exec
        $target = new ChatService();
        $target->createServer($props);

        // assert
        $rec = DB::table("mst_chat")->where("domain", $servername)->first();
        $this->assertEquals(101, $rec->mst_company_id);
        $this->assertEquals(new DateTime("2022-01-01"), new DateTime($rec->trial_start_date));
        $this->assertEquals(new DateTime("2022-01-31"), new DateTime($rec->trial_end_date));
        $this->assertEquals(new DateTime("2022-02-01"), new DateTime($rec->contract_start_date));
        $this->assertEquals(new DateTime("2023-02-01"), new DateTime($rec->contract_end_date));
        $this->assertEquals(100, $rec->user_max_limit);
        $this->assertEquals(500, $rec->storage_max_limit);
        $this->assertEquals($servername, $rec->domain);
        $this->assertEquals(1, $rec->contract_type);
        $this->assertEquals($tenantkey, $rec->tenant_key);
        $this->assertEquals(str_replace("{servername}", $servername, env("CHAT_TEST_SERVER_URL")), $rec->url);
        $this->assertNull($rec->admin_id);
        $this->assertNull($rec->admin_token);
        $this->assertEquals(ChatStatus::INVALID, $rec->status);
        $this->assertNotEmpty($rec->create_user);
        $this->assertNotEmpty($rec->create_at);
        $this->assertNotEmpty($rec->update_user);
        $this->assertNotEmpty($rec->update_at);

        $this->assertEquals(ChatServiceStatus::WAITING_FOR_STARTUP, $rec->service_status);
        $this->assertNotNull($rec->service_status_at);
        $this->assertEquals(2, $rec->version);




    }

    public function test_createServer_mock_ChatEcsClient() {
        $mock = Mockery::mock("overload:" . ChatEcsClient::class);
        $mock->shouldReceive("registerTaskDefinition")->andReturnUsing(function($treg){
            $ret = new ChatRegisteredTaskDefinitionProperties();
            $ret->task_definition_arn("arn:testtaskdefinition");
            return $ret;
        });
        $mock->shouldReceive("craeteService")->andReturnUsing(function($treg){
            $ret = new ChatEcsServiceProperties();
            $ret->task_definition("arn:testtaskdefinition");
            $ret->service_arn("arn:testservice");
            $ret->service_name("testservice");
            return $ret;
        });

        $mock9 = Mockery::mock("overload:" . ChatIdServerClient::class);
        $mock9->shouldReceive("reserveServerName")
            ->withArgs(function ($a1, $a2, $a3, $a4) {
                $ok = true;
                if ($ok) $ok = $a1 == "testservername";
                if ($ok) $ok = $a2 == 101;
                if ($ok) $ok = $a3 == "testcompany";
                if ($ok) $ok = $a4 == 1;
                return $ok;
            })
            ->andReturnUsing(function(){
                $ret = new ChatTenantProperties();
                $ret->cluster_arn("arn:testcluster");
                $ret->image("arn:testecrimage");
                $ret->mongo_url("mongomongo://mongomogno");
                $ret->tenant_key("testtenantkey");
                $ret->server_url("https://testservername.example.com");
                $ret->company_id(101);
                $ret->server_name("testservername");
                return $ret;
            });
        $mock9->shouldReceive("updateServerInfo")
            ->withArgs(function ($t) {
                $ok = true;
                if ($ok) $ok = $t->company_id == 101;
                if ($ok) $ok = $t->server_name == "testservername";
                if ($ok) $ok = $t->service_arn == "arn:testservice";
                if ($ok) $ok = $t->task_definition == "arn:testtaskdefinition";
                if ($ok) $ok = $t->status == "created";
                return $ok;
            });

        $props = new ChatServerInfoProperties();
        $props->company_id(101);
        $props->server_name("testservername");
        $props->trial_start_date(new DateTime("2022-01-01"));
        $props->trial_end_date(new DateTime("2022-01-31"));
        $props->contract_start_date(new DateTime("2022-02-01"));
        $props->contract_end_date(new DateTime("2023-02-01"));
        $props->user_max(100);
        $props->storage_max_mega(500);
        $props->is_trial(true);
        $props->is_contract(true);
        $props->plan(1);
        $props->callback_url("https://c.example.com/api/v1/a?id=2");

        // exec
        $target = new ChatService();
        $target->createServer($props);

        // assert
        $rec = DB::table("mst_chat")->where("domain", "testservername")->first();
        $this->assertEquals(101, $rec->mst_company_id);
        $this->assertEquals(new DateTime("2022-01-01"), new DateTime($rec->trial_start_date));
        $this->assertEquals(new DateTime("2022-01-31"), new DateTime($rec->trial_end_date));
        $this->assertEquals(new DateTime("2022-02-01"), new DateTime($rec->contract_start_date));
        $this->assertEquals(new DateTime("2023-02-01"), new DateTime($rec->contract_end_date));
        $this->assertEquals(100, $rec->user_max_limit);
        $this->assertEquals(500, $rec->storage_max_limit);
        $this->assertEquals("testservername", $rec->domain);
        $this->assertEquals(1, $rec->contract_type);
        $this->assertEquals("testtenantkey", $rec->tenant_key);
        $this->assertEquals("https://testservername.example.com", $rec->url);
        $this->assertNull($rec->admin_id);
        $this->assertNull($rec->admin_token);
        $this->assertEquals(ChatStatus::INVALID, $rec->status);
        $this->assertNotEmpty($rec->create_user);
        $this->assertNotEmpty($rec->create_at);
        $this->assertNotEmpty($rec->update_user);
        $this->assertNotEmpty($rec->update_at);

        $this->assertEquals(ChatServiceStatus::WAITING_FOR_STARTUP, $rec->service_status);
        $this->assertNotNull($rec->service_status_at);
        $this->assertEquals(2, $rec->version);


        $rec2 = DB::table("contracted_sites_callback_url")->where("mst_chat_id", $rec->id)->first();
        $this->assertEquals($props->callback_url(), $rec2->call_back_url);
        $this->assertEquals(ChatCallbackStatusToContractSite::WAITING, $rec2->status);

    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_addUser()
    {
        DB::beginTransaction();
        $chatId = DB::table("mst_chat")->insertGetId([
            'mst_company_id' => 101,
            'trial_start_date' => "2022-01-01",
            'trial_end_date' => "2023-01-01",
            'contract_start_date' => "2024-01-01",
            'contract_end_date' => "2025-01-01",
            'user_max_limit' => 100,
            'storage_max_limit' => 500,
            'domain' => "testserver",
            'contract_type' => ChatPlan::BUSINESS,
            'status' => ChatStatus::VALID, // 0:無効
            'create_at' => Carbon::now(),
            'create_user' => "test",
            'service_status' => ChatServiceStatus::RUNNING,
            'url' => $this->url,
            'tenant_key' => 'tenant101',
            'admin_id' => $this->auth_id,
            'admin_token' => $this->auth_token
        ]);

        $suf = date("YmdHis");
        DB::table("chat_server_users")->insert([
            [
                'mst_company_id' => 101,
                'mst_user_id' => 2001,
                'mst_chat_id' => $chatId,
                'chat_personal_name' => "p01a".$suf,
                'chat_user_name' => "u01a".$suf,
                'chat_email' => "u01+a$suf@example.com",
                'chat_role_flg' => 1,
                'chat_user_id' => null,
                'system_remark' => null,
                'status' => ChatUserStatus::WAITING_TO_REGISTER,
                'create_user'=>"testcreateuser",
            ],
            [
                'mst_company_id' => 101,
                'mst_user_id' => 2002,
                'mst_chat_id' => $chatId,
                'chat_personal_name' => "p02a".$suf,
                'chat_user_name' => "u02a".$suf,
                'chat_email' => "u02$suf@example.com",
                'chat_role_flg' => 1,
                'chat_user_id' => null,
                'system_remark' => null,
                'status' => ChatUserStatus::INVALID,
                'create_user'=>"testcreateuser",
            ],
            [
                'mst_company_id' => 101,
                'mst_user_id' => 2003,
                'mst_chat_id' => $chatId,
                'chat_personal_name' => "p03a".$suf,
                'chat_user_name' => "u03a".$suf,
                'chat_email' => "u03a$suf@example.com",
                'chat_role_flg' => 0,
                'chat_user_id' => null,
                'system_remark' => null,
                'status' => ChatUserStatus::WAITING_TO_REGISTER,
                'create_user'=>"testcreateuser",
            ],
            [
                'mst_company_id' => 101,
                'mst_user_id' => 2004,
                'mst_chat_id' => $chatId,
                'chat_personal_name' => "p04a".$suf,
                'chat_user_name' => "u04a".$suf,
                'chat_email' => "u03a$suf@example.com",
                'chat_role_flg' => 1,
                'chat_user_id' => null,
                'system_remark' => null,
                'status' => ChatUserStatus::WAITING_TO_REGISTER,
                'create_user'=>"testcreateuser",
            ],
        ]);
        DB::commit();

        // target
        $target = new ChatService();
        $act = $target->addUser($chatId);

        // assert
        $this->assertEquals(3, $act["target"]);
        $this->assertEquals(2, $act["success"]);
        $this->assertEquals(1, $act["failure"]);

        $rec = DB::table("chat_server_users")->where("mst_chat_id", $chatId)->orderBy("mst_user_id")->get();
        $r1 = $rec[0];
        $this->assertIsString($r1->chat_user_id);
        $this->assertEquals(ChatUserStatus::VALID, $r1->status);
        $this->assertNull($r1->system_remark);
        $r2 = $rec[1];
        $this->assertNull($r2->chat_user_id);
        $this->assertEquals(ChatUserStatus::INVALID, $r2->status);
        $this->assertNull($r2->system_remark);
        $r3 = $rec[2];
        $this->assertIsString($r3->chat_user_id);
        $this->assertEquals(ChatUserStatus::VALID, $r3->status);
        $this->assertNull($r3->system_remark);
        $r4 = $rec[3];
        $this->assertNull($r4->chat_user_id);
        $this->assertEquals(ChatUserStatus::PROCESSED_REGISTER_FAIL, $r4->status);
        $this->assertIsString($r4->system_remark);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_initServer_ok() {

        // $admin_name = env("CHAT_TEST_CHAT_ADMIN_NAME");
        // $admin_pass = env("CHAT_TEST_CHAT_ADMIN_PASS");

        // $tenant = new ChatTenantClient($this->url);
        // $tenant->loginServer($admin_name, $admin_pass);
        // $key = "key" . date("YmdHis");
        // $act = $tenant->getPersonalAccessToken($key);

        $target = new ChatService();
        DB::beginTransaction();
        $chatId = DB::table("mst_chat")->insertGetId([
            'mst_company_id' => 101,
            'trial_start_date' => "2022-01-01",
            'trial_end_date' => "2023-01-01",
            'contract_start_date' => "2024-01-01",
            'contract_end_date' => "2025-01-01",
            'user_max_limit' => 100,
            'storage_max_limit' => 500,
            'domain' => "testserver",
            'contract_type' => ChatPlan::BUSINESS,
            'status' => ChatStatus::INVALID, // 0:無効
            'create_at' => Carbon::now(),
            'create_user' => "test",
            'service_status' => ChatServiceStatus::RUNNING,
            'url' => "http://host.docker.internal:3000",
            'tenant_key' => 'testtenant001'
        ]);

        DB::table("chat_server_onetime_token")->insert([
            'sub_domain_id' => $chatId,
            'onetime_token' => "testonetimetoken",
            'create_at' => Carbon::now(),
            'create_user' => "testuser",
        ]);

        $url = "https://www.matisse.co.jp/";
        DB::table("contracted_sites_callback_url")->insert([
            "mst_chat_id" => $chatId,
            "call_back_url" => $url,
            "status"=>ChatCallbackStatusToContractSite::WAITING,
            "create_at" => "2022-02-01 13:14:15",
            "create_user" => "testuser",
        ]);

        DB::commit();

        $mock9 = Mockery::mock("overload:" . ChatIdServerClient::class);
        $mock9->shouldReceive("updateServerInfo");

        // exec
        $target->initServer("testtenant001", "testonetimetoken");

        $rec2 = DB::table("contracted_sites_callback_url")->where("mst_chat_id", $chatId)->first();
        $this->assertEquals($url, $rec2->call_back_url);
        $this->assertEquals(ChatCallbackStatusToContractSite::CALLBACKED, $rec2->status);
        $this->assertTrue(true);


    }


        /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_initServer_ok_2() {

        $mock9 = Mockery::mock("overload:" . ChatIdServerClient::class);
        $mock9->shouldReceive("updateServerInfo");

        $target = new ChatService();
        // exec
        $target->initServer("testtenant0309141915", "OGVjYtesttenant0309141915Tk4ZDIzODJiZGZmOTYzNzcwMzE1YTFhNzcyZTIwNWFjNWJiNGIyY2U3ZGFkMjY1YzI0ZDExNDdkOWQ5Mg");

        // $rec2 = DB::table("contracted_sites_callback_url")->where("mst_chat_id", $chatId)->first();
        // $this->assertEquals($url, $rec2->call_back_url);
        // $this->assertEquals(ChatCallbackStatusToContractSite::CALLBACKED, $rec2->status);
        $this->assertTrue(true);


    }

        /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_initServer_failed_callback() {

        // $admin_name = env("CHAT_TEST_CHAT_ADMIN_NAME");
        // $admin_pass = env("CHAT_TEST_CHAT_ADMIN_PASS");

        // $tenant = new ChatTenantClient($this->url);
        // $tenant->loginServer($admin_name, $admin_pass);
        // $key = "key" . date("YmdHis");
        // $act = $tenant->getPersonalAccessToken($key);

        $target = new ChatService();
        DB::beginTransaction();
        $chatId = DB::table("mst_chat")->insertGetId([
            'mst_company_id' => 101,
            'trial_start_date' => "2022-01-01",
            'trial_end_date' => "2023-01-01",
            'contract_start_date' => "2024-01-01",
            'contract_end_date' => "2025-01-01",
            'user_max_limit' => 100,
            'storage_max_limit' => 500,
            'domain' => "testserver",
            'contract_type' => ChatPlan::BUSINESS,
            'status' => ChatStatus::INVALID, // 0:無効
            'create_at' => Carbon::now(),
            'create_user' => "test",
            'service_status' => ChatServiceStatus::RUNNING,
            'url' => "http://host.docker.internal:3000",
            'tenant_key' => 'testtenant001'
        ]);

        DB::table("chat_server_onetime_token")->insert([
            'sub_domain_id' => $chatId,
            'onetime_token' => "testonetimetoken",
            'create_at' => Carbon::now(),
            'create_user' => "testuser",
        ]);

        $url = "https://contracted.example.com/api/v1/callback?id=xxx1234";
        DB::table("contracted_sites_callback_url")->insert([
            "mst_chat_id" => $chatId,
            "call_back_url" => $url,
            "status"=>ChatCallbackStatusToContractSite::WAITING,
            "create_at" => "2022-02-01 13:14:15",
            "create_user" => "testuser",
        ]);

        DB::commit();

        $mock9 = Mockery::mock("overload:" . ChatIdServerClient::class);
        $mock9->shouldReceive("updateServerInfo");

        // exec
        $target->initServer("testtenant001", "testonetimetoken");

        $rec2 = DB::table("contracted_sites_callback_url")->where("mst_chat_id", $chatId)->first();
        $this->assertEquals($url, $rec2->call_back_url);
        $this->assertEquals(ChatCallbackStatusToContractSite::FAILED, $rec2->status);
        $this->assertTrue(true);


    }


    public function test_getPersonalAccessToken()
    {
        // target
        $target = new ChatTenantClient($this->url);
        $target->loginServer($this->admin_name, $this->admin_pass);
        $key = "key" . date("YmdHis");
        $act = $target->getPersonalAccessToken($key);

        // assert
        $this->assertIsString($act);
        $this->assertIsString($target->auth_token());

        $msg = "★pat = " . $target->auth_id() . " : $key : " . $act;
        echo $msg;
        Log::debug($msg);
    }

    public function test_createUser()
    {

        // target
        $target = new ChatTenantClient($this->url, $this->auth_id, $this->auth_token);

        $suf = date("YmdHis");
        $uname = "user" . $suf;
        $user = new ChatUserDataProperties();
        $user->name("test" . $suf);
        $user->username($uname);
        $user->email("user$suf@example.com");
        $user->password("test");
        $user->roles(["user"]);
        $user->requirePasswordChange(true);
        $user->sendWelcomeEmail(false);
        $user->setRandomPassword(true);
        $user->verified(false);

        try {
            $act = $target->createUser($user);
            $this->assertIsString($act->_id);

            $msg = "★cuser $uname = " . $act->_id;
            echo $msg;
            Log::debug($msg);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }


}
