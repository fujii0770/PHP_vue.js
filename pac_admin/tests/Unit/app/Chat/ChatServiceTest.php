<?php

namespace Tests\Unit\app\Chat;

use Tests\TestCase;

use App\Chat\ChatTenantClient;
use App\Chat\ChatIdServerClient;
use App\Chat\ChatRepository;
use App\Chat\ChatServerCreator;
use App\Chat\ChatService;
use App\Chat\Consts\ChatPlan;
use App\Chat\Consts\ChatServiceStatus;
use App\Chat\Consts\ChatStatus;
use App\Chat\Consts\ChatUserStatus;
use App\Chat\Exceptions\CompanyNotFoundException;
use App\Chat\Exceptions\DataAccessException;
use App\Chat\Exceptions\DataNotFoundException;
use App\Chat\Exceptions\ExclusiveException;
use App\Chat\Exceptions\FailedHttpAccessException;
use App\Chat\Properties\ChatServerInfoProperties;
use App\Chat\Properties\ChatUserDataProperties;
use App\Models\Company;
use Closure;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Mockery;
use Psr\Http\Message\ResponseInterface;

class ChatServiceTest extends TestCase
{


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_checkAvailableServerName()
    {

        $mock = Mockery::mock("overload:" . ChatIdServerClient::class);
        $mock->shouldReceive("checkAvailableServerName")
            ->andReturn(true);

        // target
        $target = new TestChatService();

        // exec
        $act = $target->checkAvailableServerName("testservername");

        // assert
        $this->assertTrue($act);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_createServer_ok()
    {
        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getCompany")->andReturn(new Company());

        $mock = Mockery::mock("overload:" . ChatServerCreator::class);
        $mock->shouldReceive("createServer")
            ->andReturn(true);

        // target
        $target = new TestChatService();

        $props = new ChatServerInfoProperties();
        // exec
        $target->createServer($props);
        $this->assertTrue(true);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_createServer_ng_company_notfound()
    {
        $this->expectException(CompanyNotFoundException::class);

        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getCompany")->andReturn(null);

        // $mock = Mockery::mock("overload:" . ChatServerCreator::class);
        // $mock->shouldReceive("createServer")
        //     ->andReturn(true);

        // target
        $target = new TestChatService();

        $props = new ChatServerInfoProperties();
        $props->company_id(1001);
        // exec
        $target->createServer($props);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_initServer_ok()
    {

        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getClientCallbackUrl")->andReturn("http://client.example.com");
        $mock1->shouldReceive("getServerIdFromOnetimeKey")
            ->withArgs(function ($o) {
                return $o === "testonetimekey";
            })
            ->andReturn(111);
        $mock1->shouldReceive("deleteServerOnetimeToken")->withArgs(function ($id, $key) {
            return $id === 111 && $key === "testonetimekey";
        })
            ->andReturn(1);
        $mock1->shouldReceive("updateServerOpen")->andReturn(1);
        $mock1->shouldReceive("updateServerInfoServerStatus")->andReturn(1);
        $mock1->shouldReceive("updateServerAdminToken")->andReturn(1);
        $mock1->shouldReceive("getCompany")->andReturnUsing(function ($coid) {
            return (object) [
                "id" => $coid,
                "company_name" => "testcompanyname",
            ];
        });
        $mock1->shouldReceive("getContractCallbackUrl")->andReturn("https://t.example.com/a/v?i=1");
        $mock1->shouldReceive("updateContractCallbackUrlStatus")->andReturn(1);


        $info = new ChatServerInfoProperties();
        $info->company_id(1234);
        $info->server_name("testservername");

        $mock1->shouldReceive("getServerInfoPreOpenByIdAndKey")
            ->withArgs(function ($a1, $a2) {
                $ret = $a1 === 111;
                $ret = $ret && $a2 === "testtenantkey";
                return $ret;
            })
            ->andReturn($info);
        $mock1->shouldReceive("getLoginRetries")->andReturn(33);
        $mock1->shouldReceive("getLoginSleepSeconds")->andReturn(222);
        $mock1->shouldReceive("updateServerCache");

        $mock9 = Mockery::mock("overload:" . ChatIdServerClient::class);
        $mock9->shouldReceive("updateServerInfo")
            ->withArgs(function ($t) {
                $ok = true;
                if ($ok) $ok = $t->company_id == 1234;
                if ($ok) $ok = $t->server_name == "testservername";
                if ($ok) $ok = $t->admin_id == "testauthid";
                if ($ok) $ok = $t->admin_token == "testtoken";
                if ($ok) $ok = $t->status == ChatStatus::VALID;
                return $ok;
            });


        $mock2 = Mockery::mock("overload:" . ChatTenantClient::class);
        $mock2->shouldReceive("loginServer")->andReturn(true);
        $mock2->shouldReceive("getPersonalAccessToken")->andReturn("testtoken");
        $mock2->shouldReceive("auth_id")->andReturn("testauthid");
        $mock2->shouldReceive("root_url")->andReturn("https://chat.example.com");
        $mock2->shouldReceive("setOrganization");
        $mock2->shouldReceive("setServer");
        $mock2->shouldReceive("setSiteUrl");
        $mock2->shouldReceive("setSmtp");
        $mock2->shouldReceive("initSetting");
        $mock2->shouldReceive("initSettingFromFiles");
        $mock2->shouldReceive("restartServer");
        $mock2->shouldReceive("completeSetupWizard");


        $mockc = Mockery::mock("overload:" . Client::class);
        $mockc->shouldReceive("request")->andReturnUsing(function () {
            return new class()
            {
                public function getStatusCode()
                {
                    return 200;
                }
            };
        });

        // target
        $target = new TestChatService();


        // exec
        $target->initServer("testtenantkey", "testonetimekey");
        $this->assertTrue(true);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_initServer_ng_onetimetoken_not_found()
    {
        $this->expectException(DataNotFoundException::class);

        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getServerIdFromOnetimeKey")->andReturn(null);
        // target
        $target = new TestChatService();
        // exec
        $target->initServer("testtenantkey", "testonetimekey");
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_getServerInfo_ok()
    {

        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getCompany")->andReturnUsing(function () {
            $ret = new Company();
            $ret->company_id = 1001;
            return $ret;
        });
        $mock1->shouldReceive("getServerInfoByCompanyId")->andReturnUsing(function () {

            $ret = new ChatServerInfoProperties();
            $ret->company_id(1001);
            $ret->is_trial(false);
            $ret->trial_start_date(new DateTime("2021-01-01"));
            $ret->trial_end_date(new DateTime("2021-01-31"));
            $ret->contract_start_date(new DateTime("2021-02-01"));
            $ret->contract_end_date(new DateTime("2099-02-01"));
            $ret->user_max(123);
            $ret->storage_max_mega(12);
            $ret->server_name("subdomain-name");
            $ret->plan(ChatPlan::PRO);
            $ret->status(ChatStatus::VALID);
            $ret->server_url("https://www.example.com");
            $ret->tenant_key("testtenantkey");
            $ret->admin_id("testadminid");
            $ret->admin_token("testadmintoken");
            $ret->service_status(ChatServiceStatus::INITIALIZING);
            $ret->version(99);

            return $ret;
        });
        // target
        $target = new TestChatService();
        // exec
        $act = $target->getServerInfo(1001);

        $this->assertEquals(1001, $act->company_id());
        $this->assertEquals(ChatStatus::VALID, $act->status());
        $this->assertFalse($act->is_trial());
        $this->assertEquals(new DateTime("2021-01-01"), $act->trial_start_date());
        $this->assertEquals(new DateTime("2021-01-31"), $act->trial_end_date());
        $this->assertEquals(new DateTime("2021-02-01"), $act->contract_start_date());
        $this->assertEquals(new DateTime("2099-02-01"), $act->contract_end_date());
        $this->assertEquals(123, $act->user_max());
        $this->assertEquals(12, $act->storage_max_mega());
        $this->assertEquals("subdomain-name", $act->server_name());
        $this->assertEquals(ChatPlan::PRO, $act->plan());
        $this->assertEquals(ChatStatus::VALID, $act->status());
        $this->assertEquals("https://www.example.com", $act->server_url());
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfo_ok_change_all()
    {
        $mockRepo = Mockery::mock("overload:" . ChatRepository::class);
        $mockRepo->shouldReceive("getCompany")->andReturnUsing(function () {
            $ret = new Company();
            $ret->company_id = 1001;
            $ret->chat_flg = 1;
            $ret->chat_trial_flg = 1;
            return $ret;
        })->once();

        $mockRepo->shouldReceive("updateCompanyFlags")->withArgs(
            function ($opuser, $coid, $istry, $iscont) {
                $ok = $coid == 1001;
                if ($ok) $ok = $istry === false;
                if ($ok) $ok = $iscont === true;
                return $ok;
            }
        )->once();

        $mockRepo->shouldReceive("selectForUpdateByCompanyAndServerName")
            ->withArgs(function ($company_id, $server_name) {
                $ok = $company_id == 1001;
                if ($ok) $ok = $server_name === "subdomain-name";
                return $ok;
            })
            ->andReturnUsing(function () {
                return (object) [
                    "id" => 9876,
                    "version" => 99,
                ];
            })->once();

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
        $props->version(99);

        $mockRepo->shouldReceive("updateServerInfo")
            ->withArgs(function ($id, $arg_props, $opuser) use ($props) {
                $ok = $id == 9876;
                if ($ok) $ok = $arg_props === $props;
                return $ok;
            })
            ->andReturnUsing(function () {
                return 1;
            })->once();

        // target
        $target = new TestChatService();
        // exec
        $act = $target->updateServerInfo($props);

        $this->assertEquals(1, $act);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfo_ok_change_flags1_only()
    {
        $mockRepo = Mockery::mock("overload:" . ChatRepository::class);
        $mockRepo->shouldReceive("getCompany")->andReturnUsing(function () {
            $ret = new Company();
            $ret->company_id = 1001;
            $ret->chat_flg = 1;
            $ret->chat_trial_flg = 1;
            return $ret;
        })->once();

        $mockRepo->shouldReceive("updateCompanyFlags")->withArgs(
            function ($opuser, $coid, $istry, $iscont) {
                $ok = $coid == 1001;
                if ($ok) $ok = $istry === false;
                if ($ok) $ok = $iscont === true;
                return $ok;
            }
        )->once();

        $mockRepo->shouldReceive("selectForUpdateByCompanyAndServerName")
            ->withArgs(function ($company_id, $server_name) {
                $ok = $company_id == 1001;
                if ($ok) $ok = $server_name === "subdomain-name";
                return $ok;
            })
            ->andReturnUsing(function () {
                return (object) [
                    "id" => 9876,
                    "version" => 99,
                ];
            })->once();

        $props = new ChatServerInfoProperties();
        $props->company_id(1001);
        $props->server_name("subdomain-name");
        $props->is_contract(true);
        $props->is_trial(false);
        $props->version(99);


        $mockRepo->shouldReceive("updateServerInfo")
            ->withArgs(function ($id, $arg_props, $opuser) use ($props) {
                $ok = $id == 9876;
                if ($ok) $ok = $arg_props === $props;
                return $ok;
            })
            ->andReturnUsing(function () {
                return false;
            })->once();

        // target
        $target = new TestChatService();
        // exec
        $act = $target->updateServerInfo($props);

        $this->assertEquals(1, $act);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfo_ok_change_flags2_only()
    {
        $mockRepo = Mockery::mock("overload:" . ChatRepository::class);
        $mockRepo->shouldReceive("getCompany")->andReturnUsing(function () {
            $ret = new Company();
            $ret->company_id = 1001;
            $ret->chat_flg = 1;
            $ret->chat_trial_flg = 1;
            return $ret;
        })->once();

        $mockRepo->shouldReceive("updateCompanyFlags")->withArgs(
            function ($opuser, $coid, $istry, $iscont) {
                $ok = $coid == 1001;
                if ($ok) $ok = $istry === true;
                if ($ok) $ok = $iscont === false;
                return $ok;
            }
        )->once();

        $mockRepo->shouldReceive("selectForUpdateByCompanyAndServerName")
            ->withArgs(function ($company_id, $server_name) {
                $ok = $company_id == 1001;
                if ($ok) $ok = $server_name === "subdomain-name";
                return $ok;
            })
            ->andReturnUsing(function () {
                return (object) [
                    "id" => 9876,
                    "version" => 99,
                ];
            })->once();

        $props = new ChatServerInfoProperties();
        $props->company_id(1001);
        $props->server_name("subdomain-name");
        $props->is_trial(true);
        $props->is_contract(false);
        $props->version(99);


        $mockRepo->shouldReceive("updateServerInfo")
            ->withArgs(function ($id, $arg_props, $opuser) use ($props) {
                $ok = $id == 9876;
                if ($ok) $ok = $arg_props === $props;
                return $ok;
            })
            ->andReturnUsing(function () {
                return false;
            })->once();

        // target
        $target = new TestChatService();
        // exec
        $act = $target->updateServerInfo($props);

        $this->assertEquals(1, $act);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfo_ok_no_flags()
    {
        $props = new ChatServerInfoProperties();
        $props->company_id(1001);
        $props->server_name("subdomain-name");
        $props->version(99);

        $this->_test_updateServerInfo_ok_change_flags(function($m){
            $m->never();
        }, $props);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfo_ok_contract_flag_nochange()
    {
        $props = new ChatServerInfoProperties();
        $props->company_id(1001);
        $props->is_contract(true);
        $props->server_name("subdomain-name");
        $props->version(99);

        $this->_test_updateServerInfo_ok_change_flags(function($m){
            $m->never();
        }, $props);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfo_ok_contract_flag_change()
    {
        $props = new ChatServerInfoProperties();
        $props->company_id(1001);
        $props->is_contract(false);
        $props->server_name("subdomain-name");
        $props->version(99);

        $this->_test_updateServerInfo_ok_change_flags(function($m){
            $m->withArgs(
                function ($opuser, $coid, $istry, $iscont) {
                        $ok = $coid == 1001;
                        if ($ok) $ok = $iscont === false;
                        if ($ok) $ok = $istry === true;
                        return $ok;
                    }
                )->once();
        }, $props);
    }

        /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfo_ok_trial_flag_nochange()
    {
        $props = new ChatServerInfoProperties();
        $props->company_id(1001);
        $props->is_trial(true);
        $props->server_name("subdomain-name");
        $props->version(99);

        $this->_test_updateServerInfo_ok_change_flags(function($m){
            $m->never();
        }, $props);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfo_ok_trial_flag_change()
    {
        $props = new ChatServerInfoProperties();
        $props->company_id(1001);
        $props->is_trial(false);
        $props->server_name("subdomain-name");
        $props->version(99);

        $this->_test_updateServerInfo_ok_change_flags(function($m){
            $m->withArgs(
                function ($opuser, $coid, $istry, $iscont) {
                        $ok = $coid == 1001;
                        if ($ok) $ok = $iscont === true;
                        if ($ok) $ok = $istry === false;
                        return $ok;
                    }
                )->once();
        }, $props);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    private function _test_updateServerInfo_ok_change_flags(Closure $funct, ChatServerInfoProperties $props)
    {
        $mockRepo = Mockery::mock("overload:" . ChatRepository::class);
        $mockRepo->shouldReceive("getCompany")->andReturnUsing(function () {
            $ret = new Company();
            $ret->company_id = 1001;
            $ret->chat_flg = 1;
            $ret->chat_trial_flg = 1;
            return $ret;
        })->once();

        $funct($mockRepo->shouldReceive("updateCompanyFlags"));
        // $mockRepo->shouldReceive("updateCompanyFlags")->withArgs(
        //     function ($opuser, $coid, $istry, $iscont) {
        //         $ok = $coid == 1001;
        //         if ($ok) $ok = $iscont === false;
        //         if ($ok) $ok = $istry === true;
        //         return $ok;
        //     }
        // )->once();

        $mockRepo->shouldReceive("selectForUpdateByCompanyAndServerName")
            ->withArgs(function ($company_id, $server_name) {
                $ok = $company_id == 1001;
                if ($ok) $ok = $server_name === "subdomain-name";
                return $ok;
            })
            ->andReturnUsing(function () {
                return (object) [
                    "id" => 9876,
                    "version" => 99,
                ];
            })->once();


        $mockRepo->shouldReceive("updateServerInfo")
            ->withArgs(function ($id, $arg_props, $opuser) use ($props) {
                $ok = $id == 9876;
                if ($ok) $ok = $arg_props === $props;
                return $ok;
            })
            ->andReturnUsing(function () {
                return false;
            })->once();

        // target
        $target = new TestChatService();
        // exec
        $act = $target->updateServerInfo($props);

        $this->assertEquals(1, $act);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfo_ok_change_mstchat_only()
    {
        $mockRepo = Mockery::mock("overload:" . ChatRepository::class);
        $mockRepo->shouldReceive("getCompany")->andReturnUsing(function () {
            $ret = new Company();
            $ret->company_id = 1001;
            $ret->chat_flg = 1;
            $ret->chat_trial_flg = 1;
            return $ret;
        })->once();

        $mockRepo->shouldReceive("updateCompanyFlags")->never();

        $mockRepo->shouldReceive("selectForUpdateByCompanyAndServerName")
            ->withArgs(function ($company_id, $server_name) {
                $ok = $company_id == 1001;
                if ($ok) $ok = $server_name === "subdomain-name";
                return $ok;
            })
            ->andReturnUsing(function () {
                return (object) [
                    "id" => 9876,
                    "version" => 99,
                ];
            })->once();

        $props = new ChatServerInfoProperties();
        $props->company_id(1001);
        $props->is_contract(true);
        $props->is_trial(true);
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
        $props->version(99);

        $mockRepo->shouldReceive("updateServerInfo")
            ->withArgs(function ($id, $arg_props, $opuser) use ($props) {
                $ok = $id == 9876;
                if ($ok) $ok = $arg_props === $props;
                return $ok;
            })
            ->andReturnUsing(function () {
                return 1;
            })->once();

        // target
        $target = new TestChatService();
        // exec
        $act = $target->updateServerInfo($props);

        $this->assertEquals(1, $act);
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfo_ng_no_company()
    {
        $this->expectException(CompanyNotFoundException::class);

        $mockRepo = Mockery::mock("overload:" . ChatRepository::class);
        $mockRepo->shouldReceive("getCompany")->andReturnUsing(function () {
            return null;
        })->once();

        $mockRepo->shouldReceive("updateCompanyFlags")->never();
        $mockRepo->shouldReceive("selectForUpdateByCompanyAndServerName")->never();
        $mockRepo->shouldReceive("updateServerInfo")->never();


        $props = new ChatServerInfoProperties();
        $props->company_id(1001);
        $props->is_contract(true);
        $props->is_trial(true);
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

        // target
        $target = new TestChatService();
        // exec
        $act = $target->updateServerInfo($props);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfo_ng_notfound_mstchat()
    {

        $mockRepo = Mockery::mock("overload:" . ChatRepository::class);
        $mockRepo->shouldReceive("getCompany")->andReturnUsing(function () {
            $ret = new Company();
            $ret->company_id = 1001;
            $ret->chat_flg = 1;
            $ret->chat_trial_flg = 1;
            return $ret;
        })->once();

        $mockRepo->shouldReceive("updateCompanyFlags")->once();

        $mockRepo->shouldReceive("selectForUpdateByCompanyAndServerName")
            ->andReturnUsing(function () {
                return null;
            })->once();

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

        $mockRepo->shouldReceive("updateServerInfo")->never();

        // target
        $target = new TestChatService();
        // exec
        // exec
        try {
            $target->updateServerInfo($props);
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf(DataNotFoundException::class, $e);
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfo_ng_exclusive_version()
    {

        $mockRepo = Mockery::mock("overload:" . ChatRepository::class);
        $mockRepo->shouldReceive("getCompany")->andReturnUsing(function () {
            $ret = new Company();
            $ret->company_id = 1001;
            $ret->chat_flg = 1;
            $ret->chat_trial_flg = 1;
            return $ret;
        })->once();

        $mockRepo->shouldReceive("updateCompanyFlags")->once();

        $mockRepo->shouldReceive("selectForUpdateByCompanyAndServerName")
            ->andReturnUsing(function () {
                return (object) ["id" => 9876, "version" => 90];
            })->once();

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

        $mockRepo->shouldReceive("updateServerInfo")->never();

        // target
        $target = new TestChatService();
        // exec
        // exec
        try {
            $target->updateServerInfo($props);
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf(ExclusiveException::class, $e);
        }
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfo_ng_exclusive_noudpate()
    {
        $mockRepo = Mockery::mock("overload:" . ChatRepository::class);
        $mockRepo->shouldReceive("getCompany")->andReturnUsing(function () {
            $ret = new Company();
            $ret->company_id = 1001;
            $ret->chat_flg = 1;
            $ret->chat_trial_flg = 1;
            return $ret;
        })->once();

        $mockRepo->shouldReceive("updateCompanyFlags")->once();

        $mockRepo->shouldReceive("selectForUpdateByCompanyAndServerName")
            ->andReturnUsing(function () {
                return (object) ["id" => 9876, "version" => 99];
            })->once();

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
        $props->version(99);

        $mockRepo->shouldReceive("updateServerInfo")->andReturn(0)->once();

        // target
        $target = new TestChatService();
        // exec
        try {
            $target->updateServerInfo($props);
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf(ExclusiveException::class, $e);
        }
    }



    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_addUser()
    {

        // https://readouble.com/mockery/1.0/ja/expectations.html
        $aret = new ChatServerInfoProperties();
        $aret->server_url("https://chat.example.com");
        $aret->admin_id("testadminid");
        $aret->admin_token("testadmintoken");

        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getServerInfoById")->withArgs(function ($o) {
            return $o === "testsubdomainid";
        })->andReturn($aret);

        $mock1->shouldReceive("getAwaitingUsers")->withArgs(function ($o, $flg) {
            return $o === "testsubdomainid" && $flg === ChatUserStatus::WAITING_TO_REGISTER;
        })->andReturn($this->makeMockUsers(3));

        $mock1->shouldReceive("updateRegistUsers")->withArgs(function ($o) {
            $ok = count($o) === 3;
            return $ok;
        })->once();

        $mock2 = Mockery::mock("overload:" . ChatTenantClient::class);
        $mock2->shouldReceive("createUser")->withArgs(function ($o) {
            $ok = $o instanceof ChatUserDataProperties;
            if ($ok) $ok = preg_match("/^testpersonalname[123]$/", $o->name()) === 1;
            if ($ok) $ok = preg_match("/^testusername[123]$/", $o->username()) === 1;
            if ($ok) $ok = preg_match("/^testuser[123]@example.com$/", $o->email()) === 1;
            if ($ok) $ok = !empty($o->password());
            if ($ok) $ok = $o->roles() == ["user"];
            if ($ok) $ok = $o->requirePasswordChange() === true;
            if ($ok) $ok = $o->sendWelcomeEmail() === false;
            if ($ok) $ok = $o->setRandomPassword() === true;
            if ($ok) $ok = $o->verified() === false;
            return $ok;
        })
            ->andReturnUsing(function ($o) {
                return (object)["_id" => "testuserid"];
            })->times(3);
        $mock2->shouldReceive("auth_id")->andReturn("testauthid");
        $mock2->shouldReceive("root_url")->andReturn("https://chat.example.com");

        $mockc = Mockery::mock("overload:" . Client::class);
        $mockc->shouldReceive("request");

        // target
        $target = new TestChatService();


        // exec
        $act = $target->addUser("testsubdomainid");
        $this->assertEquals(3, $act["target"]);
        $this->assertEquals(3, $act["success"]);
        $this->assertEquals(0, $act["failure"]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_addUser_ng_notargetusers()
    {
        $this->expectException(DataNotFoundException::class);

        $aret = new ChatServerInfoProperties();
        $aret->server_url("https://chat.example.com");
        $aret->admin_id("testadminid");
        $aret->admin_token("testadmintoken");

        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getServerInfoById")->withArgs(function ($o) {
            return $o === "testsubdomainid";
        })->andReturn($aret);

        $mock1->shouldReceive("getAwaitingUsers")->andReturn(null);

        // target
        $target = new TestChatService();
        // exec
        $act = $target->addUser("testsubdomainid");
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_addUser_include_failure()
    {

        // https://readouble.com/mockery/1.0/ja/expectations.html
        $aret = new ChatServerInfoProperties();
        $aret->server_url("https://chat.example.com");
        $aret->admin_id("testadminid");
        $aret->admin_token("testadmintoken");

        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getServerInfoById")->withArgs(function ($o) {
            return $o === "testsubdomainid";
        })->andReturn($aret);

        $mock1->shouldReceive("getAwaitingUsers")->withArgs(function ($o, $flg) {
            return $o === "testsubdomainid" && $flg === ChatUserStatus::WAITING_TO_REGISTER;
        })->andReturn($this->makeMockUsers(3));

        $mock1->shouldReceive("updateRegistUsers")->withArgs(function ($users) {
            $ok = count($users) === 3;
            $u1 = $users[0];
            if ($ok) $ok = $u1->chat_user_id === "testchatuserid1";
            if ($ok) $ok = $u1->status === ChatUserStatus::VALID;
            if ($ok) $ok = $u1->system_remark === null;
            if ($ok) $ok = $u1->operation_user === "testupdateuser1";

            $u2 = $users[1];
            if ($ok) $ok = $u2->chat_user_id === null;
            if ($ok) $ok = $u2->status === ChatUserStatus::PROCESSED_REGISTER_FAIL;
            if ($ok) $ok = $u2->system_remark === "Response code : 400";
            if ($ok) $ok = $u2->operation_user === "testupdateuser2";

            $u3 = $users[2];
            if ($ok) $ok = $u3->chat_user_id === "testchatuserid3";
            if ($ok) $ok = $u3->status === ChatUserStatus::VALID;
            if ($ok) $ok = $u3->system_remark === null;
            if ($ok) $ok = $u3->operation_user === "testcreateuser3";

            return $ok;
        })->once();

        $mock2 = Mockery::mock("overload:" . ChatTenantClient::class);
        $mock2->shouldReceive("createUser")->withArgs(function ($o) {
            if (preg_match("/^testpersonalname2$/", $o->name())) {
                throw new FailedHttpAccessException("", 400);
            }
            $ok = $o instanceof ChatUserDataProperties;
            if ($ok) $ok = preg_match("/^testpersonalname[123]$/", $o->name()) === 1;
            if ($ok) $ok = preg_match("/^testusername[123]$/", $o->username()) === 1;
            if ($ok) $ok = preg_match("/^testuser[123]@example.com$/", $o->email()) === 1;
            if ($ok) $ok = !empty($o->password());
            if ($ok) $ok = $o->roles() == ["user"];
            if ($ok) $ok = $o->requirePasswordChange() === true;
            if ($ok) $ok = $o->sendWelcomeEmail() === false;
            if ($ok) $ok = $o->setRandomPassword() === true;
            if ($ok) $ok = $o->verified() === false;
            return $ok;
        })
            ->andReturnUsing(function ($o) {
                $u = $o->username();
                if ($u === "testusername1") {
                    $cid = "testchatuserid1";
                } else if ($u === "testusername2") {
                    $cid = "testchatuserid2";
                } else if ($u === "testusername3") {
                    $cid = "testchatuserid3";
                }
                return (object)["_id" => $cid];
            })->times(2);
        $mock2->shouldReceive("auth_id")->andReturn("testauthid");
        $mock2->shouldReceive("root_url")->andReturn("https://chat.example.com");
        $mock2->shouldReceive("setOrganization");
        $mock2->shouldReceive("setServer");
        $mock2->shouldReceive("setSiteUrl");
        $mock2->shouldReceive("completeSetupWizard");


        $mockc = Mockery::mock("overload:" . Client::class);
        $mockc->shouldReceive("request");

        // target
        $target = new TestChatService();


        // exec
        $act = $target->addUser("testsubdomainid");
        $this->assertEquals(3, $act["target"]);
        $this->assertEquals(2, $act["success"]);
        $this->assertEquals(1, $act["failure"]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_deleteUser()
    {
        $delmockusers = function ($ix) {
            $ret = [];
            for ($i = 1; $i <= $ix; $i++) {
                $ret[] = (object) [
                    "id" => 1000 + $i,
                    "chat_user_id" => "xxx$i",
                    "update_user" => $i == 3 ? null : "testupdateuser$i",
                    "create_user" => "testcreateuser$i",
                ];
            };
            return $ret;
        };

        // https://readouble.com/mockery/1.0/ja/expectations.html
        $aret = new ChatServerInfoProperties();
        $aret->server_url("https://chat.example.com");
        $aret->admin_id("testadminid");
        $aret->admin_token("testadmintoken");

        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getServerInfoById")->withArgs(function ($o) {
            return $o === "testsubdomainid";
        })->andReturn($aret);

        $mock1->shouldReceive("getAwaitingUsers")->withArgs(function ($o, $flg) {
            return $o === "testsubdomainid" && $flg === ChatUserStatus::WAITING_TO_DELETE;
        })->andReturn($delmockusers(3));

        $mock1->shouldReceive("updateUsersStatus")->withArgs(function ($users, $flg) {

            $ok = count($users) === 3;
            $u1 = $users[0];
            if ($ok) $ok = $u1->chat_user_id === "xxx1";
            if ($ok) $ok = $u1->status === ChatUserStatus::DELETED;
            if ($ok) $ok = $u1->system_remark === null;
            if ($ok) $ok = $u1->operation_user === "testupdateuser1";

            $u2 = $users[1];
            if ($ok) $ok = $u2->chat_user_id === "xxx2";
            if ($ok) $ok = $u2->status === ChatUserStatus::PROCESSED_DELETE_FAIL;
            if ($ok) $ok = $u2->system_remark === "Response code : 400";
            if ($ok) $ok = $u2->operation_user === "testupdateuser2";

            $u3 = $users[2];
            if ($ok) $ok = $u3->chat_user_id === "xxx3";
            if ($ok) $ok = $u3->status === ChatUserStatus::DELETED;
            if ($ok) $ok = $u3->system_remark === null;
            if ($ok) $ok = $u3->operation_user === "testcreateuser3";

            return $ok;
        })->once();

        $mock2 = Mockery::mock("overload:" . ChatTenantClient::class);
        $mock2->shouldReceive("deleteUser")->withArgs(function ($uid) {
            if ($uid == "xxx2") {
                throw new FailedHttpAccessException("", 400);
            }
            return ($uid === "xxx1" || $uid === "xxx3");
        })
            ->andReturn($aret)->times(2);
        $mock2->shouldReceive("auth_id")->andReturn("testauthid");


        $mockc = Mockery::mock("overload:" . Client::class);
        $mockc->shouldReceive("request");

        // target
        $target = new TestChatService();


        // exec
        $act = $target->deleteUser("testsubdomainid");
        $this->assertEquals(3, $act["target"]);
        $this->assertEquals(2, $act["success"]);
        $this->assertEquals(1, $act["failure"]);
    }

    private function makeMockUsers($ix)
    {
        $ret = [];
        for ($i = 1; $i <= $ix; $i++) {
            $ret[] = (object) [
                "id" => 1000 + $i,
                "chat_personal_name" => "testpersonalname$i",
                "chat_user_name" => "testusername$i",
                "chat_email" => "testuser$i@example.com",
                "chat_role_flg" => 1,
                "status" => 10,
                "system_remark" => null,
                "chat_user_id" => null,
                "update_user" => $i == 3 ? null : "testupdateuser$i",
                "create_user" => "testcreateuser$i",
            ];
        };
        return $ret;
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_stopUser()
    {
        $mockusers = function ($ix) {
            $ret = [];
            for ($i = 1; $i <= $ix; $i++) {
                $ret[] = (object) [
                    "id" => 1000 + $i,
                    "chat_user_id" => "xxx$i",
                    "update_user" => $i == 3 ? null : "testupdateuser$i",
                    "create_user" => "testcreateuser$i",
                ];
            };
            return $ret;
        };

        // https://readouble.com/mockery/1.0/ja/expectations.html
        $aret = new ChatServerInfoProperties();
        $aret->server_url("https://chat.example.com");
        $aret->admin_id("testadminid");
        $aret->admin_token("testadmintoken");

        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getServerInfoById")->withArgs(function ($o) {
            return $o === "testsubdomainid";
        })->andReturn($aret);

        $mock1->shouldReceive("getAwaitingUsers")->withArgs(function ($o, $flg) {
            return $o === "testsubdomainid" && $flg === ChatUserStatus::WAITING_TO_STOP;
        })->andReturn($mockusers(3));

        $mock1->shouldReceive("updateUsersStatus")->withArgs(function ($users, $flg) {

            $ok = count($users) === 3;
            $u1 = $users[0];
            if ($ok) $ok = $u1->chat_user_id === "xxx1";
            if ($ok) $ok = $u1->status === ChatUserStatus::STOPPED;
            if ($ok) $ok = $u1->system_remark === null;
            if ($ok) $ok = $u1->operation_user === "testupdateuser1";

            $u2 = $users[1];
            if ($ok) $ok = $u2->chat_user_id === "xxx2";
            if ($ok) $ok = $u2->status === ChatUserStatus::PROCESSED_TO_STOP;
            if ($ok) $ok = $u2->system_remark === "Response code : 400";
            if ($ok) $ok = $u2->operation_user === "testupdateuser2";

            $u3 = $users[2];
            if ($ok) $ok = $u3->chat_user_id === "xxx3";
            if ($ok) $ok = $u3->status === ChatUserStatus::STOPPED;
            if ($ok) $ok = $u3->system_remark === null;
            if ($ok) $ok = $u3->operation_user === "testcreateuser3";

            return $ok;
        })->once();

        $mock2 = Mockery::mock("overload:" . ChatTenantClient::class);
        $mock2->shouldReceive("stopUser")->withArgs(function ($uid) {
            if ($uid == "xxx2") {
                throw new FailedHttpAccessException("", 400);
            }
            return ($uid === "xxx1" || $uid === "xxx3");
        })
            ->andReturn($aret)->times(2);
        $mock2->shouldReceive("auth_id")->andReturn("testauthid");


        $mockc = Mockery::mock("overload:" . Client::class);
        $mockc->shouldReceive("request");

        // target
        $target = new TestChatService();


        // exec
        $act = $target->stopUser("testsubdomainid");
        $this->assertEquals(3, $act["target"]);
        $this->assertEquals(2, $act["success"]);
        $this->assertEquals(1, $act["failure"]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_unStopUser()
    {
        $mockusers = function ($ix) {
            $ret = [];
            for ($i = 1; $i <= $ix; $i++) {
                $ret[] = (object) [
                    "id" => 1000 + $i,
                    "chat_user_id" => "xxx$i",
                    "update_user" => $i == 3 ? null : "testupdateuser$i",
                    "create_user" => "testcreateuser$i",
                ];
            };
            return $ret;
        };

        // https://readouble.com/mockery/1.0/ja/expectations.html
        $aret = new ChatServerInfoProperties();
        $aret->server_url("https://chat.example.com");
        $aret->admin_id("testadminid");
        $aret->admin_token("testadmintoken");

        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getServerInfoById")->withArgs(function ($o) {
            return $o === "testsubdomainid";
        })->andReturn($aret);

        $mock1->shouldReceive("getAwaitingUsers")->withArgs(function ($o, $flg) {
            return $o === "testsubdomainid" && $flg === ChatUserStatus::WAITING_TO_UNSTOP;
        })->andReturn($mockusers(3));

        $mock1->shouldReceive("updateUsersStatus")->withArgs(function ($users, $flg) {

            $ok = count($users) === 3;
            $u1 = $users[0];
            if ($ok) $ok = $u1->chat_user_id === "xxx1";
            if ($ok) $ok = $u1->status === ChatUserStatus::VALID;
            if ($ok) $ok = $u1->system_remark === null;
            if ($ok) $ok = $u1->operation_user === "testupdateuser1";

            $u2 = $users[1];
            if ($ok) $ok = $u2->chat_user_id === "xxx2";
            if ($ok) $ok = $u2->status === ChatUserStatus::PROCESSED_TO_UNSTOP;
            if ($ok) $ok = $u2->system_remark === "Response code : 400";
            if ($ok) $ok = $u2->operation_user === "testupdateuser2";

            $u3 = $users[2];
            if ($ok) $ok = $u3->chat_user_id === "xxx3";
            if ($ok) $ok = $u3->status === ChatUserStatus::VALID;
            if ($ok) $ok = $u3->system_remark === null;
            if ($ok) $ok = $u3->operation_user === "testcreateuser3";

            return $ok;
        })->once();

        $mock2 = Mockery::mock("overload:" . ChatTenantClient::class);
        $mock2->shouldReceive("unStopUser")->withArgs(function ($uid) {
            if ($uid == "xxx2") {
                throw new FailedHttpAccessException("", 400);
            }
            return ($uid === "xxx1" || $uid === "xxx3");
        })
            ->andReturn($aret)->times(2);
        $mock2->shouldReceive("auth_id")->andReturn("testauthid");


        $mockc = Mockery::mock("overload:" . Client::class);
        $mockc->shouldReceive("request");

        // target
        $target = new TestChatService();


        // exec
        $act = $target->unStopUser("testsubdomainid");
        $this->assertEquals(3, $act["target"]);
        $this->assertEquals(2, $act["success"]);
        $this->assertEquals(1, $act["failure"]);
    }



    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_private_generateSystemRemark()
    {
        $target = new TestChatService();
        $act = $this->invokePrivateFunction($target, "generateSystemRemark", [new Exception("", -1)]);
        $this->assertEquals(__('message.false.api.system_error'), $act);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_private_getServerInfoById()
    {
        $this->expectException(DataNotFoundException::class);

        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getServerInfoById")->andReturn(null);

        $target = new TestChatService();
        $this->invokePrivateFunction($target, "getServerInfoById", [1001]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_private_getChatRole()
    {
        $target = new TestChatService();
        $act = $this->invokePrivateFunction($target, "getChatRole", [0]);
        $this->assertEquals(config("chat.tenant_admin_role_name"), $act);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_private_callback()
    {
        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getContractCallbackUrl")->andReturn(null);

        $target = new TestChatService();
        $act = $this->invokePrivateFunction($target, "callback", [11, "test"]);
        $this->assertEmpty($act);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_private_callback_catch_exception()
    {
        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getContractCallbackUrl")->andReturn("https://www.example.com");
        $mock1->shouldReceive("updateContractCallbackUrlStatus")->withArgs(function ($id, $status, $opuser) {
            return $id === 11;
        })->andReturn(1);

        $mockc = Mockery::mock("overload:" . Client::class);
        $mockc->shouldReceive("request")->andThrow(new Exception("test"));

        $target = new TestChatService();
        $act = $this->invokePrivateFunction($target, "callback", [11, "test"]);
        $this->assertEmpty($act);
    }
}


class TestChatService extends ChatService
{
}
