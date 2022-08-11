<?php

namespace App\Http\Controllers\API;

use App\Chat\Exceptions\ChatException;
use App\Chat\Properties\AbstractProperties;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Null_;

trait APITrait
{

    /**
     * レスポンス
     * @param $result_data
     * @param $result_code
     * @param $result_message
     * @return \Illuminate\Http\JsonResponse
     */
    private function responseOk(ResponseBody $body)
    {
        return $this->responseJson(200, $body);
    }

    // private static function ifval($value, $expect, $then = "")
    // {
    //     $ret = $value;
    //     if ($value === $expect) {
    //         $ret = $then;
    //     }
    //     return $ret;
    // }

    // private static function ifemp($value, $then = "")
    // {
    //     $ret = $value;
    //     if (empty($value)) {
    //         $ret = $then;
    //     }
    //     return $ret;
    // }

    // private static function ifnull($value, $then = "")
    // {
    //     return self::ifval($value, null, $then);
    // }

    private function responseException(Exception $exception)
    {
        $status = 200; // ?
        if ($exception instanceof AuthException) {
            $status = 401;
        }

        $msg = null;
        $data = [];
        if ($exception instanceof PacException) {
            $msg = $exception->getDescribe();
        }
        if ($exception instanceof ChatException) {
            $data = $exception->getResult();
        }
        if (empty($msg)) {
            $msg = __('api.fail.system_error');
        }

        Log::debug($msg.$exception->getMessage() . $exception->getTraceAsString());

        $body = new ResponseBody($data, $msg, 0);
        return $this->responseJson($status, $body);
    }

    private function responseJson(int $status, ResponseBody $body)
    {
        $res = [
            'result_code' => $body->getCode(),
            'result_message' => $body->getMessage()
        ];
        $dt = [];
        $d = $body->getData();
        if (is_array($d)) {
            $dt = $d;
        } else if ($d === null) {
            $dt = [];
        } else {
            $dt = [$d];
        }
        $res['result_data'] = $dt;

        return response()->json($res)->setStatusCode($status);
    }

    // private function responseNoParse(Request $request, Closure $invoker)
    // {
    //     $this->response($request, function () use ($request) {
    //         return $request;
    //     }, $invoker);
    // }

    // private function responseNoParam(Request $request, Closure $invoker)
    // {
    //     $this->responseNoParse($request, $invoker);
    // }


    /**
     * @param Request $request
     * @param Closure $parser 引数の検証と変換を行う関数（引数は $request 戻り値は $invoder に渡す引数の配列）
     * @param Closure $invoder リクエストに対する処理を行う関数
     *                      （引数は $request の戻り値を展開、戻り値は データまたはResponseBodyのインスタンス）
     * @param Closure $no_auth 認証が不要な場合に true, 省略時は false.
     */
    private function response(Request $request, Closure $parser, Closure $invoker, $no_auth = false)
    {
        $ret = null;
        try {
            if (!$no_auth) {
                $this->auth($request);
            }
            $param = null;
            $param = $parser($request);
            if (!is_array($param)) {
                $param = [$param];
            }
            $res = $invoker(...$param);
            if (!($res instanceof ResponseBody)) {
                $res = new ResponseBody($res);
            }
            $ret = $this->responseOK($res);
        } catch (Exception $e) {
            Log::error($e);
            if ($e instanceof HttpException) {
                $ret = response("", $e->getCode());
            } else {
                $ret = $this->responseException($e);
            }
        }
        return $ret;
    }

    /**
     *
     */
    private function auth(Request $request, $key = null)
    {
        if ($key === null) {
            $key = $this->getAuthKey();
        }

        //アクセス認証
        if (!$request->has('accessId')) {
            throw new AuthException("AccessId was not found.");
        }
        if (!$request->has('accessCode')) {
            throw new AuthException("AccessCode was not found.");
        }

        $dao = new AuthDao();
        $api_authority = $dao->getAuth($key);
        if (!$api_authority) {
            throw new AuthException("$key was not found in api_authentication");
        }

        if ($api_authority->access_id != $request->accessId) {
            throw new AuthException("ID of DB and ID of Request did not match.");
        }

        if ($api_authority->access_code != $request->accessCode) {
            throw new AuthException("Code of DB and Code of Request did not match.");
        }

        return $api_authority;
    }

    protected function getAuthKey()
    {
        return "HomePage";
    }

    /**
     * 必須リクエストパラメータを取得します。
     *
     * @return mixed|array パラメータの値. 引数と同じ順番で配列に格納します。
     *
     * @throws ParameterNotFoundException パラメータが存在しない場合の例外
     */
    private function getRequiredParameters(Request $request, ...$parameterKeys)
    {
        $ret = [];
        $mx = count($parameterKeys);
        $ngKeys = [];
        for ($i = 0; $i < $mx; $i++) {
            $key = $parameterKeys[$i];
            $ok = $this->checkRequiredParameter($request, $key);
            if ($ok){
                $ret[] = $request->get($key);
            } else {
                $ret[] = null;
                $ngKeys[] = $key;
            }
        }
        if (count($ngKeys) > 0) {
            $keystr = "";
            foreach($ngKeys as $nk){
                $keystr .= ",".$nk;
            }
            $keystr = substr($keystr, 1);
            $th = new ParameterNotFoundException($keystr);
            $th->key = $ngKeys;
            throw $th;
        }

        return $ret;
    }

    private function getRequiredParameter(Request $request, string $key)
    {
        $ret = null;
        if ($this->checkRequiredParameter($request, $key)) {
            $ret = $request->get($key);
        } else {
            $th = new ParameterNotFoundException($key);
            $th->key = $key;
            throw $th;
        }
        return $ret;
    }

    private function checkRequiredParameter(Request $request, string $key)
    {
        $ok = $request->has($key);
        $val = null;
        if ($ok) {
            $val = $request->get($key);
            $ok = $val !== null;
            if ($ok && is_string($val)) $ok = trim($val) !== "";
            if ($ok && is_array($val)) $ok = count($val) > 0;
        }
        return $ok;
    }

    private function getParam(Request $request, string $parameterKey, $default = null)
    {
        return $this->getParameter($request, $parameterKey, $default);
    }

    private function getParameter(Request $request, string $parameterKey, $default = null)
    {
        $ret = null;
        if ($request->has($parameterKey)) {
            $ret = $request->get($parameterKey);
            if (empty($ret)) {
                $ret = $default;
            }
        }
        return $ret;
    }

    private function existsIfSetParam(
        Request $request,
        string $parameterKey,
        AbstractProperties $props,
        String $propname = null
    ) {
        $ret = null;
        if ($request->has($parameterKey)) {
            $ret = $request->get($parameterKey);
            if ($propname === null) {
                $propname = $parameterKey;
            }
            $props->$propname($ret);
        }
        return $ret;
    }
}
