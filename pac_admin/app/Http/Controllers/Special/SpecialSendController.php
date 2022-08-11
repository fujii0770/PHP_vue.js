<?php

namespace App\Http\Controllers\Special;

use App\Http\Utils\AppUtils;
use App\Http\Utils\SpecialAppApiUtils;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use DB;
use App\Models\Department;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Session;

class SpecialSendController extends AdminController
{

    private $model;
    private $department;

    public function __construct(Department $department)
    {
        parent::__construct();
        $this->department = $department;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){

        $user       = $request->user();

        $action     = $request->get('action','');

        $page       = $request->get('page', 1);
        $limit      = $request->get('limit', 10);
        $orderBy    = $request->get('orderBy', "cc.cooperation_company_id");
        $orderDir   = $request->get('orderDir', "DESC");
        $group_name = $request->get('group_name');
        $region_name = $request->get('region_name');
        $state = $request->get('state');

        //地域リストの取得
        $regionList = DB::table('mst_region')
            ->select(DB::raw('region_name,region_id'))
            ->pluck('region_name','region_id')->toArray();
        $regionNameList = DB::table('mst_region')
            ->select(DB::raw('region_name,region_id'))
            ->get();
        foreach ($regionNameList as $region) {
            if ($region->region_id == $region_name) {
                $region_name = $region->region_name;
            }
        }
        //API開始
        $specialClient = SpecialAppApiUtils::getAuthorizeClient();
        if (!$specialClient) {
            return response()->json(['status' => false,
                'message' => ['Cannot connect to Special App']
            ]);
        }
        //SRS-003 申請可能会社取得
        $getReceiveCompany = "/sp/api/get-receive-company";
        $response = $specialClient->post($getReceiveCompany,
            [
                RequestOptions::JSON => [
                    "company_id"=>$user->mst_company_id,
                    "env_flg"=>config('app.pac_app_env'),
                    "edition_flg"=>config('app.pac_contract_app'),
                    "server_flg"=>config('app.pac_contract_server'),
                    "search_option"=>[
                        "group_name"=>$group_name,
                        "region_name"=>$region_name,
                        "state"=>$state,
                        "order_dir"=>$orderDir,
                        "order_by"=>$orderBy,
                    ]
                ]
            ]);
        $response_dencode = json_decode($response->getBody(),true);  //配列へ
        if ($response->getStatusCode() == 200) {
            $response_body = json_decode($response->getBody(),true);  //配列へ
            if($response_body['status'] == "success"){
                $itemsSend = $response_body['result']['companies'];
                foreach ($itemsSend as $item){
                    foreach ($regionList as $key => $regionItem){
                        if($item['region_name'] == $key){
                            $item['region_id'] = $key;
                            $item['region_name'] = $regionItem;
                        }
                    }
                }
                $itemsSend = new LengthAwarePaginator(array_slice( $itemsSend, ($page - 1) * $limit, $limit, false), count($itemsSend), $limit);
                $itemsSend->setPath($request->url());
                $itemsSend->appends(request()->input()); // sort params etc
            }else{
                Log::error('Get Receive Company:' .$response_body['message']);
                Log::error($response_dencode);
                return response()->json(['status' => false, 'message' => [$response_dencode['message']] ]);
            }
        } else {
            Log::error('Api storeBoard companyId:' . $user->mst_company_id);
            Log::error($response_dencode);
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }



        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', strtolower($orderDir)=="asc"?"desc":"asc");
        $this->assign('itemsSend', $itemsSend);
        $this->assign('regionList', $regionList);
        $this->assign('group_name', $group_name);
        foreach ($regionNameList as $region) {
            if ($region->region_name == $region_name) {
                $region_name = $region->region_id;
            }
        }
        $this->assign('region_name', $region_name);
        $this->assign("state", $state);

        $this->setMetaTitle('連携申請');

        return $this->render('Special.Send.index');
    }

