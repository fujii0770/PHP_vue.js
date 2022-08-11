<?php

namespace Tests\Unit\app\Chat;

use Tests\TestCase;

use App\Chat\ChatRepository;
use App\Chat\ChatTrait;
use App\Chat\ChatUtils;
use Closure;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Mockery;
use ReflectionClass;

class ChatUtilsTest extends TestCase
{

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_makeHttpClient()
    {
        $act = ChatUtils::makeHttpClient();

        $this->assertTrue($act instanceof Client);
        $this->assertEquals(123, $act->getConfig("timeout"));
        $this->assertEquals(456, $act->getConfig("connect_timeout"));
        $this->assertFalse($act->getConfig("http_errors"));
        $this->assertFalse($act->getConfig("verify"));
    }

    public function test_transaction_commit()
    {
        DB::shouldReceive("beginTransaction")->once();
        DB::shouldReceive("commit")->once();
        DB::shouldReceive("rollback")->never();

        ChatUtils::transaction(function () {
        });
    }

    public function test_transaction_rollback()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(0);

        DB::shouldReceive("beginTransaction")->once();
        DB::shouldReceive("rollback")->once();
        DB::shouldReceive("commit")->never();

        ChatUtils::transaction(function () {
            throw new Exception("", 1231231);
        });
    }

    public function test_constract_ng()
    {
        $this->expectError();
        new ChatUtils();
    }

    public function test_private_constract_ok()
    {
        $this->assertIsObject($this->superNewInstanceArgs(ChatUtils::class));
    }

    private function superNewInstanceArgs($class, array $arguments = [])
    {
        $object = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        call_user_func(Closure::bind(
            function () use ($arguments) {
                call_user_func_array([$this, '__construct'], $arguments);
            },
            $object,
            $object
        ));
        return $object;
    }
}
