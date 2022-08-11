<?php

namespace Tests\Feature\app\Http\Controllers\API\Chat;

use App\Chat\ChatEcsClient;
use Tests\TestCase;

use App\Chat\ChatRepository;
use App\Chat\ChatService;
use App\Chat\Properties\ChatServerInfoProperties;
use App\Http\Controllers\API\Chat\ChatCompanyAPIController;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;

class ChatCompanyAPIControllerTest extends TestCase
{


    // /**
    //  * @runInSeparateProcess
    //  * @preserveGlobalState disabled
    //  */
    protected function setUp(): void
    {
        parent::setUp();
        DB::table("api_authentication")->delete();
        DB::table("api_authentication")->insert([
            "api_name" => "HomePage",
            "access_id" => "testaccessid",
            "access_code" => "testaccesscode"
        ]);
        DB::table("mst_chat")->delete();
        DB::table("mst_company")->delete();
        DB::table("mst_company")->insert(
            [
                "id"=>1001,
                "company_name"=>"testcompany",
                "company_name_kana"=>"テストガイシャ",
                "create_user"=>"test",
                "department_stamp_flg"=>0,
                "domain"=>"testdomain",
                "dstamp_style"=>"test",
                "esigned_flg"=>0,
                "host_app_env"=>0,
                "host_company_name"=>"ホスト会社",
                "login_type"=>0,
                "long_term_storage_flg"=>0,
                "max_usable_capacity"=>0,
                "stamp_flg"=>0,
                "state"=>0,
                "use_api_flg"=>0,
                "upper_limit"=>0,
                'chat_flg' => 1,
                'chat_trial_flg' => 1,
            ]
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_surface_insertChatInfo()
    {

        $mock = Mockery::mock("overload:" . ChatService::class);
        $mock->shouldReceive("createServer");

        $response = $this->postJson("/api/chat/insertChatInfo",
        [
            "accessId" => "testaccessid",
            "accessCode" => "testaccesscode",
            "domainid" => 1001,
            "chat_flg" => 1,
            "chat_trial_flg" => 0,
            "trial_start_date" => "20220101",
            "trial_end_date" => "20220131",
            "contract_start_date" => "20220201",
            "contract_end_date" => "20991231",
            "user_max_limit" => 123,
            "domain" => "kibousurudomain",
            "storage_max_limit" => 500,
            "contract_type" => 2,
            "calllback_url" => "https://keiyaku.example.com",
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            "result_code" => 1,
            "result_message" => "",
            "result_data" => []
        ]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_it_insertChatInfo()
    {

        $response = $this->postJson("/api/chat/insertChatInfo",
        [
            "accessId" => "testaccessid",
            "accessCode" => "testaccesscode",
            "domainid" => 1001,
            "chat_flg" => 1,
            "chat_trial_flg" => 0,
            "trial_start_date" => "20220101",
            "trial_end_date" => "20220131",
            "contract_start_date" => "20220201",
            "contract_end_date" => "20991231",
            "user_max_limit" => 123,
            "domain" => "kibousurudomain",
            "storage_max_limit" => 500,
            "contract_type" => 0,
            "calllback_url" => "https://keiyaku.example.com",
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            "result_code" => 1,
            "result_message" => "",
            "result_data" => []
        ]);
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_it_getChatInfo()
    {
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
            'status' => 1, // 0:無効
            'create_at' => "2022-01-20 12:30:11",
            'create_user' => "testcreateuser",
            'update_at' => null,
            'update_user' => "aaa",
            'service_status' => 2,
            'service_status_at' => "2022-01-20 12:33:11",
            'version' => 2
        ]);

        $response = $this->postJson("/api/chat/getChatInfo",
        [
            "accessId" => "testaccessid",
            "accessCode" => "testaccesscode",
            "domainid" => 1001,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            "result_code" => 1,
            "result_message" => "",
            "result_data" => [
                "chat_flg" => 1,
                "chat_trial_flg" => 1,
                "trial_start_date" => "20210101",
                "trial_end_date" => "20210131",
                "contract_start_date" => "20210201",
                "contract_end_date" => "20990201",
                "user_max_limit" => 123,
                "domain" => "testservername",
                "url" => "https://test.example.com",
                "storage_max_limit" => 500,
                "contract_type" => 2
            ]
        ]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_it_getChatInfo_nochat()
    {
        DB::table("mst_chat")->delete();
        $response = $this->postJson("/api/chat/getChatInfo",
        [
            "accessId" => "testaccessid",
            "accessCode" => "testaccesscode",
            "domainid" => 1001,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            "result_code" => 1,
            "result_message" => "",
            "result_data" => [
                "chat_flg" => 1,
                "chat_trial_flg" => 1,
                "trial_start_date" => "",
                "trial_end_date" => "",
                "contract_start_date" => null,
                "contract_end_date" => null,
                "user_max_limit" => null,
                "domain" => null,
                "url" => null,
                "storage_max_limit" => null,
                "contract_type" => null
            ]
        ]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_idsvr_insertChatInfo()
    {



        $response = $this->postJson("/api/chat/insertChatInfo",
        [
            "accessId" => "testaccessid",
            "accessCode" => "testaccesscode",
            "domainid" => 1001,
            "chat_flg" => 1,
            "chat_trial_flg" => 0,
            "trial_start_date" => "20220101",
            "trial_end_date" => "20220131",
            "contract_start_date" => "20220201",
            "contract_end_date" => "20991231",
            "user_max_limit" => 123,
            "domain" => "kibousurudomain",
            "storage_max_limit" => 500,
            "contract_type" => 2,
            "calllback_url" => "https://keiyaku.example.com",
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            "result_code" => 1,
            "result_message" => "",
            "result_data" => []
        ]);
    }

        /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_surface_updateChatInfo()
    {
        $mock = Mockery::mock("overload:" . ChatService::class);
        $mock->shouldReceive("updateServerInfo");

        $response = $this->postJson("/api/chat/updateChatInfo",
        [
            "accessId" => "testaccessid",
            "accessCode" => "testaccesscode",
            "domainid" => 1001,
            "chat_flg" => 1,
            "chat_trial_flg" => 0,
            "trial_start_date" => "20220101",
            "trial_end_date" => "20220131",
            "contract_start_date" => "20220201",
            "contract_end_date" => "20991231",
            "user_max_limit" => 123,
            "storage_max_limit" => 500,
            "contract_type" => 2,
            "version"=>1
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            "result_code" => 1,
            "result_message" => "",
            "result_data" => []
        ]);
    }

    private function _insert_mstchat_for_update() {
        $id = DB::table("mst_chat")->insertGetId([
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
            'status' => 1, // 0:無効
            'create_at' => "2022-01-20 12:30:11",
            'create_user' => "testcreateuser",
            'update_at' => null,
            'update_user' => "aaa",
            'service_status' => 2,
            'service_status_at' => "2022-01-20 12:33:11",
            'version' => 2
        ]);
        DB::commit();
        return $id;
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateChatInfo_full_param()
    {
        $this->_insert_mstchat_for_update();

        $response = $this->postJson("/api/chat/updateChatInfo",
        [
            "accessId" => "testaccessid",
            "accessCode" => "testaccesscode",
            "domainid" => 1001,
            "chat_flg" => 1,
            "chat_trial_flg" => 0,
            "trial_start_date" => "20220101",
            "trial_end_date" => "20220131",
            "contract_start_date" => "20220201",
            "contract_end_date" => "20991231",
            "user_max_limit" => 123,
            "storage_max_limit" => 500,
            "contract_type" => 2,
            "version" => 2
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            "result_code" => 1,
            "result_message" => "",
            "result_data" => []
        ]);
    }

        /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateChatInfo_only_required()
    {
        $this->_insert_mstchat_for_update();

        $response = $this->postJson("/api/chat/updateChatInfo",
        [
            "accessId" => "testaccessid",
            "accessCode" => "testaccesscode",
            "domainid" => 1001,
            "version"=>2
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            "result_code" => 1,
            "result_message" => "",
            "result_data" => []
        ]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_surface_getChatInfo()
    {
        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getCompany")->andReturn(new Company());

        $mock = Mockery::mock("overload:" . ChatService::class);
        $mock->shouldReceive("getServerInfo")->andReturnUsing(function(){
            $ret = new ChatServerInfoProperties();
            $ret->server_name("shiyouchuunodomain");
            return $ret;
        });

        $response = $this->postJson("/api/chat/getChatInfo",
        [
            "accessId" => "testaccessid",
            "accessCode" => "testaccesscode",
            "domainid" => 1001,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            "result_code" => 1,
            "result_message" => "",
            "result_data" => ["domain" => "shiyouchuunodomain"],
        ], true);

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_surface_getChatSubDomain()
    {
        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getCompany")->andReturn(new Company());

        $mock = Mockery::mock("overload:" . ChatService::class);
        $mock->shouldReceive("checkAvailableServerName")->andReturn(true);

        $response = $this->postJson("/api/chat/getChatSubDomain",
        [
            "accessId" => "testaccessid",
            "accessCode" => "testaccesscode",
            "domainName" => "kibousurudomain",
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            "result_code" => 1,
            "result_message" => "",
            "result_data" => ["subdomain_flg" => 1],
        ], true);

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_it_getChatSubDomain_ok()
    {

        $response = $this->postJson("/api/chat/getChatSubDomain",
        [
            "accessId" => "testaccessid",
            "accessCode" => "testaccesscode",
            "domainName" => "kibousurudomain",
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            "result_code" => 1,
            "result_message" => "",
            "result_data" => ["subdomain_flg" => 1],
        ], true);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_it_getChatSubDomain_ng_format()
    {

        $response = $this->postJson("/api/chat/getChatSubDomain",
        [
            "accessId" => "testaccessid",
            "accessCode" => "testaccesscode",
            "domainName" => "kibousuru--domain",
        ]);

        $response->assertStatus(200);

        $json = $response->getContent();
        $content = json_decode($json);
        $this->assertEquals(0, $content->result_code);
        $this->assertNotEmpty($content->result_message);
        $this->assertNotEquals("システムエラー", substr($content->result_message,0,7));

        $data = $content->result_data;
        $this->assertEquals(0, $data->subdomain_flg);
    }

    /**
     *
     * ※ 事前に 統合IDのDBにデータの登録が必要
     * INSERT INTO `pac_id_management`.`mst_chat_domain` (`company_id`, `company_name`, `contract_app`, `contract_server`, `app_env`, `domain_name`, `group_domain_id`, `tenant_key`, `ecr_img_uri_id`, `status`, `create_at`, `create_user`, `chat_mongo_db_id`)
     * VALUES ('50001', 'テスト用会社', '1', '5', '1', 'testsubdomain', '1', 'testtenantkey', '1', '9', '2022-01-01', 'testuser', '1');
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_it_getChatSubDomain_ng_exists()
    {

        $response = $this->postJson("/api/chat/getChatSubDomain",
        [
            "accessId" => "testaccessid",
            "accessCode" => "testaccesscode",
            "domainName" => "testsubdomain",
        ]);

        $response->assertStatus(200);

        $json = $response->getContent();
        $content = json_decode($json);
        $this->assertEquals(0, $content->result_code);
        $this->assertNotEmpty($content->result_message);
        $this->assertNotEquals("システムエラー", substr($content->result_message,0,7));

        $data = $content->result_data;
        $this->assertEquals(0, $data->subdomain_flg);
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_surface_initServer(){
        $mock = Mockery::mock("overload:" . ChatService::class);
        $mock->shouldReceive("initServer");

        $response = $this->get("/api/chat/initServer?a=testonetimetoken&b=testtenantkey");

        $response->assertStatus(200);
    }

    // /**
    //  * @runInSeparateProcess
    //  * @preserveGlobalState disabled
    //  */
    // public function test_temp_initServer(){

    //     $response = $this->get("/api/chat/initServer?a=OWE0Ntesttenant0131192022DU3MDk4NThhNGQ5NmIwYjZjNzlhZWNlMzZjY2FmYzEwMDA3ZjUyZGQxY2Y3YzM4Mjc1NGE3YzE5OGRlMg&b=testtenant0131192022");

    //     $response->assertStatus(200);
    // }
}

class TestChatCompanyAPIController extends ChatCompanyAPIController
{
}
