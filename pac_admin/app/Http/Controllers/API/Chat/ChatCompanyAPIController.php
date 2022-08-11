<?php

namespace App\Http\Controllers\API\Chat;

use App\Chat\ChatService;
use App\Chat\ChatUtils;
use App\Chat\Exceptions\ChatException;
use App\Chat\Properties\AbstractProperties;
use App\Chat\Properties\ChatServerInfoProperties;
use App\Http\Controllers\API\APITrait;
use App\Http\Controllers\API\HttpException;
use App\Http\Controllers\API\InvalidParameterException;
use App\Http\Controllers\API\ParameterNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Authority;
use App\Models\Constraint;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatCompanyAPIController extends Controller
{
    use APITrait;

    private $modelAuthority;
    private $constraint;
    private $operation_user = "system"; // TODO

    public function __construct(Constraint $constraint, Authority $modelAuthority)
    {
        parent::__construct();
        $this->constraint = $constraint;
        $this->modelAuthority  = $modelAuthority;
    }



    /**
     * テナントチャットサーバーの構築
     *
     * ### Request
     * | パラメーター名      | 概要             | 必 | 備考                             |
     * | ------------------- | ---------------- | -- | -------------------------------- |
     * | accessId            | 認証ID           | 必 |                                  |
     * | accessCode          | 認証パスワード   | 必 |                                  |
     * | domainid            | 企業ID           | 必 | mst_company.id                   |
     * | chat_flg            | 契約状態         | 必 | [0,1]　※無効/有効               |
     * | chat_trial_flg      | トライアル状態   | 必 | [0,1]　※非トライアル/トライアル |
     * | trial_start_date    | トライアル開始日 |    | yyyymmdd（未設定の場合当日日付） |
     * | trial_end_date      | トライアル終了日 |    | yyyymmdd                         |
     * | contract_start_date | 契約開始日       |    | yyyymmdd（未設定の場合当日日付） |
     * | contract_end_date   | 契約終了日       |    | yyyymmdd                         |
     * | user_max_limit      | 利用上限人数     | 必 |                                  |
     * | domain              | 希望ドメイン     | 必 | チャットのサブドメイン部分       |
     * | storage_max_limit   | 保管サイズ上限   | 必 | 数値のみ（1-9999）               |
     * | contract_type       | 契約種別         | 必 | [0,1,2]　※standard/business/pro |
     * | callback_url        | コールバックURL  |    | 契約サイトへコールバックするURL  |
     *
     * ### Response Data
     * Nothing
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function insertChatInfo(Request $request)
    {
        $parser = function (Request $request) {
            $req = $request;
            [
                $company_id,
                $chat_flg,
                $chat_trial_flg,
                $user_max_limit,
                $domain,
                $storage_max_limit,
                $contract_type
            ] = $this->getRequiredParameters(
                $req,
                'domainid',
                'chat_flg',
                'chat_trial_flg',
                'user_max_limit',
                'domain',
                'storage_max_limit',
                'contract_type'
            );

            $ret = new ChatServerInfoProperties(true);
            $ret->company_id($company_id);
            $ret->is_contract($chat_flg);
            $ret->is_trial($chat_trial_flg);
            $ret->trial_start_date($this->getParamToDate($req, 'trial_start_date'));
            $ret->trial_end_date($this->getParamToDate($req, 'trial_end_date'));
            $ret->contract_start_date($this->getParamToDate($req, 'contract_start_date'));
            $ret->contract_end_date($this->getParamToDate($req, 'contract_end_date'));
            $ret->user_max($user_max_limit);
            $ret->server_name($domain);
            $ret->storage_max_mega($storage_max_limit);
            $ret->plan($contract_type);
            $ret->callback_url($this->getParam($req, "callback_url"));

            return $ret;
        };

        $opeuser = $this->operation_user;
        $invoker = function (ChatServerInfoProperties $props) use ($opeuser) {
            $svc = new ChatService();
            $svc->createServer($props);

            $coid = $props->company_id();
            ChatUtils::transaction(function() use($coid, $opeuser){
                $this->modelAuthority->initDefaultValueTalk($coid, $opeuser);
            });
        };

        return $this->response(
            $request,
            $parser,
            $invoker
        );
    }

    /**
     *
     */
    private function getParamToDate(Request $request, $key, $default = null){
        $ret = $this->getParam($request, $key, $default);
        if ($ret !== null) {
            $ret = self::toDate($ret);
            if ($ret === false) {
                throw new InvalidParameterException();
            }
        }
        return $ret;
    }

    /**
     *
     */
    private static function toDate(string $value) {
        return self::_toDate(["Ymd","Y-n-j","Y/n/j", "Y.n.j"], $value);
    }

    /**
     *
     */
    private static function _toDate(array $formats, string $value) {
        $ret = false;
        foreach($formats as $fmt) {
            $ret = DateTime::createFromFormat($fmt, $value);
            if ($ret !== false) break;
        }
        return $ret;
    }

    private static function dateToString(DateTime $date = null) {
        $ret = "";
        if ($date !== null) {
            $ret = date("Ymd", $date->getTimestamp());
        }
        return $ret;
    }

    /**
     *
     */
    private function existsIfSetDateParam(
        Request $request,
        string $parameterKey,
        AbstractProperties $props,
        String $propname = null
    ) {
        $ret = null;
        if ($request->has($parameterKey)) {
            $ret = $this->getParamToDate($request, $parameterKey);
            if ($propname === null) {
                $propname = $parameterKey;
            }
            $props->$propname($ret);
        }
        return $ret;
    }

    /**
     *
     * ### Request
     * | パラメーター名          | 概要                     | 必 | 備考                             |
     * | ----------------------- | ------------------------ | -- | -------------------------------- |
     * | accessId                | 認証ID                   | 必 |                                  |
     * | accessCode              | 認証パスワード           | 必 |                                  |
     * | domainid                | 企業ID                   | 必 | mst_company.id                   |
     * | chat_flg                | 契約状態                 |    | [0,1]　※無効/有効               |
     * | chat_trial_flg          | トライアル状態           |    | [0,1]　※非トライアル/トライアル |
     * | trial_start_date        | トライアル開始日         |    | yyyymmdd（未設定の場合当日日付） |
     * | trial_end_date          | トライアル終了日         |    | yyyymmdd                         |
     * | contract_start_date     | 契約開始日               |    | yyyymmdd（未設定の場合当日日付） |
     * | contract_end_date       | 契約終了日               |    | yyyymmdd                         |
     * | user_max_limit          | 利用上限人数             |    |                                  |
     * | storage_max_limit       | 保管サイズ上限           |    | 数値のみ（1-9999）               |
     * | contract_type           | 契約種別                 |    | [0,1,2]　※standard/business/pro |
     * | version                 | version                  | 必 | 排他処理用バージョン番号          |
     *
     * ### Response Data
     * Nothing
     *
     */
    public function updateChatInfo(Request $request)
    {
        Log::debug("updateContractInfo" . $request);
        $parser = function (Request $request) {
            $req = $request;
            [$company_id, $version] = $this->getRequiredParameters(
                $req,
                'domainid',
                'version'
            );

            $ret = new ChatServerInfoProperties(true);
            $ret->company_id($company_id);
            $ret->version($version);

            $this->existsIfSetParam($req, 'domain', $ret, "server_name");
            $this->existsIfSetParam($req, 'chat_flg', $ret, "is_contract");
            $this->existsIfSetParam($req, 'chat_trial_flg', $ret, "is_trial");

            $this->existsIfSetDateParam($req, 'trial_start_date', $ret);
            $this->existsIfSetDateParam($req, 'trial_end_date', $ret);
            $this->existsIfSetDateParam($req, 'contract_start_date', $ret);
            $this->existsIfSetDateParam($req, 'contract_end_date', $ret);
            $this->existsIfSetParam($req, 'user_max_limit', $ret, "user_max");
            $this->existsIfSetParam($req, 'storage_max_limit', $ret, "storage_max_mega");

            return $ret;
        };

        $invoker = function (ChatServerInfoProperties $props) {
            $svc = new ChatService();
            $svc->updateServerInfo($props);
        };

        return $this->response(
            $request,
            $parser,
            $invoker
        );
    }

    /**
     * 指定した企業の契約情報（トライアル、本契約）を取得する
     *
     * ### Request
     * | パラメーター名     | 概要             | 必 | 備考             |
     * | ------------------ | ---------------- | -- | ---------------- |
     * | accessId           | 認証ID           | 必 |                  |
     * | accessCode         | 認証パスワード   | 必 |                  |
     * | domainid           | 企業ID           | 必 | mst_company.id   |
     * | * domain           | ドメイン名       |    |                  |
     *
     * ### Response Data
     * |  パラメータ名       | 概要             | 備考                             |
     * | ------------------- | ---------------- | -------------------------------- |
     * | chat_flg            | 契約状態         | [0,1]　※無効/有効               |
     * | chat_trial_flg      | トライアル状態   | [0,1]　非トライアル/トライアル   |
     * | trial_start_date    | トライアル開始日 | yyyymmdd                         |
     * | trial_end_date      | トライアル終了日 | yyyymmdd                         |
     * | contract_start_date | 契約開始日       | yyyymmdd                         |
     * | contract_end_date   | 契約終了日       | yyyymmdd                         |
     * | user_max_limit      | 利用上限人数     |                                  |
     * | domain              | 希望ドメイン     |                                  |
     * | url                 | 接続用URL        |                                  |
     * | storage_max_limit   | 保管サイズ上限   |     数値のみ（1-9999）           |
     * | contract_type       | 契約種別         | [0,1,2]　※standard/business/pro |
     * | version             | version          | 排他処理用バージョン番号 |
     */
    public function getChatinfo(Request $request)
    {
        Log::debug("getChatinfo" . $request);

        $parser = function (Request $request) {
            $company_id = $this->getRequiredParameter($request, "domainid");
            $server_name = $this->getParameter($request, "domain");
            return [$company_id, $server_name];
        };

        $invoker = function ($company_id, $server_name) {
            $svc = new ChatService();
            $info = $svc->getServerInfo($company_id, $server_name);

            $data = [
                "chat_flg" => $info->is_contract() ? 1 : 0,
                "chat_trial_flg" => $info->is_trial() ? 1 : 0,
                "trial_start_date" => self::dateToString($info->trial_start_date()),
                "trial_end_date" => self::dateToString($info->trial_end_date()),
                "contract_start_date" => self::dateToString($info->contract_start_date()),
                "contract_end_date" => self::dateToString($info->contract_end_date()),
                "user_max_limit" => $info->user_max(),
                "domain" => $info->server_name(),
                "url" => $info->server_url(),
                "storage_max_limit" => $info->storage_max_mega(),
                "contract_type" => $info->plan(),
                "version" => $info->version()
                // "user_count" => $info->user_count(),
                // "user_count_details" => $info->user_count_detail(),
            ];
            return $data;
        };

        return $this->response(
            $request,
            $parser,
            $invoker
        );

    }

    /**
     * RocketChatサブドメイン存在チェック
     *
     * ### Request
     * | パラメーター名     | 概要             | 必 | 備考             |
     * | ------------------ | ---------------- | -- | ---------------- |
     * | accessId           | 認証ID           | 必 |                  |
     * | accessCode         | 認証パスワード   | 必 |                  |
     * | domainName         | サブドメイン     | 必 |                  |
     *
     *
     * ### Response Data
     * |  パラメータ名       | 概要             | 備考                             |
     * | ------------------- | ---------------- | -------------------------------- |
     * | subdomain_flg       | サブドメイン利用 | [0,1]　利用不可/利用可           |
     *
     */
    public function getChatSubDomain(Request $request)
    {
        Log::debug("getChatSubDomain" . $request);

        $parser = function (Request $request) {
            return $this->getRequiredParameter($request, "domainName");
        };

        $invoker = function ($server_name) {
            $svc = new ChatService();
            $is_available = false;
            try {
                $is_available = $svc->checkAvailableServerName($server_name);
            } catch (ChatException $e) {
                throw $e->setResult(["subdomain_flg" => $is_available ? 1 :0]);
            }
            return ["subdomain_flg" => $is_available ? 1 :0];
        };

        return $this->response(
            $request,
            $parser,
            $invoker
        );

    }

    /**
     * 【非公開】チャットサーバーからの初期化処理受付用
     *
     * ### Request
     * | パラメーター名 | 概要               | 必 | 備考             |
     * | -------------- | ------------------ | -- | ---------------- |
     * | a              | ワンタイムトークン | 必 |                  |
     * | b              | テナントキー       | 必 |                  |
     *
     *
     */
    public function initServer(Request $request)
    {
        Log::debug("initServer" . $request);
        $parser = function (Request $request) {
            return $this->getRequiredParameters($request, "b", "a");
        };

        $invoker = function ($tkey, $token) {
            $svc = new ChatService();
            try {
                $svc->initServer($tkey, $token);
            }catch(Exception $e) {
                throw new HttpException("", 400, $e);
            }
            return [];
        };

        return $this->response(
            $request,
            $parser,
            $invoker,
            true
        );
    }

}
