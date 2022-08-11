<?php

namespace Tests\Unit\app\Http\Controllers\API\Chat;

use Tests\TestCase;

use App\Chat\ChatTenantClient;
use App\Chat\ChatIdServerClient;
use App\Chat\ChatRepository;
use App\Chat\ChatServerCreator;
use App\Chat\ChatService;
use App\Chat\Consts\ChatPlan;
use App\Chat\Consts\ChatServiceStatus;
use App\Chat\Consts\ChatStatus;
use App\Chat\Properties\ChatServerInfoProperties;
use App\Chat\Properties\ChatUserDataProperties;
use App\Http\Controllers\API\AuthDao;
use App\Http\Controllers\API\Chat\ChatCompanyAPIController;
use App\Models\Authority;
use App\Models\Company;
use App\Models\Constraint;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $mock1 = Mockery::mock("overload:" . AuthDao::class);

        $ret1 = (object)[
            "access_id" => "testaccessid",
            "access_code" => "testaccesscode",
        ];
        $mock1->shouldReceive("getAuth")->andReturn($ret1);
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_insertChatInfo()
    {
        // $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        // $mock1->shouldReceive("getCompany")->andReturn(new Company());

        $mock = Mockery::mock("overload:" . ChatService::class);
        $mock->shouldReceive("createServer");

        // args
        $arg1 = new Constraint();
        $arg2 = new Authority();
        // target
        $target = new TestChatCompanyAPIController($arg1, $arg2);
        $props = new ChatServerInfoProperties();

        // request
        $req = new Request([
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
        ]);

        // exec
        $act = $target->insertChatInfo($req);
        $this->assertEquals(200, $act->getStatusCode());
        $json = $act->getContent();
        $content = json_decode($json);
        $this->assertEquals(1, $content->result_code);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateChatInfo()
    {

        $mock = Mockery::mock("overload:" . ChatService::class);
        $mock->shouldReceive("updateServerInfo");

        // args
        $arg1 = new Constraint();
        $arg2 = new Authority();
        // target
        $target = new TestChatCompanyAPIController($arg1, $arg2);

        // request
        $req = new Request([
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
                "version" => 1
            ]);

        // exec
        $act = $target->updateChatInfo($req);
        $this->assertEquals(200, $act->getStatusCode());
        $json = $act->getContent();
        $content = json_decode($json);
        $this->assertEquals(1, $content->result_code);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_getChatInfo()
    {
        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getCompany")->andReturn(new Company());

        $mock = Mockery::mock("overload:" . ChatService::class);
        $mock->shouldReceive("getServerInfo")->andReturnUsing(function () {
            $props = new ChatServerInfoProperties();
            $props->company_id(1001);
            $props->is_contract(true);
            $props->is_trial(false);
            $props->trial_start_date(new DateTime("2021-01-01"));
            $props->trial_end_date(new DateTime("2021-01-31"));
            $props->contract_start_date(new DateTime("2021-02-01"));
            $props->contract_end_date(new DateTime("2099-02-01"));
            $props->user_max(123);
            $props->storage_max_mega(12);
            $props->server_name("subdomain-name");
            $props->plan(ChatPlan::PRO);
            $props->status(ChatStatus::VALID);
            $props->server_url("https://www.example.com");
            $props->tenant_key("testtenantkey");
            $props->admin_id("testadminid");
            $props->admin_token("testadmintoken");
            $props->service_status(ChatServiceStatus::INITIALIZING);
            $props->version(321);
            return $props;
        });

        // args
        $arg1 = new Constraint();
        $arg2 = new Authority();
        // target
        $target = new TestChatCompanyAPIController($arg1, $arg2);
        $props = new ChatServerInfoProperties();

        // request
        $req = new Request([
                "accessId" => "testaccessid",
                "accessCode" => "testaccesscode",
                "domainid" => 1001,
            ]
        );

        // exec
        $act = $target->getChatInfo($req);
        $this->assertEquals(200, $act->getStatusCode());
        $json = $act->getContent();
        $content = json_decode($json);
        $this->assertEquals(1, $content->result_code);
        $data = $content->result_data;
        $this->assertEquals(1, $data->chat_flg, true);
        $this->assertEquals(0, $data->chat_trial_flg, true);
        $this->assertEquals("20210101", $data->trial_start_date);
        $this->assertEquals("20210131", $data->trial_end_date);
        $this->assertEquals("20210201", $data->contract_start_date);
        $this->assertEquals("20990201", $data->contract_end_date);
        $this->assertEquals(123, $data->user_max_limit);
        $this->assertEquals("subdomain-name", $data->domain);
        $this->assertEquals("https://www.example.com", $data->url);
        $this->assertEquals(12, $data->storage_max_limit);
        $this->assertEquals(2, $data->contract_type);
        $this->assertEquals(321, $data->version);

        
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_getChatSubDomain()
    {
        $mock = Mockery::mock("overload:" . ChatService::class);
        $mock->shouldReceive("checkAvailableServerName")->andReturn(true);

        // args
        $arg1 = new Constraint();
        $arg2 = new Authority();
        // target
        $target = new TestChatCompanyAPIController($arg1, $arg2);
        $props = new ChatServerInfoProperties();

        // request
        $req = new Request([
                "accessId" => "testaccessid",
                "accessCode" => "testaccesscode",
                "domainName" => "kibousurudomain",
            ]);

        // exec
        $act = $target->getChatSubDomain($req);
        $this->assertEquals(200, $act->getStatusCode());
        $json = $act->getContent();
        $content = json_decode($json);
        $this->assertEquals(1, $content->result_code);
        $this->assertEquals(1, $content->result_data->subdomain_flg);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_initServer()
    {
        $mock = Mockery::mock("overload:" . ChatService::class);
        $mock->shouldReceive("initServer");

        // args
        $arg1 = new Constraint();
        $arg2 = new Authority();
        // target
        $target = new TestChatCompanyAPIController($arg1, $arg2);
        $props = new ChatServerInfoProperties();

        // request
        $req = new Request(
            ["a" => "testonetimetoken", "b" => "testtenentkey"],
            );

        // exec
        $act = $target->initServer($req);
        $this->assertEquals(200, $act->getStatusCode());
        $json = $act->getContent();
        $content = json_decode($json);
        $this->assertEquals(1, $content->result_code);
    }
}

class TestChatCompanyAPIController extends ChatCompanyAPIController
{
}
