<?php

namespace Tests\Unit\app\Chat;

use App\Chat\ChatIdServerClient;
use App\Chat\Consts\ChatIdServerReturnCode;
use App\Chat\Exceptions\DataAccessException;
use Tests\TestCase;

use App\Chat\Exceptions\ExistsSubdomainException;
use App\Chat\Exceptions\InvalidSubdomainFormatException;
use App\Chat\Exceptions\UnknownValueException;
use App\Chat\Properties\ChatServerInfoProperties;
use App\Chat\Properties\ChatTenantProperties;
use App\Http\Utils\IdAppApiUtils;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Mockery;

class ChatIdServerClientTest extends TestCase
{


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_reserveServerName_ok()
    {

        // mock
        $resmock = $this->makeJsonResponse([
            "success" => true,
            "message" => "ok",
            "data" => [
                "code"=>0,
                "company_id" => 1001,
                "company_name" => "testcompany",
                "subdomain" => "mychat",
                "cluster_arn" => "arn:cluster001",
                "image" => "image:1",
                "mongo" => "mongo://mongo/mongo",
                "tenant_key" => "c001",
                "server_url" => "https://www.example.com"
            ]
        ]);

        $mock = $this->makeIdClientMockery();
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) {
                $ok = true;
                if ($ok) $ok = $m === "POST";
                if ($ok) $ok = $u === "sasattotalk.reserve";
                if ($ok) {
                    $jkey = 'json';
                    $ok = array_key_exists($jkey, $o);
                    if ($ok) {
                        $ja = $o[$jkey];
                        if ($ok) $ok = $ja["company_id"] === 1001;
                        if ($ok) $ok = $ja["company_name"] === "testcompany";
                        if ($ok) $ok = $ja["subdomain"] === "mychat";
                        if ($ok) $ok = $ja["app_env"] === config("app.pac_app_env");
                        if ($ok) $ok = $ja["contract_app"] === config("app.pac_contract_app");
                        if ($ok) $ok = $ja["contract_server"] === config("app.pac_contract_server");
                        if ($ok) $ok = $ja["plan"] === 2;
                    }
                }
                return $ok;
            })
            ->andReturn($resmock);


        // target
        $target = new TestChatIdServerClient();

        // exec
        $act = $target->reserveServerName("mychat", 1001, "testcompany", 2);

        // assert
        $this->assertEquals("arn:cluster001", $act->cluster_arn());
        $this->assertEquals("image:1", $act->image());
        $this->assertEquals(1001, $act->company_id());
        $this->assertEquals("mychat", $act->server_name());
        $this->assertEquals("mongo://mongo/mongo", $act->mongo_url());
        $this->assertEquals("c001", $act->tenant_key());
        $this->assertEquals("https://www.example.com", $act->server_url());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_reserveServerName_ng_failclient()
    {
        $this->expectException(DataAccessException::class);

        // mock
        $resmock = $this->makeJsonResponse([
            "success" => false,
            "message" => "",
            "data" => [
                "code"=>1,
            ]
        ], 400);

        $this->makeIdClientMockery()->shouldReceive("request")
            ->andReturn($resmock);

        // target
        $target = new TestChatIdServerClient();

        // exec
        $target->reserveServerName("mychat", 1001, "testcompany", 2);

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_reserveServerName_ng_response_requiredkey()
    {
        $this->expectException(DataAccessException::class);

        // mock
        $resmock = $this->makeJsonResponse([
            "success" => true,
            "message" => "",
            "data" => [
            ]
        ], 200);

        $this->makeIdClientMockery()->shouldReceive("request")
            ->andReturn($resmock);

        // target
        $target = new TestChatIdServerClient();

        // exec
        $target->reserveServerName("mychat", 1001, "testcompany", 2);

    }

    public function test_private_requestIdServer_ok_retrunResponse() {


        $resmock = $this->makeJsonResponse([
            "success" => true,
            "message" => "",
            "data" => [
                "code"=>0,
            ]
        ]);

        $this->makeIdClientMockery()->shouldReceive("request")
            ->andReturn($resmock);

        // target
        $target = new TestChatIdServerClient();

        $args = ["POST", "https://www.example.com", [], true];
        // exec
        $act = $this->invokePrivateFunction($target, "requestIdServer", $args);

        $this->assertEquals(200, $act->getStatusCode());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_reserveServerName_ng_failrequest()
    {
        $this->expectException(DataAccessException::class);

        Mockery::mock("overload:".IdAppApiUtils::class)->shouldReceive("getAuthorizeClient")->andReturn(null);

        // target
        $target = new TestChatIdServerClient();

        // exec
        $act = $target->reserveServerName("mychat", 1001, "testcompany", 2);

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_checkAvailableServerName_0()
    {
        // mock
        $this->_mock_availabvleServerName();
        // target
        $target = new TestChatIdServerClient();
        // exec
        $act = $target->checkAvailableServerName("mychat0");
        // assert
        $this->assertTrue($act);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_checkAvailableServerName_1()
    {
        $this->expectException(InvalidSubdomainFormatException::class);
        // mock
        $this->_mock_availabvleServerName();
        // target
        $target = new TestChatIdServerClient();
        // exec
        $target->checkAvailableServerName("mychat1");
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_checkAvailableServerName_2()
    {
        $this->expectException(ExistsSubdomainException::class);
        // mock
        $this->_mock_availabvleServerName();
        // target
        $target = new TestChatIdServerClient();
        // exec
        $target->checkAvailableServerName("mychat2");
        $this->fail();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_checkAvailableServerName_3()
    {
        // mock
        $this->_mock_availabvleServerName();
        // target
        $target = new TestChatIdServerClient();
        // exec
        try {
            $target->checkAvailableServerName("mychat3");
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf(UnknownValueException::class, $e);
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_checkAvailableServerName_x()
    {
        // mock
        $this->_mock_availabvleServerName();
        // target
        $target = new TestChatIdServerClient();
        // exec
        try {
            $target->checkAvailableServerName("mychatx");
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf(UnknownValueException::class, $e);
        }
    }

    private function _mock_availabvleServerName()
    {

        $mock = $this->makeIdClientMockery();
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) {
                $ok = true;
                if ($ok) $ok = $m === "GET";
                if ($ok) $ok = $u === "sasattotalk.available";
                if ($ok) {
                    $ok = array_key_exists('query', $o);
                    if ($ok) {
                        $ja = $o["query"];
                        if ($ok) $ok = array_key_exists("subdomain", $ja);
                        $name = $ja["subdomain"];
                        if ($ok) $ok = !empty($name);
                    }
                }

                return $ok;
            })
            ->andReturnUsing(function ($m, $u, $o) {
                $name = $o["query"]["subdomain"];
                $f = function ($flg, $code, $msg) {
                    return [
                        "success" => $flg,
                        "data" => ["code"=>$code],
                        "message" => $msg
                    ];
                };
                $res = [];
                switch ($name) {
                    case "mychat0":
                        $res = $f(true, ChatIdServerReturnCode::OK, "ok");
                        break;
                    case "mychat1":
                        $res = $f(true, ChatIdServerReturnCode::CHAT_SUBDOMAIN_INVALID_FORMAT, "書式不正");
                        break;
                    case "mychat2":
                        $res = $f(true, ChatIdServerReturnCode::CHAT_SUBDOMAIN_ALREADY_IN_USE, "既存");
                        break;
                    case "mychat3":
                        $res = $f(true, ChatIdServerReturnCode::CHAT_CONTAINER_IMAGE_NOT_FOUND, "はて？");
                        break;
                    default:
                        $res = $f(false, -1, "error");
                        unset($res["code"]);
                        break;
                }

                return $this->makeJsonResponse($res);
            });
    }



    private function makeIdClientMockery()
    {
        $ret = Mockery::mock("overload:" . Client::class);
        Session::put('id_app_api_access_token', "testsession");
        return $ret;
    }

    private function makeJsonResponse(array $body, $code = 200)
    {
        return new class($code, $body)
        {
            private $code = null;
            private $body = null;

            public function __construct($code, $body)
            {
                $this->code = $code;
                $this->body = $body;
            }

            public function getStatusCode()
            {
                return $this->code;
            }

            public function getBody()
            {
                return json_encode($this->body);
            }
        };
    }


        /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateServerInfo_ok_0()
    {

        // mock
        $resmock = $this->makeJsonResponse([
            "success" => true,
            "message" => "ok",
            "data" => [
                "code"=>0
            ]
        ]);

        $mock = $this->makeIdClientMockery();
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) {
                $ok = true;
                if ($ok) $ok = $m === "POST";
                if ($ok) $ok = $u === "sasattotalk.update";
                if ($ok) {
                    $jkey = 'json';
                    $ok = array_key_exists($jkey, $o);
                    if ($ok) {
                        $ja = $o[$jkey];
                        if ($ok) $ok = $ja["company_id"] === 1001;
                        if ($ok) $ok = $ja["subdomain"] === "mychat";
                        if ($ok) $ok = $ja["app_env"] === config("app.pac_app_env");
                        if ($ok) $ok = $ja["contract_app"] === config("app.pac_contract_app");
                        if ($ok) $ok = $ja["contract_server"] === config("app.pac_contract_server");
                        if ($ok) $ok = $ja["status"] === 1;
                        if ($ok) $ok = $ja["service_arn"] === "arn:aws:ecs:service:1";
                        if ($ok) $ok = $ja["task_definition_arn"] === "arn:aws:ecs:taskdefinition:1";
                        if ($ok) $ok = $ja["admin_id"] === "adminid001";
                        if ($ok) $ok = $ja["admin_token"] === "admintoken001";
                    }
                }
                return $ok;
            })
            ->andReturn($resmock);


        // target
        $target = new TestChatIdServerClient();

        $props = new ChatTenantProperties();
        $props->server_name("mychat");
        $props->company_id(1001);
        $props->status(1);
        $props->service_arn("arn:aws:ecs:service:1");
        $props->task_definition("arn:aws:ecs:taskdefinition:1");
        $props->admin_id("adminid001");
        $props->admin_token("admintoken001");

        // exec
        $target->updateServerInfo($props);

        $this->assertTrue(true);
    }

    public function test_updateServerInfo_ok_1()
    {

        // mock
        $resmock = $this->makeJsonResponse([
            "success" => true,
            "message" => "ok",
            "data" => [
                "code"=>0
            ]
        ]);

        $mock = $this->makeIdClientMockery();
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) {
                $ok = true;
                if ($ok) $ok = $m === "POST";
                if ($ok) $ok = $u === "sasattotalk.update";
                if ($ok) {
                    $jkey = 'json';
                    $ok = array_key_exists($jkey, $o);
                    if ($ok) {
                        $ja = $o[$jkey];
                        if ($ok) $ok = $ja["company_id"] === 1001;
                        if ($ok) $ok = $ja["subdomain"] === "mychat";
                        if ($ok) $ok = $ja["app_env"] === config("app.pac_app_env");
                        if ($ok) $ok = $ja["contract_app"] === config("app.pac_contract_app");
                        if ($ok) $ok = $ja["contract_server"] === config("app.pac_contract_server");
                        if ($ok) $ok = $ja["status"] === 1;
                        if ($ok) $ok = $ja["service_arn"] === "arn:aws:ecs:service:1";
                        if ($ok) $ok = $ja["task_definition_arn"] === "arn:aws:ecs:taskdefinition:1";
                        if ($ok) $ok = !isset($ja["admin_id"]);
                        if ($ok) $ok = !isset($ja["admin_token"]);
                    }
                }
                return $ok;
            })
            ->andReturn($resmock);


        // target
        $target = new TestChatIdServerClient();

        $props = new ChatTenantProperties();
        $props->server_name("mychat");
        $props->company_id(1001);
        $props->status(1);
        $props->service_arn("arn:aws:ecs:service:1");
        $props->task_definition("arn:aws:ecs:taskdefinition:1");

        // exec
        $target->updateServerInfo($props);

        $this->assertTrue(true);
    }

}


class TestChatIdServerClient extends ChatIdServerClient
{
}
