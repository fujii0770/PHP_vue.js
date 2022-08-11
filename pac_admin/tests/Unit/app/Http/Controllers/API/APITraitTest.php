<?php

namespace Tests\Unit\app\Http\Controllers\API;

use App\Chat\Properties\AbstractProperties;
use App\Http\Controllers\API\APITrait;
use App\Http\Controllers\API\AuthDao;
use App\Http\Controllers\API\AuthException;
use App\Http\Controllers\API\ResponseBody;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class APITraitTest extends TestCase
{


    /**
     * 
     */
    public function test_existsIfSetParam()
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive("has")
            ->andReturnUsing(function($key){
                $ret = false;
                switch ($key) {
                    case "param1":
                        $ret =true;
                        break;
                    case "param2":
                        $ret = true;
                        break;
                }
                return $ret;
            });
        $request->shouldReceive("get")
            ->andReturnUsing(function($key){
                $ret = null;
                switch ($key) {
                    case "param1":
                        $ret = "value1";
                        break;
                    case "param2":
                        $ret = "value2";
                        break;
                }
                return $ret;
            });

        $props = new TestAPITraitProperties();

        $target = new TestAPITraited();

        $this->invokePrivateFunction($target, "existsIfSetParam",[$request, "param1", $props]);
        $this->assertEquals("value1", $props->param1());

        $this->invokePrivateFunction($target, "existsIfSetParam",[$request, "param2", $props]);
        $this->assertEquals("value2", $props->param2());

        $this->invokePrivateFunction($target, "existsIfSetParam",[$request, "param3", $props]);
        $this->assertNull($props->param3());

        $ary = $props->toArray();
        $this->assertArrayHasKey("param1", $ary);
        $this->assertArrayHasKey("param2", $ary);
        $this->assertArrayNotHasKey("param3", $ary);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_auth_ok() {
        $target = new TestAPITraited();

        $mock1 = Mockery::mock("overload:" . AuthDao::class);
        $mock1->shouldReceive("getAuth")->andReturnUsing(function(){
            return (object) [
                "api_name" => "HomePage",
                "access_id" => "testid",
                "access_code" => "testcode",
            ];
        })->once();
        
        $req = new Request(["accessId" => "testid", "accessCode" => "testcode"]);
        $act = $target->test_auth($req);

        $this->assertEquals("testid", $act->access_id);
    }

    public function test_auth_ng_01() {
        $target = new TestAPITraited();

        $this->expectException(AuthException::class);
        
        $req = new Request([]);
        $target->test_auth($req);
    }

    public function test_auth_ng_02() {
        $target = new TestAPITraited();

        $this->expectException(AuthException::class);
        
        $req = new Request(["accessId" => "testid"]);
        $target->test_auth($req);
    }

    
    public function test_auth_ng_03() {
        $target = new TestAPITraited();

        $this->expectException(AuthException::class);
        
        $req = new Request(["accessId" => "testid", "accessCode" => "testcode"]);
        $target->test_auth($req);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_auth_ng_04() {
        $target = new TestAPITraited();

        $this->expectException(AuthException::class);

        $mock1 = Mockery::mock("overload:" . AuthDao::class);
        $mock1->shouldReceive("getAuth")->andReturn(null)->once();
        
        $req = new Request(["accessId" => "testid", "accessCode" => "testcode"]);
        $target->test_auth($req);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_auth_ng_05() {
        $target = new TestAPITraited();

        $this->expectException(AuthException::class);

        $mock1 = Mockery::mock("overload:" . AuthDao::class);
        $mock1->shouldReceive("getAuth")->andReturnUsing(function(){
            return (object) [
                "access_id" => "aa",
                "access_code" => "testcode",
            ];
        })->once();
        
        $req = new Request(["accessId" => "testid", "accessCode" => "testcode"]);
        $target->test_auth($req);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_auth_ng_06() {
        $target = new TestAPITraited();

        $this->expectException(AuthException::class);

        $mock1 = Mockery::mock("overload:" . AuthDao::class);
        $mock1->shouldReceive("getAuth")->andReturnUsing(function(){
            return (object) [
                "access_id" => "testid",
                "access_code" => "bb",
            ];
        })->once();
        
        $req = new Request(["accessId" => "testid", "accessCode" => "testcode"]);
        $target->test_auth($req);
    }
}


class TestAPITraited
{
    use APITrait;

    public function test_responseException(Exception $e) {
        return $this->responseException($e);
    }

    public function test_responseJson(int $status, ResponseBody $body) {
        return $this->responseJson($status, $body);
    }

    public function test_response(Request $request, Closure $parser, Closure $invoker, $no_auth = false) 
    {
        return $this->response($request, $parser, $invoker, $no_auth);
    }

    public function test_auth(Request $request, $key = null) 
    {
        return $this->auth($request, $key);
    }

}

class TestAPITraitProperties extends AbstractProperties {

    public function param1(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function param2(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function param3(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    
}
