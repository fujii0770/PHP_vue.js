<?php

namespace App\Chat;

use App\Chat\Consts\ChatIdServerReturnCode;
use App\Chat\Exceptions\DataAccessException;
use App\Chat\Exceptions\ExistsSubdomainException;
use App\Chat\Exceptions\FailedHttpAccessException;
use App\Chat\Exceptions\InvalidSubdomainFormatException;
use App\Chat\Exceptions\UnknownValueException;
use App\Chat\Properties\ChatTenantProperties;
use App\Http\Utils\IdAppApiUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;

class ChatIdServerClient
{
    use ChatTrait;

    private $cluster_arn = null;
    private $mongo_url = null;
    private $image_arn = null;
    private $container_name = null;

    private $props = null;

    /**
     *
     * @return bool
     *
     * @throws InvalidSubdomainFormatException
     * @throws ExistsSubdomainException
     * @throws UnknownValueException
     * @throws DataAccessException
     */
    public function checkAvailableServerName($server_name)
    {
        $result = $this->requestIdServer("GET", "sasattotalk.available", [
            RequestOptions::QUERY => [
                'subdomain' => $server_name
            ]
        ]);

        $this->checkResponseBodyCode($result);
        return true;
    }

    /**
     * チャットサーバーの作成予約を行い、チャットサーバーの作成に必要な情報を取得します。
     *
     * @return ChatTenantProperties
     *
     * @throws InvalidSubdomainFormatException
     * @throws ExistsSubdomainException
     * @throws UnknownValueException
     * @throws DataAccessException
     */
    public function reserveServerName($server_name, $company_id, $company_name, $plan)
    {

        $result = $this->requestIdServer("POST", 'sasattotalk.reserve', [
            RequestOptions::JSON => [
                'subdomain' => $server_name,
                'company_id' => $company_id,
                'company_name' => $company_name,
                'app_env' => $this->app_env(),
                'contract_app' => $this->contract_app(),
                'contract_server' => $this->contract_server(),
                'plan' => $plan
            ]
        ]);

        $res = $result["data"];
        $ret = new ChatTenantProperties();

        $ret->cluster_arn($res["cluster_arn"]);
        $ret->image($res["image"]);
        $ret->mongo_url($res["mongo"]);
        $ret->tenant_key($res["tenant_key"]);
        $ret->server_url($res["server_url"]);
        $ret->company_id($res["company_id"]);
        $ret->server_name($res["subdomain"]);

        return $ret;
    }

    public function updateServerInfo(ChatTenantProperties $props)
    {

        $req = [
            'subdomain' => $props->server_name(),
            'company_id' => $props->company_id(),
            'app_env' => $this->app_env(),
            'contract_app' => $this->contract_app(),
            'contract_server' => $this->contract_server(),
        ];

        $list = [
            "status" => $props->status(),
            "service_arn" => $props->service_arn(),
            "task_definition_arn" => $props->task_definition(),
            "admin_id" => $props->admin_id(),
            "admin_token" => $props->admin_token()
        ];

        foreach ($list as $key => $val) {
            if ($val !== null) {
                if (is_string($val) && $val === "") {
                    continue;
                }
                $req[$key] = $val;
            }
        }

        $this->requestIdServer("POST", 'sasattotalk.update', [
            RequestOptions::JSON => $req
        ]);

    }


    /**
     *
     */
    protected function requestIdServer($method, $uri, array $options = [], $retrunResponse = false)
    {
        $client = $this->getIdClient();
        Log::debug("Request : [ $method ] $uri : " . var_export($options, true));
        $res = $client->request($method, $uri, $options);
        $code = $res->getStatusCode();
        if ($code != 200) {
            $msg = __("api.fail.chat.exservice_api_call", ["service"=>__("api.columns.pac_id_api"), "uri"=>$uri, "code"=>$code]);
            $th = new DataAccessException("", -1, new FailedHttpAccessException($msg, $code));
            Log::error($msg. " ". $th->getDescribeErrorCode());
            throw $th;
        }

        $ret = null;
        if ($retrunResponse) {
            $ret = $res;
        } else {
            $ret = json_decode((string) $res->getBody(), true);
            $this->checkResponseBodyCode($ret);
        }
        return $ret;
    }

    /**
     *
     * @return \GuzzleHttp\Client
     *
     * @throws DataAccessException
     */
    protected function getIdClient()
    {
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            throw new DataAccessException('Cannot connect to ID App', -1);
        }
        return $client;
    }



    private function checkResponseBodyCode($result)
    {
        $this->requiredKey("success", $result);
        $data = $this->requiredKey("data", $result);;
        $code = $this->requiredKey("code", $data);
        $msg = array_key_exists("message", $result) ? $result["message"] : "";
        switch ($code) {
            case ChatIdServerReturnCode::OK:
                $ret = true;
                break;
            case ChatIdServerReturnCode::CHAT_SUBDOMAIN_INVALID_FORMAT:
                throw new InvalidSubdomainFormatException($msg);
            case ChatIdServerReturnCode::CHAT_SUBDOMAIN_ALREADY_IN_USE:
                throw new ExistsSubdomainException($msg);
            default:
                Log::warning($msg. ":code=$code");
                throw new UnknownValueException($msg);
        }
    }

    private function requiredKey($key, $result)
    {
        if (!array_key_exists($key, $result)) {
            throw new DataAccessException("The key \"$key\" required for the array does not exist.", -1);
        }
        return $result[$key];
    }
}
