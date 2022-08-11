<?php

namespace Tests\Unit\app\Chat;

use Tests\TestCase;

use App\Chat\ChatRepository;
use App\Chat\Consts\ChatCallbackStatusToContractSite;
use App\Chat\Consts\ChatPlan;
use App\Chat\Consts\ChatServiceStatus;
use App\Chat\Consts\ChatStatus;
use App\Chat\Consts\ChatUserStatus;
use App\Chat\Properties\ChatServerInfoProperties;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

class ChatRepositoryTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        DB::table("mst_chat")->delete();
        DB::table("chat_server_onetime_token")->delete();
        DB::table("chat_server_users")->delete();
        DB::table("contracted_sites_callback_url")->delete();
        DB::table("mst_company")->delete();
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_getAwsProperties()
    {
        $target = new TestChatRepository();

        $act = $target->getAwsProperties();
        $this->assertEquals(config("chat.aws_key"), $act->access_key_id());
        $this->assertEquals(config("chat.aws_secret"), $act->secret_access_key());
        $this->assertEquals(config("chat.aws_region"), $act->region());
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_getDefaultTaskEnvironmentValues()
    {

        $target = new TestChatRepository();

        $act = $target->getDefaultTaskEnvironmentValues();

        $this->assertEquals(env("CHAT_DEFAULT_ADMIN_EMAIL"), $act->admin_email());
        $this->assertEquals(env("CHAT_DEFAULT_ADMIN_PASSWORD"), $act->admin_password());
        $this->assertEquals(env("CHAT_DEFAULT_ADMIN_USERNAME"), $act->admin_username());
        $this->assertEquals("Asia/Tokyo", $act->timezone());
        $this->assertEquals("AmazonS3", $act->fileupload_storage_type());
        $this->assertEquals(env("CHAT_AWS_S3_ACCESS_KEY_ID"), $act->fileupload_s3_awsaccesskeyid());
        $this->assertEquals(env("CHAT_AWS_S3_SECRET_ACCESS_KEY"), $act->fileupload_s3_awssecretaccesskey());
        $this->assertEquals(env("CHAT_AWS_S3_REGION"), $act->fileupload_s3_region());
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_createServerInfo()
    {
        // args
        $arg1 = new ChatServerInfoProperties();
        $arg1->company_id(6);
        $arg1->server_name("test");
        //$arg1->tenant_key("testtenatkey");
        $arg1->operation_user("testuser");
        //$arg1->server_url("https://chat1.001.example.com");
        $arg1->trial_start_date(new DateTime("2021-01-01"));
        $arg1->trial_end_date(new DateTime("2021-01-31"));
        $arg1->contract_start_date(new DateTime("2021-02-01"));
        $arg1->contract_end_date(new DateTime("2099-02-01"));
        $arg1->user_max("123");
        $arg1->storage_max_mega("5");
        //$arg1->admin_id("no insert");
        //$arg1->admin_token("no insert");
        $arg1->plan("1");

        // mock
        // target
        $target = new TestChatRepository();
        // exec
        $pk = $target->createServerInfo($arg1);
        // assert
        $this->assertIsInt($pk);
        $rec = DB::table("mst_chat")->where("id", $pk)->first();
        $this->assertEquals($arg1->company_id(), $rec->mst_company_id);
        $this->assertEquals($arg1->trial_start_date(), new DateTime($rec->trial_start_date));
        $this->assertEquals($arg1->trial_end_date(), new DateTime($rec->trial_end_date));
        $this->assertEquals($arg1->contract_start_date(), new DateTime($rec->contract_start_date));
        $this->assertEquals($arg1->contract_end_date(), new DateTime($rec->contract_end_date));
        $this->assertEquals($arg1->user_max(), $rec->user_max_limit);
        $this->assertEquals($arg1->storage_max_mega(), $rec->storage_max_limit);
        $this->assertEquals($arg1->server_name(), $rec->domain);
        $this->assertEquals($arg1->plan(), $rec->contract_type);
        $this->assertNull($rec->tenant_key);
        $this->assertNull($rec->url);
        $this->assertNull($rec->admin_id);
        $this->assertNull($rec->admin_token);
        $this->assertNotEmpty($rec->create_at);
        $this->assertEquals(0, $rec->status);
        $this->assertEquals($arg1->operation_user(), $rec->create_user);

        $this->assertEquals(0, $rec->service_status);
        $this->assertNull($rec->service_status_at);
        $this->assertEquals(0, $rec->version);
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfoForPreCreate()
    {
        // args
        $arg1 = new ChatServerInfoProperties();
        $arg1->company_id(1001);
        $arg1->server_name("testservername");
        $arg1->tenant_key("testtenatkey");
        $arg1->operation_user("testuser");
        $arg1->server_url("https://chat1.001.example.com");
        $arg1->trial_start_date(new DateTime("2021-01-01"));
        $arg1->trial_end_date(new DateTime("2021-01-31"));
        $arg1->contract_start_date(new DateTime("2021-02-01"));
        $arg1->contract_end_date(null);
        $arg1->user_max(123);
        $arg1->storage_max_mega(500);
        $arg1->plan(2);
        $arg1->admin_id("no target");
        $arg1->admin_token("no target");

        // mock
        // target
        $target = new TestChatRepository();
        // exec
        $pk = $target->createServerInfo($arg1);
        $rec0 = DB::table("mst_chat")->where("id", $pk)->first();

        $ret = $target->updateServerInfoForPreCreate(
            $pk,
            "https://chat1001.001.example.com",
            "testtenatkey1001",
            "testmongo://mongotest",
            "testuser1001"
        );
        // assert
        $this->assertEquals(1, $ret);
        $rec = DB::table("mst_chat")->where("id", $pk)->first();
        $this->assertEquals($arg1->company_id(), $rec->mst_company_id);
        $this->assertEquals($arg1->trial_start_date(), new DateTime($rec->trial_start_date));
        $this->assertEquals($arg1->trial_end_date(), new DateTime($rec->trial_end_date));
        $this->assertEquals($arg1->contract_start_date(), new DateTime($rec->contract_start_date));
        $this->assertNull($rec->contract_end_date);
        $this->assertEquals($arg1->user_max(), $rec->user_max_limit);
        $this->assertEquals($arg1->storage_max_mega(), $rec->storage_max_limit);
        $this->assertEquals($arg1->server_name(), $rec->domain);
        $this->assertEquals($arg1->plan(), $rec->contract_type);
        $this->assertEquals("testtenatkey1001", $rec->tenant_key);
        $this->assertEquals("testmongo://mongotest", $rec->mongo_url);
        $this->assertEquals("https://chat1001.001.example.com", $rec->url);
        $this->assertNull($rec->admin_id);
        $this->assertNull($rec->admin_token);
        $this->assertEquals(0, $rec->status);
        $this->assertEquals($arg1->operation_user, $rec->create_user);
        $this->assertNotEmpty($rec->create_at);
        $this->assertEquals("testuser1001", $rec->update_user);
        $this->assertNotEmpty($rec->update_at);

        $this->assertEquals(0, $rec->service_status);
        $this->assertNull($rec->service_status_at);
        $this->assertEquals($rec0->version + 1, $rec->version);
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfoServerStatus()
    {
        // args
        $arg1 = new ChatServerInfoProperties();
        $arg1->company_id(1001);
        $arg1->server_name("testservername");
        $arg1->tenant_key("testtenatkey");
        $arg1->operation_user("testuser");
        $arg1->server_url("https://chat1.001.example.com");
        $arg1->trial_start_date(new DateTime("2021-01-01"));
        $arg1->trial_end_date(new DateTime("2021-01-31"));
        $arg1->contract_start_date(new DateTime("2021-02-01"));
        $arg1->contract_end_date(null);
        $arg1->user_max(123);
        $arg1->storage_max_mega(500);
        $arg1->plan(2);
        $arg1->admin_id("no target");
        $arg1->admin_token("no target");

        // mock
        // target
        $target = new TestChatRepository();
        // prepare
        $pk = $target->createServerInfo($arg1);
        $rec0 = DB::table("mst_chat")->where("id", $pk)->first();

        // exec
        $ret = $target->updateServerInfoServerStatus(
            $pk,
            1,
            "testuser1001"
        );
        // assert
        $this->assertEquals(1, $ret);
        $rec = DB::table("mst_chat")->where("id", $pk)->first();
        $this->assertEquals($arg1->company_id(), $rec->mst_company_id);
        $this->assertEquals($arg1->trial_start_date(), new DateTime($rec->trial_start_date));
        $this->assertEquals($arg1->trial_end_date(), new DateTime($rec->trial_end_date));
        $this->assertEquals($arg1->contract_start_date(), new DateTime($rec->contract_start_date));
        $this->assertNull($rec->contract_end_date);
        $this->assertEquals($arg1->user_max(), $rec->user_max_limit);
        $this->assertEquals($arg1->storage_max_mega(), $rec->storage_max_limit);
        $this->assertEquals($arg1->server_name(), $rec->domain);
        $this->assertEquals($arg1->plan(), $rec->contract_type);
        $this->assertNull($rec->tenant_key);
        $this->assertNull($rec->url);
        $this->assertNull($rec->admin_id);
        $this->assertNull($rec->admin_token);
        $this->assertEquals(0, $rec->status);
        $this->assertEquals($arg1->operation_user, $rec->create_user);
        $this->assertNotEmpty($rec->create_at);
        $this->assertEquals("testuser1001", $rec->update_user);
        $this->assertNotEmpty($rec->update_at);

        $this->assertEquals(1, $rec->service_status);
        $this->assertNotNull($rec->service_status_at);
        $this->assertEquals($rec0->version + 1, $rec->version);
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_registerServerOnetimeToken()
    {

        // args
        $arg1 = new ChatServerInfoProperties();
        $arg1->company_id(1001);
        $arg1->server_name("testservername");
        $arg1->tenant_key("testtenatkey");
        $arg1->operation_user("testuser");
        $arg1->onetime_key("asdfg1234qwer");
        // mock
        // target
        $target = new TestChatRepository();
        // exec
        $res = $target->registerServerOnetimeToken(101, "asdfg1234qwer", "testuser");

        // assert
        $this->assertTrue($res);
        $rec = DB::table("chat_server_onetime_token")->where("sub_domain_id", 101)->first();
        $this->assertEquals("asdfg1234qwer", $rec->onetime_token);
        $this->assertNotEmpty($rec->create_at);
        $this->assertEquals("testuser", $rec->create_user);
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_getServerIdFromOnetimeKey()
    {
        // args
        $pk = 101;
        $otoken = "asdfg1234qwer";
        // target
        $target = new TestChatRepository();
        // prepare
        $res = $target->registerServerOnetimeToken($pk, $otoken, "testuser");
        // exec
        $act = $target->getServerIdFromOnetimeKey($otoken);
        // assert
        $this->assertEquals($pk, $act);
    }


    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_deleteServerOnetimeToken()
    {
        // args
        $pk = 101;
        $otoken = "asdfg1234qwer";
        // target
        $target = new TestChatRepository();
        // prepare
        $target->registerServerOnetimeToken($pk, $otoken, "testuser");
        // exec
        $act = $target->deleteServerOnetimeToken($pk, $otoken);
        // assert
        $this->assertEquals(1, $act);
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_getServerInfoPreOpenByIdAndKey()
    {
        // args
        // args
        $arg1 = new ChatServerInfoProperties();
        $arg1->company_id(1001);
        $arg1->server_name("testservername");
        $arg1->tenant_key("testtenatkey");
        $arg1->operation_user("testuser");
        $arg1->server_url("https://chat1.001.example.com");
        $arg1->trial_start_date(new DateTime("2021-01-01"));
        $arg1->trial_end_date(new DateTime("2021-01-31"));
        $arg1->contract_start_date(new DateTime("2021-02-01"));
        $arg1->contract_end_date(null);
        $arg1->user_max(123);
        $arg1->storage_max_mega(500);
        $arg1->admin_id("no insert");
        $arg1->admin_token("no insert");
        $arg1->plan(2);

        $pk = DB::table("mst_chat")->insertGetId([
            'mst_company_id' => 1001,
            'trial_start_date' => "2021-01-01",
            'trial_end_date' => "2021-01-31",
            'contract_start_date' => "2021-02-01",
            'contract_end_date' => "2099-02-01",
            'user_max_limit' => 123,
            'storage_max_limit' => 500,
            'domain' => "testservername",
            'url' => "https://test.example.com",
            'tenant_key' => "testtenantkey",
            // 'admin_id' => "testadminid",
            // 'admin_token' => "testadmintoken",
            'contract_type' => 2,
            'status' => 0, // 1:有効
            'create_at' => "2022-01-20 12:30:11",
            'create_user' => "testcreateuser",
            'update_at' => "2022-01-20 12:33:14",
            'update_user' => "testupdateuser"
        ]);

        // target
        $target = new TestChatRepository();
        // exec
        $act = $target->getServerInfoPreOpenByIdAndKey($pk, "testtenantkey");
        // assert
        $this->assertEquals(1001, $act->company_id());
        $this->assertNull($act->trial_start_date());
        $this->assertNull($act->trial_end_date);
        $this->assertNull($act->contract_start_date);
        $this->assertNull($act->contract_end_date);
        $this->assertNull($act->user_max);
        $this->assertNull($act->storage_max_mega);
        $this->assertEquals("testservername", $act->server_name);
        $this->assertNull($act->contract_type);
        $this->assertEquals("testtenantkey", $act->tenant_key);
        $this->assertEquals("https://test.example.com", $act->server_url);
        $this->assertNull($act->admin_id);
        $this->assertNull($act->admin_token);
        $this->assertNull($act->status);
        $this->assertNull($act->create_at);
        $this->assertNull($act->create_user);
        $this->assertNull($act->update_at);
        $this->assertNull($act->update_user);

        $this->assertEquals(config("chat.default_admin_username"), $act->admin_username());
        $this->assertEquals(config("chat.default_admin_password"), $act->admin_password());
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_getServerInfoByCompanyId()
    {

        DB::table("mst_company")->insert(
            [
                "id" => 1001,
                "company_name" => "testcompany",
                "company_name_kana" => "テストガイシャ",
                "create_user" => "test",
                "department_stamp_flg" => 0,
                "domain" => "testdomain",
                "dstamp_style" => "test",
                "esigned_flg" => 0,
                "host_app_env" => 0,
                "host_company_name" => "ホスト会社",
                "login_type" => 0,
                "long_term_storage_flg" => 0,
                "max_usable_capacity" => 0,
                "stamp_flg" => 0,
                "state" => 0,
                "use_api_flg" => 0,
                "upper_limit" => 0,
                'chat_flg' => 1,
                'chat_trial_flg' => 1,
            ]
        );
        DB::table("mst_chat")->insertGetId([
            'mst_company_id' => 1001,
            'trial_start_date' => "2021-01-01",
            'trial_end_date' => "2021-01-31",
            'contract_start_date' => "2021-02-01",
            'contract_end_date' => "2099-02-01",
            'user_max_limit' => 123,
            'storage_max_limit' => 500,
            'domain' => "testservername",
            'url' => "https://test.example.com",
            'tenant_key' => "testtenantkey",
            'admin_id' => "testadminid",
            'admin_token' => "testadmintoken",
            'contract_type' => 2,
            'status' => 1, // 1:有効
            'create_at' => "2022-01-20 12:30:11",
            'create_user' => "testcreateuser",
            'update_at' => "2022-01-20 12:33:14",
            'update_user' => "testupdateuser"
        ]);

        // target
        $target = new TestChatRepository();
        // exec
        $act = $target->getServerInfoByCompanyId(1001, "testservername");
        // assert
        $this->assertEquals(1001, $act->company_id());
        $this->assertEquals(new DateTime("2021-01-01"), $act->trial_start_date());
        $this->assertEquals(new DateTime("2021-01-31"), $act->trial_end_date);
        $this->assertEquals(new DateTime("2021-02-01"), $act->contract_start_date);
        $this->assertEquals(new DateTime("2099-02-01"), $act->contract_end_date);
        $this->assertEquals(123, $act->user_max);
        $this->assertEquals(500, $act->storage_max_mega);
        $this->assertEquals("testservername", $act->server_name());
        $this->assertEquals(2, $act->plan());
        $this->assertEquals("testtenantkey", $act->tenant_key());
        $this->assertEquals("https://test.example.com", $act->server_url);
        $this->assertEquals("testadminid", $act->admin_id);
        $this->assertEquals("testadmintoken", $act->admin_token);
        $this->assertEquals(1, $act->status);
        $this->assertEquals(new DateTime("2022-01-20 12:30:11"), $act->create_at);
        $this->assertEquals("testcreateuser", $act->create_user);
        $this->assertEquals(new DateTime("2022-01-20 12:33:14"), $act->update_at);
        $this->assertEquals("testupdateuser", $act->update_user);
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_getServerInfoByCompanyId_2()
    {

        DB::table("mst_company")->insert(
            [
                "id" => 1001,
                "company_name" => "testcompany",
                "company_name_kana" => "テストガイシャ",
                "create_user" => "test",
                "department_stamp_flg" => 0,
                "domain" => "testdomain",
                "dstamp_style" => "test",
                "esigned_flg" => 0,
                "host_app_env" => 0,
                "host_company_name" => "ホスト会社",
                "login_type" => 0,
                "long_term_storage_flg" => 0,
                "max_usable_capacity" => 0,
                "stamp_flg" => 0,
                "state" => 0,
                "use_api_flg" => 0,
                "upper_limit" => 0,
                'chat_flg' => 1,
                'chat_trial_flg' => 1,
            ]
        );
        DB::table("mst_chat")->insertGetId([
            'mst_company_id' => 1001,
            'trial_start_date' => "2021-01-01",
            'trial_end_date' => "2021-01-31",
            'contract_start_date' => "2021-02-01",
            'contract_end_date' => "2099-02-01",
            'user_max_limit' => 123,
            'storage_max_limit' => 500,
            'domain' => "testservername",
            'url' => "https://test.example.com",
            'tenant_key' => "testtenantkey",
            'admin_id' => "testadminid",
            'admin_token' => "testadmintoken",
            'contract_type' => 2,
            'status' => 1, // 1:有効
            'create_at' => "2022-01-20 12:30:11",
            'create_user' => "testcreateuser",
            'update_at' => "2022-01-20 12:33:14",
            'update_user' => "testupdateuser"
        ]);

        // target
        $target = new TestChatRepository();
        // exec
        $act = $target->getServerInfoByCompanyId(1001);
        // assert
        $this->assertEquals(1001, $act->company_id());
        $this->assertEquals(new DateTime("2021-01-01"), $act->trial_start_date());
        $this->assertEquals(new DateTime("2021-01-31"), $act->trial_end_date);
        $this->assertEquals(new DateTime("2021-02-01"), $act->contract_start_date);
        $this->assertEquals(new DateTime("2099-02-01"), $act->contract_end_date);
        $this->assertEquals(123, $act->user_max);
        $this->assertEquals(500, $act->storage_max_mega);
        $this->assertEquals("testservername", $act->server_name());
        $this->assertEquals(2, $act->plan());
        $this->assertEquals("testtenantkey", $act->tenant_key());
        $this->assertEquals("https://test.example.com", $act->server_url);
        $this->assertEquals("testadminid", $act->admin_id);
        $this->assertEquals("testadmintoken", $act->admin_token);
        $this->assertEquals(1, $act->status);
        $this->assertEquals(new DateTime("2022-01-20 12:30:11"), $act->create_at);
        $this->assertEquals("testcreateuser", $act->create_user);
        $this->assertEquals(new DateTime("2022-01-20 12:33:14"), $act->update_at);
        $this->assertEquals("testupdateuser", $act->update_user);
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_getServerInfoByCompanyId_nochat()
    {

        DB::table("mst_company")->insert(
            [
                "id" => 1001,
                "company_name" => "testcompany",
                "company_name_kana" => "テストガイシャ",
                "create_user" => "test",
                "department_stamp_flg" => 0,
                "domain" => "testdomain",
                "dstamp_style" => "test",
                "esigned_flg" => 0,
                "host_app_env" => 0,
                "host_company_name" => "ホスト会社",
                "login_type" => 0,
                "long_term_storage_flg" => 0,
                "max_usable_capacity" => 0,
                "stamp_flg" => 0,
                "state" => 1,
                "use_api_flg" => 0,
                "upper_limit" => 0,
                'chat_flg' => 1,
                'chat_trial_flg' => 1,
            ]
        );
        DB::table("mst_chat")->insertGetId([
            'mst_company_id' => 1002,
            'trial_start_date' => "2021-01-01",
            'trial_end_date' => "2021-01-31",
            'contract_start_date' => "2021-02-01",
            'contract_end_date' => "2099-02-01",
            'user_max_limit' => 123,
            'storage_max_limit' => 500,
            'domain' => "testservername",
            'url' => "https://test.example.com",
            'tenant_key' => "testtenantkey",
            'admin_id' => "testadminid",
            'admin_token' => "testadmintoken",
            'contract_type' => 2,
            'status' => 1, // 1:有効
            'create_at' => "2022-01-20 12:30:11",
            'create_user' => "testcreateuser",
            'update_at' => "2022-01-20 12:33:14",
            'update_user' => "testupdateuser"
        ]);

        // target
        $target = new TestChatRepository();
        // exec
        $act = $target->getServerInfoByCompanyId(1001);
        // assert
        $this->assertEquals(1001, $act->company_id());
        $this->assertNull($act->trial_start_date());
        $this->assertNull($act->trial_end_date);
        $this->assertNull($act->contract_start_date);
        $this->assertNull($act->contract_end_date);
        $this->assertEquals(0, $act->user_max);
        $this->assertEquals(0, $act->storage_max_mega);
        $this->assertNull($act->server_name());
        $this->assertEquals(0, $act->plan());
        $this->assertNull($act->tenant_key());
        $this->assertNull($act->server_url);
        $this->assertNull($act->admin_id);
        $this->assertNull($act->admin_token);
        $this->assertEquals(0, $act->status);
        $this->assertNull($act->create_at);
        $this->assertNull($act->create_user);
        $this->assertNull($act->update_at);
        $this->assertNull($act->update_user);
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_getServerInfoById()
    {
        DB::table("mst_company")->insert(
            [
                "id" => 1001,
                "company_name" => "testcompany",
                "company_name_kana" => "テストガイシャ",
                "create_user" => "test",
                "department_stamp_flg" => 0,
                "domain" => "testdomain",
                "dstamp_style" => "test",
                "esigned_flg" => 0,
                "host_app_env" => 0,
                "host_company_name" => "ホスト会社",
                "login_type" => 0,
                "long_term_storage_flg" => 0,
                "max_usable_capacity" => 0,
                "stamp_flg" => 0,
                "state" => 0,
                "use_api_flg" => 0,
                "upper_limit" => 0,
                'chat_flg' => 1,
                'chat_trial_flg' => 1,
            ]
        );
        $pk = DB::table("mst_chat")->insertGetId([
            'mst_company_id' => 1001,
            'trial_start_date' => "2021-01-01",
            'trial_end_date' => "2021-01-31",
            'contract_start_date' => "2021-02-01",
            'contract_end_date' => "2099-02-01",
            'user_max_limit' => 123,
            'storage_max_limit' => 500,
            'domain' => "testservername",
            'url' => "https://test.example.com",
            'tenant_key' => "testtenantkey",
            'admin_id' => "testadminid",
            'admin_token' => "testadmintoken",
            'contract_type' => 2,
            'status' => 1, // 1:有効
            'create_at' => "2022-01-20 12:30:11",
            'create_user' => "testcreateuser",
            'update_at' => "2022-01-20 12:33:14",
            'update_user' => "testupdateuser"
        ]);

        // target
        $target = new TestChatRepository();
        // exec
        $act = $target->getServerInfoById($pk);
        // assert
        $this->assertEquals(1001, $act->company_id());
        $this->assertEquals(new DateTime("2021-01-01"), $act->trial_start_date());
        $this->assertEquals(new DateTime("2021-01-31"), $act->trial_end_date);
        $this->assertEquals(new DateTime("2021-02-01"), $act->contract_start_date);
        $this->assertEquals(new DateTime("2099-02-01"), $act->contract_end_date);
        $this->assertEquals(123, $act->user_max);
        $this->assertEquals(500, $act->storage_max_mega);
        $this->assertEquals("testservername", $act->server_name());
        $this->assertEquals(2, $act->plan());
        $this->assertEquals("testtenantkey", $act->tenant_key());
        $this->assertEquals("https://test.example.com", $act->server_url);
        $this->assertEquals("testadminid", $act->admin_id);
        $this->assertEquals("testadmintoken", $act->admin_token);
        $this->assertEquals(1, $act->status);
        $this->assertEquals(new DateTime("2022-01-20 12:30:11"), $act->create_at);
        $this->assertEquals("testcreateuser", $act->create_user);
        $this->assertEquals(new DateTime("2022-01-20 12:33:14"), $act->update_at);
        $this->assertEquals("testupdateuser", $act->update_user);
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerAdminToken()
    {

        $pk = DB::table("mst_chat")->insertGetId([
            'mst_company_id' => 1001,
            'trial_start_date' => "2021-01-01",
            'trial_end_date' => "2021-01-31",
            'contract_start_date' => "2021-02-01",
            'contract_end_date' => "2099-02-01",
            'user_max_limit' => 123,
            'storage_max_limit' => 500,
            'domain' => "testservername",
            'url' => "https://test.example.com",
            'tenant_key' => "testtenantkey",
            'admin_id' => null,
            'admin_token' => null,
            'contract_type' => 2,
            'status' => 0, // 0:無効
            'create_at' => "2022-01-20 12:30:11",
            'create_user' => "testcreateuser",
            'update_at' => null,
            'update_user' => null,
            'service_status' => 2,
            'service_status_at' => "2022-01-20 12:33:11",
            'version' => 2
        ]);

        // target
        $target = new TestChatRepository();
        // exec
        $act = $target->updateServerAdminToken($pk, "testadminid", "testadmintoken", "testuser");
        // assert
        $this->assertEquals(1, $act);
        $rec = DB::table("mst_chat")->where("id", $pk)->first();
        $this->assertEquals(1001, $rec->mst_company_id);
        $this->assertEquals("2021-01-01 00:00:00", $rec->trial_start_date);
        $this->assertEquals("2021-01-31 00:00:00", $rec->trial_end_date);
        $this->assertEquals("2021-02-01 00:00:00", $rec->contract_start_date);
        $this->assertEquals("2099-02-01 00:00:00", $rec->contract_end_date);
        $this->assertEquals(123, $rec->user_max_limit);
        $this->assertEquals(500, $rec->storage_max_limit);
        $this->assertEquals("testservername", $rec->domain);
        $this->assertEquals(2, $rec->contract_type);
        $this->assertEquals("testtenantkey", $rec->tenant_key);
        $this->assertEquals("https://test.example.com", $rec->url);
        $this->assertEquals("testadminid", $rec->admin_id);
        $this->assertEquals("testadmintoken", $rec->admin_token);
        $this->assertEquals(0, $rec->status);
        $this->assertEquals("testcreateuser", $rec->create_user);
        $this->assertNotEmpty($rec->create_at);
        $this->assertEquals("testuser", $rec->update_user);
        $this->assertNotEmpty($rec->update_at);
        $this->assertEquals(2, $rec->service_status);
        $this->assertEquals("2022-01-20 12:33:11", $rec->service_status_at);
        $this->assertEquals(3, $rec->version);
    }


    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_udpateServerOpen()
    {

        $pk = DB::table("mst_chat")->insertGetId([
            'mst_company_id' => 1001,
            'trial_start_date' => "2021-01-01",
            'trial_end_date' => "2021-01-31",
            'contract_start_date' => "2021-02-01",
            'contract_end_date' => "2099-02-01",
            'user_max_limit' => 123,
            'storage_max_limit' => 500,
            'domain' => "testservername",
            'url' => "https://test.example.com",
            'tenant_key' => "testtenantkey",
            'admin_id' => "testadminid",
            'admin_token' => "testadmintoken",
            'contract_type' => 2,
            'status' => 0, // 0:無効
            'create_at' => "2022-01-20 12:30:11",
            'create_user' => "testcreateuser",
            'update_at' => null,
            'update_user' => "aaa",
            'service_status' => 2,
            'service_status_at' => "2022-01-20 12:33:11",
            'version' => 2
        ]);

        // target
        $target = new TestChatRepository();
        // exec
        $act = $target->updateServerOpen($pk, "testuser");
        // assert
        $this->assertEquals(1, $act);
        $rec = DB::table("mst_chat")->where("id", $pk)->first();
        $this->assertEquals(1001, $rec->mst_company_id);
        $this->assertEquals("2021-01-01 00:00:00", $rec->trial_start_date);
        $this->assertEquals("2021-01-31 00:00:00", $rec->trial_end_date);
        $this->assertEquals("2021-02-01 00:00:00", $rec->contract_start_date);
        $this->assertEquals("2099-02-01 00:00:00", $rec->contract_end_date);
        $this->assertEquals(123, $rec->user_max_limit);
        $this->assertEquals(500, $rec->storage_max_limit);
        $this->assertEquals("testservername", $rec->domain);
        $this->assertEquals(2, $rec->contract_type);
        $this->assertEquals("testtenantkey", $rec->tenant_key);
        $this->assertEquals("https://test.example.com", $rec->url);
        $this->assertEquals("testadminid", $rec->admin_id);
        $this->assertEquals("testadmintoken", $rec->admin_token);
        $this->assertEquals(1, $rec->status);
        $this->assertEquals("testcreateuser", $rec->create_user);
        $this->assertNotEmpty($rec->create_at);
        $this->assertEquals("testuser", $rec->update_user);
        $this->assertNotEmpty($rec->update_at);
    }

    public function test_getLoginRetries()
    {
        $target = new TestChatRepository();
        $act = $target->getLoginRetries();
        $this->assertIsInt($act);
        $this->assertEquals(config("chat.login_retries"), $act);
    }

    public function test_getLoginSleepSeconds()
    {
        $target = new TestChatRepository();
        $act = $target->getLoginSleepSeconds();
        $this->assertIsInt($act);
        $this->assertEquals(config("chat.login_sleep_seconds"), $act);
    }


    public function test_getAwaitingRegistUsers()
    {
        $this->_makeChatUser();

        $target = new TestChatRepository();
        $act = $target->getAwaitingRegistUsers(51);

        $this->assertEquals(3, count($act));
    }

    public function test_updateRegistUsers()
    {
        $this->_makeChatUser();

        $target = new TestChatRepository();
        $users = $target->getAwaitingRegistUsers(51);
        $u1 = $users[0];
        $u1->chat_user_id = "testchatuserid-001";
        $u1->status = 1;
        $u1->system_remark = null;
        $u1->operation_user = "tester1";

        $u2 = $users[1];
        $u2->chat_user_id = null;
        $u2->status = 99;
        $u2->system_remark = "なぜか登録失敗しました";
        $u2->operation_user = "tester2";

        $u3 = $users[2];
        $u3->chat_user_id = "testchatuserid-003";
        $u3->status = 1;
        $u3->system_remark = null;
        $u3->operation_user = "tester3";

        $act = $target->updateRegistUsers($users);

        $this->assertEquals(3, $act);
        $rec1 = DB::table("chat_server_users")->where("id", $u1->id)->first();
        $this->assertEquals("testchatuserid-001", $rec1->chat_user_id);
        $this->assertEquals(1, $rec1->status);
        $this->assertNull($rec1->system_remark);
        $this->assertNotNull($rec1->update_at);
        $this->assertEquals("tester1", $rec1->update_user);

        $rec2 = DB::table("chat_server_users")->where("id", $u2->id)->first();
        $this->assertNull($rec2->chat_user_id);
        $this->assertEquals(99, $rec2->status);
        $this->assertEquals("なぜか登録失敗しました", $rec2->system_remark);
        $this->assertNotNull($rec2->update_at);
        $this->assertEquals("tester2", $rec2->update_user);

        $rec3 = DB::table("chat_server_users")->where("id", $u3->id)->first();
        $this->assertEquals("testchatuserid-003", $rec3->chat_user_id);
        $this->assertEquals(1, $rec3->status);
        $this->assertNull($rec3->system_remark);
        $this->assertNotNull($rec3->update_at);
        $this->assertEquals("tester3", $rec3->update_user);
    }



    public function test_getAwaitingDeleteUsers()
    {
        $this->_makeChatUser();

        $target = new TestChatRepository();
        $act = $target->getAwaitingDeleteUsers(51);

        $this->assertEquals(2, count($act));
    }

    public function test_updateDeleteUsers()
    {
        $this->_makeChatUser();

        $target = new TestChatRepository();
        $users = $target->getAwaitingDeleteUsers(51);
        $u1 = $users[0];
        $u1->status = 9;
        $u1->system_remark = null;
        $u1->operation_user = "tester1";

        $u2 = $users[1];
        $u2->status = 99;
        $u2->system_remark = "なぜか削除に失敗しました";
        $u2->operation_user = "tester2";

        $act = $target->updateDeleteUsers($users);

        $this->assertEquals(2, $act);
        $rec1 = DB::table("chat_server_users")->where("id", $u1->id)->first();
        $this->assertEquals("chatuser-11-01", $rec1->chat_user_id);
        $this->assertEquals(9, $rec1->status);
        $this->assertNull($rec1->system_remark);
        $this->assertNotNull($rec1->update_at);
        $this->assertEquals("tester1", $rec1->update_user);

        $rec2 = DB::table("chat_server_users")->where("id", $u2->id)->first();
        $this->assertEquals("chatuser-11-02", $rec2->chat_user_id);
        $this->assertEquals(99, $rec2->status);
        $this->assertEquals("なぜか削除に失敗しました", $rec2->system_remark);
        $this->assertNotNull($rec2->update_at);
        $this->assertEquals("tester2", $rec2->update_user);
    }

    public function test_updateUsers_deleted_ok()
    {
        $this->_makeChatUser();

        $target = new TestChatRepository();
        $users = $target->getAwaitingUsers(51, ChatUserStatus::WAITING_TO_DELETE);
        $u1 = $users[0];
        $u1->status = ChatUserStatus::DELETED;
        $u1->system_remark = null;
        $u1->operation_user = "tester1";

        $u2 = $users[1];
        $u2->status = 99;
        $u2->system_remark = "なぜか削除に失敗しました";
        $u2->operation_user = "tester2";

        $act = $target->updateUsers($users, ChatUserStatus::WAITING_TO_DELETE);

        $this->assertEquals(2, $act);
        $rec1 = DB::table("chat_server_users")->where("id", $u1->id)->first();
        $this->assertEquals("chatuser-11-01", $rec1->chat_user_id);
        $this->assertEquals(9, $rec1->status);
        $this->assertNull($rec1->system_remark);
        $this->assertNotNull($rec1->update_at);
        $this->assertEquals("tester1", $rec1->update_user);

        $rec2 = DB::table("chat_server_users")->where("id", $u2->id)->first();
        $this->assertEquals("chatuser-11-02", $rec2->chat_user_id);
        $this->assertEquals(99, $rec2->status);
        $this->assertEquals("なぜか削除に失敗しました", $rec2->system_remark);
        $this->assertNotNull($rec2->update_at);
        $this->assertEquals("tester2", $rec2->update_user);
    }



    private function _makeChatUser()
    {
        $keys = [
            "mst_company_id",
            "mst_user_id",
            "mst_chat_id",
            "chat_user_id",
            "chat_personal_name",
            "chat_user_name",
            "chat_email",
            "chat_role_flg", // 0:テナント管理者 1:user
            "status", // 0：無効, 1：有効, 2:停止, 9：削除, 10:登録待ち, 11:削除待ち, 99:処理失敗
            "system_remark", // 失敗時等のコメント
            "create_user",
            "create_at",
            "update_user",
            "update_at",
        ];
        $ds = [
            [1001, 20001, 51, null,             "今日之晩御飯 無効", "kyounobangohan0001", "kyo0001@example.com", 1, 0, null, "test", now(), null, null],
            [1001, 20002, 51, "chatuser-01-01", "今日之晩御飯 有効", "kyounobangohan0101", "kyo0101@example.com", 1, 1, null, "test", now(), null, null],
            [1001, 20003, 51, "chatuser-02-01", "今日之晩御飯 停止", "kyounobangohan00201", "kyo0201@example.com", 1, 2, null, "test", now(), null, null],
            [1001, 20004, 51, "chatuser-09-01", "今日之晩御飯 削除", "kyounobangohan0901", "kyo0901@example.com", 1, 9, null, "test", now(), null, null],
            [1001, 20005, 51, null,             "今日之晩御飯 登録待ち1", "kyounobangohan1001", "kyo1001@example.com", 1, 10, null, "test", now(), null, null],
            [1001, 20006, 51, "chatuser-11-01", "今日之晩御飯 削除待ち", "kyounobangohan1101", "kyo1101@example.com", 1, 11, null, "test", now(), null, null],
            [1001, 20007, 51, "chatuser-99-01", "今日之晩御飯 失敗", "kyounobangohan9901", "kyo9901@example.com",     1, 99, "寝落ち💦", "test", now(), null, null],
            [1001, 20008, 51, null,             "今日之晩御飯 登録待ち2", "kyounobangohan1002", "kyo1002@example.com", 1, 10, null, "test", now(), null, null],
            [1001, 20009, 51, null,             "今日之晩御飯 登録待ち3", "kyounobangohan1003", "kyo1003@example.com", 1, 10, null, "test", now(), null, null],
            [1001, 20010, 51, "chatuser-11-02", "今日之晩御飯 削除待ち2", "kyounobangohan1102", "kyo1102@example.com", 1, 11, null, "test", now(), null, null],
            [1201, 22001, 52, null,             "今日之晩御飯 無効", "kyounobangohan20001", "kyo20001@example.com", 1, 0, null, "test", now(), null, null],
            [1201, 22002, 52, "chatuser2-01-01", "今日之晩御飯 有効", "kyounobangohan20101", "kyo20101@example.com", 1, 1, null, "test", now(), null, null],
            [1201, 22003, 52, "chatuser2-02-01", "今日之晩御飯 停止", "kyounobangohan20201", "kyo20201@example.com", 1, 2, null, "test", now(), null, null],
            [1201, 22004, 52, null,              "今日之晩御飯 登録待ち2", "kyounobangohan21002", "kyo21002@example.com", 1, 10, null, "test", now(), null, null],
            [1201, 22005, 52, "chatuser2-11-02", "今日之晩御飯 削除待ち2", "kyounobangohan21102", "kyo21102@example.com", 1, 11, null, "test", now(), null, null],
            [1201, 22016, 52, "chatuser2-11-03", "今日之晩御飯 削除待ち3", "kyounobangohan21003", "kyo21103@example.com", 1, 11, null, "test", now(), null, null],
        ];
        $dlist = [];
        foreach ($ds as $d) {
            $dlist[] = array_combine($keys, $d);
        }
        DB::table("chat_server_users")->insert($dlist);
    }



    public function test_registerContractCallback()
    {
        $id = 1001;
        $url = "https://contracted.example.com/api/v1/callback?id=xxx1234";
        $ope = "testuser";

        $target = new TestChatRepository();
        $target->registerContractCallback($id, $url, $ope);

        $rec = DB::table("contracted_sites_callback_url")->where("mst_chat_id", $id)->first();
        $this->assertEquals($url, $rec->call_back_url);
        $this->assertEquals(ChatCallbackStatusToContractSite::WAITING, $rec->status);
        $this->assertEquals($ope, $rec->create_user);
        $this->assertNotNull($rec->create_at);
    }

    public function test_getContractCallbackUrl()
    {
        $id = 1001;
        $url = "https://contracted.example.com/api/v1/callback?id=xxx1234";
        $ope = "testuser";

        DB::table("contracted_sites_callback_url")->insert([
            "mst_chat_id" => $id,
            "call_back_url" => $url,
            "status" => ChatCallbackStatusToContractSite::WAITING,
            "create_at" => "2022-02-01 13:14:15",
            "create_user" => $ope,
        ]);

        $target = new TestChatRepository();
        $act = $target->getContractCallbackUrl($id);

        $this->assertEquals($url, $act);
    }

    public function test_getContractCallbackUrl_notfound()
    {
        $target = new TestChatRepository();
        $act = $target->getContractCallbackUrl(999);
        $this->assertNull($act);
    }

    public function test_updateContractCallbackUrlStatus()
    {
        $id = 1001;
        $url = "https://contracted.example.com/api/v1/callback?id=xxx1234";
        $cope = "testcreateuser";
        $uope = "testupdateuser";

        DB::table("contracted_sites_callback_url")->insert([
            "mst_chat_id" => $id,
            "call_back_url" => $url,
            "status" => ChatCallbackStatusToContractSite::WAITING,
            "create_at" => "2022-02-01 13:14:15",
            "create_user" => $cope,
        ]);

        $target = new TestChatRepository();
        $target->updateContractCallbackUrlStatus($id, ChatCallbackStatusToContractSite::CALLBACKED, $uope);

        $rec = DB::table("contracted_sites_callback_url")->where("mst_chat_id", $id)->first();
        $this->assertEquals($url, $rec->call_back_url);
        $this->assertEquals(ChatCallbackStatusToContractSite::CALLBACKED, $rec->status);
        $this->assertEquals($cope, $rec->create_user);
        $this->assertNotNull($rec->create_at);
        $this->assertEquals($uope, $rec->update_user);
        $this->assertNotNull($rec->update_at);
    }

    public function test_getCompany_ok()
    {
        DB::table("mst_company")->insert(
            [
                "id" => 1001,
                "company_name" => "testcompany",
                "company_name_kana" => "テストガイシャ",
                "create_user" => "test",
                "department_stamp_flg" => 0,
                "domain" => "testdomain",
                "dstamp_style" => "test",
                "esigned_flg" => 0,
                "host_app_env" => 0,
                "host_company_name" => "ホスト会社",
                "login_type" => 0,
                "long_term_storage_flg" => 0,
                "max_usable_capacity" => 0,
                "stamp_flg" => 0,
                "state" => 0,
                "use_api_flg" => 0,
                "upper_limit" => 0,
            ]
        );

        $target = new TestChatRepository();
        $act = $target->getCompany(1001);

        $this->assertEquals("testcompany", $act->company_name);
    }

    public function test_getCompany_null()
    {
        DB::table("mst_company")->insert(
            [
                "id" => 100111,
                "company_name" => "testcompany",
                "company_name_kana" => "テストガイシャ",
                "create_user" => "test",
                "department_stamp_flg" => 0,
                "domain" => "testdomain",
                "dstamp_style" => "test",
                "esigned_flg" => 0,
                "host_app_env" => 0,
                "host_company_name" => "ホスト会社",
                "login_type" => 0,
                "long_term_storage_flg" => 0,
                "max_usable_capacity" => 0,
                "stamp_flg" => 0,
                "state" => 0,
                "use_api_flg" => 0,
                "upper_limit" => 0,
            ]
        );

        $target = new TestChatRepository();
        $this->assertNull($target->getCompany(1001));
    }

    public function test_updateCompanyFlags_1()
    {
        DB::table("mst_company")->insert(
            [
                "id" => 1001,
                "company_name" => "testcompany",
                "company_name_kana" => "テストガイシャ",
                "create_user" => "test",
                "department_stamp_flg" => 0,
                "domain" => "testdomain",
                "dstamp_style" => "test",
                "esigned_flg" => 0,
                "host_app_env" => 0,
                "host_company_name" => "ホスト会社",
                "login_type" => 0,
                "long_term_storage_flg" => 0,
                "max_usable_capacity" => 0,
                "stamp_flg" => 0,
                "state" => 0,
                "use_api_flg" => 0,
                "upper_limit" => 0,
            ]
        );

        $target = new TestChatRepository();
        $act = $target->updateCompanyFlags("test", 1001, false);

        $this->assertEquals(1, $act);

        $rec = DB::table("mst_company")->where("id", 1001)->first();
        $this->assertEquals(1, $rec->chat_flg, true);
        $this->assertEquals(0, $rec->chat_trial_flg, true);
        $this->assertEquals("test", $rec->update_user);
        $this->assertNotNull($rec->update_at);
    }

    public function test_updateCompanyFlags_0()
    {
        DB::table("mst_company")->insert(
            [
                "id" => 1001,
                "company_name" => "testcompany",
                "company_name_kana" => "テストガイシャ",
                "create_user" => "test",
                "department_stamp_flg" => 0,
                "domain" => "testdomain",
                "dstamp_style" => "test",
                "esigned_flg" => 0,
                "host_app_env" => 0,
                "host_company_name" => "ホスト会社",
                "login_type" => 0,
                "long_term_storage_flg" => 0,
                "max_usable_capacity" => 0,
                "stamp_flg" => 0,
                "state" => 0,
                "use_api_flg" => 0,
                "upper_limit" => 0,
            ]
        );

        $target = new TestChatRepository();
        $act = $target->updateCompanyFlags("test", 1001, true, false);

        $this->assertEquals(1, $act);

        $rec = DB::table("mst_company")->where("id", 1001)->first();
        $this->assertEquals(0, $rec->chat_flg, true);
        $this->assertEquals(1, $rec->chat_trial_flg, true);
        $this->assertEquals("test", $rec->update_user);
        $this->assertNotNull($rec->update_at);
    }

    public function test_countChatUsers()
    {
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
            'url' => "https://www.example.com",
            'tenant_key' => 'tenant101',
            'admin_id' => null,
            'admin_token' => null
        ]);

        $suf = date("YmdHis");
        DB::table("chat_server_users")->insert([
            [
                'mst_company_id' => 101,
                'mst_user_id' => 2001,
                'mst_chat_id' => $chatId,
                'chat_personal_name' => "p01a" . $suf,
                'chat_user_name' => "u01a" . $suf,
                'chat_email' => "u01+a$suf@example.com",
                'chat_role_flg' => 1,
                'chat_user_id' => null,
                'system_remark' => null,
                'status' => ChatUserStatus::WAITING_TO_REGISTER,
                'create_user' => "testcreateuser",
            ],
            [
                'mst_company_id' => 101,
                'mst_user_id' => 2002,
                'mst_chat_id' => $chatId,
                'chat_personal_name' => "p02a" . $suf,
                'chat_user_name' => "u02a" . $suf,
                'chat_email' => "u02$suf@example.com",
                'chat_role_flg' => 1,
                'chat_user_id' => null,
                'system_remark' => null,
                'status' => ChatUserStatus::INVALID,
                'create_user' => "testcreateuser",
            ],
            [
                'mst_company_id' => 101,
                'mst_user_id' => 2003,
                'mst_chat_id' => $chatId,
                'chat_personal_name' => "p03a" . $suf,
                'chat_user_name' => "u03a" . $suf,
                'chat_email' => "u03a$suf@example.com",
                'chat_role_flg' => 0,
                'chat_user_id' => null,
                'system_remark' => null,
                'status' => ChatUserStatus::WAITING_TO_REGISTER,
                'create_user' => "testcreateuser",
            ],
            [
                'mst_company_id' => 101,
                'mst_user_id' => 2004,
                'mst_chat_id' => $chatId,
                'chat_personal_name' => "p04a" . $suf,
                'chat_user_name' => "u04a" . $suf,
                'chat_email' => "u04a$suf@example.com",
                'chat_role_flg' => 1,
                'chat_user_id' => null,
                'system_remark' => null,
                'status' => ChatUserStatus::WAITING_TO_REGISTER,
                'create_user' => "testcreateuser",
            ],
            [
                'mst_company_id' => 101,
                'mst_user_id' => 2005,
                'mst_chat_id' => $chatId,
                'chat_personal_name' => "p05a" . $suf,
                'chat_user_name' => "u05a" . $suf,
                'chat_email' => "u05a$suf@example.com",
                'chat_role_flg' => 1,
                'chat_user_id' => null,
                'system_remark' => null,
                'status' => ChatUserStatus::VALID,
                'create_user' => "testcreateuser",
            ],
            [
                'mst_company_id' => 101,
                'mst_user_id' => 2006,
                'mst_chat_id' => $chatId + 100,
                'chat_personal_name' => "p06a" . $suf,
                'chat_user_name' => "u06a" . $suf,
                'chat_email' => "u06a$suf@example.com",
                'chat_role_flg' => 1,
                'chat_user_id' => null,
                'system_remark' => null,
                'status' => ChatUserStatus::VALID,
                'create_user' => "testcreateuser",
            ],
            [
                'mst_company_id' => 101,
                'mst_user_id' => 2007,
                'mst_chat_id' => $chatId,
                'chat_personal_name' => "p07a" . $suf,
                'chat_user_name' => "u07a" . $suf,
                'chat_email' => "u07a$suf@example.com",
                'chat_role_flg' => 1,
                'chat_user_id' => null,
                'system_remark' => null,
                'status' => ChatUserStatus::VALID,
                'create_user' => "testcreateuser",
            ],
            [
                'mst_company_id' => 101,
                'mst_user_id' => 2008,
                'mst_chat_id' => $chatId,
                'chat_personal_name' => "p08a" . $suf,
                'chat_user_name' => "u08a" . $suf,
                'chat_email' => "u08a$suf@example.com",
                'chat_role_flg' => 1,
                'chat_user_id' => null,
                'system_remark' => null,
                'status' => ChatUserStatus::STOPPED,
                'create_user' => "testcreateuser",
            ],
            [
                'mst_company_id' => 101,
                'mst_user_id' => 2009,
                'mst_chat_id' => $chatId,
                'chat_personal_name' => "p09a" . $suf,
                'chat_user_name' => "u09a" . $suf,
                'chat_email' => "u09a$suf@example.com",
                'chat_role_flg' => 1,
                'chat_user_id' => null,
                'system_remark' => null,
                'status' => ChatUserStatus::DELETED,
                'create_user' => "testcreateuser",
            ],
        ]);


        $target = new TestChatRepository();
        $act = $target->countChatUsers($chatId);

        foreach ($act as $row) {
            switch ($row->status) {
                case ChatUserStatus::DELETED:
                    $this->assertEquals(1, $row->user_count);
                    break;
                case ChatUserStatus::INVALID:
                    $this->assertEquals(1, $row->user_count);
                    break;
                case ChatUserStatus::STOPPED:
                    $this->assertEquals(1, $row->user_count);
                    break;
                case ChatUserStatus::VALID:
                    $this->assertEquals(2, $row->user_count);
                    break;
                case ChatUserStatus::WAITING_TO_REGISTER:
                    $this->assertEquals(3, $row->user_count);
                    break;
                default:
                    $this->fail();
            }
        }
    }


    public function test_selectForUpdateByCompanyAndServerName()
    {
        $chatId1 = DB::table("mst_chat")->insertGetId([
            'mst_company_id' => 101,
            'trial_start_date' => "2022-01-01",
            'trial_end_date' => "2023-01-01",
            'contract_start_date' => "2024-01-01",
            'contract_end_date' => "2025-01-01",
            'user_max_limit' => 100,
            'storage_max_limit' => 500,
            'domain' => "testserver111",
            'contract_type' => ChatPlan::BUSINESS,
            'status' => ChatStatus::VALID, // 0:無効
            'create_at' => Carbon::now(),
            'create_user' => "test",
            'service_status' => ChatServiceStatus::RUNNING,
            'url' => "https://www.example.com",
            'tenant_key' => 'tenant101',
            'admin_id' => null,
            'admin_token' => null,
            'version' => 99
        ]);
        $chatId2 = DB::table("mst_chat")->insertGetId([
            'mst_company_id' => 101,
            'trial_start_date' => "2022-01-01",
            'trial_end_date' => "2023-01-01",
            'contract_start_date' => "2024-01-01",
            'contract_end_date' => "2025-01-01",
            'user_max_limit' => 100,
            'storage_max_limit' => 500,
            'domain' => "testserver222",
            'contract_type' => ChatPlan::BUSINESS,
            'status' => ChatStatus::VALID, // 0:無効
            'create_at' => Carbon::now(),
            'create_user' => "test",
            'service_status' => ChatServiceStatus::RUNNING,
            'url' => "https://www.example.com",
            'tenant_key' => 'tenant101',
            'admin_id' => null,
            'admin_token' => null,
            'version' => 99
        ]);


        $target = new TestChatRepository();
        $act = $target->selectForUpdateByCompanyAndServerName(101, "testserver222");
        DB::rollBack();

        $this->assertEquals($chatId2, $act->id);
        $this->assertEquals(99, $act->version);
    }

    public function test_selectForUpdateByCompanyAndServerName_noservername()
    {
        $chatId1 = DB::table("mst_chat")->insertGetId([
            'mst_company_id' => 101,
            'trial_start_date' => "2022-01-01",
            'trial_end_date' => "2023-01-01",
            'contract_start_date' => "2024-01-01",
            'contract_end_date' => "2025-01-01",
            'user_max_limit' => 100,
            'storage_max_limit' => 500,
            'domain' => "testserver111",
            'contract_type' => ChatPlan::BUSINESS,
            'status' => ChatStatus::VALID, // 0:無効
            'create_at' => Carbon::now(),
            'create_user' => "test",
            'service_status' => ChatServiceStatus::RUNNING,
            'url' => "https://www.example.com",
            'tenant_key' => 'tenant101',
            'admin_id' => null,
            'admin_token' => null,
            'version' => 99
        ]);
        $chatId2 = DB::table("mst_chat")->insertGetId([
            'mst_company_id' => 101,
            'trial_start_date' => "2022-01-01",
            'trial_end_date' => "2023-01-01",
            'contract_start_date' => "2024-01-01",
            'contract_end_date' => "2025-01-01",
            'user_max_limit' => 100,
            'storage_max_limit' => 500,
            'domain' => "testserver222",
            'contract_type' => ChatPlan::BUSINESS,
            'status' => ChatStatus::VALID, // 0:無効
            'create_at' => Carbon::now(),
            'create_user' => "test",
            'service_status' => ChatServiceStatus::RUNNING,
            'url' => "https://www.example.com",
            'tenant_key' => 'tenant101',
            'admin_id' => null,
            'admin_token' => null,
            'version' => 99
        ]);


        $target = new TestChatRepository();
        $act = $target->selectForUpdateByCompanyAndServerName(101);
        DB::rollBack();

        $this->assertEquals($chatId1, $act->id);
        $this->assertEquals(99, $act->version);
    }

    /**
     * 
     */
    public function test_private_toArrayExistsParams_01()
    {

        $props = new ChatServerInfoProperties();
        $props->trial_start_date(new Datetime("2022-01-01"));
        $props->trial_end_date(new Datetime("2023-01-01"));
        $props->contract_start_date(new Datetime("2024-01-01"));
        $props->contract_end_date(new Datetime("2025-01-01"));
        $props->user_max(123);
        $props->storage_max_mega(234);
        $props->plan(1);

        $names = [
            "trial_start_date"    =>   "a",
            "trial_end_date"      =>   "b",
            "contract_start_date" =>   "c",
            "contract_end_date"   =>   "d",
            "user_max"            =>   "e",
            "storage_max_mega"    =>   "f",
            "plan"                =>   "g",
        ];

        $target = new TestChatRepository();
        $act = $this->invokePrivateFunction($target, "toArrayExistsParams", [$props, $names]);

        $this->assertEquals(new Datetime("2022-01-01"), $act["a"]);
        $this->assertEquals(new Datetime("2023-01-01"), $act["b"]);
        $this->assertEquals(new Datetime("2024-01-01"), $act["c"]);
        $this->assertEquals(new Datetime("2025-01-01"), $act["d"]);
        $this->assertEquals(123, $act["e"]);
        $this->assertEquals(234, $act["f"]);
        $this->assertEquals(1, $act["g"]);
    }

    /**
     * 
     */
    public function test_private_toArrayExistsParams_02()
    {

        $props = new ChatServerInfoProperties();
        $props->trial_start_date(new Datetime("2022-01-01"));
        $props->trial_end_date(new Datetime("2023-01-01"));
        $props->contract_start_date(new Datetime("2024-01-01"));
        $props->contract_end_date(new Datetime("2025-01-01"));
        $props->user_max(123);
        $props->storage_max_mega(234);
        $props->plan(1);

        $names = [
            "trial_start_date"    =>   "a",
            "trial_end_date",
            "contract_start_date" =>   "c",
            "contract_end_date"   =>   "d",
            "user_max",
            "storage_max_mega"    =>   "f",
            "plan"                =>   "g",
        ];

        $target = new TestChatRepository();
        $act = $this->invokePrivateFunction($target, "toArrayExistsParams", [$props, $names]);

        $this->assertEquals(new Datetime("2022-01-01"), $act["a"]);
        $this->assertEquals(new Datetime("2023-01-01"), $act["trial_end_date"]);
        $this->assertEquals(new Datetime("2024-01-01"), $act["c"]);
        $this->assertEquals(new Datetime("2025-01-01"), $act["d"]);
        $this->assertEquals(123, $act["user_max"]);
        $this->assertEquals(234, $act["f"]);
        $this->assertEquals(1, $act["g"]);
    }

    /**
     * 
     */
    public function test_private_toArrayExistsParams_03()
    {

        $props = new ChatServerInfoProperties();
        $props->trial_start_date(new Datetime("2022-01-01"));
        $props->contract_end_date(new Datetime("2025-01-01"));
        $props->user_max(123);
        $props->plan(1);

        $names = [
            "trial_start_date",
            "trial_end_date",
            "contract_start_date",
            "contract_end_date",
            "user_max" => "user_max_limit",
            "storage_max_mega"    =>   "storage_max",
            "plan",
        ];

        $target = new TestChatRepository();
        $act = $this->invokePrivateFunction($target, "toArrayExistsParams", [$props, $names]);

        $this->assertEquals(new Datetime("2022-01-01"), $act["trial_start_date"]);
        $this->assertArrayNotHasKey("trial_end_date", $act);
        $this->assertArrayNotHasKey("contract_start_date", $act);
        $this->assertEquals(new Datetime("2025-01-01"), $act["contract_end_date"]);
        $this->assertEquals(123, $act["user_max_limit"]);
        $this->assertArrayNotHasKey("storage_max", $act);
        $this->assertEquals(1, $act["plan"]);
    }

    /**
     * 
     */
    public function test_updateServerInfo_01()
    {
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
            'url' => "https://www.example.com",
            'tenant_key' => 'tenant101',
            'admin_id' => null,
            'admin_token' => null,
            'version' => 99
        ]);

        $props = new ChatServerInfoProperties();
        $props->company_id(101);
        $props->server_name("testserver");
        $props->trial_start_date(new Datetime("2022-02-01"));
        $props->trial_end_date(new Datetime("2023-03-01"));
        $props->contract_start_date(new Datetime("2024-04-01"));
        $props->contract_end_date(new Datetime("2025-05-01"));
        $props->user_max(2123);
        $props->storage_max_mega(3234);
        $props->plan(2);
        $props->version(99);

        $target = new TestChatRepository();
        $act = $target->updateServerInfo($chatId, $props, "test");

        $this->assertEquals(1, $act);

        $rec = DB::table("mst_chat")->where("id", $chatId)->first();
        $this->assertEquals("2022-02-01 00:00:00", $rec->trial_start_date);
        $this->assertEquals("2023-03-01 00:00:00", $rec->trial_end_date);
        $this->assertEquals("2024-04-01 00:00:00", $rec->contract_start_date);
        $this->assertEquals("2025-05-01 00:00:00", $rec->contract_end_date);
        $this->assertEquals(2123, $rec->user_max_limit);
        $this->assertEquals(3234, $rec->storage_max_limit);
        $this->assertEquals(2, $rec->contract_type);
        $this->assertNotNull($rec->update_at);
        $this->assertEquals("test", $rec->update_user);
        $this->assertEquals(100, $rec->version);
    }

    /**
     * 
     */
    public function test_updateServerInfo_02()
    {
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
            'url' => "https://www.example.com",
            'tenant_key' => 'tenant101',
            'admin_id' => null,
            'admin_token' => null,
            'version' => 99
        ]);

        $props = new ChatServerInfoProperties();
        $props->company_id(101);
        $props->server_name("testserver");
        $props->trial_start_date(new Datetime("2022-02-01"));
        $props->contract_end_date(new Datetime("2025-05-01"));
        $props->user_max(2123);
        $props->plan(2);
        $props->version(99);

        $target = new TestChatRepository();
        $act = $target->updateServerInfo($chatId, $props, "test");

        $this->assertEquals(1, $act);

        $rec = DB::table("mst_chat")->where("id", $chatId)->first();
        $this->assertEquals("2022-02-01 00:00:00", $rec->trial_start_date);
        $this->assertEquals("2023-01-01 00:00:00", $rec->trial_end_date);
        $this->assertEquals("2024-01-01 00:00:00", $rec->contract_start_date);
        $this->assertEquals("2025-05-01 00:00:00", $rec->contract_end_date);
        $this->assertEquals(2123, $rec->user_max_limit);
        $this->assertEquals(500, $rec->storage_max_limit);
        $this->assertEquals(2, $rec->contract_type);
        $this->assertNotNull($rec->update_at);
        $this->assertEquals("test", $rec->update_user);
        $this->assertEquals(100, $rec->version);
    }

    /**
     * 
     */
    public function test_updateServerInfo_nocolumns()
    {
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
            'url' => "https://www.example.com",
            'tenant_key' => 'tenant101',
            'admin_id' => null,
            'admin_token' => null,
            'version' => 99
        ]);

        $props = new ChatServerInfoProperties();
        $props->company_id(101);
        $props->server_name("testserver");
        $props->is_trial(true);
        $props->is_contract(true);
        $props->version(99);

        $target = new TestChatRepository();
        $act = $target->updateServerInfo($chatId, $props, "test");

        $this->assertFalse($act);
    }

        /**
     * 
     */
    public function test_updateServerInfo_notarget()
    {
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
            'url' => "https://www.example.com",
            'tenant_key' => 'tenant101',
            'admin_id' => null,
            'admin_token' => null,
            'version' => 99
        ]);

        $props = new ChatServerInfoProperties();
        $props->company_id(101);
        $props->server_name("testserver");
        $props->trial_start_date(new Datetime("2022-02-01"));
        $props->contract_end_date(new Datetime("2025-05-01"));
        $props->user_max(2123);
        $props->plan(2);
        $props->version(90);

        $target = new TestChatRepository();
        $act = $target->updateServerInfo($chatId, $props, "test");

        $this->assertEquals(0, $act);
    }
}


class TestChatRepository extends ChatRepository
{
}
