<?php

namespace Tests\Feature\app\Chat;

use App\Chat\ChatEcsClient;
use Tests\TestCase;

use App\Chat\ChatRepository;
use App\Chat\ChatService;
use App\Chat\ChatTenantClient;
use App\Chat\Properties\ChatServerInfoProperties;
use App\Chat\Properties\ChatSmtpProperties;
use App\Chat\Properties\ChatUserDataProperties;
use App\Chat\Properties\ChatUserProperties;
use App\Http\Controllers\API\Chat\ChatCompanyAPIController;
use App\Models\Company;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;

class ChatTenantClientTest extends TestCase
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
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_loginServer()
    {
        // target
        $target = new ChatTenantClient($this->url);
        $target->loginServer($this->admin_name, $this->admin_pass);

        // assert
        $this->assertIsString($target->auth_token());
        $this->assertIsString($target->auth_id());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
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
            // exec
            $act = $target->createUser($user);
            $this->assertIsString($act->_id);

            $msg = "★cuser $uname = " . $act->_id;
            echo $msg;
            Log::debug($msg);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }


    public function test_setSmtp()
    {
        // smpt
        $smtp = new ChatSmtpProperties();
        $smtp->protocol("smtp");
        $smtp->host("smtp.example.com");
        $smtp->port("456");
        $smtp->ignore_tls(false);
        $smtp->pool(false);
        $smtp->username("username@example.com");
        $smtp->password("testpassword");
        $smtp->from("from@example.com");

        // target
        try {
            $target = new ChatTenantClient($this->url, $this->auth_id, $this->auth_token);
            $target->setSmtp($smtp);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function test_setSetting(){
        try {
            $target = new ChatTenantClient($this->url, $this->auth_id, $this->auth_token);
            $target->setSetting("Accounts_RegistrationForm", false);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function test_initSetting(){
        try {
            $target = new ChatTenantClient($this->url, $this->auth_id, $this->auth_token);
            $target->password($this->admin_pass);
            $target->initSetting();
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function test_initSettingFromFiles(){
        try {
            $target = new ChatTenantClient($this->url, $this->auth_id, $this->auth_token);
            $target->password($this->admin_pass);
            $target->initSettingFromFiles();
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function test_restartServer(){
        try {
            $target = new ChatTenantClient($this->url, $this->auth_id, $this->auth_token);
            $target->password($this->admin_pass);
            $res = $target->restartServer();
            echo "\n".var_export($res, true),"\n";
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
