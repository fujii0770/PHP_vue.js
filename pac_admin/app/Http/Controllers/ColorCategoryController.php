<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Session;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\IdAppApiUtils;

class ColorCategoryController extends AdminController
{
    // route list
    private $arrRoutePath = [];
    private $strServer = "";
    private $strDomain = "";
    // GW client
    private $resClient = null;
    // current user
    private $objUser = null;
    private $strEncodeData = [];

    private function _initParams()
    {
        $this->strServer = config('app.gw_domain');
        $this->strDomain = 'https://' . $this->strServer;
        $this->arrRoutePath = [
            'findColor' => $this->strDomain . '/api/v1/admin/mst-schedule-type/getColor',
            'get' => $this->strDomain . '/api/v1/admin/mst-schedule-type/detail',
            'list' => $this->strDomain . '/api/v1/admin/mst-schedule-type/getByCompanyId',
            'create' => $this->strDomain . '/api/v1/admin/mst-schedule-type',
            'delete' => $this->strDomain . '/api/v1/admin/mst-schedule-type/delete',
            'update' => $this->strDomain . '/api/v1/admin/mst-schedule-type/update',
        ];

        $client = IdAppApiUtils::getAuthorizeClient();
        if ($client) {
            $this->resClient = $client;
        }
        // user
        $this->objUser = \Auth::user();

        $this->strEncodeData = json_encode([
            "portalCompanyId" => $this->objUser['mst_company_id'],
            "portalEmail" => $this->objUser['email'],
            "editionFlg" => config('app.pac_contract_app'),
            "envFlg" => config('app.pac_app_env'),
            "serverFlg" => config('app.pac_contract_server')
        ]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_VIEW)){
            return redirect()->route('home');
        }
        $this->_initParams();
        if (!$this->resClient || !$this->objUser) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        // 結果を判断
        $success_message = "";
        $failure_message = "";
        $orderDir = $request->get('orderDir') ? $request->get('orderDir') : 'asc';
        $limit = 10;
        $arrColorData = [];
        $arrListData = [];

        // sort
        if ($orderDir == 'asc') {
            asort($arrColorData);
        } else {
            arsort($arrColorData);
        }

        // find current company all colorCategory data
        $objListRes = $this->resClient->request('POST', $this->arrRoutePath['list'], [
            RequestOptions::JSON => [
                    "portalEmail" => $this->objUser['email'],
                    "portalCompanyId" => $this->objUser['mst_company_id'],
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg" => config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server'),
            ]
        ]);
        if ($objListRes->getStatusCode() != 200) {
            Log::error('Get Facility response body ' . $objListRes->getBody());
            $failure_message = "種別情報が取得できませんでした。";
        }else{
            $arrListData = json_decode((string)$objListRes->getBody(), true);
        }



        // find all color data
        $objColorsRes = $this->resClient->request('GET', $this->arrRoutePath['findColor'], ['body' => $this->strEncodeData]);

        if ($objColorsRes->getStatusCode() != 200) {
            Log::error('Get Facility response body ' . $objColorsRes->getBody());
            $failure_message = "種別情報が取得できませんでした。";
        }else{
            $arrColorData = json_decode((string)$objColorsRes->getBody(), true);
        }


        // count
        $dataCount = count($arrListData);
        // from
        $page = $request->get('page') ? $request->get('page') : 1;
        $dataFrom = ($page - 1) * $limit + 1;

        // to
        $dataTo = $page * $limit;
        if ($dataTo > $dataCount) {
            $dataTo = $dataCount;
        }
        $facility_array = array();
        $returnData = [];
        $returnData = array_slice($arrListData, ($page - 1) * $limit, $limit);
        if ($dataCount) {
            // 役職存在する場合
            $chunk_array = array_chunk($arrListData, $limit, true);

            if (!isset($chunk_array[$page - 1])) {
                $page = count($chunk_array);
            }
            $facility_array = $chunk_array[$page - 1];
            $currentPage = $page;
            $lastPage = count($chunk_array);

        } else {
            //存在しない場合
            $currentPage = $page;
            $lastPage = 1;
        }
        $this->assign('allColor', $arrColorData);
        $this->assign('arrListData', $returnData);
        $this->setMetaTitle('カテゴリ設定');
        $this->assign('success_message', $success_message);
        $this->assign('failure_message', $failure_message);
        $this->assign('orderDir', $orderDir);
        $this->assign('dataCount', $dataCount);
        $this->assign('dataFrom', $dataFrom);
        $this->assign('dataTo', $dataTo);
        $this->assign('currentPage', $currentPage);
        $this->assign('lastPage', $lastPage);
        $this->assign('facility_data', $facility_array);
        $this->assign("portalCompanyId", $this->objUser['mst_company_id']);
        $this->assign('portalEmail', $this->objUser['email']);

