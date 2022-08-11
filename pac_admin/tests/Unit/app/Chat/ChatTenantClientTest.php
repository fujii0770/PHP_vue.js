<?php

namespace Tests\Unit\app\Chat;

use Tests\TestCase;

use App\Chat\ChatTenantClient;
use App\Chat\Exceptions\FailedHttpAccessException;
use App\Chat\Exceptions\MultiException;
use App\Chat\Properties\ChatOrganizationProperties;
use App\Chat\Properties\ChatServerProperties;
use App\Chat\Properties\ChatSmtpProperties;
use App\Chat\Properties\ChatUserDataProperties;
use App\Chat\Properties\ChatUserProperties;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use \Mockery;

class ChatTenantClientTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_getPersonalAccessToken()
    {
        $rooturl = "http://www.example.com";
        $exp_token = "testtoken";

        // mock
        $resmock = new class($exp_token)
        {
            private $exp_token;
            public function __construct($t)
            {
                $this->exp_token = $t;
            }
            public function getStatusCode()
            {
                return 200;
            }
            public function getBody()
            {
                return '{"token":"' . $this->exp_token . '"}';
            }
        };

        $mock = Mockery::mock("overload:" . Client::class);
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) use ($rooturl) {
                $vals = [
                    "tokenName" => "forsystem",
                    "bypassTwoFactor" => true
                ];
                return $m == "POST"
                    && $u == $rooturl . "/api/v1/users.generatePersonalAccessToken"
                    && array_key_exists('json', $o) && $o['json'] == $vals;
            })
            ->andReturn($resmock);

        // target
        $target = new TestChatTenentClient($rooturl);
        $target->auth_id("testauthid");
        $target->auth_token("testauthtoken");
        $target->password("testpassword");

        // args

        // exec
        $act = $target->getPersonalAccessToken("forsystem");

        // assert
        $this->assertEquals($exp_token, $act);
        $this->assertEquals($exp_token, $target->auth_token());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_getPersonalAccessToken_ng()
    {
        $rooturl = "http://www.example.com";
        $exp_token = "testtoken";

        // mock
        $resmock = new class($exp_token)
        {
            private $exp_token;
            public function __construct($t)
            {
                $this->exp_token = $t;
            }
            public function getStatusCode()
            {
                return 400;
            }
            public function getBody()
            {
                return '{"token":"' . $this->exp_token . '"}';
            }
        };

        $mock = Mockery::mock("overload:" . Client::class);
        $mock->shouldReceive("request")
            ->andReturn($resmock);

        // target
        $target = new TestChatTenentClient($rooturl);
        $target->auth_id("testauthid");
        $target->auth_token("testauthtoken");
        $target->password("testpassword");

        //
        $this->expectException(FailedHttpAccessException::class);

        // exec
        $act = $target->getPersonalAccessToken();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_createUser()
    {
        $rooturl = "http://www.example.com";
        // mock
        $resmock = new class()
        {
            public function getStatusCode()
            {
                return 200;
            }
            public function getBody()
            {
                return '{"user":{"_id": "xxx1"}}';
            }
        };

        $mock = Mockery::mock("overload:" . Client::class);
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) use ($rooturl) {
                $vals = [
                    "name" => "testpersonalname",
                    "username" => "testusername",
                    "email" => "a@example.com",
                    "password" => "test",
                    "roles" => ["user"],
                    "requirePasswordChange" => true,
                    "sendWelcomeEmail" => false,
                    "setRandomPassword" => true,
                    "verified" => false,
                ];
                return $m == "POST"
                    && $u == $rooturl . "/api/v1/users.create"
                    && array_key_exists('json', $o) && $o['json'] == $vals;
            })
            ->andReturn($resmock);

        // target
        $target = new TestChatTenentClient($rooturl);

        // args
        $user = new ChatUserDataProperties();
        $user->name("testpersonalname");
        $user->username("testusername");
        $user->email("a@example.com");
        $user->password("test");
        $user->roles(["user"]);
        $user->requirePasswordChange(true);
        $user->sendWelcomeEmail(false);
        $user->setRandomPassword(true);
        $user->verified(false);

        // exec
        $act = $target->createUser($user);

        // assert
        $this->assertEquals("xxx1", $act->_id);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_loginServer_ok()
    {
        $rooturl = "http://www.example.com";

        $mock = Mockery::mock("overload:" . Client::class);
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) use ($rooturl) {
                $vals = [
                    "user" => "testuser",
                    "password" => "testpassword",
                ];
                return $m == "POST"
                    && $u == $rooturl . "/api/v1/login"
                    && array_key_exists('form_params', $o) && $o['form_params'] == $vals;
            })
            ->andReturnUsing(function () {
                return new class()
                {
                    public function getStatusCode()
                    {
                        return 200;
                    }
                    public function getBody()
                    {
                        return '{"data":{"userId": "testuserid", "authToken":"testauthtoken"}}';
                    }
                };
            });

        $user = "testuser";
        $password = "testpassword";

        $target = new TestChatTenentClient($rooturl);

        $target->loginServer($user, $password);

        $this->assertEquals("testuserid", $target->auth_id());
        $this->assertEquals("testauthtoken", $target->auth_token());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_loginServer_ng_retry()
    {
        $rooturl = "http://www.example.com";

        $mock = Mockery::mock("overload:" . Client::class);
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) use ($rooturl) {
                $vals = [
                    "user" => "testuser",
                    "password" => "testpassword",
                ];
                return $m == "POST"
                    && $u == $rooturl . "/api/v1/login"
                    && array_key_exists('form_params', $o) && $o['form_params'] == $vals;
            })
            ->times(4)
            ->andReturnUsing(function () {
                return new class()
                {
                    public function getStatusCode()
                    {
                        return 400;
                    }
                    public function getBody()
                    {
                        return '{}';
                    }
                };
            });

        $user = "testuser";
        $password = "testpassword";

        $this->expectException(MultiException::class);

        $target = new TestChatTenentClient($rooturl);
        $target->loginServer($user, $password, 3, 2);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_loginServer_ng_noretry()
    {
        $rooturl = "http://www.example.com";

        $mock = Mockery::mock("overload:" . Client::class);
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) use ($rooturl) {
                $vals = [
                    "user" => "testuser",
                    "password" => "testpassword",
                ];
                return $m == "POST"
                    && $u == $rooturl . "/api/v1/login"
                    && array_key_exists('form_params', $o) && $o['form_params'] == $vals;
            })
            ->once()
            ->andReturnUsing(function () {
                return new class()
                {
                    public function getStatusCode()
                    {
                        return 400;
                    }
                    public function getBody()
                    {
                        return '{}';
                    }
                };
            });

        $user = "testuser";
        $password = "testpassword";

        $this->expectException(FailedHttpAccessException::class);

        $target = new TestChatTenentClient($rooturl);
        $target->loginServer($user, $password, 0);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_setSetting_ok()
    {
        $rooturl = "http://www.example.com";

        $mock = Mockery::mock("overload:" . Client::class);
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) use ($rooturl) {
                $vals = [
                    "value" => "testvalue",
                ];
                return $m == "POST"
                    && $u == $rooturl . "/api/v1/settings/testsetting"
                    && array_key_exists('json', $o) && $o['json'] == $vals;
            })
            ->andReturnUsing(function () {
                return new class()
                {
                    public function getStatusCode()
                    {
                        return 200;
                    }
                    public function getBody()
                    {
                        return '{"data" : "testresult"}';
                    }
                };
            });


        $target = new TestChatTenentClient($rooturl);

        $act = $target->setSetting("testsetting", "testvalue");

        $this->assertEquals("testresult", $act->data);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_setSetting_ng()
    {
        $rooturl = "http://www.example.com";

        $mock = Mockery::mock("overload:" . Client::class);
        $mock->shouldReceive("request")
            ->andThrow(new Exception());

        $this->expectException(FailedHttpAccessException::class);

        $target = new TestChatTenentClient($rooturl);

        $act = $target->setSetting("testsetting", "testvalue");
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_getSetting_ok()
    {
        $rooturl = "http://www.example.com";

        $mock = Mockery::mock("overload:" . Client::class);
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) use ($rooturl) {
                return $m == "GET"
                    && $u == $rooturl . "/api/v1/settings/testsetting";
            })
            ->andReturnUsing(function () {
                return new class()
                {
                    public function getStatusCode()
                    {
                        return 200;
                    }
                    public function getBody()
                    {
                        return '{"data" : "testresult"}';
                    }
                };
            });


        $target = new TestChatTenentClient($rooturl);

        $act = $target->getSetting("testsetting");

        $this->assertEquals("testresult", $act->data);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_deleteUser_ok()
    {
        $rooturl = "http://www.example.com";

        $mock = Mockery::mock("overload:" . Client::class);
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) use ($rooturl) {
                $vals = [
                    "userId" => "testvalue",
                    "confirmRelinquish" => true,
                ];
                return $m == "POST"
                    && $u == $rooturl . "/api/v1/users.delete"
                    && array_key_exists('json', $o) && $o['json'] == $vals;
            })
            ->andReturnUsing(function () {
                return new class()
                {
                    public function getStatusCode()
                    {
                        return 200;
                    }
                    public function getBody()
                    {
                        return '{"data" : "testresult"}';
                    }
                };
            });


        $target = new TestChatTenentClient($rooturl);

        $act = $target->deleteUser("testvalue");

        $this->assertEquals("testresult", $act->data);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updateUser_ok()
    {
        $rooturl = "http://www.example.com";

        $mock = Mockery::mock("overload:" . Client::class);
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) use ($rooturl) {
                $vals = [
                    "userId" => "testuserid",
                    "data" => [
                        "name" => "testname",
                    ]
                ];
                return $m == "POST"
                    && $u == $rooturl . "/api/v1/users.update"
                    && array_key_exists('json', $o) && $o['json'] == $vals;
            })
            ->andReturnUsing(function () {
                return new class()
                {
                    public function getStatusCode()
                    {
                        return 200;
                    }
                    public function getBody()
                    {
                        return '{"data" : "testresult"}';
                    }
                };
            });


        $target = new TestChatTenentClient($rooturl);

        $arg = new ChatUserProperties();
        $arg->userId("testuserid");
        $udata = new ChatUserDataProperties();
        $udata->name("testname");
        $arg->data($udata);

        $act = $target->updateUser($arg);

        $this->assertEquals("testresult", $act->data);
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_setSiteUrl_ok()
    {
        $rooturl = "http://www.example.com";

        $mock = Mockery::mock("overload:" . Client::class);
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) use ($rooturl) {
                $vals = [
                    "value" => $rooturl,
                ];
                return $m == "POST"
                    && $u == $rooturl . "/api/v1/settings/Site_Url"
                    && array_key_exists('json', $o) && $o['json'] == $vals;
            })
            ->andReturnUsing(function () {
                return new class()
                {
                    public function getStatusCode()
                    {
                        return 200;
                    }
                    public function getBody()
                    {
                        return '{"data" : "testresult"}';
                    }
                };
            });


        $target = new TestChatTenentClient($rooturl);

        $target->setSiteUrl();

        $this->assertTrue(true);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_setOrganization_ok()
    {

        $vals = [
            ChatOrganizationProperties::API_ID_TYPE => 2,
            ChatOrganizationProperties::API_ID_NAME => "組織名",
            ChatOrganizationProperties::API_ID_INDUSTRY => 3,
            ChatOrganizationProperties::API_ID_SIZE => 4,
            ChatOrganizationProperties::API_ID_COUNTRY => "jp",
            ChatOrganizationProperties::API_ID_WEBSITE => "https://www2.example.com",
        ];

        $target = Mockery::mock(TestChatTenentClient::class)->makePartial();
        $called = [];
        $target->shouldReceive("setSetting")
            ->withArgs(function ($id, $val) use (&$called) {
                $called[$id] = $val;
                return true;
            })->times(6)
            ->andReturnUsing(function () {
                return new class()
                {
                    public function getStatusCode()
                    {
                        return 200;
                    }
                    public function getBody()
                    {
                        return '{"data" : "testresult"}';
                    }
                };
            });


        $arg = new ChatOrganizationProperties();
        $arg->type_code($vals[ChatOrganizationProperties::API_ID_TYPE]);
        $arg->name($vals[ChatOrganizationProperties::API_ID_NAME]);
        $arg->industry($vals[ChatOrganizationProperties::API_ID_INDUSTRY]);
        $arg->size($vals[ChatOrganizationProperties::API_ID_SIZE]);
        $arg->country($vals[ChatOrganizationProperties::API_ID_COUNTRY]);
        $arg->website($vals[ChatOrganizationProperties::API_ID_WEBSITE]);

        $target->setOrganization($arg);

        $this->assertEquals($vals, $called);
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_setServer_ok()
    {

        $vals = [
            ChatServerProperties::API_ID_SERVER_TYPE => 2,
            ChatServerProperties::API_ID_SITE_NAME => "テストサイト",
            ChatServerProperties::API_ID_LANGUAGE => "ja",
            ChatServerProperties::API_ID_2FA_EMAIL_AUTO => false,
            ChatServerProperties::API_ID_REGISTER_SERVER => false,
        ];

        $target = Mockery::mock(TestChatTenentClient::class)->makePartial();
        $called = [];
        $target->shouldReceive("setSetting")
            ->withArgs(function ($id, $val) use (&$called) {
                $called[$id] = $val;
                return true;
            })->times(5)
            ->andReturnUsing(function () {
                return new class()
                {
                    public function getStatusCode()
                    {
                        return 200;
                    }
                    public function getBody()
                    {
                        return '{"data" : "testresult"}';
                    }
                };
            });


        $arg = new ChatServerProperties();
        $arg->server_type($vals[ChatServerProperties::API_ID_SERVER_TYPE]);
        $arg->site_name($vals[ChatServerProperties::API_ID_SITE_NAME]);
        $arg->language($vals[ChatServerProperties::API_ID_LANGUAGE]);
        $arg->acconts_2fa_email_auto($vals[ChatServerProperties::API_ID_2FA_EMAIL_AUTO]);
        $arg->register_server($vals[ChatServerProperties::API_ID_REGISTER_SERVER]);

        $target->setServer($arg);

        $this->assertEquals($vals, $called);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_setSmtp_ok()
    {
        $vals = [
            ChatSmtpProperties::PROTOCOL => "smtps",
            ChatSmtpProperties::HOST => "mail.example.com",
            ChatSmtpProperties::PORT => 432,
            ChatSmtpProperties::IGNORE_TLS => false,
            ChatSmtpProperties::POOL => true,
            ChatSmtpProperties::USERNAME => "test@example.com",
            ChatSmtpProperties::PASSWORD => "testpassword",
            ChatSmtpProperties::FROM => "testfrom@example.com",
        ];

        $target = Mockery::mock(TestChatTenentClient::class)->makePartial();
        $called = [];
        $target->shouldReceive("setSetting")
            ->withArgs(function ($id, $val) use (&$called) {
                $called[$id] = $val;
                return true;
            })->times(8)
            ->andReturnUsing(function () {
                return new class()
                {
                    public function getStatusCode()
                    {
                        return 200;
                    }
                    public function getBody()
                    {
                        return '{"data" : "testresult"}';
                    }
                };
            });


        $arg = new ChatSmtpProperties();
        $arg->protocol($vals[ChatSmtpProperties::PROTOCOL]);
        $arg->host($vals[ChatSmtpProperties::HOST]);
        $arg->port($vals[ChatSmtpProperties::PORT]);
        $arg->ignore_tls($vals[ChatSmtpProperties::IGNORE_TLS]);
        $arg->pool($vals[ChatSmtpProperties::POOL]);
        $arg->username($vals[ChatSmtpProperties::USERNAME]);
        $arg->password($vals[ChatSmtpProperties::PASSWORD]);
        $arg->from($vals[ChatSmtpProperties::FROM]);

        $target->setSmtp($arg);

        $this->assertEquals($vals, $called);
    }

    public function test_completeSetupWizard_ok()
    {

        //$target = new TestChatTenentClient($rooturl);
        $target = Mockery::mock(TestChatTenentClient::class)->makePartial();
        $target->shouldReceive("setSetting")
            ->withArgs(function ($id, $val) {
                return $id === "Show_Setup_Wizard" && $val === "complete";
            })->once();

        $target->completeSetupWizard();
    }

    public function test_initSetting()
    {
        //$target = new TestChatTenentClient($rooturl);
        $target = Mockery::mock(TestChatTenentClient::class)->makePartial();
        $target->shouldReceive("setSetting");
        $target->initSetting();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_changeUserActive_ok_true()
    {
        $rooturl = "http://www.example.com";

        $mock = Mockery::mock("overload:" . Client::class);
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) use ($rooturl) {
                $vals = [
                    "userId" => "testuserid",
                    "data" => [
                        "active" => true,
                    ]
                ];
                return $m == "POST"
                    && $u == $rooturl . "/api/v1/users.update"
                    && array_key_exists('json', $o) && $o['json'] == $vals;
            })
            ->andReturnUsing(function () {
                return new class()
                {
                    public function getStatusCode()
                    {
                        return 200;
                    }
                    public function getBody()
                    {
                        return '{"data" : "testresult"}';
                    }
                };
            });


        $target = new TestChatTenentClient($rooturl);

        $act = $target->changeUserActive("testuserid");

        $this->assertIsObject($act);
    }
        /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_changeUserActive_ok_false()
    {
        $rooturl = "http://www.example.com";

        $mock = Mockery::mock("overload:" . Client::class);
        $mock->shouldReceive("request")
            ->withArgs(function ($m, $u, $o) use ($rooturl) {
                $vals = [
                    "userId" => "testuserid",
                    "data" => [
                        "active" => false,
                    ]
                ];
                return $m == "POST"
                    && $u == $rooturl . "/api/v1/users.update"
                    && array_key_exists('json', $o) && $o['json'] == $vals;
            })
            ->andReturnUsing(function () {
                return new class()
                {
                    public function getStatusCode()
                    {
                        return 200;
                    }
                    public function getBody()
                    {
                        return '{"data" : "testresult"}';
                    }
                };
            });


        $target = new TestChatTenentClient($rooturl);

        $act = $target->changeUserActive("testuserid", false);

        $this->assertIsObject($act);
    }


    public function test_stopUser_ok()
    {
        //$target = new TestChatTenentClient($rooturl);
        $target = Mockery::mock(TestChatTenentClient::class)->makePartial();
        $target->shouldReceive("changeUserActive")
            ->withArgs(function ($id, $val) {
                return $id === "testuserid" && $val === false;
            })->once();

        $target->stopUser("testuserid");
    }

    public function test_unStopUser_ok()
    {
        //$target = new TestChatTenentClient($rooturl);
        $target = Mockery::mock(TestChatTenentClient::class)->makePartial();
        $target->shouldReceive("changeUserActive")
            ->withArgs(function ($id, $val) {
                return $id === "testuserid" && $val === true;
            })->once();

        $target->unStopUser("testuserid");
    }


    public function test_initSettingFromFiles_ok_01() {
        $mock = Mockery::mock("overload:" .Storage::class);
        $mock->shouldReceive("disk")->once()->withArgs(function($key){
            return $key === "chat_settings";
        })->andReturn($mock);

        $mock->shouldReceive("files")->once()->andReturn([
            "Email_Header.setting",
            "Email_Footer.setting",
            "Email_Footer.setting.local",
            "Forgot_Password_Email_Subject.setting.local",
            "Forgot_Password_Email_Subject.setting",
        ]);
        $mock->shouldReceive("get")->withArgs(function($key){
            switch($key){
                case "Email_Header.setting";
                case "Email_Footer.setting.local";
                case "Forgot_Password_Email_Subject.setting.local";
                    return true;
                default:
                    return false;
            }
        })->andReturnUsing(function($key){
            switch($key){
                case "Email_Header.setting";
                    return "header1";
                case "Email_Footer.setting.local";
                    return "footer1";
                case "Forgot_Password_Email_Subject.setting.local";
                    return "passwd1";
                default:
                    return false;
            }
        })->times(3);

        $target = Mockery::mock(TestChatTenentClient::class)->makePartial();
        $target->shouldReceive("setSetting")->withArgs(function($key, $val){
            switch($key){
                case "Email_Header":
                    return $val === "header1";
                case "Email_Footer":
                    return $val === "footer1";
                case "Forgot_Password_Email_Subject":
                    return $val === "passwd1";
                default:
                    return false;
            }
        })->times(3);

        $target->initSettingFromFiles();

    }

    // public function test_initSettingFromFiles_ok_02() {

    //     $target = Mockery::mock(TestChatTenentClient::class)->makePartial();
    //     $target->shouldReceive("setSetting")->withArgs(function($key, $val){
    //         switch($key){
    //             case "Email_Header":
    //                 return $val === "テストヘッダ";
    //             case "Email_Footer":
    //                 return $val === "テスト振った";
    //             default:
    //                 return false;
    //         }
    //     })->times(2);

    //     $target->initSettingFromFiles();

    // }
}


class TestChatTenentClient extends ChatTenantClient
{
}
