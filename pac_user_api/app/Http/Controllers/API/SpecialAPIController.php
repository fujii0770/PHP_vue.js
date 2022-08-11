<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\SearchSpecialAPIRequest;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\SpecialApiUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Http\Utils\TemplateUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Session;
use Response;
use Symfony\Component\VarDumper\Cloner\Data;

/**
 * Class SpecialAPIController
 * @package App\Http\Controllers\API
 */

class SpecialAPIController extends AppBaseController
{

    public function __construct()
    {

    }

    /**
     * Display a listing of the CircularUser.
     * GET|HEAD /circularUsers
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $user        = $request->user();
        $page        = 1;
        $limit       = AppUtils::normalizeLimit($request->get('limit', 10), 10);

        try{
            //地域リストの取得
            $regionList = DB::table('mst_region')
                ->select(DB::raw('region_name,region_id'))
                ->pluck('region_name','region_id')->toArray();

            // api呼出
            // SRS-014 連携承認済み会社取得
            $client = SpecialApiUtils::getAuthorizeClient();
            if (!$client) {
                Log::error(__('message.false.auth_client'));
            }
            $response = $client->post("/sp/api/get-approved-company", [
                RequestOptions::JSON => [
                    'company_id' => $user->mst_company_id,
                    "env_flg"=>config('app.server_env'),
                    "edition_flg"=>config('app.edition_flg'),
                    "server_flg"=>config('app.server_flg'),
                ]
            ]);
            if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK) {
                $response_body = json_decode((string)$response->getBody());
                $data = $response_body['result']['companies'];
                foreach ($data as $item){
                    foreach ($regionList as $key => $regionItem){
                        if($item['region_name'] == $key){
                            $item['region_id'] = $key;
                            $item['region_name'] = $regionItem;
                        }
                    }
                }
                $data = new LengthAwarePaginator(array_slice( $data, ($page - 1) * $limit, $limit, false), count($data), $limit);
                $data->setPath($request->url());
                $data->appends(request()->input()); // sort params etc
            }else{
                Log::error(__('message.false.get_approved_company.bad_request', ['company_id' => $user->mst_company_id]));
                Log::error($response->getBody());
            }

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse(['data' => $data], __('message.success.data_get', ['attribute'=>'受取連携会社']));
    }

    /**
     * 受信一覧リスト画面初期化
     *
     * @param SearchSpecialAPIRequest $request
     * @return mixed
     */
    public function indexReceived(SearchSpecialAPIRequest $request){

        $user        = $request->user();
        $group_name  = $request->get('group_name');
        $region_name = $request->get('region_name');
        $orderBy     = $request->get('orderBy');
        $orderDir    = $request->get('orderDir');
        $page        = $request->get('page', 1);
        $limit       = AppUtils::normalizeLimit($request->get('limit', 10), 10);

        try{
            $result = [];
            //地域リストの取得
            $regionNameList = DB::table('mst_region')
                ->select(DB::raw('region_name,region_id'))
                ->get()
                ->toArray();
            // api呼出
            // SRS-014 連携承認済み会社取得
            $client = SpecialApiUtils::getAuthorizeClient();
            if (!$client) {
                throw new \Exception('Cannot connect to Env Api');
            }
            $response = $client->post("/sp/api/get-approved-company", [
                RequestOptions::JSON => [
                    'company_id' => $user->mst_company_id,
                    "env_flg"=>config('app.server_env'),
                    "edition_flg"=>config('app.edition_flg'),
                    "server_flg"=>config('app.server_flg'),
                ]
            ]);
            if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                $response_body = json_decode($response->getBody());
                $data = $response_body->result->companies;
                if($group_name == "" && $region_name == ""){
                    $result = $data;
                }else{
                    foreach ($data as $item){
                        if($group_name != "" && $region_name != ""){
                            if(stristr($item->group_name, $group_name) !== false && stristr($item->region_name, $region_name) !== false){
                                array_push($result, $item);
                            }
                        }else{
                            if($group_name != ""){
                                if(stristr($item->group_name, $group_name) !== false){
                                    array_push($result, $item);
                                }
                            }
                            if($region_name != ""){
                                if(stristr($item->region_name, $region_name) !== false){
                                    array_push($result, $item);
                                }
                            }
                        }
                    }
                }
                if(count($result) > 0 && $orderBy != "") {
                    if($orderBy == "group_name"){
                        foreach ($result as $item) {
                            $val[]= $item->group_name;
                        }
                    }
                    if($orderBy == "region_name"){
                        foreach ($result as $item) {
                            $val[]= $item->region_name;
                        }
                    }
                    if(strtolower($orderDir)=="asc"){
                        array_multisort($val,SORT_ASC,SORT_STRING,$result);
                    }else{
                        array_multisort($val,SORT_DESC,SORT_STRING,$result);
                    }
                }
                $result = new LengthAwarePaginator(array_slice( $result, ($page - 1) * $limit, $limit, false), count($result), $limit);
                $result->setPath($request->url());
                $result->appends(request()->input()); // sort params etc
            }else{
                Log::error(__('message.false.get_approved_company.bad_request', ['company_id' => $user->mst_company_id]));
                Log::error($response->getBody());
            }

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse(['data' => $result, 'regionList' => $regionNameList], __('message.success.data_get', ['attribute'=>'受取連携会社']));
    }

