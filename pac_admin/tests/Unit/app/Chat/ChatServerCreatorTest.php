<?php

namespace Tests\Unit\app\Chat;

use Tests\TestCase;

use App\Chat\ChatEcsClient;
use App\Chat\ChatIdServerClient;
use App\Chat\ChatRepository;
use App\Chat\ChatServerCreator;
use App\Chat\Consts\ChatPlan;
use App\Chat\Properties\ChatAwsProperties;
use App\Chat\Properties\ChatRegisteredTaskDefinitionProperties;
use App\Chat\Properties\ChatServerInfoProperties;
use App\Chat\Properties\ChatTaskEnvironmentValues;
use App\Chat\Properties\ChatTenantProperties;
use App\Chat\Properties\ChatEcsServiceProperties;
use DateTime;
use Mockery;

class ChatServerCreatorTest extends TestCase
{


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_createServer()
    {


        // $mock0 = Mockery::mock("overload:" . DB::class);

        $mock1 = Mockery::mock("overload:" . ChatRepository::class);
        $mock1->shouldReceive("getAwsProperties")->andReturnUsing(function(){
            return new ChatAwsProperties();
        });
        $mock1->shouldReceive("createServerInfo")->andReturn(234);
        $mock1->shouldReceive("updateServerInfoForPreCreate")->andReturn(1);
        $mock1->shouldReceive("updateServerInfoServerStatus")->andReturn(1);
        $mock1->shouldReceive("registerServerOnetimeToken")->andReturn(true);
        $mock1->shouldReceive("getDefaultTaskEnvironmentValues")->andReturnUsing(function(){
            $ret = new ChatTaskEnvironmentValues();
            return $ret;
        });
        $mock1->shouldReceive("updateCompanyFlags")->andReturn(1);
        $mock1->shouldReceive("registerContractCallback");

        $mock5 = Mockery::mock("overload:" . ChatEcsClient::class);
        $mock5->shouldReceive("registerTaskDefinition")
            ->andReturnUsing(function(){
                $ret = new ChatRegisteredTaskDefinitionProperties();
                $ret->task_definition_arn("arn:testtaskdifinition");
                return $ret;
            });
        $mock5->shouldReceive("craeteService")
            ->andReturnUsing(function(){
                $ret = new ChatEcsServiceProperties();
                $ret->cluster("arn:testcluster");
                $ret->service_arn("arn:testservice");
                $ret->task_definition("arn:testtaskdifinition");
                return $ret;
            });

        $mock9 = Mockery::mock("overload:" . ChatIdServerClient::class);
        $mock9->shouldReceive("reserveServerName")
            ->withArgs(function ($a1, $a2, $a3, $a4) {
                $ok = true;
                if ($ok) $ok = $a1 == "kibousuru-name";
                if ($ok) $ok = $a2 == 101;
                if ($ok) $ok = $a3 == "testcompanyname";
                if ($ok) $ok = $a4 == 2;
                return $ok;
            })
            ->andReturnUsing(function(){
                $ret = new ChatTenantProperties();
                $ret->cluster_arn("arn:testcluster");
                $ret->image("arn:testecrimage");
                $ret->mongo_url("mongomongo://mongomogno");
                $ret->tenant_key("testtenantkey");
                $ret->server_url("https://kibousuru-name.example.com");
                $ret->company_id(102);
                $ret->server_name("kibousuru-name");
                return $ret;
            });
        $mock9->shouldReceive("updateServerInfo")
            ->withArgs(function ($t) {
                $ok = true;
                if ($ok) $ok = $t->company_id == 101;
                if ($ok) $ok = $t->server_name == "kibousuru-name";
                if ($ok) $ok = $t->service_arn == "arn:testservice";
                if ($ok) $ok = $t->task_definition == "arn:testtaskdifinition";
                // if ($ok) $ok = $t->status == "created";
                return $ok;
            });


        // args
        $props = new ChatServerInfoProperties();
        $props->company_id(101);
        $props->company_name("testcompanyname");
        $props->server_name("kibousuru-name");
        $props->trial_start_date(new DateTime("2022-01-05"));
        $props->trial_end_date(new DateTime("2022-01-31"));
        $props->contract_start_date(new DateTime("2022-02-01"));
        $props->contract_end_date(new DateTime("2099-01-31"));
        $props->user_max(123);
        $props->storage_max_mega(500);
        $props->plan(ChatPlan::PRO);
        $props->callback_url("https://keiyaku.example.com");

        // target
        $target = new TestChatServerCreator($props);
        // exec
        $target->createServer();
        // assert
        $this->assertTrue(true);
    }

}


class TestChatServerCreator extends ChatServerCreator
{
    public function t_makeCallbackUrl($tenant_key, $onetime_key) {
        return parent::makeCallbackUrl($tenant_key, $onetime_key);
    }

    public function t_makeOnetimeKey($tenant_key) {
        return parent::makeOnetimeKey($tenant_key);
    }

    public function t_makeS3Bucket($tenant_key) {
        return parent::makeS3Bucket($tenant_key);
    }
}
