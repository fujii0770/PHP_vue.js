<?php

namespace App\Http\Controllers;

use App\Http\Utils\DispatchUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use DB;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use App\Models\DispatchArea;
use App\Models\DispatchAreaAgency;
use App\Models\DispatchCode;
use App\Models\DispatchAreaOption;
use Session;
use Illuminate\Support\Facades\Validator;

class DispatchAreaController extends AdminController
{
    private $dispatcharea;
    private $dispatcharea_agency;
    private $dispatcharea_option;
    private $dispatch_code;
    
    public function __construct(DispatchArea $dispatcharea, DispatchAreaAgency $dispatcharea_agency, DispatchAreaOption $dispatcharea_option, DispatchCode $dispatch_code )
    {
        parent::__construct();
        $this->dispatcharea = $dispatcharea;
        $this->dispatcharea_agency = $dispatcharea_agency;
        $this->dispatcharea_option = $dispatcharea_option;
        $this->dispatch_code = $dispatch_code;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $user = \Auth::user();

        if(!$user->can(PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }          
        $action   = $request->get('action','');
        $agency_limit = $request->get('agency_limit') ? $request->get('agency_limit') : 20;
        $agency_orderBy  = $request->get('agency_orderBy') ? $request->get('agency_orderBy') : 'create_at';
        $agency_orderDir = $request->get('agency_orderDir') ? $request->get('agency_orderDir'): 'desc';        
        $dispatcharea_limit = $request->get('dispatcharea_limit') ? $request->get('dispatcharea_limit') : 20;
        $dispatcharea_orderBy  = $request->get('dispatcharea_orderBy') ? $request->get('dispatcharea_orderBy') : 'create_at';
        $dispatcharea_orderDir = $request->get('dispatcharea_orderDir') ? $request->get('dispatcharea_orderDir'): 'desc';        
        $agency_list = [];
        $dispatcharea_list = [];

        if ($action != ''){
            $ag_where     = [];
            $ag_where_arg = [];
            $te_where     = [];
            $te_where_arg = [];
            $this->getParam($user->mst_company_id, $request
                , $ag_where, $ag_where_arg, $te_where, $te_where_arg);

            $agency_list = $this->searchAgency($agency_limit
            , $ag_where, $ag_where_arg
            , $agency_orderBy, $agency_orderDir);

            $dispatcharea_list = $this->searchDispatchArea($dispatcharea_limit
            , $ag_where, $ag_where_arg, $te_where, $te_where_arg
            , $dispatcharea_orderBy, $dispatcharea_orderDir);

            $agency_orderDir = strtolower($agency_orderDir)=="asc"?"desc":"asc";
            $dispatcharea_orderDir = strtolower($dispatcharea_orderDir)=="asc"?"desc":"asc";

        }

        $codeall = $this->dispatch_code->getCodeAll();

        $code_holiday = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_HOLIDAY);
        $code_welfare = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_WELFARE);
        $code_fraction = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_FRACTION);
        $code_status = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_STATUS);

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->assign('code_holiday', $code_holiday);
        $this->assign('code_welfare', $code_welfare);
        $this->assign('code_fraction', $code_fraction);
        $this->assign('code_status', $code_status);
        $this->assign('allow_create', $user->can(PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_CREATE));
        $this->assign('allow_update', $user->can(PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_UPDATE));
        $this->assign('allow_delete', $user->can(PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_DELETE));
        $this->assign('agency_list', $agency_list);
        $this->assign('dispatcharea_list', $dispatcharea_list);

        $this->assign('agency_limit', $agency_limit);
        $this->assign('agency_orderBy', $agency_orderBy);
        $this->assign('agency_orderDir', $agency_orderDir);

        $this->assign('dispatcharea_limit', $dispatcharea_limit);
        $this->assign('dispatcharea_orderBy', $dispatcharea_orderBy);
        $this->assign('dispatcharea_orderDir', $dispatcharea_orderDir);
        $this->setMetaTitle('派遣先管理');
        return $this->render('Dispatch.DispatchArea.index');
    }

    public function getagency(Request $request){
        $page         = $request->get('page','1');
        $limit        = $request->get('limit') ? $request->get('limit') : 20;
        $user         = $request->user();
        $id           = $request->get('id','0');
        $orderBy      = $request->get('orderBy','create_at');
        $orderDir     = $request->get('orderDir','desc');
        $where = [];
        $where_arg = [];
        if(!$user->can(PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_VIEW) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }   
        $this->getParamAgency($user->mst_company_id, $id, $request->get('items'), $where, $where_arg);

        try{
            $agency = $this->dispatcharea_agency
            ->selectRaw("id, company_name, office_name, conflict_date, postal_code, address1, address2, billing_address,  DATE_FORMAT(create_at, '%Y-%m-%d') as disp_create_at")
            ->whereRaw(implode(" AND ", $where), $where_arg)
            ->orderBy($orderBy, $orderDir);

            if ($id != 0){
                $agency = $agency->first();             
                return response()->json(['status' => true, 'agency' => $agency]);
            }else{
                $agency = $agency->get();
                $pageagency = new LengthAwarePaginator($agency->forPage($page, $limit), count($agency), $limit);
                return response()->json(['status' => true, 'items' => $pageagency]);
            }

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        
    }
    public function getEditData(Request $request){

        $id = $request->get('id','0');
        $user         = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_VIEW) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }   
        try{
            $dispatcharea = $this->dispatcharea
            ->where('del_flg', 0)
            ->where('id', $id)
            ->first();

            $agency = $this->dispatcharea_agency
            ->selectRaw("id, company_name, office_name, conflict_date, postal_code, address1, address2, billing_address, DATE_FORMAT(create_at, '%Y-%m-%d') as create_at")
            ->where('del_flg', 0)
            ->where('id', $dispatcharea->dispatcharea_agency_id)
            ->first();

            $dispatchareaopt = DB::table('dispatch_code')
                ->selectRaw("dispatch_code.id as id, dispatch_code.kbn as kbn, ifnull(dispatcharea_option.status, 0) as checked")
                ->leftJoin('dispatcharea_option', function ($join) use ($id) {
                    $join->on('dispatcharea_option.dispatch_code_id', 'dispatch_code.id')
                         ->where('dispatcharea_option.dispatcharea_id', $id);
                })
                ->get();
            foreach($dispatchareaopt as &$item){
                $item->checked = $item->checked == 1;
            };
            $holiday = $dispatchareaopt->where('kbn', DispatchUtils::CODE_KBN_HOLIDAY)->values();
            $welfare = $dispatchareaopt->where('kbn', DispatchUtils::CODE_KBN_WELFARE)->values();
            $status = $dispatchareaopt->where('kbn', DispatchUtils::CODE_KBN_STATUS)->values();
             
            $dispatcharea['dispatcharea_holiday'] = $holiday;
            $dispatcharea['welfare_kbn'] = $welfare;
            $dispatcharea['status_kbn'] = $status;

            $data = [
                'text'=>$dispatcharea['dispatcharea_holiday_other'],
                'checked' => $dispatcharea['dispatcharea_holiday_other'] ? true: false
            ];            
            unset($dispatcharea['dispatcharea_holiday_other']);            
            $dispatcharea['dispatcharea_holiday_other'] =  $data;
            $data = [
                'text'=>$dispatcharea['welfare_other'],
                'checked' => $dispatcharea['welfare_other'] ? true: false
            ];            
            unset($dispatcharea['welfare_other']);            
            $dispatcharea['welfare_other'] =  $data; 

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true, 'agency' => $agency, 'dispatcharea'=>$dispatcharea]);
    }
    public function agencysave(Request $request)
    {
        $user       = $request->user();


        $item_info = $request->get('item');
        $id = $item_info['id'];

        if ($id == 0) {
            if(!$user->can(PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_CREATE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }   
        }else{
            if(!$user->can(PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_UPDATE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }   
        }   
        $agency = $item_info;

        $agency['update_user'] = $user->getFullName();
        unset($agency['id']);
      
        if ($agency['conflict_date']) $agency['conflict_date'] = date('Y/m/d', strtotime($agency['conflict_date']));

        $validator = Validator::make($agency
        , $this->dispatcharea_agency->rules()
        , $this->dispatcharea_agency->messages()
        , $this->dispatcharea_agency->attributes());
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        DB::beginTransaction();
        try{
            if ($id == 0) {
                $agency['create_user'] = $user->getFullName();
                $agency['mst_admin_id'] = $user->id;
                $agency['mst_company_id'] = $user->mst_company_id;
                DispatchAreaAgency::insert($agency);
            }else{

                DispatchAreaAgency::find($id)
                    ->fill($agency)
                    ->save();
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true, 'items' => $agency]);
    }

    public function dispatchareasave(Request $request)
    {
        $user       = $request->user();
        $username = $user->getFullName();
        $item_info = $request->get('item');
        $id = $item_info['id'];
        if ($id == 0) {
            if(!$user->can(PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_CREATE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }   
        }else{
            if(!$user->can(PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_UPDATE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }   
        }

        $dispatcharea = $item_info;
        $dispatcharea['update_user'] = $username;

        unset($dispatcharea['id']);
        unset($dispatcharea['dispatcharea_holiday']);
        unset($dispatcharea['dispatcharea_holiday_other']);
        unset($dispatcharea['welfare_kbn']);
        unset($dispatcharea['welfare_other']);
        unset($dispatcharea['status_kbn']);
        $dispatcharea['dispatcharea_holiday_other'] = $item_info['dispatcharea_holiday_other']['text'];
        $dispatcharea['welfare_other'] = $item_info['welfare_other']['text'];
        if ($dispatcharea['department_date']) $dispatcharea['department_date'] = date('Y/m/d', strtotime($dispatcharea['department_date']));
        $validator = Validator::make($dispatcharea
        , $this->dispatcharea->rules()
        , $this->dispatcharea->messages()
        , $this->dispatcharea->attributes());
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        DB::beginTransaction();
        try{
            if ($id == 0) {
                $dispatcharea['create_user'] = $user->getFullName();

                $id = DispatchArea::insertGetId($dispatcharea);
                $options = collect();
                $this->getoption($item_info['dispatcharea_holiday'], $id, $username, $options);
                $this->getoption($item_info['welfare_kbn'], $id, $username, $options);
                $this->getoption($item_info['status_kbn'], $id, $username, $options);
                DispatchAreaOption::insert($options->toArray());
            }else{

                DispatchArea::find($id)
                    ->fill($dispatcharea)
                    ->save();
                $options = collect();
                $this->getoption($item_info['dispatcharea_holiday'], $id, $username, $options);
                $this->getoption($item_info['welfare_kbn'], $id, $username, $options);
                $this->getoption($item_info['status_kbn'], $id, $username, $options);
                DispatchAreaOption::where('dispatcharea_id', $id)->delete();
                if (count($options)>0) DispatchAreaOption::insert($options);
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true]);
    }
    public function agencydeletes(Request $request){
        $user       = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_DELETE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }   
        $ids = $request->get('cids', []);
        DB::beginTransaction();
        try{
            $agency = $this->dispatcharea_agency
            ->wherein('id', $ids)
            ->update(['del_flg'=> 1]);

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true]);       
    }
    public function dispatchareadeletes(Request $request){
        $user       = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_DELETE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }   
        $ids = $request->get('cids', []);
        DB::beginTransaction();
        try{
            $agency = $this->dispatcharea
            ->wherein('id', $ids)
            ->update(['del_flg'=> 1]);

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true]);       
    }

    private function getoption($codes, $dispatcharea_id, $username, Collection $dispatchareaoptions){

        foreach($codes as $key => $value){
            if ($value['checked'] == true) { 
                  
                $dispatchareaoptions->push(array(
                    'dispatcharea_id' => $dispatcharea_id,
                    'dispatch_code_id' =>$value['id'] ,
                    'status' => true,
                    'create_user' => $username,
                    'update_user' => $username
                ));
              
            } 
        }
    }
    private function getParamAgency($companyid, $id, $param, &$where, &$where_arg){
        $where = [ ];
        $where_arg = [];
        $company_name = $param['scag_company_name'] ? $param['scag_company_name'] : '';
        $todate       = $param['scagsl_todate']     ? $param['scagsl_todate'] : '';
        $fromdate     = $param['scagsl_fromdate']   ? $param['scagsl_fromdate'] : '';

        try{
            $where[]        = ' del_flg = ?';
            $where_arg[]    = 0;
            $where[]        = ' mst_company_id = ?';
            $where_arg[]    = $companyid;
            if($id != 0){
                $where[]        = ' id = ?';
                $where_arg[]    = $id;
            }
            if($company_name){
                $where[]        = ' company_name like ?';
                $where_arg[]    = "%".$company_name."%";
            }
            if($fromdate){
                $where[]        = " DATE_FORMAT(create_at, '%Y/%m/%d') >= ?";
                $where_arg[]    = date('Y/m/d', strtotime($fromdate));
            }
            if($todate){
                $where[]        = " DATE_FORMAT(create_at, '%Y/%m/%d') <= ?";
                $where_arg[]    = date('Y/m/d', strtotime($todate));
            }
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
    }
    private function getParam($companyid, $param, &$ag_where, &$ag_where_arg, &$te_where, &$te_where_arg){
        $ag_where     = [];
        $ag_where_arg = [];
        $te_where     = [];
        $te_where_arg = [];
        $company_name = $param['sc_company_name'] ? $param['sc_company_name'] : '';
        $ag_todate    = $param['scag_fromdate']   ? $param['scag_fromdate'] : '';
        $ag_fromdate  = $param['scag_todate']     ? $param['scag_todate'] : '';
        $department   = $param['sc_department']   ? $param['sc_department'] : '';
        $te_todate    = $param['scte_fromdate']   ? $param['scte_fromdate'] : '';
        $te_fromdate  = $param['scte_todate']     ? $param['scte_todate'] : '';
        $address      = $param['sc_address']      ? $param['sc_address'] : '';
        $phoneno      = $param['sc_phone_no']     ? $param['sc_phone_no'] : '';

        try{
            $ag_where[]        = ' dispatcharea_agency.del_flg = ?';
            $ag_where_arg[]    = 0;
            $ag_where[]        = ' dispatcharea_agency.mst_company_id = ?';
            $ag_where_arg[]    = $companyid;
            if($company_name){
                $ag_where[]        = ' dispatcharea_agency.company_name like ?';
                $ag_where_arg[]    = "%".$company_name."%";
            }
            if($ag_fromdate){
                $ag_where[]        = " DATE_FORMAT(dispatcharea_agency.create_at, '%Y/%m/%d') >= ?";
                $ag_where_arg[]    = date('Y/m/d', strtotime($ag_fromdate));
            }
            if($ag_todate){
                $ag_where[]        = " DATE_FORMAT(dispatcharea_agency.create_at, '%Y/%m/%d') <= ?";
                $ag_where_arg[]    = date('Y/m/d', strtotime($ag_todate));
            }
            $te_where[]        = ' dispatcharea.del_flg = ?';
            $te_where_arg[]    = 0;
            if($department){
                $te_where[]        = ' dispatcharea.department like ?';
                $te_where_arg[]    = "%".$department."%";
            }
            if($te_fromdate){
                $te_where[]        = " DATE_FORMAT(dispatcharea.create_at, '%Y/%m/%d') >= ?";
                $te_where_arg[]    = date('Y/m/d', strtotime($te_fromdate));
            }
            if($te_todate){
                $te_where[]        = " DATE_FORMAT(dispatcharea.create_at, '%Y/%m/%d') <= ?";
                $te_where_arg[]    = date('Y/m/d', strtotime($te_todate));
            }
            if($address){
                $te_where[]        = ' dispatcharea.address1 like ?';
                $te_where_arg[]    = "%".$address."%";
            }
            if($phoneno){
                $te_where[]        = ' dispatcharea.main_phone_no like ?';
                $te_where_arg[]    = "%".$phoneno."%";
            }

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
    }
    private function searchAgency($limit, $where, $where_arg, $orderBy, $orderDir)
    {

        try
        {
            $sch_agency = $this->dispatcharea_agency
            ->whereRaw(implode(" AND ", $where), $where_arg)
            ->orderBy($orderBy, $orderDir)
            ->paginate($limit, ["*"], 'agency_page')->appends(request()->input());
 
            $dispatcharea = $this->dispatcharea
                ->where('del_flg', 0)    
                ->whereExists(function ($query) use ($where, $where_arg) {
                    $query->select(DB::raw(1))
                        ->from('dispatcharea_agency')
                        ->whereRaw(implode(" AND ", $where), $where_arg)
                        ->whereRaw('dispatcharea_agency.id = dispatcharea.dispatcharea_agency_id');
                })              
                ->get();             
            foreach ($sch_agency as &$agency) {
                $department = $dispatcharea
                     ->where('dispatcharea_agency_id', $agency['id'])
                     ->count();
                    if ($department==0){
                        $agency['department'] = '';
                    }else{
                        $agency['department'] = $department.'件 登録済';
                    }

                }

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
        return $sch_agency;
    }
    private function searchDispatchArea($limit, $ag_where, $ag_where_arg, $te_where, $te_where_arg, $orderBy, $orderDir)
    {
        try
        {
            $orderByName = 'dispatcharea.'.$orderBy;
            if($orderBy == "company_name") $orderByName = 'dispatcharea_agency.'.$orderBy;
            $sch_dispatcharea = $this->dispatcharea
            ->selectRaw('dispatcharea.id ,dispatcharea_agency.company_name ,dispatcharea.department, dispatcharea.responsible_name, dispatcharea.responsible_phone_no')
            ->join('dispatcharea_agency', function ($query) use ($ag_where, $ag_where_arg) {
                $query->on('dispatcharea_agency.id', '=' , 'dispatcharea.dispatcharea_agency_id')
                    ->whereRaw(implode(" AND ", $ag_where), $ag_where_arg);
            })
            ->whereRaw(implode(" AND ", $te_where), $te_where_arg)
            ->orderBy($orderByName, $orderDir)
            ->paginate($limit, ["*"], 'dispatcharea_page')->appends(request()->input());

        }catch(\Exception $e){
            throw $e;
        }
        return $sch_dispatcharea;
    }

}
