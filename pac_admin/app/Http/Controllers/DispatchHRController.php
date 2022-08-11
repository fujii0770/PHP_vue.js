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
use App\Models\Dispatchhr;
use App\Models\DispatchhrInfo;
use App\Models\DispatchhrOption;
use App\Models\DispatchhrTemplate;
use App\Models\DispatchhrScreenitems;
use App\Models\DispatchhrTemplateSetting;
use App\Models\DispatchhrJobcareer;
use App\Models\DispatchCode;
use Session;
use Illuminate\Support\Facades\Validator;

class DispatchHRController extends AdminController
{

    private $dispatchhr;
    private $dispatchhr_info;
    private $dispatchhr_option;
    private $dispatchhr_template;
    private $dispatchhr_screenitems;
    private $dispatchhr_template_setting;
    private $dispatchhr_jobcareer;
    private $dispatch_code;
    private $maxitemsid;
    private $screenitems_list;

    public function __construct(Dispatchhr $dispatchhr, DispatchhrInfo $dispatchhr_info, DispatchhrOption $dispatchhr_option
    , DispatchhrTemplate $dispatchhr_template, DispatchhrScreenitems $dispatchhr_screenitems, DispatchhrTemplateSetting $dispatchhr_template_setting
    , DispatchhrJobcareer $dispatchhr_jobcareer, DispatchCode $dispatch_code )
    {
        parent::__construct();
        $this->dispatchhr = $dispatchhr;
        $this->dispatchhr_info = $dispatchhr_info;
        $this->dispatchhr_option = $dispatchhr_option;
        $this->dispatchhr_template = $dispatchhr_template;
        $this->dispatchhr_screenitems = $dispatchhr_screenitems;
        $this->dispatchhr_template_setting = $dispatchhr_template_setting;
        $this->dispatchhr_jobcareer = $dispatchhr_jobcareer;
        $this->dispatch_code = $dispatch_code;

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $user = \Auth::user();

        if(!$user->can(PermissionUtils::PERMISSION_DISPATCHHR_SETTING_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }              
        $action = $request->get('action','');
        $page       = $request->get('page', 1);
        $limit = $request->get('limit') ? $request->get('limit') : 20;
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'create_at';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir'): 'desc';
        $dispatchhr_list = [];
        $settinginfo = new Collection(); 
        $templateinfo = new Collection(); 
        $this->getSetting($user->mst_company_id, $templateinfo,$settinginfo);
        if ($action=='delete') $this->deletes($request);
        if ($action != ''){

            $where     = [];
            $where_arg = [];
            $this->getParam($request, $user->mst_company_id, $where, $where_arg);
            $dispatchhr_list = $this->dispatchhr
                ->leftJoin('dispatch_code', 'dispatchhr.gender_type', 'dispatch_code.id')
                ->selectRaw("dispatchhr.id, dispatchhr.furigana, dispatchhr.name, dispatchhr.nearest_station, dispatch_code.name as gender_type, dispatchhr.age")
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->orderBy('dispatchhr.'.$orderBy, $orderDir)
                ->paginate($limit)->appends(request()->input());
            $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
        }
        $codeall = $this->dispatch_code->getCodeAll();
        $code_registkbn = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_REGISTKBN);
        $code_gender = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_GENDER);
        $code_contact = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_CONTACT);
        $code_employment = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_EMPLOYMENT);
        $code_employmentkbn = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_EMPLOYMENT_KBN);
        $code_attendance = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_ATTENDANCE);
        $code_finished = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_FINISHED);
        $code_worklocation = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_WORKLOCATION);
        $code_employmentform = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_EMPLOYMENTFORM);
        $code_desiredamount = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_DESIREDAMOUNT);
        $code_desiredjob = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_DESIREDJOB);
        $code_yearsofexperience = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_YEARSOFEXPERIENCE);
        $code_experiencedjob = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_EXPERIENCEDJOB);
        $code_5stages = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_5STAGES);
        $code_abcstages = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_ABCSTAGES);
        $code_basicmanner = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_BASICMANNER);
        $code_workmanner = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_WORKMANNER);
        $code_teamwork = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_TEAMWORK);
        $code_communication = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_COMMUNICATION);
        $code_cooperation = DispatchUtils::getCode($codeall, DispatchUtils::CODE_KBN_COOPERATION);
        $i = 1;
        foreach($code_contact as &$item){
            $item->model = 'dispatchhr.contact_method'.$i;
            $i++;
        }

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->assign('code_registkbn', $code_registkbn);
        $this->assign('code_gender', $code_gender);
        $this->assign('code_contact', $code_contact);
        $this->assign('code_employment', $code_employment);
        $this->assign('code_employmentkbn', $code_employmentkbn);
        $this->assign('code_attendance', $code_attendance);
        $this->assign('code_finished', $code_finished);
        $this->assign('code_worklocation', $code_worklocation);
        $this->assign('code_employmentform', $code_employmentform);
        $this->assign('code_desiredamount', $code_desiredamount);
        $this->assign('code_desiredjob', $code_desiredjob);
        $this->assign('code_yearsofexperience', $code_yearsofexperience);
        $this->assign('code_experiencedjob', $code_experiencedjob);
        $this->assign('code_5stages', $code_5stages);
        $this->assign('code_abcstages', $code_abcstages);
        $this->assign('code_basicmanner', $code_basicmanner);
        $this->assign('code_workmanner', $code_workmanner);
        $this->assign('code_teamwork', $code_teamwork);
        $this->assign('code_communication', $code_communication);
        $this->assign('code_cooperation', $code_cooperation);

        $this->assign('allow_create', $user->can(PermissionUtils::PERMISSION_DISPATCHHR_SETTING_CREATE));
        $this->assign('allow_update', $user->can(PermissionUtils::PERMISSION_DISPATCHHR_SETTING_UPDATE));
        $this->assign('allow_delete', $user->can(PermissionUtils::PERMISSION_DISPATCHHR_SETTING_DELETE));

        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);
        $this->assign('dispatchhr_list', $dispatchhr_list);
        $this->assign('settinginfo', $settinginfo);
        $this->assign('templateinfo', $templateinfo);

        $this->setMetaTitle('人材管理');
        return $this->render('Dispatch.DispatchHR.index');
    }
    public function geteditdata(Request $request){
        $id = $request->get('id','0');
        $user         = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_DISPATCHHR_SETTING_VIEW) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        $this->maxitemsid = $this->dispatchhr_screenitems->getMaxId();
        $this->screenitems_list = $this->dispatchhr_screenitems->get();
        try{
            $dispatchhr = $this->dispatchhr
            ->where('del_flg', 0)
            ->where('id', $id)
            ->first();

            $dispatchhr_infos = $this->dispatchhr_info
            ->where('dispatchhr_id', $id)
            ->get();
            $this->setEditDispatchhr_info($dispatchhr_infos, $dispatchhr);

            $jobcareer_list = $this->getjobcareer($id);

            $this->setEditCheckbox($dispatchhr);

            $dispatchhropt = DB::table('dispatch_code')
                ->selectRaw("dispatch_code.id as id, dispatch_code.kbn as kbn, ifnull(dispatchhr_option.status, 0) as checked, dispatchhr_option.dispatchhr_screenitems_id as itemsid")
                ->leftJoin('dispatchhr_option', function ($join) use ($id) {
                    $join->on('dispatchhr_option.dispatch_code_id', 'dispatch_code.id')
                         ->where('dispatchhr_option.dispatchhr_id', $id);
                })
                ->get();

            foreach($dispatchhropt as &$item){
                switch($item->itemsid){
                    case 47:
                    case 48:
                    case 49:
                    case 50:
                    case 51:
                        break;
                    default:
                        $item->checked = $item->checked == 1;
                }
            };

            $scitem_33 = $dispatchhropt->where('kbn', DispatchUtils::CODE_KBN_WORKLOCATION)->values();
            $scitem_34 = $dispatchhropt->where('kbn', DispatchUtils::CODE_KBN_EMPLOYMENTFORM)->values();
            $scitem_35 = $dispatchhropt->where('kbn', DispatchUtils::CODE_KBN_DESIREDAMOUNT)->values();
            $scitem_36 = $dispatchhropt->where('kbn', DispatchUtils::CODE_KBN_DESIREDJOB)->values();
            $scitem_40 = $dispatchhropt->where('kbn', DispatchUtils::CODE_KBN_EXPERIENCEDJOB)->values();
            $scitem_47 = $dispatchhropt->where('kbn', DispatchUtils::CODE_KBN_BASICMANNER)->values();
            $scitem_48 = $dispatchhropt->where('kbn', DispatchUtils::CODE_KBN_WORKMANNER)->values();
            $scitem_49 = $dispatchhropt->where('kbn', DispatchUtils::CODE_KBN_TEAMWORK)->values();
            $scitem_50 = $dispatchhropt->where('kbn', DispatchUtils::CODE_KBN_COMMUNICATION)->values();
            $scitem_51 = $dispatchhropt->where('kbn', DispatchUtils::CODE_KBN_COOPERATION)->values();

            $dispatchhr['scitem_33'] = $scitem_33;
            $dispatchhr['scitem_34'] = $scitem_34;
            $dispatchhr['scitem_35'] = $scitem_35;
            $dispatchhr['scitem_36'] = $scitem_36;
            $dispatchhr['scitem_40'] = $scitem_40;
            $dispatchhr['scitem_47'] = $scitem_47;
            $dispatchhr['scitem_48'] = $scitem_48;
            $dispatchhr['scitem_49'] = $scitem_49;
            $dispatchhr['scitem_50'] = $scitem_50;
            $dispatchhr['scitem_51'] = $scitem_51;


        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true, 'dispatchhr' => $dispatchhr, 'jobcareer_list'=>$jobcareer_list]);

    }
    public function save(Request $request){
        $user       = $request->user();
        $userid = $user->id;
        $companyid = $user->mst_company_id;
        $username = $user->getFullName();
        $item_info = $request->get('item');

        $id =$item_info['id'];
        if ($id == 0) {
            if(!$user->can(PermissionUtils::PERMISSION_DISPATCHHR_SETTING_CREATE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }   
        }else{
            if(!$user->can(PermissionUtils::PERMISSION_DISPATCHHR_SETTING_UPDATE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }   
        }

        $this->maxitemsid = $this->dispatchhr_screenitems->getMaxId();
        $this->screenitems_list = $this->dispatchhr_screenitems->get();
        $dispatchhr = array();
        $dispatchhr_info = new Collection();
        $dispatchhr_option = new Collection();
        DB::beginTransaction();
        try{
            $dispatchhr = $this->setdispatchhr($companyid, $userid, $username, $item_info);

            $validator = Validator::make($dispatchhr, $this->dispatchhr->rules()
                , $this->dispatchhr->messages()
                , $this->dispatchhr->attributes());
            if ($validator->fails())
            {
                $message = $validator->messages();
                $message_all = $message->all();         
                return response()->json(['status' => false,'message' => $message_all]);
            }

            if ($id == 0) {

                $id = Dispatchhr::insertGetId($dispatchhr);
                $options = collect();
                $this->setdispatchhr_other($username, $id, $item_info, $dispatchhr_info, $dispatchhr_option);

                DispatchhrInfo::insert($dispatchhr_info->toArray());
                DispatchhrOption::insert($dispatchhr_option->toArray());

            }else{

                Dispatchhr::find($id)
                    ->fill($dispatchhr)
                    ->save();
                $options = collect();
                $this->setdispatchhr_other($username, $id, $item_info, $dispatchhr_info, $dispatchhr_option);

                DispatchhrInfo::where('dispatchhr_id', $id)->delete();
                if (count($dispatchhr_info)>0) DispatchhrInfo::insert($dispatchhr_info->toArray());

                DispatchhrOption::where('dispatchhr_id', $id)->delete();
                if (count($dispatchhr_option)>0) DispatchhrOption::insert($dispatchhr_option->toArray());
              
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true]);
    }
    public function deletes(Request $request){
        $user = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_DISPATCHHR_SETTING_DELETE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }   
        $ids  = $request->get('cids', []);
        DB::beginTransaction();
        try{
            $dispatchhr = $this->dispatchhr
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

    public function savesetting(Request $request){
        $user = $request->user();
        $companyid = $user->mst_company_id;
        $username = $user->getFullName();
        if(!$user->can(PermissionUtils::PERMISSION_DISPATCHHR_SETTING_VIEW) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }   
        $ids  = $request->get('cids', []);
        $savedata  = new Collection(); 
        foreach($ids as $key => $value){
            if ($value==0) continue;
            $savedata->push(array(
                'mst_company_id' =>$companyid,
                'dispatchhr_template_id' =>$value ,
                'create_user' => $username,
                'update_user' => $username
            ));
        }

        DB::beginTransaction();
        try{

            DispatchhrTemplateSetting::where('mst_company_id', $companyid)->delete();

            DispatchhrTemplateSetting::insert($savedata->toArray());

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        return response()->json(['status' => true]);       
    }
    public function geteditjobcareer(Request $request){

        $id = $request->get('id','0');
        $user = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_DISPATCHHR_SETTING_VIEW) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        try{
            $select = 'id'
            .', dispatchhr_id'
            .", cast(concat(work_startym,'01') as datetime) as work_startym"
            .", cast(concat(work_toym,'01') as datetime) as work_toym"
            .', company_department'
            .', industry'
            .', employment'
            .', business_content'
            .', salary'
            .', retirement_reason';
            $jobcareer = $this->dispatchhr_jobcareer
            ->selectRaw($select)
            ->where('id', $id)
            ->first();

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true, 'jobcareer'=>$jobcareer]);

    }
    public function savejobcareer(Request $request){
        $user = $request->user();
        $item_info = $request->get('item');

        if(!$user->can(PermissionUtils::PERMISSION_DISPATCHHR_SETTING_VIEW) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }   
        $id = $item_info['id'];
        $dispatchhrid = $item_info['dispatchhr_id'];
        if ($id == 0) {
            if(!$user->can(PermissionUtils::PERMISSION_DISPATCHHR_SETTING_CREATE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }   
        }else{
            if(!$user->can(PermissionUtils::PERMISSION_DISPATCHHR_SETTING_UPDATE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }   
        }   
        $jobcareer = $item_info;
        $jobcareer['update_user'] = $user->getFullName();
        $this->setYearMonthOnly($jobcareer, 'work_startym');
        $this->setYearMonthOnly($jobcareer, 'work_toym');

        unset($jobcareer['id']);
        $validator = Validator::make($jobcareer
        , $this->dispatchhr_jobcareer->rules());
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        DB::beginTransaction();
        try{
            if ($id == 0) {
                $jobcareer['create_user'] = $user->getFullName();
                Dispatchhrjobcareer::insert($jobcareer);
            }else{

                Dispatchhrjobcareer::find($id)
                    ->fill($jobcareer)
                    ->save();
            }
            DB::commit();
            $jobcareer_list = $this->getjobcareer($dispatchhrid);

        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        return response()->json(['status' => true , 'jobcareer_list' => $jobcareer_list]);       
    }
    public function deletejobcareer(Request $request){
        $user = $request->user();
        $companyid = $user->mst_company_id;
        $username = $user->getFullName();
        if(!$user->can(PermissionUtils::PERMISSION_DISPATCHHR_SETTING_VIEW) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }   
        $ids  = $request->get('cids', []);
        $dispatchhrid  = $request->get('dispatchhrid', 0);

        DB::beginTransaction();
        try{

            DispatchhrJobcareer::wherein('id', $ids)->delete();            
            DB::commit();
            $jobcareer_list = $this->getjobcareer($dispatchhrid);
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        return response()->json(['status' => true, 'jobcareer_list' => $jobcareer_list]);       
    }

    private function setdispatchhr($companyid, $userid, $username, $item_info){

        $arrkey = array(
            'regist_kbn', 'gender_type', 'birthdate', 'age',
             'phone_no', 'mobile_phone_no', 'fax_no', 
             'email', 'mail_send_flg', 'mobile_email', 'mobile_mail_send_flg', 
             'contact_method1', 'contact_method2', 'contact_method3', 'contact_method4', 'contact_method5',
             'nearest_station', 'postal_code', 'address1', 'address2'
        );
        $ret = array();
        $ret['update_user'] = $username;
        $ret['name'] = $item_info['name'];
        $ret['furigana'] = $item_info['furigana'];
    if ($item_info['id'] == 0){
            $ret['mst_admin_id'] = $userid;
            $ret['mst_company_id'] = $companyid;
            $ret['create_user'] = $username;

            foreach($arrkey as $key){
                if (array_key_exists($key, $item_info) && isset($item_info[$key])) $ret[$key] = $item_info[$key];
            }
            $this->setDateCheckbox($ret);

           return $ret;
    
        } else{
            foreach($arrkey as $key){
                if (array_key_exists($key, $item_info) && isset($item_info[$key])) $ret[$key] = $item_info[$key];
            }
            $this->setDateCheckbox($ret);
            return $ret;
        }
    }

    private function setdispatchhr_other($username, $id, $item_info, Collection &$dispatchhr_info, Collection &$dispatchhr_option){

        for ($i = 1; $i <= $this->maxitemsid; $i++){
            if (!array_key_exists('scitem_'.$i, $item_info)) continue;
            $screenitem = $this->screenitems_list->where('id', $i)->first();

            if ($screenitem) {
                switch($screenitem->regist_table_kbn){
                    case 0:
                        $ischeckbox = false;
                        if ($screenitem->type == 'checkbox')$ischeckbox = true;
                        $dispatchhr_info->push(
                            $this->setdispatchhr_info($username, $id, $i, $ischeckbox, $item_info['scitem_'.$i])
                        );
                        break;
                    case 1:
                        $isradio = false;
                        if ($screenitem->type == 'radio') $isradio = true;
                        $this->getoption($item_info['scitem_'.$i], $id, $i, $username, $isradio, $dispatchhr_option);
                        break;
                }
            }
        }
    }

    private function setdispatchhr_info($username, $dispatchhrid, $screenitemsid, $ischeckbox, $value){
        $val = "";
        if ($ischeckbox){
            $val = $value['checked']==true?1:0;
        } else{
            $val=$value;
        }
        return array(
            'dispatchhr_id' => $dispatchhrid,
            'dispatchhr_screenitems_id' => $screenitemsid,
            'value' => $val,
            'create_user' => $username,
            'update_user' => $username
        );
    }
    private function getoption($codes, $id, $itemsid, $username, $isradio, Collection $dispatchhroptions){
        foreach($codes as $key => $value){
            if ($isradio) {
                if ($value['checked']) { 
                     $dispatchhroptions->push(array(
                        'dispatchhr_id' => $id,
                        'dispatchhr_screenitems_id' => $itemsid,
                        'dispatch_code_id' =>$value['id'] ,
                        'status' => $value['checked'],
                        'create_user' => $username,
                        'update_user' => $username
                    ));
                  
                } 
            }else{
                if ($value['checked'] == true) { 
                  
                    $dispatchhroptions->push(array(
                        'dispatchhr_id' => $id,
                        'dispatchhr_screenitems_id' => $itemsid,
                        'dispatch_code_id' =>$value['id'] ,
                        'status' => true,
                        'create_user' => $username,
                        'update_user' => $username
                    ));
                  
                } 
    
            }
        }
    }

    private function getSetting($company_id, &$template, &$setting){
        try{
            $select = 'dispatchhr_template.id as id '
                    .' , CASE dispatchhr_template_setting.dispatchhr_template_id IS NULL WHEN 0 THEN 1 ELSE 0 END as setid';
            $setting_list =  $this->dispatchhr_template
                ->leftJoin('dispatchhr_template_setting', function ($join) use ($company_id) {
                    $join->on('dispatchhr_template.id', 'dispatchhr_template_setting.dispatchhr_template_id')
                        ->where('dispatchhr_template_setting.mst_company_id', $company_id);
                })
               ->selectRaw($select)
                ->get();
            $setting = new Collection(); 
            $template = new Collection();  
            $setting->put('set_0', 1);
            $template->put('set_0', 1);            
            foreach($setting_list as $key => $value){
                $key = 'set_'.$value->id; 
                $setting->put($key, $value->setid);
                $template->put($key, 1);
            }

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
    }
    private function getjobcareer($id){
        $select = 'dispatchhr_jobcareer.id'
        .', dispatchhr_jobcareer.work_startym'
        .', dispatchhr_jobcareer.work_toym'
        .', dispatchhr_jobcareer.company_department'
        .', dispatchhr_jobcareer.industry'
        .', dispatch_code.name as employment'
        .', dispatchhr_jobcareer.business_content';
        $jobcareer_list = $this->dispatchhr_jobcareer
        ->leftJoin('dispatch_code', 'dispatchhr_jobcareer.employment', 'dispatch_code.id')
        ->selectRaw($select)
        ->where('dispatchhr_jobcareer.dispatchhr_id', $id)
        ->orderBy('dispatchhr_jobcareer.work_toym', 'desc')
        ->get();
        return $jobcareer_list;
    }
    private function getParam(Request $request, $companyid, &$where, &$where_arg){
        $where     = [];
        $where_arg = [];

        try{
            $where[]        = ' dispatchhr.del_flg = ?';
            $where_arg[]    = 0;
            $where[]        = ' dispatchhr.mst_company_id = ?';
            $where_arg[]    = $companyid;
            if($request->get('sc_name')){
                $where[]        = ' dispatchhr.name like ?';
                $where_arg[]    = "%".$request->get('sc_name')."%";
            }

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
    }
    private function setDateCheckbox(&$dispatchhr){
        $this->setDateOnly($dispatchhr, 'birthdate');
        $this->setCheckBoxOnly($dispatchhr, 'mail_send_flg');
        $this->setCheckBoxOnly($dispatchhr, 'mobile_mail_send_flg');
        $this->setCheckBoxOnly($dispatchhr, 'contact_method1');
        $this->setCheckBoxOnly($dispatchhr, 'contact_method2');
        $this->setCheckBoxOnly($dispatchhr, 'contact_method3');
        $this->setCheckBoxOnly($dispatchhr, 'contact_method4');
        $this->setCheckBoxOnly($dispatchhr, 'contact_method5');
    }
    private function setEditCheckbox(&$dispatchhr){
        $this->setEditCheckboxOnly($dispatchhr, 'mail_send_flg');
        $this->setEditCheckboxOnly($dispatchhr, 'mobile_mail_send_flg');
        $this->setEditCheckboxOnly($dispatchhr, 'contact_method1');
        $this->setEditCheckboxOnly($dispatchhr, 'contact_method2');
        $this->setEditCheckboxOnly($dispatchhr, 'contact_method3');
        $this->setEditCheckboxOnly($dispatchhr, 'contact_method4');
        $this->setEditCheckboxOnly($dispatchhr, 'contact_method5');
    }

    private function setDateOnly(&$dispatchhr, $value){
        if (isset($dispatchhr[$value])) $dispatchhr[$value] = date('Y/m/d', strtotime($dispatchhr[$value]));

    }
    private function setYearMonthOnly(&$dispatchhr, $value){
        if (isset($dispatchhr[$value])) $dispatchhr[$value] = date('Ym', strtotime($dispatchhr[$value]));

    }
    private function setCheckBoxOnly(&$dispatchhr, $value){
        if (isset($dispatchhr[$value])){
            $dispatchhr[$value] = $dispatchhr[$value]['checked']==true?1:0;
        }
    }
    private function setEditCheckboxOnly(&$dispatchhr, $value){
        $data = [
            'checked' => $dispatchhr[$value] ? true: false
        ];   
        unset($dispatchhr[$value]);            
        $dispatchhr[$value] =  $data;
    }

    private function setEditDispatchhr_info($dispatchhr_infos, &$dispatchhr){

        foreach($dispatchhr_infos as $dispatchhr_info){
            $itemid =  $dispatchhr_info['dispatchhr_screenitems_id'];
            $screenitem = $this->screenitems_list->where('id', $itemid)->first();
            if ($screenitem) {
                switch($screenitem->type){
                    case 'text':
                    case 'textarea':
                    case 'label':
                    case 'date':
                        $scitemid = 'scitem_'.$itemid;
                        $dispatchhr[$scitemid] = $dispatchhr_info['value'];
                        break;
                    case 'radio':
                    case 'checkbox':
                        $scitemid = 'scitem_'.$itemid;
                        $dispatchhr[$scitemid] = (int)$dispatchhr_info['value'];
                        if ($screenitem->type == 'checkbox'){
                            $this->setEditCheckboxOnly($dispatchhr, $scitemid);
                        }
                        break;
            
                }
            }
        }

    }
}
