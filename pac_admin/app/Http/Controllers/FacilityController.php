<?php

namespace App\Http\Controllers;

use App\Http\Utils\GwAppApiUtils;
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
use App\Models\Facility;
use App\Http\Utils\IdAppApiUtils;

class FacilityController extends AdminController
{

    private $model;

   public function __construct(Facility $model)
   {
        parent::__construct();
        $this->model        = $model;

   }

    /**
     * Display a setting for DateStamp
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = \Auth::user();


        $facility_data = array();


        // 結果を判断
        $success_message = "";
        $failure_message = "";

        $json_decode_data = GwAppApiUtils::getFacility($user['email'], $user['mst_company_id']);
//        if (!$json_decode_data){
//            $failure_message = "設備情報が取得できませんでした。";
//        }

        //返却データを成形
        if ($json_decode_data){
            foreach($json_decode_data as $key => $val) {
                $facility_data[$key]['id'] = $val['id'];
                $facility_data[$key]['name'] = $val['name'];
            }
        }


        $orderDir   = $request->get('orderDir') ? $request->get('orderDir'): 'asc';
        $limit      = 10;

        // sort
        if ($orderDir == 'asc') {
            asort($facility_data);
        } else {
            arsort($facility_data);
        }

        // count
        $dataCount = count($facility_data);

        // from
        $page      = $request->get('page')?$request->get('page'):1;
        $dataFrom = ($page-1) * $limit + 1;

        // to
        $dataTo = $page * $limit;
        if($dataTo > $dataCount){
            $dataTo = $dataCount;
        }

        $facility_array = array();
        if($dataCount){
            // 役職存在する場合
            $chunk_array = array_chunk($facility_data, $limit, true);

            if(!isset($chunk_array[$page-1])) {
                $page = count($chunk_array);
            }
            $facility_array = $chunk_array[$page-1];
            $currentPage = $page;
            $lastPage = count($chunk_array);

        }else{
            //存在しない場合
            $currentPage = $page;
            $lastPage = 1;
        }

        $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
        $this->setMetaTitle('設備');
        $this->assign('success_message', $success_message);
        $this->assign('failure_message', $failure_message);
        $this->assign('orderDir', $orderDir);
        $this->assign('dataCount', $dataCount);
        $this->assign('dataFrom', $dataFrom);
        $this->assign('dataTo', $dataTo);
        $this->assign('currentPage', $currentPage);
        $this->assign('lastPage', $lastPage);        
        $this->assign('facility_data', $facility_array);

        $this->assign('portalCompanyId', $user['mst_company_id']);
        $this->assign('portalEmail', $user['email']);

        $this->assign('mstCompanyId', $user['mst_company_id']);
        $this->assign('editionFlg', config('app.pac_contract_app'));
        $this->assign('envFlg', config('app.pac_app_env'));
        $this->assign('serverFlg', config('app.pac_contract_server'));

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));

        return $this->render('SettingGroupware.index');
    }

    /**
     * delete a facility.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    function delete($id, Request $request){

        try{
            $user = \Auth::user();
            //削除API呼び出し
            $del_result = GwAppApiUtils::deleteFacility($id, $user['email'], $user['mst_company_id']);
            if (!$del_result){
                Log::error('Delete Facility FacilityID:' . $id);
                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
            }
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true, 'message' => [__('設備を削除しました。')]]);
    }

}