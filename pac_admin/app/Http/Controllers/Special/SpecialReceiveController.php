<?php

namespace App\Http\Controllers\Special;

use App\Http\Utils\AppUtils;
use App\Http\Controllers\AdminController;
use App\Http\Utils\SpecialAppApiUtils;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use DB;
use App\Models\Department;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Session;

class SpecialReceiveController extends AdminController
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
        $request_user = $request->get('request_user_name');
        $state = $request->get('state');
        $request_fromdate = $request->get('request_fromdate');
        $request_todate = $request->get('request_todate');
        $update_fromdate = $request->get('update_fromdate');
        $update_todate = $request->get('update_todate');
        $available_fromdate = $request->get('available_fromdate');
        $available_todate = $request->get('available_todate');
        $adminList = [];

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
        //SRS-006 連携会社取得
        $cooperationCompanyInfo = "/sp/api/get-cooperation-company";
        $response = $specialClient->post($cooperationCompanyInfo,
            [
                RequestOptions::JSON => [
                    "company_id"=>$user->mst_company_id,
                    "env_flg"=>config('app.pac_app_env'),
                    "edition_flg"=>config('app.pac_contract_app'),
                    "server_flg"=>config('app.pac_contract_server'),
                    "search_option"=>[
                        "group_name"=>$group_name,
                        "region_name"=>$region_name,
                        "request_user_name"=>$request_user,
                        "state"=>$state,
                        "request_at_from"=>$request_fromdate,
                        "request_at_to"=>$request_todate,
                        "update_at_from"=>$update_fromdate,
                        "update_at_to"=>$update_todate,
                        "approval_period_from"=>$available_fromdate,
                        "approval_period_to"=>$available_todate,
                        "order_by"=>($orderBy=="request_user_name")?"cc.cooperation_company_id":$orderBy,
                        "order_dir"=>$orderDir,
                        ]
                ]
            ]);
        $response_dencode = json_decode($response->getBody(),true);  //配列へ

        if ($response->getStatusCode() == 200) {
            $response_body = json_decode($response->getBody(),true);  //配列へ
            if($response_body['status'] == "success"){
                $itemsReceive = $response_body['result']['companies'];
                foreach ($itemsReceive as &$item) {
                    if ($item['approval_period'] == '' || $item['approval_period'] == '9999-12-31'){
                        $item['approval_period'] = '';
                    }else{
                        $item['approval_period'] = Carbon::parse($item['approval_period'])->format('Y-m-d');
                    }
                }
                if($orderBy=="request_user_name" || $orderBy=="state") {
                    foreach ($itemsReceive as $item) {
                      $val[]= $item[$orderBy];
                    }
                    if(strtolower($orderDir)=="asc"){
                        array_multisort($val,SORT_ASC,SORT_STRING,$itemsReceive);
                    }else{
                        array_multisort($val,SORT_DESC,SORT_STRING,$itemsReceive);
                    }
                }
                $itemsReceive = new LengthAwarePaginator(array_slice( $itemsReceive, ($page - 1) * $limit, $limit, false), count($itemsReceive), $limit);
                $itemsReceive->setPath($request->url());
                $itemsReceive->appends(request()->input()); // sort params etc
            }else{
                Log::error('Get Cooperation Company:' .$response_body['message']);
                Log::error($response_dencode);
                return response()->json(['status' => false, 'message' => [$response_dencode['message']] ]);
            }
        } else {
            Log::error('Api storeBoard companyId:' . $user->mst_company_id);
            Log::error($response_dencode);
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        $approval_period_default=Carbon::now()->addMonthsNoOverflow()->format('Y-m-d');

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', strtolower($orderDir)=="asc"?"desc":"asc");
        $this->assign('regionList', $regionList);
        $this->assign('itemsReceive', $itemsReceive);
        $this->assign('group_name', $group_name);

        $region_name = $request->get('region_name');

        $this->assign('region_name', $region_name);
        $this->assign('request_user', $request_user);
        $this->assign("state", $state);
        $this->assign("request_fromdate", $request_fromdate);
        $this->assign("request_todate", $request_todate);
        $this->assign("update_fromdate", $update_fromdate);
        $this->assign("update_todate", $update_todate);
        $this->assign("available_fromdate", $available_fromdate);
        $this->assign("available_todate", $available_todate);
        $this->assign("approval_period_default", $approval_period_default);

        $this->setMetaTitle('連携承認');

        return $this->render('Special.Receive.index');
    }

    function update(Request $request)
    {
        $user = \Auth::user();
        $mst_company_id = $user->mst_company_id;
        $kbn = $request->get('kbn');
        $cids = $request->get('ids',[]);
        $id = $request->get('id');
        $state = $request->get('state');
        if($kbn == AppUtils::RECEIVE_APP){
            //一括承認
            if($request->get('approval_period_all') == ''){
                $approval_period = Carbon::parse('9999-12-31')->toDateTimeString();
            }else{
                $approval_period = $request->get('approval_period_all');
            }
        }else{
            if($request->get('approval_period') == ''){
                $approval_period = Carbon::parse('9999-12-31')->toDateTimeString();
            }else{
                $approval_period = $request->get('approval_period');
            }
        }
        try{
            //API開始
            $specialClient = SpecialAppApiUtils::getAuthorizeClient();
            if (!$specialClient) {
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to Special App']
                ]);
            }

            if($kbn == AppUtils::RECEIVE_APP){
                //SRS-007 連携承認
                $cooperation_companies = [];
                foreach ($cids as $c => $id) {
                    $cooperation_company = [
                        'company_id'=>$id,
                        "env_flg"=>config('app.pac_app_env'),
                        "edition_flg"=>config('app.pac_contract_app'),
                        "server_flg"=>config('app.pac_contract_server'),
                    ];
                    array_push($cooperation_companies, $cooperation_company);
                }
                $receiveInfo = "/sp/api/approval-cooperation";
                $response = $specialClient->post($receiveInfo,
                    [
                        RequestOptions::JSON => [
                            "company_id"=>$mst_company_id,
                            "env_flg"=>config('app.pac_app_env'),
                            "edition_flg"=>config('app.pac_contract_app'),
                            "server_flg"=>config('app.pac_contract_server'),
                            "approval_period"=>$approval_period,
                            "cooperation_companies"=>$cooperation_companies
                        ]
                    ]);
                $response_dencode = json_decode($response->getBody(),true);  //配列へ

                if ($response->getStatusCode() == 200) {
                    if($response_dencode['status'] == "error") {
                        Log::error('Approval Cooperation:' . $response_dencode['message']);
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
            }else if ($kbn == AppUtils::RECEIVE_CLN){
                //SRS-008 連携承認解除
                $cooperation_companies = [];
                foreach ($cids as $c => $id) {
                    $cooperation_company = [
                        'company_id'=>$id,
                        "env_flg"=>config('app.pac_app_env'),
                        "edition_flg"=>config('app.pac_contract_app'),
                        "server_flg"=>config('app.pac_contract_server'),
                    ];
                    array_push($cooperation_companies, $cooperation_company);
                }
                $receiveInfo = "/sp/api/cancel-approval-cooperation";
                $response = $specialClient->post($receiveInfo,
                    [
                        RequestOptions::JSON => [
                            "company_id"=>$mst_company_id,
                            "env_flg"=>config('app.pac_app_env'),
                            "edition_flg"=>config('app.pac_contract_app'),
                            "server_flg"=>config('app.pac_contract_server'),
                            "cooperation_companies"=>$cooperation_companies
                        ]
                    ]);
                $response_dencode = json_decode($response->getBody(),true);  //配列へ

                if ($response->getStatusCode() == 200) {
                    if($response_dencode['status'] == "error") {
                        Log::error('Cancel Approval Cooperation:' . $response_dencode['message']);
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
            }else{
                //SRS-009 公開設定更新
                $receiveInfo = "/sp/api/update-cooperation-company-info";
                $response = $specialClient->post($receiveInfo,
                    [
                        RequestOptions::JSON => [
                            "company_id"=>$mst_company_id,
                            "env_flg"=>config('app.pac_app_env'),
                            "edition_flg"=>config('app.pac_contract_app'),
                            "server_flg"=>config('app.pac_contract_server'),
                            "cooperation_company"=>[
                                "company_id"=>$id,
                                "env_flg"=>config('app.pac_app_env'),
                                "edition_flg"=>config('app.pac_contract_app'),
                                "server_flg"=>config('app.pac_contract_server'),
                                "state"=>$state,
                                "approval_period"=>$approval_period
                            ]
                        ]
                    ]);
                $response_dencode = json_decode($response->getBody(),true);  //配列へ

                if ($response->getStatusCode() == 200) {
                    if($response_dencode['status'] == "error") {
                        Log::error('Update Cooperation Company Info:' . $response_dencode['message']);
                        Log::error($response_dencode);
                        return response()->json(['status' => false, 'message' => [$response_dencode['message']]]);
                    }
                } else {
                    Log::error('Api storeBoard companyId:' . $mst_company_id);
                    Log::error($response_dencode);
                    return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
                }
            }
            if($kbn == 2) {
                return response()->json(['status' => true, 'message' => [__('message.success.receive_update')]]);
            }elseif($kbn == 0){
                return response()->json(['status' => true, 'message' => [__('message.success.receive_cancel_update')]]);
            }
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }
}
