<?php

namespace Tests\Unit\app\Chat;

use Tests\TestCase;

use App\Chat\ChatRepository;
use App\Chat\ChatTrait;
use App\Chat\Exceptions\DataAccessException;
use App\Chat\Exceptions\ExistsSubdomainException;
use App\Chat\Exceptions\InvalidSubdomainFormatException;
use App\Chat\Exceptions\UnknownValueException;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;
use Mockery;
use ReflectionClass;

class ChatTraitTest extends TestCase
{


    /**
     *
     */
    public function test_app_env()
    {
        $target = new TestChatTraited();

        $act = $this->invokePrivateFunction($target, "app_env");

        $this->assertEquals(0, $act);
    }

    /**
     *
     */
    public function test_contract_app()
    {
        $target = new TestChatTraited();

        $act = $this->invokePrivateFunction($target, "contract_app");

        $this->assertEquals(1, $act);
    }

    /**
     *
     */
    public function test_contract_server()
    {
        $target = new TestChatTraited();

        $act = $this->invokePrivateFunction($target, "contract_server");

        $this->assertEquals(0, $act);
    }
}


class TestChatTraited
{
    use ChatTrait;
}
