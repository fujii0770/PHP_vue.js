<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController; 
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\HrInfo;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\HrWorkHours;
use App\Http\Utils\AppUtils;
use App\Http\Utils\OperationsHistoryUtils;
use App\Http\Utils\PermissionUtils;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;

class HrUserRegistrationController extends AdminController
{

    private $model;
    private $department;
    private $position;
    private $model_user;
    private $hr_work_hours;
    
    public function __construct(HrInfo $model, Department $department, Position $position, User $model_user, HrWorkHours $hr_work_hours)
    {
        parent::__construct();
        $this->model = $model;
        $this->department = $department;
        $this->position = $position;
        $this->model_user = $model_user;
        $this->hr_work_hours = $hr_work_hours;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $user   = \Auth::user();
        $arrHistory  =  null;
        $action = $request->get('action','');

         // get list user
        $limit      = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
        $optionFlg  = $request->get('optionFlg') ? $request->get('optionFlg'): '0';        
        $orderBy    = $request->get('orderBy') ? $request->get('orderBy') : 'time';
        $orderDir   = $request->get('orderDir') ? $request->get('orderDir'): 'desc';
        $arrOrder   = ['user' => 'user_name','email' => 'U.email','hrUserFlg' => 'U.hr_user_flg',
                       'adminDepartment' => 'D.department_name','position' => 'P.position_name','assignedCompany' => 'H.assigned_company',
                       'startTime' => 'H.Regulations_work_start_time','endTime' => 'H.Regulations_work_end_time'
                      ];

        $filter_email               = $request->get('email','');
        $filter_user                = $request->get('username','');
        $filter_assigned_company    = $request->get('assignedcompany','');

        $where      = ['1 = 1'];
        $where_arg  = [];

        //部門リストの取得
        $listDepartment = $this->department
            ->select('id','parent_id' , 'department_name as name')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('state',1)
            ->get()->keyBy('id');

        $listDepartmentTree = \App\Http\Utils\CommonUtils::arrToTree($listDepartment);

        $listDepartmentTree = \App\Http\Utils\CommonUtils::treeToArr($listDepartmentTree);

        //役職リストの取得
        $listPosition = $this->position
            ->where('state',1)
            ->where('mst_company_id',$user->mst_company_id)
            ->pluck('position_name', 'id')->toArray();
        // get working hours
        $listWorkHours = $this->hr_work_hours
            ->where('mst_company_id',$user->mst_company_id)
            ->pluck('definition_name', 'id')->toArray();

        //if($filter_email){
        //    $where[]        = 'U.email = ?';
        //    $where_arg[]    = $filter_email;
        //}
        
            if(!$user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_HISTORY_VIEW)){
                //$this->raiseWarning(__('message.not_permission_access'));
                //return redirect()->route('home');
            }

            // 検索条件がユーザ区分:一般利用
            if($optionFlg == AppUtils::USER_NORMAL){
                //回覧利用者を表示
                $filter_option_flg = AppUtils::USER_NORMAL;
            }else{
                //以外は受信専用利用者を表示
                $filter_option_flg = AppUtils::USER_RECEIVE;
            }

