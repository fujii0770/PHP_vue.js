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
use App\Models\Contract;
use App\Models\ContractOption;
use App\Models\DispatchCode;
use Session;
use Illuminate\Support\Facades\Validator;

class ContractController extends AdminController
{

    private $contract;
    private $contract_option;
    private $dispatch_code;
    
    public function __construct(Contract $contract, ContractOption $contract_option, DispatchCode $dispatch_code )
    {
        parent::__construct();
        $this->contract = $contract;
        $this->contract_option = $contract_option;
        $this->dispatch_code = $dispatch_code;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_CONTRACT_SETTING_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }              
        $action = $request->get('action','');
        $page       = $request->get('page', 1);
        $limit = $request->get('limit') ? $request->get('limit') : 20;
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'create_at';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir'): 'desc';        
        $contract_list = [];
        if ($action=='delete') $this->deletes($request);
        if ($action != ''){

            $where     = [];
            $where_arg = [];
            $this->getParam($request,$user->mst_company_id, $where, $where_arg);
            $contract_list = $this->contract
                ->selectRaw("id, DATE_FORMAT(contract_fromdate, '%Y-%m-%d') as disp_contract_fromdate, DATE_FORMAT(contract_todate, '%Y-%m-%d') as disp_contract_todate, dispatcharea_name, name")
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->orderBy($orderBy, $orderDir)
                ->paginate($limit)->appends(request()->input());
            $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";    
        }