        $this->assign('mstCompanyId', $this->objUser['mst_company_id']);
        $this->assign('editionFlg', config('app.pac_contract_app'));
        $this->assign('envFlg', config('app.pac_app_env'));
        $this->assign('serverFlg', config('app.pac_contract_server'));

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        return $this->render('SettingGWColorCategory.index');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_CREATE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        $this->_initParams();
        if (!$this->resClient || !$this->objUser) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $strTypeName = $request->input("typeName");
        $strMstColorId = $request->input('mstColorId');
        // find current company all colorCategory data
        $objListRes = $this->resClient->request('POST', $this->arrRoutePath['create'], [
            RequestOptions::JSON => [
                "adminRequest" => [
                    "portalEmail" => $this->objUser['email'],
                    "portalCompanyId" => $this->objUser['mst_company_id'],
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg" => config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server'),
                ],
                "mstColorId" => $strMstColorId,
                "typeName" => $strTypeName,
            ]
        ]);

        if ($objListRes->getStatusCode() != 200) {
            Log::error('Get Facility response body ' . $objListRes->getBody());
            return response()->json(['status' => false, 'message' => ["種別の登録は失敗しました。"]]);
        }
        return response()->json(['status' => true, 'message' => ["種別を登録しました。"]]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $intId
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_VIEW)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        $this->_initParams();
        if (!$this->resClient || !$this->objUser) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $intId = $request->input('id');
        // find current company all colorCategory data
        $objListRes = $this->resClient->request('POST', $this->arrRoutePath['get'], [
            RequestOptions::JSON => [
                "adminRequest" => [
                    "portalEmail" => $this->objUser['email'],
                    "portalCompanyId" => $this->objUser['mst_company_id'],
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg" => config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server'),
                ],
                "id" => $intId,
            ]
        ]);

        if ($objListRes->getStatusCode() != 200) {
            Log::error('Get Facility response body ' . $objListRes->getBody());
            return response()->json(['status' => false, 'message' => ["設備情報が取得できませんでした。"]]);
        }

        $arrListData = json_decode((string)$objListRes->getBody(), true);
        return response()->json(['status' => true, 'message' => ["種別を登録しました。"], 'data' => $arrListData]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_UPDATE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        $this->_initParams();
        if (!$this->resClient || !$this->objUser) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $intID = $request->input("id");
        $strMstColorId = $request->input("mstColorId");
        $strTypeName = $request->input("typeName");
        // find current company all colorCategory data
        $objListRes = $this->resClient->request('POST', $this->arrRoutePath['update'], [
            RequestOptions::JSON => [
                "adminRequest" => [
                    "portalEmail" => $this->objUser['email'],
                    "portalCompanyId" => $this->objUser['mst_company_id'],
                    "editionFlg" => config('app.pac_contract_app'),
                    "envFlg" => config('app.pac_app_env'),
                    "serverFlg" => config('app.pac_contract_server'),
                ],
                "id" => $intID,
                "mstColorId" => $strMstColorId,
                "typeName" => $strTypeName,
            ]
        ]);

        if ($objListRes->getStatusCode() != 200) {
            Log::error('Get Facility response body ' . $objListRes->getBody());
            return response()->json(['status' => false, 'message' => ["種別の更新は失敗しました。"]]);
        }

        $arrListData = json_decode((string)$objListRes->getBody(), true);
        return response()->json(['status' => true, 'message' => ["種別を更新しました。"]]);
    }


    /**
     * delete a ColorCategory.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_DELETE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        try {
            $this->_initParams();
            if (!$this->resClient || !$this->objUser) {
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            $intID = $request->input("id");
            //削除API呼び出し
            $response_del = $this->resClient->request("POST", $this->arrRoutePath['delete'], [
                RequestOptions::JSON => [
                    "adminRequest" => [
                        "portalEmail" => $this->objUser['email'],
                        "portalCompanyId" => $this->objUser['mst_company_id'],
                        "editionFlg" => config('app.pac_contract_app'),
                        "envFlg" => config('app.pac_app_env'),
                        "serverFlg" => config('app.pac_contract_server'),
                    ],
                    "id" => $intID,
                ]
            ]);

            if ($response_del->getStatusCode() != 200) {
                Log::error('Delete Facility FacilityID:' . $response_del->getBody());
                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
            }

            return response()->json(['status' => true, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);

        } catch (\Exception $e) {
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
        }

        return response()->json(['status' => true, 'message' => ['種別を削除しました。']]);
    }

}