    function update(Request $request)
    {
        $user = \Auth::user();
        $mst_company_id = $user->mst_company_id;
        $kbn = $request->get('kbn');
        $cids = $request->get('ids',[]);

        try{
            // 申請
            if($kbn == AppUtils::SEND_SEND){
                //SRS-004 連携申請
                //API開始
                $specialClient = SpecialAppApiUtils::getAuthorizeClient();
                if (!$specialClient) {
                    return response()->json(['status' => false,
                        'message' => ['Cannot connect to Special App']
                    ]);
                }
                $receive_companies = [];
                foreach ($cids as $c => $id) {
                    $receive_company = [
                        'company_id'=>$id,
                        "env_flg"=>config('app.pac_app_env'),
                        "edition_flg"=>config('app.pac_contract_app'),
                        "server_flg"=>config('app.pac_contract_server'),
                    ];
                    array_push($receive_companies, $receive_company);
                }
                $receiveInfo = "/sp/api/request-cooperation";
                $response = $specialClient->post($receiveInfo,
                    [
                        RequestOptions::JSON => [
                            "company_id"=>$mst_company_id,
                            "env_flg"=>config('app.pac_app_env'),
                            "edition_flg"=>config('app.pac_contract_app'),
                            "server_flg"=>config('app.pac_contract_server'),
                            "request_user_id"=>$user->getFullName(),
                            "receive_companies"=>$receive_companies
                        ]
                    ]);
                $response_dencode = json_decode($response->getBody(),true);  //配列へ

                if ($response->getStatusCode() == 200) {
                    if($response_dencode['status'] == "error") {
                        Log::error('Request Cooperation:' . $response_dencode['message']);
                        Log::error($response_dencode);
                        return response()->json(['status' => false, 'message' => [$response_dencode['message']]]);
                    }
                    elseif ( !empty($response_dencode['message']) && $response_dencode['status']=="success"){
                        return response()->json(['status' => true, 'message' => $response_dencode['message']]);
                    }
                } else {
                    Log::error('Api storeBoard companyId:' . $mst_company_id);
                    Log::error($response_dencode);
                    return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
                }
            }
            // 申請取消
            else if ($kbn == AppUtils::SEND_CLN){
                //SRS-005 連携申請取消
                //API開始
                $specialClient = SpecialAppApiUtils::getAuthorizeClient();
                if (!$specialClient) {
                    return response()->json(['status' => false,
                        'message' => ['Cannot connect to Special App']
                    ]);
                }
                $receive_companies = [];
                foreach ($cids as $c => $id) {
                    $receive_company = [
                        'company_id'=>$id,
                        "env_flg"=>config('app.pac_app_env'),
                        "edition_flg"=>config('app.pac_contract_app'),
                        "server_flg"=>config('app.pac_contract_server'),
                    ];
                    array_push($receive_companies, $receive_company);
                }
                $receiveInfo = "/sp/api/cancel-request-cooperation";
                $response = $specialClient->post($receiveInfo,
                    [
                        RequestOptions::JSON => [
                            "company_id"=>$mst_company_id,
                            "env_flg"=>config('app.pac_app_env'),
                            "edition_flg"=>config('app.pac_contract_app'),
                            "server_flg"=>config('app.pac_contract_server'),
                            "receive_companies"=>$receive_companies
                        ]
                    ]);
                $response_dencode = json_decode($response->getBody(),true);  //配列へ

                if ($response->getStatusCode() == 200) {
                    if($response_dencode['status'] == "error") {
                        Log::error('Cancel Request Cooperation:' . $response_dencode['message']);
                        Log::error($response_dencode);
                        return response()->json(['status' => false, 'message' => [$response_dencode['message']]]);
                    }
                    elseif ( !empty($response_dencode['message']) && $response_dencode['status']=="success"){
                        return response()->json(['status' => true, 'message' => $response_dencode['message']]);
                    }
                } else {
                    Log::error('Api storeBoard companyId:' . $mst_company_id);
                    Log::error($response_dencode);
                    return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
                }
            }

            if($kbn == 1) {
                return response()->json(['status' => true, 'message' => [__('message.success.send_update')]]);
            }else{
                return response()->json(['status' => true, 'message' => [__('message.success.send_cancel_update')]]);
            }
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }
}