        $codeall = $this->dispatch_code->getCodeAll();
        $code_intro = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_INTRO);
        $code_period = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_PERIOD);
        $code_update = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_UPDATE);
        $code_week = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_WEEK);
        $code_maxworkmonth = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_MAX_WORK_M);
        $code_maxworkweek = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_MAX_WORK_W);
        $code_maxweek = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_MAX_WEEK);
        $wherecode = [
            'codefrom'=> 1,
            'codeto'=>99
        ];
        $code_deadlineprice = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_DEADLINE, $wherecode);
        $code_deadlinewage = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_DEADLINE);
        $code_round = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_ROUND);
        $code_timeflat = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_TIMEFLAT);
        $code_yesno = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_YESNO);
        $code_business = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_BUSINESS);
        $code_indefinite = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_INDEFINITE);
        $code_indefinitereason = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_INDEFINITE_R);
        $code_indefinitedetail = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_INDEFINITE_D);
        $code_fraction = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_FRACTION);
        $code_gender = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_GENDER);
        $code_defaultchar = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_DEFAULTCHAR);

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->assign('code_intro', $code_intro);
        $this->assign('code_period', $code_period);
        $this->assign('code_update', $code_update);
        $this->assign('code_week', $code_week);
        $this->assign('code_maxworkmonth', $code_maxworkmonth);
        $this->assign('code_maxworkweek', $code_maxworkweek);
        $this->assign('code_maxweek', $code_maxweek);
        $this->assign('code_deadlineprice', $code_deadlineprice);
        $this->assign('code_deadlinewage', $code_deadlinewage);
        $this->assign('code_round', $code_round);
        $this->assign('code_timeflat', $code_timeflat);
        $this->assign('code_yesno', $code_yesno);
        $this->assign('code_business', $code_business);
        $this->assign('code_indefinite', $code_indefinite);
        $this->assign('code_indefinitereason', $code_indefinitereason);
        $this->assign('code_indefinitedetail', $code_indefinitedetail);
        $this->assign('code_fraction', $code_fraction);
        $this->assign('code_gender', $code_gender);
        $this->assign('code_defaultchar', $code_defaultchar);
        $this->assign('allow_create', $user->can(PermissionUtils::PERMISSION_CONTRACT_SETTING_CREATE));
        $this->assign('allow_update', $user->can(PermissionUtils::PERMISSION_CONTRACT_SETTING_UPDATE));
        $this->assign('allow_delete', $user->can(PermissionUtils::PERMISSION_CONTRACT_SETTING_DELETE));

        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);
        $this->assign('contract_list', $contract_list);

        $this->setMetaTitle('契約管理');
        return $this->render('Dispatch.Contract.index');
    }
    public function getEditData(Request $request){
        $user = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_CONTRACT_SETTING_VIEW) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }      
        $id = $request->get('id','0');
        try{
            $contract = $this->contract
            ->where('del_flg', 0)
            ->where('id', $id)
            ->first();

            $data = [
                'checked' => $contract['price_month_overtime_flg'] ? true: false
            ];   
            unset($contract['price_month_overtime_flg']);            
            $contract['price_month_overtime_flg'] =  $data;
            $data = [
                'checked' => $contract['wage_month_overtime_flg'] ? true: false
            ];   
            unset($contract['wage_month_overtime_flg']);            
            $contract['wage_month_overtime_flg'] =  $data;

            $contractopt = DB::table('dispatch_code')
                ->selectRaw("dispatch_code.id as id, dispatch_code.kbn as kbn, ifnull(contract_option.status, 0) as checked")
                ->leftJoin('contract_option', function ($join) use ($id) {
                    $join->on('contract_option.dispatch_code_id', 'dispatch_code.id')
                         ->where('contract_option.contract_id', $id);
                })
                ->get();
            foreach($contractopt as &$item){
                $item->checked = $item->checked == 1;
            };
            $workday = $contractopt->where('kbn', DispatchUtils::CODE_KBN_WEEK)->values();
            $business = $contractopt->where('kbn', DispatchUtils::CODE_KBN_BUSINESS)->values();
             
            $contract['workdays'] = $workday;
            $contract['businesses'] = $business;

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true, 'contract'=>$contract]);
    }

    public function deletes(Request $request){
        $user = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_CONTRACT_SETTING_DELETE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }   
        $ids  = $request->get('cids', []);
        DB::beginTransaction();
        try{
            $contract = $this->contract
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

    public function save(Request $request){

        $user       = $request->user();
        $username = $user->getFullName();
        $item_info = $request->get('item');
        $id = $item_info['id'];

        if ($id == 0) {
            if(!$user->can(PermissionUtils::PERMISSION_CONTRACT_SETTING_CREATE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }   
        }else{
            if(!$user->can(PermissionUtils::PERMISSION_CONTRACT_SETTING_UPDATE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }   
        }
        $contract = $item_info;
        $contract['update_user'] = $username;
        unset($contract['id']);
        unset($contract['workdays']);
        unset($contract['businesses']);
        $this->setDate($contract);

        if (isset($contract['price_month_overtime_flg'])){
            $contract['price_month_overtime_flg'] = $contract['price_month_overtime_flg']['checked']==true?1:0;
        }
        if (isset($contract['wage_month_overtime_flg'])){
            $contract['wage_month_overtime_flg'] = $contract['wage_month_overtime_flg']['checked']==true?1:0;
        }

        $validator = Validator::make($contract, $this->contract->rules()
        , $this->contract->messages()
        , $this->contract->attributes());
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();         
            return response()->json(['status' => false,'message' => $message_all]);
        }

        DB::beginTransaction();
        try{
            if ($id == 0) {
                $contract['create_user'] = $username;
                $contract['mst_admin_id'] = $user->id;
                $contract['mst_company_id'] = $user->mst_company_id;

                $id = contract::insertGetId($contract);
                $options = collect();
                $this->getoption($item_info['workdays'], $id, $username, $options);
                $this->getoption($item_info['businesses'], $id, $username, $options);

                ContractOption::insert($options->toArray());

            }else{

                Contract::find($id)
                    ->fill($contract)
                    ->save();
                $options = collect();
                $this->getoption($item_info['workdays'], $id, $username, $options);
                $this->getoption($item_info['businesses'], $id, $username, $options);

                ContractOption::where('contract_id', $id)->delete();
                if (count($options)>0) ContractOption::insert($options->toArray());
              
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true, 'items' => $contract]);
    }
    public function getdispatcharea(Request $request){
        $page         = $request->get('page','1');
        $limit        = $request->get('limit') ? $request->get('limit') : 20;
        $user         = $request->user();

        $orderBy      = $request->get('orderBy','create_at');
        $orderDir     = $request->get('orderDir','desc');
        $where = [];
        $where_arg = [];
        if(!$user->can(PermissionUtils::PERMISSION_CONTRACT_SETTING_VIEW) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }  
        $this->getParamDispatchArea($user->mst_company_id, $request->get('items'), $where, $where_arg);

        try{
            $ordertable = 'dispatcharea.';
            switch($orderBy){
                case 'company_name':
                case 'office_name':
                    $ordertable = 'dispatcharea_agency.';
                    break;
            }


            $dispatcharea = DB::table('dispatcharea_agency')
            ->join('dispatcharea', 'dispatcharea_agency.id', 'dispatcharea.dispatcharea_agency_id')
            ->selectRaw("dispatcharea_agency.company_name "
                .", dispatcharea_agency.office_name"
                .", CONCAT(dispatcharea_agency.address1, dispatcharea_agency.address2) as office_address"
                .", dispatcharea.department"
                .", dispatcharea.position"
                .", CONCAT(dispatcharea.address1, dispatcharea.address2) as employment_address"
                .", dispatcharea.main_phone_no as employment_phone_no"
                .", dispatcharea.responsible_name"
                .", dispatcharea.responsible_phone_no"
                .", dispatcharea.commander_name"
                .", dispatcharea.commander_phone_no"
                .", dispatcharea.troubles_name"
                .", dispatcharea.troubles_phone_no"
                .", dispatcharea.fraction_type")
            ->whereRaw(implode(" AND ", $where), $where_arg)
            ->orderBy($ordertable.$orderBy, $orderDir);

            $dispatcharea = $dispatcharea->get();
            $pagedispatcharea = new LengthAwarePaginator($dispatcharea->forPage($page, $limit), count($dispatcharea), $limit);

            return response()->json(['status' => true, 'items' => $pagedispatcharea]);

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

    }
    public function getuser(Request $request){
        $page         = $request->get('page','1');
        $limit        = $request->get('limit') ? $request->get('limit') : 20;
        $user         = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_CONTRACT_SETTING_VIEW) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }  
        $orderBy      = $request->get('orderBy','create_at');
        $orderDir     = $request->get('orderDir','desc');
        $where = [];
        $where_arg = [];
        $this->getParamUser($user->mst_company_id, $request->get('items'), $where, $where_arg);

        try{
            $users = DB::table('mst_user')
            ->selectRaw(" CONCAT(family_name, given_name) as name")
            ->whereRaw(implode(" AND ", $where), $where_arg)
            ->orderBy($orderBy, $orderDir);

            $users = $users->get();
            $pageusers = new LengthAwarePaginator($users->forPage($page, $limit), count($users), $limit);
            return response()->json(['status' => true, 'items' => $pageusers]);

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }
    private function getoption($codes, $contract_id, $username, Collection $contractoptions){

        foreach($codes as $key => $value){
            if ($value['checked'] == true) { 
                  
                $contractoptions->push(array(
                    'contract_id' => $contract_id,
                    'dispatch_code_id' =>$value['id'] ,
                    'status' => true,
                    'create_user' => $username,
                    'update_user' => $username
                ));
              
            } 
        }
    }
    private function setDate(&$contract){
        $dateitem = array('contract_fromdate', 'contract_todate', 
            'contract_date_sheet', 'basiccontract_fromdate', 'basiccontract_todate',
            'socialinsurance_fromdate', 'socialinsurance_todate', 
            'employmentinsurance_fromdate', 'employmentinsurance_todate', 
            'employmentcontract_date', 'employmentconversion_date',
            'organization_date', 'organization_date_sheet',
            'office_date', 'office_date_sheet',
            'employmentworker_fromdate', 'employmentworker_todate',
            'project_fromdate', 'project_todate',
            'closedwork_fromdate', 'closedwork_todate');

        foreach ($dateitem as $value){

            if (isset($contract[$value])) $contract[$value] = date('Y/m/d', strtotime($contract[$value]));
        }

    }
    private function getParam(Request $request, $companyid, &$where, &$where_arg){
        $where     = [];
        $where_arg = [];

        try{
            $where[]        = ' del_flg = ?';
            $where_arg[]    = 0;
            $where[]        = ' mst_company_id = ?';
            $where_arg[]    = $companyid;
            if($request->get('sc_dispatcharea_name')){
                $where[]        = ' dispatcharea_name like ?';
                $where_arg[]    = "%".$request->get('sc_dispatcharea_name')."%";
            }
            if($request->get('sc_staff_name')){
                $where[]        = ' sc_staff_name like ?';
                $where_arg[]    = "%".$request->get('sc_staff_name')."%";
            }
            if($request->get('scs_fromdate')){
                $where[]        = " DATE_FORMAT(contract_fromdate, '%Y/%m/%d') >= ?";
                $where_arg[]    = date('Y/m/d', strtotime($request->get('scs_fromdate')));
            }
            if($request->get('scs_todate')){
                $where[]        = " DATE_FORMAT(contract_todate, '%Y/%m/%d') <= ?";
                $where_arg[]    = date('Y/m/d', strtotime($request->get('scs_todate')));
            }

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
    }
    private function getParamDispatchArea($companyid, $param, &$where, &$where_arg){
        $where = [ ];
        $where_arg = [];
        $dispatcharea_name = $param['sc_dispatcharea_name'] ? $param['sc_dispatcharea_name'] : '';
        try{
            $where[]        = ' dispatcharea_agency.del_flg = ?';
            $where_arg[]    = 0;
            $where[]        = ' dispatcharea_agency.mst_company_id = ?';
            $where_arg[]    = $companyid;
            if($dispatcharea_name){
                $where[]        = ' dispatcharea_agency.company_name like ?';
                $where_arg[]    = "%".$company_name."%";
            }
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
    }
    private function getParamUser($companyid, $param, &$where, &$where_arg){
        $where = [ ];
        $where_arg = [];
        $name = $param['sc_name'] ? $param['sc_name'] : '';
        try{
            $where[]        = ' state_flg = ?';
            $where_arg[]    = 1;
            $where[]        = ' mst_company_id = ?';
            $where_arg[]    = $companyid;
            if($name){
                $where[]        = ' (family_name like ? OR given_name like ? )';
                $where_arg[]    = "%".$name."%";
                $where_arg[]    = "%".$name."%";
            }
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
    }

}