            $arrHistory = DB::table('mst_user as U')
                ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'U.id',$orderDir)
                ->leftJoin('mst_user_info as I', 'U.id','I.mst_user_id')
                ->leftJoin('mst_department as D', 'I.mst_department_id','D.id')
                ->leftJoin('mst_position as P', 'I.mst_position_id','P.id')
                ->leftJoin('mst_hr_info as H', 'I.mst_user_id','H.mst_user_id')
                ->leftJoin('hr_working_hours as W', 'W.id','H.working_hours_id')
                ->select(DB::raw('U.id, H.id as hr_info_id, U.hr_user_flg, U.option_flg, CONCAT(U.family_name, U.given_name) as user_name,
                U.email, CASE WHEN U.option_flg = ' . AppUtils::USER_NORMAL . ' THEN \'' . AppUtils::USER_NORMAL_NAME . '\' ELSE \'' . AppUtils::USER_RECEIVE_NAME . '\' END AS user_type_name, 
                mst_department_id,D.department_name, mst_position_id, P.position_name, H.assigned_company,
                 H.Regulations_work_start_time, H.Regulations_work_end_time,H.working_hours_id, W.work_form_kbn,
                 U.family_name, U.given_name, W.definition_name,
                 H.Regulations_work_start_time as H_Regulations_work_start_time,
                 H.Regulations_work_end_time as H_Regulations_work_end_time,
                 H.shift1_start_time as H_shift1_start_time,
                 H.shift1_end_time as H_shift1_end_time,
                 H.shift2_start_time as H_shift2_start_time,
                 H.shift2_end_time as H_shift2_end_time,
                 H.shift3_start_time as H_shift3_start_time,
                 H.shift3_end_time as H_shift3_end_time,
                 W.regulations_work_start_time as W_Regulations_work_start_time,
                 W.regulations_work_end_time as W_Regulations_work_end_time,
                 W.shift1_start_time as W_shift1_start_time,
                 W.shift1_end_time as W_shift1_end_time,
                 W.shift2_start_time as W_shift2_start_time,
                 W.shift2_end_time as W_shift2_end_time,
                 W.shift3_start_time as W_shift3_start_time,
                 W.shift3_end_time as W_shift3_end_time
                 
                 '))
                ->where('U.mst_company_id', $user->mst_company_id)
                ->where('U.option_flg', $filter_option_flg)
                ->where('U.state_flg', '1')
                //->whereRaw(implode(" AND ", $where), $where_arg)
                ->where('U.email', 'like', "%$filter_email%")
                //->where('U.family_name', 'like', "%$filter_user%")
                ->where(DB::raw('CONCAT(U.family_name,U.given_name)'), 'like', "%$filter_user%")
                ;
                if($request->get('assignedcompany')){
                    $arrHistory->where('H.assigned_company', 'like', "%$filter_assigned_company%");
                }
                if($request->get('work_form_kbn') != null){
                    switch($request->get('work_form_kbn')){
                        case 0:
                            $arrHistory->where(function($query){
                                $query->where(function($query2){
                                    $query2->where('H.Regulations_work_start_time', '<>', null);
                                    $query2->where('H.Regulations_work_end_time', '<>', null);
                                });
                                $query->orWhere(function($query2){
                                    $query2->where('H.shift1_start_time', null);
                                    $query2->where('H.shift1_end_time', null);
                                    $query2->where('H.shift2_start_time', null);
                                    $query2->where('H.shift2_end_time', null);
                                    $query2->where('H.shift3_start_time', null);
                                    $query2->where('H.shift3_end_time', null);
                                    $query2->where('W.work_form_kbn', 0);
                                });
                            });
                            break;
                        case 1:
                            $arrHistory->where(function($query){
                                $query->where(function($query2){
                                    $query2->orWhere('H.shift1_start_time', '<>' , null);
                                    $query2->orWhere('H.shift1_end_time', '<>' , null);
                                    $query2->orWhere('H.shift2_start_time', '<>' , null);
                                    $query2->orWhere('H.shift2_end_time', '<>' , null);
                                    $query2->orWhere('H.shift3_start_time', '<>' , null);
                                    $query2->orWhere('H.shift3_end_time', '<>' , null);
                                });
                                $query->orWhere(function($query2){
                                    $query2->where('H.Regulations_work_start_time', null);
                                    $query2->where('H.Regulations_work_end_time', null);
                                    $query2->where('W.work_form_kbn', 1);
                                });
                            });
                            break;
                        case 2:
                            $arrHistory->where(function($query){
                                $query->where('H.Regulations_work_start_time', null);
                                $query->where('H.Regulations_work_end_time', null);
                                $query->where('H.shift1_start_time', null);
                                $query->where('H.shift1_end_time', null);
                                $query->where('H.shift2_start_time', null);
                                $query->where('H.shift2_end_time', null);
                                $query->where('H.shift3_start_time', null);
                                $query->where('H.shift3_end_time', null);
                                $query->where('W.work_form_kbn', 2);
                            });
                            break;
                    }
                }
                if($request->get('working_hours_id')){
                    $arrHistory->where('H.working_hours_id', $request->get('working_hours_id'));
                }
                if(($request->get('hrUserFlg') || ($request->get('hrUserFlg') == '0' ))){
                    $arrHistory->where('U.hr_user_flg', $request->get('hrUserFlg'));
                }
            
            if($action == 'export'){
                $arrHistory = $arrHistory ->get();
            }else{
                $arrHistory = $arrHistory ->paginate($limit)->appends(request()->input());
            }
             
            $this->setMetaTitle('利用ユーザ登録');
            $this->assign('user_title', '管理者');

            $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
        
        $this->assign('listDepartment', $listDepartment);
        $this->assign('listDepartmentTree', $listDepartmentTree);
        $this->assign('listPosition', $listPosition);
        $this->assign('listWorkHours', $listWorkHours);
        $this->assign('arrHistory', $arrHistory);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);
        $this->assign('optionFlg', $optionFlg);
         
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        if($action == 'export'){
            return $this->render('OperationHistory.csv');
        }else{
            return $this->render('HrUserRegistration.index');
        }
 
    }

    public function getList(Request $request){

    }
     
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user   = \Auth::user();

        if(!$user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_HISTORY_VIEW)){
            //$this->raiseWarning(__('message.not_permission_access'));
            //return redirect()->route('home');
        }

        $item = DB::table('mst_user as U')
        ->leftJoin('mst_user_info as I', 'U.id','I.mst_user_id')
        ->leftJoin('mst_department as D', 'I.mst_department_id','D.id')
        ->leftJoin('mst_position as P', 'I.mst_position_id','P.id')
        ->leftJoin('mst_hr_info as H', 'I.mst_user_id','H.mst_user_id')
        ->select(DB::raw('U.id, H.id as hr_info_id, U.hr_user_flg, CONCAT(U.family_name, U.given_name) as user_name,U.email,D.department_name, P.position_name, H.assigned_company, H.Regulations_work_start_time,
         H.shift1_start_time,H.shift1_end_time,H.shift2_start_time,H.shift2_end_time,H.shift3_start_time,H.shift3_end_time,H.working_hours_id,
         H.Regulations_work_end_time, H.Regulations_work_end_time, H.Overtime_unit, break_time '))
        ->where('U.id', $id)
        ->first();

        $work=DB::table("hr_working_hours")->where("mst_company_id",$user->mst_company_id)->pluck('definition_name','id')->toArray();

        return response()->json(['status' => true, 'item' => $item ,'work'=>$work]);
    }
 
    /**
     * Store a hr_user_info.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        $item_info = $request->get('item');
        //mst_hr_infoの編集
        $item_info['mst_user_id'] = $item_info['id'];

        //受信したデータフォーマットは標準ではなくnullに設定されています,時間はペアで現れなければならない。
        $onetime['0']=$item_info['Regulations_work_start_time']=$this->valitime($item_info['Regulations_work_start_time']);
        $onetime['1']=$item_info['Regulations_work_end_time']=$this->valitime($item_info['Regulations_work_end_time']);
        $twotime['0']=$item_info['shift1_start_time']=$this->valitime($item_info['shift1_start_time']);
        $twotime['1']=$item_info['shift1_end_time']=$this->valitime($item_info['shift1_end_time']);
        $threetime['0']=$item_info['shift2_start_time']=$this->valitime($item_info['shift2_start_time']);
        $threetime['1']=$item_info['shift2_end_time']=$this->valitime($item_info['shift2_end_time']);
        $fourtime['0']=$item_info['shift3_start_time']=$this->valitime($item_info['shift3_start_time']);
        $fourtime['1']=$item_info['shift3_end_time']=$this->valitime($item_info['shift3_end_time']);

        $a=array_filter($onetime);
        $b=array_filter($twotime);
        $c=array_filter($threetime);
        $d=array_filter($fourtime);
        //出勤時間&就業時間をペアで指定してください
       if((sizeof($a)==1)||(sizeof($b)==1)||(sizeof($c)==1)||(sizeof($d)==1)){
            return response()->json(['status' => false,
                'message' => [__('message.false.pairstime_hr_user')]
            ]);
        }

                //出勤時間&就業時間を指定してください。
                //時間四択一
                if(
                    (empty($item_info['Regulations_work_start_time'])||empty($item_info['Regulations_work_end_time']))&&
                    (empty($item_info['shift1_start_time'])||empty($item_info['shift1_end_time']))&&
                    (empty($item_info['shift2_start_time'])||empty($item_info['shift2_end_time']))&&
                    (empty($item_info['shift3_start_time'])||empty($item_info['shift3_end_time']))&&
                    empty($item_info['workselect'])
                ){
                    return response()->json(['status' => false,
                        'message' => [__('message.false.musttime_hr_user')]
                    ]);

                }

        $validator = Validator::make($item_info, $this->model->rules());
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }
        //if($item_info['guest_company_flg'] && (!isset($item_info['mst_company_id']) || !isset($item_info['host_company_name'])) ){
        //    return response()->json(['status' => false,'message' => [__('message.false.create_guest_gompany')]]);
        //}

        $item = new $this->model;
        $item->fill($item_info);
        $item->create_user = $user->getFullName();
        $item->update_user = $user->getFullName();
        $item->id = 0;
        $item->working_hours_id=isset($item_info['workselect']) ? $item_info['workselect'] : null;
        //mst_userの編集
        $item2 = $this->model_user->find($item_info['id']);
        $item2->hr_user_flg = $item_info['hr_user_flg'];

        // 1：シフト勤務 //0：通常勤務
        if(sizeof($a)=='2'){
            $item2->shift_flg='0';
        }
        else{
            $item2->shift_flg='1';
        }
        if(($item->working_hours_id != null) && (sizeof($a)<=1) && (sizeof($b) <=1 )  && (sizeof($c) <=1 )  && (sizeof($d) <=1 )){
            $hour_work=DB::table("hr_working_hours")
                ->select("work_form_kbn")
                ->where("id",$item->working_hours_id)
                ->first();
            switch($hour_work->work_form_kbn){
                case 0:
                    $item2->shift_flg='0';
                    break;
                case 1:
                    $item2->shift_flg='1';
                    break;
                case 2:
                    $item2->shift_flg='0';
                    break;
                default:
                    $item2->shift_flg='0';
                    break;
            }
        }
        DB::beginTransaction();
        try{
            $item->save();
            $item2->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true, 'hr_info_id' => $item->id,
                'message' => [__('message.success.create_hr_user')]
        ]);
    }

    function update($id, Request $request){
        $user = \Auth::user();        

        //mst_hr_infoの編集
        $item_post  = $request->get('item');

        $onetime['0']=$item_post['Regulations_work_start_time']=$this->valitime($item_post['Regulations_work_start_time']);
        $onetime['1']=$item_post['Regulations_work_end_time']=$this->valitime($item_post['Regulations_work_end_time']);
        $twotime['0']=$item_post['shift1_start_time']=$this->valitime($item_post['shift1_start_time']);
        $twotime['1']=$item_post['shift1_end_time']=$this->valitime($item_post['shift1_end_time']);
        $threetime['0']=$item_post['shift2_start_time']=$this->valitime($item_post['shift2_start_time']);
        $threetime['1']=$item_post['shift2_end_time']=$this->valitime($item_post['shift2_end_time']);
        $fourtime['0']=$item_post['shift3_start_time']=$this->valitime($item_post['shift3_start_time']);
        $fourtime['1']=$item_post['shift3_end_time']=$this->valitime($item_post['shift3_end_time']);

        $a=array_filter($onetime);
        $b=array_filter($twotime);
        $c=array_filter($threetime);
        $d=array_filter($fourtime);
        //出勤時間&就業時間をペアで指定してください
        if((sizeof($a)==1)||(sizeof($b)==1)||(sizeof($c)==1)||(sizeof($d)==1)){
            return response()->json(['status' => false,
                'message' => [__('message.false.pairstime_hr_user')]
            ]);
        }

        //出勤時間&就業時間を指定してください。
        if(
            (empty($item_post['Regulations_work_start_time'])||empty($item_post['Regulations_work_end_time']))&&
            (empty($item_post['shift1_start_time'])||empty($item_post['shift1_end_time']))&&
            (empty($item_post['shift2_start_time'])||empty($item_post['shift2_end_time']))&&
            (empty($item_post['shift3_start_time'])||empty($item_post['shift3_end_time']))&&
            empty($item_post['workselect'])
        ){
            return response()->json(['status' => false,
                'message' => [__('message.false.musttime_hr_user')]
            ]);

        }


        $item = $this->model->find($id);

        $validator = Validator::make($item_post, $this->model->rules());        
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        $item->fill($item_post);
        $item->update_user = $user->getFullName();

        //mst_userの編集
        $item2 = $this->model->find($id)->user;
        $item2->hr_user_flg = $item_post['hr_user_flg'];
        $item->working_hours_id=isset($item_post['workselect']) ? $item_post['workselect'] : null;
        // 1：シフト勤務 //0：通常勤務
        if(sizeof($a)=='2'){
            $item2->shift_flg='0';
        }
        else{
            $item2->shift_flg='1';
        }
        if(($item->working_hours_id != null) && (sizeof($a)<=1) && (sizeof($b) <=1 )  && (sizeof($c) <=1 )  && (sizeof($d) <=1 )){
             $hour_work=DB::table("hr_working_hours")
                 ->select("work_form_kbn")
                 ->where("id",$item->working_hours_id)
                 ->first();
             switch($hour_work->work_form_kbn){
                 case 0:
                     $item2->shift_flg='0';
                     break;
                 case 1:
                     $item2->shift_flg='1';
                     break;
                 case 2:
                     $item2->shift_flg='0';
                     break;
                 default:
                     $item2->shift_flg='0';
                     break;
             }
        }

        DB::beginTransaction();
        try{
            $item->save();
            $item2->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        return response()->json(['status' => true, 'id' => $item->id, 'message' => [__('message.success.update_hr_user')]
            ]);
    }

    private   function valitime($timename){

        return strtotime($timename)?$timename:null;
    }

    function update2($id, Request $request){
        $user = \Auth::user();        

        //mst_hr_infoの編集
        $item_post  = $request->get('item');
        $item = $this->model->find($id);

        $validator = Validator::make($item_post, $this->model->rules());        
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        $item->fill($item_post);
        $item->update_user = $user->getFullName();

        //mst_userの編集
        $item2 = $this->model->find($id)->user;
        $item2->hr_user_flg = $item_post['hr_user_flg'];
 
        DB::beginTransaction();
        try{
            $item->save();
            $item2->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        return response()->json(['status' => true, 'id' => $item->id, 'message' => [__('message.success.update_hr_user')]
            ]);
    }
    public function updateHrUser(Request $request){
        $user = \Auth::user();  

        $cids = $request->get('cids',[]);
        $cidsoff = $request->get('cidsoff',[]);
        $items = [];
        if(count($cids)){
            $items = DB::table('mst_user')            
            ->where('mst_company_id',$user->mst_company_id)
            ->whereIn('id', $cids)
            ->get();
        }

        DB::beginTransaction();
        try{
            DB::table('mst_user')
                ->where('hr_user_flg','0')
//                ->where('mst_company_id',$user->mst_company_id)
                ->whereIn('id', $cids)
                ->update(['hr_user_flg' => '1']);

            DB::table('mst_user')
                ->where('hr_user_flg','1')
//                ->where('mst_company_id',$user->mst_company_id)
                ->whereIn('id', $cidsoff)
                ->update(['hr_user_flg' => '0']);

            DB::commit();
            return response()->json(['status' => true,'message' => [__('message.success.update_hr_user')]]);
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }

}