<?php

namespace App\Http\Controllers;

use App\Http\Utils\DepartmentUtils;
use App\Models\Department;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController; 
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\OperationHistory;
use App\Http\Utils\AppUtils;
use App\Http\Utils\OperationsHistoryUtils;
use App\Http\Utils\PermissionUtils;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;
use App\Models\Company;
use Illuminate\Support\Carbon;

class OperationHistoryController extends AdminController
{

    private $model;
    private $department;
    
    public function __construct(OperationHistory $model, Department $department)
    {
        parent::__construct();
        $this->model = $model;
        $this->department = $department;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type, Request $request){
        $user   = \Auth::user();
        // 無害化処理設定時はCSVダウンロード無効化するためのフラグ TODO 非同期化と無害化
        $sanitizing_flg = Company::where('id', $user->mst_company_id)
                                ->first()->sanitizing_flg;
        $long_term_storage_flg = Company::where('id', $user->mst_company_id)
            ->first()->long_term_storage_flg;
        $arrHistory  =  null;
        $action = $request->get('action','');

         // get list user
        $limit      = $request->get('limit') ? $request->get('limit') : 20;
        $orderBy    = $request->get('orderBy') ? $request->get('orderBy') : 'time';
        $orderDir   = $request->get('orderDir') ? $request->get('orderDir'): 'desc';
        // 表示順を追加 PAC_5-163 BEGIN
        $arrOrder   = ['user' => 'user_name','time' => 'H.create_at', 'status' => 'H.result',
            'type' =>'H.mst_operation_id','screen' => 'H.mst_display_id','ipAddress' => 'H.ip_address',
            'email' => 'email','adminDepartment' => 'U.department_name',
            'userDepartment' => 'D.department_name','position' => 'P.position_name'];
        // PAC_5-163 END

        // PAC_5-2098 Start
        $multiple_department_position_flg = DB::table('mst_company')->where('id', $user->mst_company_id)->select('multiple_department_position_flg')->first()->multiple_department_position_flg ?? 0;
        if ($multiple_department_position_flg === 1) {
            // PAC_5-1599 追加部署と役職 Start
            $arrOrder = array_merge($arrOrder, ['department_1' => 'department_name_1','position_1' => 'position_name_1'
                ,'department_2' => 'department_name_2','position_2' => 'position_name_2']);
            // PAC_5-1599 End
        }
        // PAC_5-2098 End
        $select_month    = $request->get('select_month','');
        $filter_user  = $request->get('user','');
        $filter_audit_user  = $request->get('audit_user','');
        $filter_screen  = $request->get('screen','');
        $filter_type    = $request->get('type','');
        $filter_status  = $request->get('status','');

//        $time=date('Y-m-d', strtotime("-2 month"));
        $time=Carbon::now()->addMonthsNoOverflow(-2)->format('Y-m-d');
        $where      = ['1 = 1'];
        $where_arg  = [];

        if($select_month){
            $where[]        = 'Date(H.create_at) like ?';
            $where_arg[]    = "%".$select_month."%";

        }else{
            $where[]        = 'Date(H.create_at) like ?';
            $where_arg[]    ="%".date("Y-m")."%";
            
        }
        if($filter_user){
            $where[]        = 'H.user_id = ?';
            $where_arg[]    = $filter_user;
        }
        if($filter_audit_user){
            $where[]        = 'H.user_id = ?';
            $where_arg[]    = $filter_audit_user;
        }





        if($filter_screen){
            $where[]        = 'H.mst_display_id  = ?';
            $where_arg[]    = "$filter_screen";
        }
        
        if($filter_status != ''){
            $where[]        = 'H.result  = ?';
            $where_arg[]    = "$filter_status";
        }
        
        if($filter_type){
            $where[]        = 'H.mst_operation_id  = ?';
            $where_arg[]    = "$filter_type";
        }
        $id_length=mb_strlen($user->mst_company_id);
        $id_end=substr($user->mst_company_id,-1);
        $listAuditUser = [];
        if($type == 'admin'){
            if(!$user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_HISTORY_VIEW)){
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }
            // PAC_5-486 START
            // 検索項目を追加 PAC_5-163 BEGIN
            if(!$select_month){

                $arrHistory = DB::table('operation_history as H')
                ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'H.id',$orderDir)
                ->leftJoin('mst_admin as U', 'H.user_id','U.id')
                ->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, CONCAT(U.family_name, U.given_name) as user_name,U.email as email, U.department_name'))
                ->where('auth_flg', OperationsHistoryUtils::HISTORY_FLG_ADMIN)
                ->where('U.mst_company_id', $user->mst_company_id)
                ->whereRaw(implode(" AND ", $where), $where_arg);
               
            }elseif($select_month<=$time){

                $arrHistory = DB::table('operation_history'."$id_end".' as H')
                ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'H.id',$orderDir)
                ->leftJoin('mst_admin as U', 'H.user_id','U.id')
                ->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, CONCAT(U.family_name, U.given_name) as user_name,U.email as email, U.department_name'))
                ->where('auth_flg', OperationsHistoryUtils::HISTORY_FLG_ADMIN)
                ->where('U.mst_company_id', $user->mst_company_id)
                ->whereRaw(implode(" AND ", $where), $where_arg);
            
            }else{

            $arrHistory = DB::table('operation_history as H')
                ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'H.id',$orderDir)
                ->leftJoin('mst_admin as U', 'H.user_id','U.id')
                ->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, CONCAT(U.family_name, U.given_name) as user_name,U.email as email, U.department_name'))
                ->where('auth_flg', OperationsHistoryUtils::HISTORY_FLG_ADMIN)
                ->where('U.mst_company_id', $user->mst_company_id)
                ->whereRaw(implode(" AND ", $where), $where_arg);
            }
            if($action == 'export'){
                $arrHistory = $arrHistory ->get();
            }else{
                $arrHistory = $arrHistory ->paginate($limit)->appends(request()->input());
            }
            // 検索項目を追加 PAC_5-163 END
            // PAC_5-486 END

            $arrDisplay = DB::table('mst_display')->where('role', OperationsHistoryUtils::HISTORY_FLG_ADMIN)->select('display_name','id')->pluck('display_name','id');
            $arrOperation_info = DB::table('mst_operation_info')->where('role', OperationsHistoryUtils::HISTORY_FLG_ADMIN)->select('info','id')->pluck('info','id');
            $listUser = DB::table('mst_admin')->where('mst_company_id', $user->mst_company_id)
                ->select(DB::raw('CONCAT(family_name, given_name) name, id'))->pluck('name','id');
             
            $this->setMetaTitle('管理者操作履歴');
            // 表示文言を変更 PAC_5-163 BEGIN
            $this->assign('user_title', '管理者');
            // 表示文言を変更 PAC_5-163 END
        }else if($type == 'user'){
            if(!$user->can(PermissionUtils::PERMISSION_USER_HISTORY_VIEW)){
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }
            // PAC_5-486 START
            // 検索項目を追加 PAC_5-163 BEGIN
            if(!$select_month){
                $arrHistory = DB::table('operation_history as H')
                    ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'H.id',$orderDir)
                    ->leftJoin('mst_audit as MA', function (JoinClause $join) {
                        $join->on("MA.id", "=", "H.user_id")
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_AUDIT_USER);
                    })
                    ->leftJoin('mst_user as U', function (JoinClause $join) {
                        $join->on("H.user_id", "=", "U.id")
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_USER);
                    })
                    ->leftJoin('mst_user_info as UI', function (JoinClause $join) {
                        $join->on('UI.mst_user_id', "=", 'U.id')
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_USER);
                    })
                    ->leftJoin('mst_department as D', 'UI.mst_department_id','D.id')
                    ->leftJoin('mst_position as P', 'UI.mst_position_id','P.id');
                if ($multiple_department_position_flg === 1) {
                    // PAC_5-1599 追加部署と役職 Start
                    $arrHistory = $arrHistory->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',CONCAT(U.family_name, U.given_name),MA.account_name) as user_name,IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',U.email,MA.email) as email,D.id as department_id, D.department_name, P.position_name
                        , mst_department_id_1, (SELECT department_name FROM mst_department md WHERE UI.mst_department_id_1=md.id) AS department_name_1
                        , mst_department_id_2, (SELECT department_name FROM mst_department md WHERE UI.mst_department_id_2=md.id) AS department_name_2
                        , mst_position_id_1, (SELECT position_name FROM mst_position mp WHERE UI.mst_position_id_1=mp.id) AS position_name_1
                        , mst_position_id_2, (SELECT position_name FROM mst_position mp WHERE UI.mst_position_id_2=mp.id) AS position_name_2
                    '));
                    // PAC_5-1599 End
                } else {
                    $arrHistory = $arrHistory->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',CONCAT(U.family_name, U.given_name),MA.account_name) as user_name,IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',U.email,MA.email) as email,D.id as department_id, D.department_name, P.position_name'));
                }
                
                $arrHistory = $arrHistory->whereIn('auth_flg', [OperationsHistoryUtils::HISTORY_FLG_USER,OperationsHistoryUtils::HISTORY_FLG_AUDIT_USER,])
                ->where(function (Builder $query) use ($user) {
                    $query->where('U.mst_company_id', $user->mst_company_id)
                        ->orWhere('MA.mst_company_id', $user->mst_company_id);
                })
                ->whereRaw(implode(" AND ", $where), $where_arg);
            }elseif($select_month<=$time){
    
                $arrHistory = DB::table('operation_history'."$id_end".' as H')
                    ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'H.id',$orderDir)
                    ->leftJoin('mst_audit as MA', function (JoinClause $join) {
                        $join->on("MA.id", "=", "H.user_id")
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_AUDIT_USER);
                    })
                    ->leftJoin('mst_user as U', function (JoinClause $join) {
                        $join->on("H.user_id", "=", "U.id")
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_USER);
                    })
                    ->leftJoin('mst_user_info as UI', function (JoinClause $join) {
                        $join->on('UI.mst_user_id', "=", 'U.id')
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_USER);
                    })
                    ->leftJoin('mst_department as D', 'UI.mst_department_id','D.id')
                    ->leftJoin('mst_position as P', 'UI.mst_position_id','P.id');
                if ($multiple_department_position_flg === 1) {
                    // PAC_5-1599 追加部署と役職 Start
                    $arrHistory = $arrHistory->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',CONCAT(U.family_name, U.given_name),MA.account_name) as user_name,IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',U.email,MA.email) as email,D.id as department_id, D.department_name, P.position_name
                        , mst_department_id_1, (SELECT department_name FROM mst_department md WHERE UI.mst_department_id_1=md.id) AS department_name_1
                        , mst_department_id_2, (SELECT department_name FROM mst_department md WHERE UI.mst_department_id_2=md.id) AS department_name_2
                        , mst_position_id_1, (SELECT position_name FROM mst_position mp WHERE UI.mst_position_id_1=mp.id) AS position_name_1
                        , mst_position_id_2, (SELECT position_name FROM mst_position mp WHERE UI.mst_position_id_2=mp.id) AS position_name_2
                    '));
                    // PAC_5-1599 End
                } else {
                    $arrHistory = $arrHistory->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',CONCAT(U.family_name, U.given_name),MA.account_name) as user_name,IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',U.email,MA.email) as email,D.id as department_id, D.department_name, P.position_name'));
                }
                $arrHistory = $arrHistory->whereIn('auth_flg', [OperationsHistoryUtils::HISTORY_FLG_USER,OperationsHistoryUtils::HISTORY_FLG_AUDIT_USER,])
                ->where(function (Builder $query) use ($user) {
                    $query->where('U.mst_company_id', $user->mst_company_id)
                        ->orWhere('MA.mst_company_id', $user->mst_company_id);
                })
                ->whereRaw(implode(" AND ", $where), $where_arg);
        
            }else{

            $arrHistory = DB::table('operation_history as H')
                ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'H.id',$orderDir)
                ->leftJoin('mst_audit as MA', function (JoinClause $join) {
                    $join->on("MA.id", "=", "H.user_id")
                        ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_AUDIT_USER);
                })
                ->leftJoin('mst_user as U', function (JoinClause $join) {
                    $join->on("H.user_id", "=", "U.id")
                        ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_USER);
                })
                ->leftJoin('mst_user_info as UI', function (JoinClause $join) {
                    $join->on('UI.mst_user_id', "=", 'U.id')
                        ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_USER);
                })
                ->leftJoin('mst_department as D', 'UI.mst_department_id','D.id')
                ->leftJoin('mst_position as P', 'UI.mst_position_id','P.id');
                if ($multiple_department_position_flg === 1) {
                    // PAC_5-1599 追加部署と役職 Start
                    $arrHistory = $arrHistory->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',CONCAT(U.family_name, U.given_name),MA.account_name) as user_name,IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',U.email,MA.email) as email,D.id as department_id, D.department_name, P.position_name
                        , mst_department_id_1, (SELECT department_name FROM mst_department md WHERE UI.mst_department_id_1=md.id) AS department_name_1
                        , mst_department_id_2, (SELECT department_name FROM mst_department md WHERE UI.mst_department_id_2=md.id) AS department_name_2
                        , mst_position_id_1, (SELECT position_name FROM mst_position mp WHERE UI.mst_position_id_1=mp.id) AS position_name_1
                        , mst_position_id_2, (SELECT position_name FROM mst_position mp WHERE UI.mst_position_id_2=mp.id) AS position_name_2
                    '));
                    // PAC_5-1599 End
                } else {
                    $arrHistory = $arrHistory->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',CONCAT(U.family_name, U.given_name),MA.account_name) as user_name,IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',U.email,MA.email) as email,D.id as department_id, D.department_name, P.position_name'));
                }
                $arrHistory = $arrHistory->whereIn('auth_flg', [OperationsHistoryUtils::HISTORY_FLG_USER,OperationsHistoryUtils::HISTORY_FLG_AUDIT_USER,])
                ->where(function (Builder $query) use ($user) {
                    $query->where('U.mst_company_id', $user->mst_company_id)
                        ->orWhere('MA.mst_company_id', $user->mst_company_id);
                })
                ->whereRaw(implode(" AND ", $where), $where_arg);
            }
            if($action == 'export') {
                $arrHistory =$arrHistory->get();
            }else{
                $arrHistory = $arrHistory ->paginate($limit)->appends(request()->input());
            }

            // 検索項目を追加 PAC_5-163 END
            // PAC_5-486 END


                $arrDisplay = DB::table('mst_display')->where('role', OperationsHistoryUtils::HISTORY_FLG_USER)->select('display_name','id')->pluck('display_name','id');
                $arrOperation_info = DB::table('mst_operation_info')->where('role', OperationsHistoryUtils::HISTORY_FLG_USER)->select('info','id')->pluck('info','id');

                $listUser = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)
                ->select(DB::raw('CONCAT(family_name, given_name) name, id'))->pluck('name','id');
                if ($long_term_storage_flg) {
                    $listAuditUser = DB::table('mst_audit')->where('mst_company_id', $user->mst_company_id)
                        ->select(DB::raw('account_name as name, id'))->pluck('name', 'id');
                }

            $this->setMetaTitle('利用者操作履歴');
            // 表示文言を変更 PAC_5-163 BEGIN
            $this->assign('user_title', '利用者');     
            // 表示文言を変更 PAC_5-163 END       
        }else if($type == 'api'){
            if(!$user->can(PermissionUtils::PERMISSION_USER_API_HISTORY_VIEW)){
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }
            $listUser = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)
                ->select(DB::raw('CONCAT(family_name, given_name) name, id'))->pluck('name','id');
            $arrDisplay = DB::table('mst_display')->where('role', OperationsHistoryUtils::HISTORY_FLG_API)
                ->select('display_name','id')->pluck('display_name','id');

            $arrOperation_info = DB::table('mst_operation_info')->where('role', OperationsHistoryUtils::HISTORY_FLG_API)->select('info','id')->pluck('info','id');

            $this->setMetaTitle('利用者API呼出履歴');
            $this->assign('user_title', 'API発行者');
        }else{
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }

        $listUser = $listUser->toArray();
        $arrDisplay = $arrDisplay->toArray();
        $arrOperation_info = $arrOperation_info->toArray();

        ksort($listUser);
        ksort($arrDisplay);
        ksort($arrOperation_info);

        $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";

        $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);
        $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);
        // PAC_5-983 END

        $this->assign('listDepartmentDetail', $listDepartmentDetail);
        $this->assign('arrDisplay', $arrDisplay);
        $this->assign('arrOperation_info', $arrOperation_info);
        $this->assign('arrHistory', $arrHistory);
        $this->assign('listUser', $listUser);
        $this->assign('listAuditUser', $listAuditUser);
        $this->assign('type', $type);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);
        $this->assign('sanitizing_flg', $sanitizing_flg);
        $this->assign('multiple_department_position_flg', $multiple_department_position_flg);
        $this->assign('long_term_storage_flg', $long_term_storage_flg);
        
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        // PAC_5-486 START
        // return $this->render('OperationHistory.index');
        if($action == 'export'){
            return $this->render('OperationHistory.csv');
        }else{
            return $this->render('OperationHistory.index');
        }
        // PAC_5-486 END

    }

    public function getList(Request $request){

    }

     
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($type, $id)
    {
        $user   = \Auth::user();

        if($type == 'admin' AND !$user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_HISTORY_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }else if($type == 'user' AND !$user->can(PermissionUtils::PERMISSION_USER_HISTORY_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }else if($type == 'api' AND !$user->can(PermissionUtils::PERMISSION_USER_API_HISTORY_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }

        $user_id= $user->id;
        if($type == 'admin'){
            $mst_company_id=DB::table('mst_admin')->where('id', $user_id)->value('mst_company_id');
        }
        if($type == 'user'){
            $mst_company_id=DB::table('mst_user')->where('id', $user_id)->value('mst_company_id');
        }

            $id_length=mb_strlen($user->mst_company_id);
            $id_end=substr($user->mst_company_id,-1);
        
        try{
            $item = DB::table('operation_history'.$id_end)->find($id);
        } catch ( Exception $ex ) {
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }

        if(!$item){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        $company_id=DB::table('mst_admin')->where('id',$item->user_id)->value('mst_company_id');
    
        if($company_id==$mst_admin_company_id){

            $item->detail_info = \json_decode($item->detail_info);
            return response()->json(['status' => true, 'item' => $item ]);

        }elseif($company_id==null && $mst_admin_company_id==null){

            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');

        }else{

            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        
        
    }
}
 