    /**
     * 文書一覧リスト画面初期化
     *
     * @param SearchSpecialAPIRequest $request
     * @return mixed
     */
    public function indexTemplate(SearchSpecialAPIRequest $request){

        $user        = $request->user();
        $page        = $request->get('page', 1);
        $limit       = AppUtils::normalizeLimit($request->get('limit', 10), 10);

        try{
            $user_info = DB::table('mst_user_info')
                ->where('mst_user_id', $user->id)->first();

            if(!$user_info) {
                return $this->sendError('Permission denied.',403);
            }

            // api呼出
            // SRS-010 テンプレート文書取得
            $client = SpecialApiUtils::getAuthorizeClient();
            if (!$client) {
                throw new \Exception('Cannot connect to Env Api');
            }
            $response = $client->post("/sp/api/get-template-circular", [
                RequestOptions::JSON => [
                    'company_id' => $user->mst_company_id,
                    "env_flg"=>config('app.server_env'),
                    "edition_flg"=>config('app.edition_flg'),
                    "server_flg"=>config('app.server_flg'),
                    "receive_company_id"=> $request->company_id,
                    "receive_env_flg"=>$request->env_flg,
                    "receive_edition_flg" =>$request->edition_flg,
                    "receive_server_flg" =>$request->server_flg,
                ]
            ]);
            $data = [];
            $template_placeholder_datas = [];
            if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                $response_body = json_decode($response->getBody());
                $data = $response_body->result->template_circulars;
                $template_placeholder_datas = $response_body->result->template_placeholders;
            }

            $data = new LengthAwarePaginator(array_slice( $data, ($page - 1) * $limit, $limit, false), count($data), $limit);
            $data->setPath($request->url());
            $data->appends(request()->input()); // sort params etc
            $items = $data->items();

            $mst_placeholder = DB::table('mst_template_placeholder')
                ->get();
            $department = DB::table('mst_department')
                ->where('id', $user_info->mst_department_id)
                ->where('mst_company_id', $user->mst_company_id)
                ->first();
            $company = DB::table('mst_company')
                ->where('id', $user->mst_company_id)
                ->first();
            $position = DB::table('mst_position')
                ->where('id', $user_info->mst_position_id)
                ->where('mst_company_id', $user->mst_company_id)
                ->first();

            foreach ($items as $item) {
                foreach ($item->placeholderData as $value) {
                    foreach ($mst_placeholder as $mp) {
                        if($value->template_placeholder_name === $mp->special_template_placeholder) {
                            if($mp->id === 1){
                                $value->template_placeholder_value = date("Y/m/d H:i:s");
                            } else if ($mp->id === 2) {
                                $value->template_placeholder_value = date("Y/m/d");
                            } else if ($mp->id === 3) {
                                $value->template_placeholder_value = $user->family_name.$user->given_name;
                            } else if ($mp->id === 4) {
                                $value->template_placeholder_value = $user->email;
                            } else if ($mp->id === 5) {
                                if($company){
                                    $value->template_placeholder_value = $company->company_name;
                                } else {
                                    $value->template_placeholder_value = '';
                                }
                            } else if ($mp->id === 6) {
                                if($department){
                                    $value->template_placeholder_value = $department->department_name;
                                } else {
                                    $value->template_placeholder_value = '';
                                }
                            } else if ($mp->id === 7) {
                                if($position){
                                    $value->template_placeholder_value = $position->position_name;
                                } else {
                                    $value->template_placeholder_value = '';
                                }
                            } else if ($mp->id === 8) {
                                $value->template_placeholder_value = $user_info->phone_number;
                            } else if ($mp->id === 9) {
                                $value->template_placeholder_value = $user_info->fax_number;
                            } else if ($mp->id === 10) {
                                $value->template_placeholder_value = $user_info->address;
                            } else if ($mp->id === 11) {
                                $value->template_placeholder_value = $user_info->postal_code;
                            }
                            break;
                        } else {
                            $value->template_placeholder_value = '';
                        }
                    }
                }
            }

            Log::debug(json_encode($template_placeholder_datas));

//            foreach ($items as &$item) {
//                foreach ($template_placeholder_datas as $template_placeholder_data) {
//                    $item->placeholderData = $template_placeholder_data->template_circular_id == $item->id;
//                }
//                $item->placeholderData = $template_placeholder_datas->filter(function ($_item) use ($item){
//                    return $_item && $_item->template_circular_id == $item->id;
//                });
//            }

            $template_files = json_decode(json_encode($data));

            $template_files->data = $items;

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse(['data' => $template_files], __('message.success.data_get', ['attribute'=>'受取連携会社']));
    }
}
