<?php

namespace App\Http\Controllers;

use App\Models\HrInfo;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Http\Controllers\AdminController; 
use App\Http\Utils\AppUtils;
use App\Http\Utils\OperationsHistoryUtils;
use App\Http\Utils\PermissionUtils;
use Carbon\Carbon;
use DB;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HrAdminRegistrationController extends AdminController
{
    private $model;
    private $department;
    private $position;
    private $model_user;
    
    public function __construct(HrInfo $model, Department $department, Position $position, User $model_user)
     {
        parent::__construct();
        $this->model = $model;
        $this->department = $department;
        $this->position = $position;
        $this->model_user = $model_user;
     }

    /*
     * 管理ユーザ登録画面
     * リストデータ表示
     */
    public function index(Request $request){
    
        $user   = \Auth::user();
        $arrHistory  =  null;
        $action = $request->get('action','');

        // リクエスト取得
        $limit       = $request->get('limit')       ? $request->get('limit')   : config('app.page_limit');
        $orderBy     = $request->get('orderBy')     ? $request->get('orderBy') : 'time';
        $orderDir    = $request->get('orderDir')    ? $request->get('orderDir'): 'desc';
        $dt_orderBy  = $request->get('dt_orderBy');
        $dt_orderDir = $request->get('dt_orderDir');
        $dt_user_id  = $request->get('dt_user_id');
 
        $arrOrder    = ['user'            => 'user_name',
                        'email'           => 'U.emailss',
                        'hrAdminFlg'      => 'U.hr_admin_flg',
                        'department'      => 'D.department_name',
                        'position'        => 'P.position_name',
                        'assignedCompany' => 'H.assigned_company'];

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

        $arrHistory = DB::table('mst_user as U')
            ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'U.id',$orderDir)
            ->leftJoin('mst_user_info as I', 'U.id','I.mst_user_id')
            ->leftJoin('mst_department as D', 'I.mst_department_id','D.id')
            ->leftJoin('mst_position as P', 'I.mst_position_id','P.id')
            ->leftJoin('mst_hr_info as H', 'I.mst_user_id','H.mst_user_id')
        
        ->select(DB::raw(
            'U.id, H.id as hr_info_id, '. 
            'U.hr_user_flg,           '.
            'U.hr_admin_flg,           '. 
            'CONCAT(U.family_name, U.given_name) as user_name, '. 
            'U.email, mst_department_id, '. 
            'D.department_name, '. 
            'mst_position_id, '.
            'P.position_name, '.
            'H.assigned_company, '.
            'H.Regulations_work_start_time,'. 
            'H.Regulations_work_end_time '))
            ->where('U.mst_company_id', $user->mst_company_id)
            ->where('U.option_flg',AppUtils::USER_NORMAL)
            ->where('U.state_flg', '1')
            ->where('U.hr_user_flg',AppUtils::HR_USE)
            ->where('U.email', 'like', "%$filter_email%")
            ->where(DB::raw('CONCAT(U.family_name,U.given_name)'), 'like', "%$filter_user%")
        ;
        if($request->get('assignedcompany')){
            $arrHistory->where('H.assigned_company', 'like', "%$filter_assigned_company%");
        }
        if($request->get('department')){
            $arrHistory->where('D.id', $request->get('department'));
        }
        if($request->get('position')){
            $arrHistory->where('P.id', $request->get('position'));
        }
        if(($request->get('hrAdminFlg') || ($request->get('hrAdminFlg') == '0' ))){
            $arrHistory->where('U.hr_Admin_flg', $request->get('hrAdminFlg'));
        }
        if($action == 'export'){
            $arrHistory = $arrHistory ->get();
        }else{
            $arrHistory = $arrHistory ->paginate($limit)->appends(request()->input());
        }

        $this->setMetaTitle('管理ユーザ登録');
        $this->assign('user_title', '管理者');
        $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
        $this->assign('listDepartment', $listDepartment);
        $this->assign('listDepartmentTree', $listDepartmentTree);
        $this->assign('listPosition', $listPosition);

        $this->assign('arrHistory', $arrHistory);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);
        $this->assign('dt_orderBy', $dt_orderBy);
        $this->assign('dt_orderDir', $dt_orderDir);
        $this->assign('dt_user_id', $dt_user_id);
         
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        if($action == 'export'){
            return $this->render('OperationHistory.csv');
        }else{
            return $this->render('HrAdminRegistration.index');
        }
    }

    /*
     * 管理ユーザ登録画面
     * 管理ユーザフラグ更新
     */
    public function updateHrAdmin(Request $request){

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

            // ユーザマスタ 選択状態を反映
            DB::table('mst_user')
                ->where('hr_admin_flg','0')
                ->whereIn('id', $cids)
                ->update(['hr_admin_flg' => '1']);
            
            // ユーザマスタ 未選択状態を反映
            DB::table('mst_user')
                ->where('hr_admin_flg','1')
                ->whereIn('id', $cidsoff)
                ->update(['hr_admin_flg' => '0']);

            DB::commit();
            return response()->json(['status' => true,'message' => [__('message.success.update_hr_admin')]]);
            
        }catch(\Exception $e){
        
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }

    /*
     * 管理ユーザ登録画面
     * リストデータ表示
     */
    public function getUsers(Request $request){
        $user   = \Auth::user();
        $arrHistory  =  null;
        $action = $request->get('action','');

        // get list user
        $limit       = $request->get('limit')       ? $request->get('limit')      : config('app.page_limit');
        $dt_orderBy  = $request->get('dt_orderBy')  ? $request->get('dt_orderBy') : 'time';
        $dt_orderDir = $request->get('dt_orderDir') ? $request->get('dt_orderDir'): 'desc';
        $arrOrder    = ['dtlst_user'            => 'dtlst_username',
                        'dtlst_email'           => 'U.email',
                        'dtlst_hrUserFlg'       => 'U.user_flg',
                        'dtlst_department'      => 'D.department_name',
                        'dtlst_position'        => 'P.position_name',
                        'dtlst_assignedCompany' => 'H.assigned_company'];

        $filter_email               = $request->get('dt_email','');
        $filter_user                = $request->get('dt_username','');
        $filter_assigned_company    = $request->get('dt_assignedcompany','');
        $dt_user_id                 = $request->get('dt_user_id');

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
            ->where('mst_company_id',   $user->mst_company_id)
            ->pluck('position_name',    'id')->toArray();

        $arrHistory = DB::table('mst_user as U')
            ->orderBy(isset($arrOrder[$dt_orderBy])?$arrOrder[$dt_orderBy]:'U.id',$dt_orderDir)
            ->leftJoin('mst_user_info as I',     'U.id',                'I.mst_user_id')
            ->leftJoin('mst_department as D',    'I.mst_department_id', 'D.id')
            ->leftJoin('mst_position as P',      'I.mst_position_id',   'P.id')
            ->leftJoin('mst_hr_info as H',       'I.mst_user_id',       'H.mst_user_id')
            ->leftJoin('hr_admin_has_users as A', function ($join) use($dt_user_id){
                $join->on('U.mst_company_id', '=',   'A.mst_company_id') 
                     ->on('A.user_mst_user_id', '=', 'U.id');
            })
            ->select(DB::raw(
                'A.del_flg, '.
                'A.admin_mst_user_id, '.
                'U.id, '. 
                'H.id as hr_info_id, '. 
                'U.hr_user_flg, '.
                'U.hr_admin_flg, '.
                'CONCAT(U.family_name, U.given_name) as dtlst_username, '.
                'U.email as dtlst_email, '. 
                'mst_department_id, '. 
                'D.department_name, '. 
                'mst_position_id, '.
                'P.position_name, '.
                'H.assigned_company, '.
                'H.Regulations_work_start_time, '. 
                'H.Regulations_work_end_time ')) 
            ->where('U.mst_company_id', $user->mst_company_id)
            ->where('U.option_flg',     AppUtils::USER_NORMAL)
            ->where('U.state_flg',      '1')
            ->where('U.email',          'like', "%$filter_email%")  
            ->where(DB::raw('CONCAT(U.family_name,U.given_name)'), 'like', "%$filter_user%")
            ->where(DB::raw('(CASE' .
                '    WHEN A.admin_mst_user_id='.$dt_user_id.' THEN'.   // 親画面のユーザIDとadmin_mst_user_idの比較
                '       1'.                                            // 一致していたら無条件に一覧表示
                '    WHEN A.del_flg = 0 OR A.del_flg IS NULL  THEN'.          
                '       1'.                                            // 削除フラグが0'解放'かnullであれば一覧表示
                '    ELSE'.
                '       0'.                                            // それ以外は一覧非表示
                '    END)' ), '1')
        ;
        if($request->get('dt_assignedcompany')){
            $arrHistory->where('H.assigned_company', 'like', "%$filter_assigned_company%");
        }
        if($request->get('dt_department')){
            $arrHistory->where('D.id', $request->get('dt_department'));
        }
        if($request->get('dt_position')){
            $arrHistory->where('P.id', $request->get('dt_position'));
        }
        if($action == 'export'){
            $arrHistory = $arrHistory ->get();
        }else{
            $arrHistory = $arrHistory ->paginate($limit)->appends(request()->input());
        }
 
        $this->setMetaTitle('管理ユーザ登録');
        $this->assign('user_title', '管理者');
        $dt_orderDir = strtolower($dt_orderDir)=="asc"?"desc":"asc";
        $this->assign('listDepartment', $listDepartment);
        $this->assign('listDepartmentTree', $listDepartmentTree);
        $this->assign('listPosition', $listPosition);

        $this->assign('arrHistory', $arrHistory);
        $this->assign('limit', $limit); 
        
        $this->assign('dt_orderBy', $dt_orderBy);
        $this->assign('dt_orderDir', $dt_orderDir);
 
        $this->addStyleSheet('tablesaw',  asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2',   'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('tablesaw',      asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2',       'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init',  '$(\'.select-2\').select2();', false);

        return response()->json(['status' => true, 'items' => $arrHistory]);    
    }

    /*
     * 管理ユーザ詳細画面
     * 管理ユーザ更新
     */
    public function updateHrUsers(Request $request){

        $user = \Auth::user();
        
        $admin_dt_user_id = $request->get('dt_user_id');
        $cids = $request->get('cidsDetile',[]);
        $cidsoff = $request->get('cidsDetileoff',[]);
        $items = [];

        DB::beginTransaction();
        
        try{
 
            // checkbox:0n
            foreach($cids as $i => $row) {
                $user_id  = $row; 
                
                $arrHistory  = DB::table('hr_admin_has_users as A')
                    ->select(DB::raw('A.id '))
                    ->where('A.mst_company_id',     $user->mst_company_id)
                    ->where('A.user_mst_user_id',  $user_id)
                    ->first();

                if(!$arrHistory){
                    $items = [
                        'mst_company_id'    => $user->mst_company_id,
                        'admin_mst_user_id' => $admin_dt_user_id,
                        'user_mst_user_id'  => $user_id,
                        'del_flg'           => AppUtils::HR_ADMIN_MNG,
                        'create_user'       => $user->getFullName(),
                        'update_user'       => $user->getFullName(),
                        'create_at'         => Carbon::now(),
                        'update_at'         => Carbon::now()
                    ];
                    $parent_id = DB::table('hr_admin_has_users')->insert($items);

                }else{

                    $items = [
                        'del_flg'           => AppUtils::HR_ADMIN_MNG,
                        'admin_mst_user_id' => $admin_dt_user_id,
                        'update_user'       => $user->getFullName(),
                        'update_at'         => Carbon::now()
                    ];

                    DB::table('hr_admin_has_users')
                        ->where('mst_company_id',    $user->mst_company_id)
                        ->where('user_mst_user_id',  $user_id)
                        ->update($items);
                } 
            }

            // checkbox:0ff
            foreach($cidsoff as $i => $row) {
                $user_id  = $row; 

                $arrHistory = DB::table('hr_admin_has_users as A')
                    ->select(DB::raw('A.id '))
                    ->where('A.mst_company_id',     $user->mst_company_id)
                    ->where('A.user_mst_user_id',  $user_id)
                    ->first();

                if($arrHistory){
                
                    $items = [
                        'del_flg'           => AppUtils::HR_ADMIN_UN_MNG,
                        'admin_mst_user_id' => $admin_dt_user_id,
                        'update_user'       => $user->getFullName(),
                        'update_at'         => Carbon::now()
                    ];

                    DB::table('hr_admin_has_users')
                        ->where('mst_company_id',    $user->mst_company_id)
                        ->where('user_mst_user_id',  $user_id)
                        ->update($items);
                } 
            }
            DB::commit();
            return response()->json(['status' => true,'message' => [__('message.success.update_hr_users')]]);           
        }catch(\Exception $e){
        
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }

    public function show($id)
    {  
    }
}
