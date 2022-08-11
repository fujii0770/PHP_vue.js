<?php

namespace App\Http\Controllers\Admin;

use App\Http\Utils\AppUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\PermissionUtils;
use App\Models\Company;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AdminController; 
use Illuminate\Support\Facades\Validator;
use DB;
use App\CompanyAdmin;
use App\Models\Permission;
use App\Models\ModelHasPermissions;
use App\Models\Authority;
use Illuminate\Support\Str;
use Session;
use Carbon\Carbon;

class SettingAdminStampGroupController extends AdminController
{
    private $model;

    private $model_type;

    private $authority;
    private $permission;

    public function __construct(CompanyAdmin $model, Authority $authority, Permission $permission)
    {
        parent::__construct();
        $this->model = $model;
        $this->model_type = get_class($model);
        $this->authority = $authority;
        $this->permission = $permission;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        $limit = $request->get('limit') ? $request->get('limit') : 20;//config('app.page_limit');
        if(!array_search($limit, array_merge(config('app.page_list_limit'),[20]))){
            $limit = config('app.page_limit');
        }
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'mst_admin.id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir'): 'desc';

        $email    = $request->get('email','');
        $group  = $request->get('group','');
        // PAC_5-2045 Start
        $department  = $request->get('department','');
        $state      = trim($request->get('state',''));
        // PAC_5-2045 End

        $where      = ['1 = 1'];
        $where_arg  = [];

        if($email){
            $where[]        = 'mst_admin.email like ?';
            $where_arg[]    = '%'.$email.'%';
        }
        if($group){
            $where[]        = 'mst_company_stamp_groups.id = ?';
            $where_arg[]    = $group;
        }
        // PAC_5-2045 Start
        if($department){
            $where[]        = 'mst_admin.department_name like ?';
            $where_arg[]    = '%'.$department.'%';
        }
        if($state != ''){
            if ($state == 0) {
                $where[] = 'mst_admin.state_flg = ?';
                $where_arg[] = $state;
            } else {
                $where[] = 'mst_admin.state_flg = ?';
                $where_arg[] = (int)$state;
            }
        }
        // PAC_5-2045 End

        $users = $this->model
            ->crossJoin('mst_company_stamp_groups')
            ->leftjoin('mst_company_stamp_groups_admin', function ($query){
                $query->on('mst_company_stamp_groups_admin.group_id','mst_company_stamp_groups.id')
                    ->on('mst_company_stamp_groups_admin.mst_admin_id','mst_admin.id')
                    ->where('mst_company_stamp_groups_admin.state',1);
            })
            ->where('mst_admin.mst_company_id','=', $user->mst_company_id)
            ->where('mst_company_stamp_groups.mst_company_id','=', $user->mst_company_id)
            ->where('mst_admin.state_flg','<>', AppUtils::STATE_DELETE)
            ->select('mst_admin.id as mst_admin_id','mst_admin.email as email',
                        'mst_admin.family_name as family_name','mst_admin.given_name as given_name',
                        'mst_company_stamp_groups.id as group_id','mst_company_stamp_groups.group_name as group_name',
                        'mst_admin.department_name as department_name',
                        'mst_admin.state_flg as state_flg',
                        'mst_company_stamp_groups_admin.state as state')
            ->whereRaw(implode(" AND ", $where), $where_arg)
            ->orderBy($orderBy,$orderDir)
            ->paginate($limit)->appends(request()->input());

        $list_group = DB::table('mst_company_stamp_groups')
            ->where('mst_company_id','=', $user->mst_company_id)
            ->where('state','=', 1)
            ->pluck('group_name', 'id')->toArray();

        $company = Company::findOrFail($user->mst_company_id);

        $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
        $this->assign('list_group', $list_group);
        $this->assign('users', $users);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);
        $this->assign('allow_create', $user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_CREATE));
        $this->assign('allow_update', $user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_UPDATE));
        $this->assign('company', $company);
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));

        $this->setMetaTitle("共通印グループ管理者割当");
        return $this->render('SettingAdminStampGroup.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }


    public function update($id, Request $request)
    {
    }

    public function destroy($id, Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    public function updates(Request $request)
    {
        $user = \Auth::user();

        $update_datas = $request->get('update_datas',[]);

        if(!$update_datas){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        foreach($update_datas as $update_data){

            if($update_data[0]['checked']){
                // 割当あり
                DB::table('mst_company_stamp_groups_admin')
                    ->where('group_id', $update_data[0]['group_id'])
                    ->where('mst_admin_id', $update_data[0]['user_id'])
                    ->delete();

                $arrInsert = array();
                $arrInsert[] = [
                    'group_id' => $update_data[0]['group_id'],
                    'mst_admin_id' => $update_data[0]['user_id'],
                    'state' => 1, // 1:有効 0:無効
                    'create_at' => Carbon::now(),
                    'create_user' => $user->getFullName(),
                    'update_at' => Carbon::now(),
                    'update_user' => $user->getFullName(),
                ];
                DB::table('mst_company_stamp_groups_admin')->insert($arrInsert);

            }else{
                // 割当なし
                DB::table('mst_company_stamp_groups_admin')
                    ->where('group_id', $update_data[0]['group_id'])
                    ->where('mst_admin_id', $update_data[0]['user_id'])
                    ->delete();
            }
        }

        return response()->json(['status' => true,'message' => [__('message.success.administrator_update')]]);

    }

}