<?php

namespace App\Http\Controllers\Admin;

use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\PermissionUtils;
use App\Http\Utils\UserApiUtils;
use App\Models\Company;
use App\Models\User;
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
use App\Http\Utils\MailUtils;
use Carbon\Carbon;
use Session;

class SettingAdminController extends AdminController
{
    private $model;

    private $model_type;

    private $modelPermission;

    private $authority;
    private $permission;

    public function __construct(CompanyAdmin $model, ModelHasPermissions $modelPermission, Authority $authority, Permission $permission)
    {
        parent::__construct();
        $this->model = $model;
        $this->model_type = get_class($model);
        $this->modelPermission = $modelPermission;
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

        $limit = $request->get('limit') ? $request->get('limit') : 20;
        if(!array_search($limit, array_merge(config('app.page_list_limit'),[20]))){ 
            $limit = config('app.page_limit');
        }
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir'): 'desc';

        $users = $this->model
            ->orderBy($orderBy,$orderDir)
            ->where('mst_company_id', $user->mst_company_id)
            ->where('state_flg','<>', AppUtils::STATE_DELETE)
            ->paginate($limit)->appends(request()->input());
        
        $company = Company::findOrFail($user->mst_company_id);

        $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
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

        $this->setMetaTitle("管理者設定");
        return $this->render('SettingAdmin.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();

        $info = $request->only('id','email', 'state_flg', 'phone_number', 'given_name',
            'family_name', 'department_name','mst_company_id','role_flg', 'email_auth_flg', 'email_auth_dest_flg', 'auth_email');

        if(!$user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_UPDATE) 
            AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)
        ){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $validator = Validator::make($info, [
            // UDP PAC_5-437 Start
            // 'email' => 'required|email|unique:'.$this->model->getTable().',email|max:256',
            'email' => 'required|email|unique:'.$this->model->getTable().',email,null,id,state_flg,!-1|max:256',
            // UDP PAC_5-437 End
            'given_name' => 'required|max:128',
            'family_name' => 'required|max:128',
            'phone_number' => 'nullable|max:128',
            'department_name' => 'nullable|max:256',
            'role_flg' => 'nullable|integer|in:0,1',
            'email_auth_flg' => 'nullable|integer|in:0,1',
            'email_auth_dest_flg' => 'nullable|integer|in:0,1',
            // 'state_flg' => 'required|integer|in:0,1,9',
            'auth_email' =>  (!empty($info['email_auth_dest_flg']) ? 'required|' : 'nullable|') . 'email|max:256',
        ]);

        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }
        
        if($request->get('sendEmail', 0))
            $info['state_flg'] = 1;
        else $info['state_flg'] = 0;
        if ($user->hasRole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)) {
            $company = DB::table('mst_company')->where('id', $request['mst_company_id'])->first();
        }else{
            $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
        }
        $apiUser = [
            "email"=> strtolower($info['email']),
            "contract_app"=> config('app.pac_contract_app'),
            "app_env"=> config('app.pac_app_env'),
            "contract_server"=> config('app.pac_contract_server'),
            "user_auth"=> 2,
            "user_first_name"=> $info['given_name'],
            "user_last_name"=> $info['family_name'],
            "company_name"=> $company?$company->company_name:'',
            "company_id"=> $company?$company->id:0,
            "status"=> AppUtils::convertState($info['state_flg']),
            "system_name"=> $company?$company->system_name:''
        ];
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client){
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
 
        $item = new $this->model;
        $info['email'] = strtolower($info['email']);
        $item->fill($info);
        if(!$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $item->role_flg = AppUtils::ADMIN_DEFAULT_ROLE_FLG;
            $item->mst_company_id = $user->mst_company_id;
        }else{
            $item->role_flg = $item->role_flg ? $item->role_flg : AppUtils::ADMIN_DEFAULT_ROLE_FLG;
        }
 
        $item->login_id = Str::uuid()->toString();
        $item->password = "";
        $item->create_user = $user->getFullName();
        $item->email_format=$company->email_format;
        DB::beginTransaction();
        try{            
            $item->save();

            Log::debug("Call ID App Api to create company admin");
            $apiUser['create_user_email'] = $user->email;
            $result = $client->post("users",[
                RequestOptions::JSON => $apiUser
            ]);

            if($result->getStatusCode() != 200) {
                DB::rollBack();
                Log::warning("Call ID App Api to create company admin failed. Response Body ".$result->getBody());
                $response = json_decode((string) $result->getBody());
                return response()->json(['status' => false,
                    'message' => [$response->message]
                ]);
            }

            // add permissions default
            $this->setDefaultPermission($item->mst_company_id,$item->id);

            if($user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN) AND $item->role_flg == AppUtils::ADMIN_MANAGER_ROLE_FLG)
            {
                // update another in company
//                $this->model->where('id','<>', $item->id)
//                    ->where('mst_company_id',$item->mst_company_id)
//                    ->update(['role_flg' => 0]);
                $company_manager = $this->model->where('id','<>', $item->id)
                    ->where('role_flg', AppUtils::ADMIN_MANAGER_ROLE_FLG)
                    ->where('mst_company_id',$item->mst_company_id)
                    ->where('state_flg','<>',AppUtils::STATE_DELETE)
                    ->first();
                if($company_manager){
                    $company_manager->role_flg = AppUtils::ADMIN_DEFAULT_ROLE_FLG;
                    $company_manager->save();
                    $company_manager->removeRole(PermissionUtils::ROLE_COMPANY_MANAGER);
                }
            }
           
        DB::commit();

        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        
        if($request->get('sendEmail', 0)){
            $this->sendMailResetPassword(AppUtils::ACCOUNT_TYPE_ADMIN, $info['email']);
            $message = [__('message.success.reset_pass_was_send_mail')];
        }else{
            $message = [__('message.success.administrator_registered')];
        }
        return response()->json(['status' => true, 'id' => $item->id, 'state_flg'=> $info['state_flg'],
                'message' => $message
            ]);
    }


    public function update($id, Request $request)
    {
        $user = \Auth::user();

        $info = $request->only('id','email', 'state_flg', 'phone_number', 'given_name',
            'family_name', 'department_name','mst_company_id','role_flg', 'email_auth_flg', 'email_auth_dest_flg', 'auth_email', 'enable_email', 'email_format');
        if($user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $item = $this->model->find($id);
        }else{
            $item = $this->model->where('mst_company_id', $user->mst_company_id)->find($id);
        } 
     
        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        
        if(!$user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_UPDATE) 
            AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)
        ){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $validator = Validator::make($info, [
            'email' => 'required|email|unique:'.$this->model->getTable().',email,'.$id.',id,state_flg,!-1|max:256',
            'given_name' => 'required|max:128',
            'family_name' => 'required|max:128',
            'phone_number' => 'nullable|max:128',
            'department_name' => 'nullable|max:256',
            'state_flg' => 'required|integer|in:0,1,9',
            'role_flg' => 'nullable|integer|in:0,1',
            'email_auth_flg' => 'nullable|integer|in:0,1',
            'email_auth_dest_flg' => 'nullable|integer|in:0,1',
            'auth_email' =>  (!empty($info['email_auth_dest_flg']) ? 'required|' : 'nullable|') . 'email|max:256',
            'enable_email' => 'required|integer|min:0|max:1',
            'email_format' => 'required|integer|min:0|max:1'
        ]);

        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }
        $company = DB::table('mst_company')->where('id', $item->mst_company_id)->first();

        $apiUser = [
            "email"=> strtolower($info['email']),
            "contract_app"=> config('app.pac_contract_app'),
            "app_env"=> config('app.pac_app_env'),
            "contract_server"=> config('app.pac_contract_server'),
            "user_auth"=> 2,
            "user_first_name"=> $info['given_name'],
            "user_last_name"=> $info['family_name'],
            "company_name"=> $company?$company->company_name:'',
            "company_id"=> $company?$company->id:0,
            "status"=> AppUtils::convertState($info['state_flg']),
            "user_email"=>$item->email
        ];
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client){
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        
        $info['email'] = strtolower($info['email']);
        $item->fill($info);
        $item->update_user = $user->getFullName();
       
        DB::beginTransaction();
        try{            
            $item->save();

            Log::debug("Call ID App Api to update company admin $item->email");
            $apiUser['update_user_email'] = $user->email;
            $apiUser['system_name'] = $company?$company->system_name:'';
            $result = $client->put("users",[
                RequestOptions::JSON => $apiUser
            ]);
            if($result->getStatusCode() != 200) {
                DB::rollBack();
                Log::warning("Call ID App Api to update company admin failed. Response Body ".$result->getBody());
                $response = json_decode((string) $result->getBody());
                return response()->json(['status' => false,
                    'message' => [$response->message]
                ]);
            }

            if($user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN) AND $item->role_flg == AppUtils::ADMIN_MANAGER_ROLE_FLG)
            {
                $company_manager = $this->model->where('id','<>', $item->id)
                    ->where('role_flg', AppUtils::ADMIN_MANAGER_ROLE_FLG)
                    ->where('mst_company_id',$item->mst_company_id)
                    ->where('state_flg','<>',AppUtils::STATE_DELETE)
                    ->first();
                if($company_manager){
                    $company_manager->role_flg = AppUtils::ADMIN_DEFAULT_ROLE_FLG;
                    $company_manager->save();
                    $company_manager->removeRole(PermissionUtils::ROLE_COMPANY_MANAGER);
                }
            }

            // ユーザ無効時、rememberToken削除
            if ($info['state_flg'] == AppUtils::STATE_INVALID) {
                CommonUtils::rememberTokenClean($item->id,'mst_admin');
            }

        DB::commit();
        
        } catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true, 'id' => $item->id, 'state_flg'=> $info['state_flg'],
                'message' => [__('message.success.administrator_update')]
        ]);
    }

    public function destroy($id, Request $request)
    {
        $user = \Auth::user();

        // PAC_5-407 ROLE_SHACHIHATA_ADMIN以外も管理者を削除できるように修正
        if(!$user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_DELETE) 
            AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        if($user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $item = $this->model->find($id);
        }else{
            $item = $this->model->where('mst_company_id', $user->mst_company_id)->find($id);
        } 
        //対象が利用責任者の場合処理を中断する
        if($item->role_flg == 1){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        // 操作レコードのmst_company_idより情報取得
        $company = DB::table('mst_company')->where('id', $item->mst_company_id)->first();

        // IDM側更新
        $item->state_flg = AppUtils::STATE_DELETE;

        $apiUser = [
            "email" => $item->email,
            "contract_app" => config('app.pac_contract_app'),
            "app_env" => config('app.pac_app_env'),
            "contract_server" => config('app.pac_contract_server'),
            "user_auth" => 2,
            "user_first_name" => $item->given_name,
            "user_last_name" => $item->family_name,
            "company_name" => $company ? $company->company_name : '',
            "company_id" => $company ? $company->id : 0,
            "status" => AppUtils::convertState($item->state_flg),
            "user_email" => $item->email,
            "system_name" => $company ? $company->system_name : ''
        ];
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }

        $item->update_user = $user->getFullName();
        $item->email = $item->email . '.del';
        DB::beginTransaction();
        try {
            // mst_admin更新
            $item->save();

            Log::debug("Call ID App Api to update company admin $item->email");
            $apiUser['update_user_email'] = $user->email;
            $result = $client->put("users", [
                RequestOptions::JSON => $apiUser
            ]);
            if ($result->getStatusCode() != 200) {
                DB::rollBack();
                Log::warning("Call ID App Api to update company admin failed. Response Body " . $result->getBody());
                $response = json_decode((string)$result->getBody());
                return response()->json(['status' => false,
                    'message' => [$response->message]
                ]);
            }

            // ユーザ削除時、rememberToken削除
            CommonUtils::rememberTokenClean($id,'mst_admin');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
        }

        return response()->json(['status' => true, 'id' => $item->id, 'state_flg' => $item->state_flg,
            'message' => [__('message.success.administrator_delete')]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = \Auth::user();
       
        if($user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $item = $this->model->select('id', 'email', 'state_flg','role_flg', 'phone_number', 'given_name','family_name',
            'department_name', 'password', 'email_auth_flg', 'email_auth_dest_flg', 'auth_email' ,'mst_company_id' ,'role_flg', 'enable_email', 'email_format')->find($id);
        }else{
            $item = $this->model->select('id', 'email', 'state_flg','role_flg', 'phone_number', 'given_name','family_name',
            'department_name', 'password', 'email_auth_flg', 'email_auth_dest_flg', 'auth_email' ,'mst_company_id' ,'role_flg', 'enable_email', 'email_format')->where('mst_company_id',$user->mst_company_id)->find($id);
        }

        if(!isset($item)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $item->passwordStatus = $item->password==""?0:1;
        $item->companyEnableEmail = Company::findOrFail($item->mst_company_id)->enable_email;
        return response()->json(['status' => true, 'info' => $item]);
    }

    public function getPermission($id)
    {
        $user = \Auth::user();
        $items = DB::table('model_has_permissions')
                    ->join('mst_admin','model_has_permissions.model_id','mst_admin.id')
                    ->select('model_has_permissions.*')
                    ->where('model_has_permissions.model_type',$this->model_type)
                    ->where('model_has_permissions.model_id',$id)
                    ->where('mst_admin.mst_company_id',$user->mst_company_id)
                    ->pluck('permission_id');

        $admin = DB::table('mst_admin')->find($id);

        return response()->json(['status' => true, 'items' => $items, 'email' => $admin->email]);
    }

    public function postPermission(Request $request, $id)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_UPDATE))
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);

        $items = $request->get('items');


        $this->modelPermission->where('model_type',$this->model_type)
            ->where('model_id',$id)->delete();
        
        $insert = [];
        for($i=0; $i<count($items); $i++){
            $insert[] = ['permission_id' => $items[$i], 'model_type'=> $this->model_type, 'model_id' => $id ];
        }

        $this->modelPermission->insert($insert);
        return response()->json(['status' => true, 'message' => ['権限定義を更新しました。']]);
    }

    public function setDefaultPermission($mst_company_id,$id){

        $arrPermission  = $this->permission->getListMaster();
        $arrAuthority   = $this->authority->where('mst_company_id',$mst_company_id)->get()->keyBy('code');

        $insert = [];           
        foreach($arrPermission as $groups){
            foreach($groups as $code => $permissions){
                if(isset($arrAuthority[$code])){
                    $authority = $arrAuthority[$code];
                    if($authority->read_authority == 1 && isset($permissions['view'])){
                        $insert[] = ['permission_id' => $permissions['view'], 'model_type'=> $this->model_type, 'model_id' => $id ];
                    }
                    if($authority->create_authority == 1 && isset($permissions['create'])){
                        $insert[] = ['permission_id' => $permissions['create'], 'model_type'=> $this->model_type, 'model_id' => $id ];
                    }
                    if($authority->update_authority == 1 && isset($permissions['update'])){
                        $insert[] = ['permission_id' => $permissions['update'], 'model_type'=> $this->model_type, 'model_id' => $id ];
                    }
                    if($authority->delete_authority == 1 && isset($permissions['delete'])){
                        $insert[] = ['permission_id' => $permissions['delete'], 'model_type'=> $this->model_type, 'model_id' => $id ];
                    }
                }                
            }
        } 
        $this->modelPermission->insert($insert);
    }

    public function resetPermission($id){
        $user = \Auth::user();

        $this->modelPermission->where('model_type',$this->model_type)
            ->where('model_id',$id)->delete();
        $this->setDefaultPermission($user->mst_company_id,$id);

        $items = $this->modelPermission
                    ->where('model_type',$this->model_type)
                    ->where('model_id',$id)
                    ->pluck('permission_id');
        return response()->json(['status' => true, 'items' => $items, 'message' => []]);
    }

    public function resetpass($id)
    {
        $user = \Auth::user();
        if(!$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN) && !$user->can([PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_UPDATE, PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_CREATE]))
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);

        $item = $this->model->where('mst_company_id', $user->mst_company_id)->find($id);
        if($item){
            Session::flash('email', $item->email);
            $this->sendMailResetPassword(AppUtils::ACCOUNT_TYPE_ADMIN, $item->email);
            return response()->json(['status' => true, 'email' => $item->email,
                    'message' => [
                        "指定したメールアドレスに、初期パスワードの通知メールを送信しました。",
                    ] 
                ]);
        }else 
            return response()->json(['status' => false, 'message' => ['管理者情報更新処理に失敗しました。']]);
    }

    public function viewChangePassword(Request $request){ 
        $user = \Auth::user();
        $passwordPolicy = DB::table('password_policy')->where('mst_company_id',$user->mst_company_id)->first();
        $this->assign('passwordPolicy', $passwordPolicy);
        $this->setMetaTitle("ログインパスワードの変更");
        $this->assign('use_angular', true);
        return $this->render('SettingAdmin.ChangePassword');
    }

    public function changePassword(Request $request){
        $user = \Auth::user();
        
        $params = $request->all();
        $validator = Validator::make($params, [          
            'password' => 'required|max:32|confirmed',
            'password_confirmation' => 'required|max:32'
        ]);

        if ($validator->fails()) {
            $message = $validator->messages();
            $message_all = $message->all();

            $res = redirect()->back()->with("errors", $message)->with(['message' => implode('<br/>', $message_all), 'message_type' => 'danger'])->withInput();
            \Session::driver()->save();
            $res->send();
            exit;
        }
        $passwordPolicy = DB::table('password_policy')->where('mst_company_id',$user->mst_company_id)->first();

        $pass_status = true;
        /*PAC_5-2848 S*/
        $regex = '/^(?=.*[0-9])(?=.*[a-zA-Z])/';
        $message = 'パスワードは、文字と数字を含める必要があります。';
        if(preg_match($regex,$params['password'])){
            for($i=0; $i<strlen($params['password']); $i++){
                if(ord($params['password'][$i]) > 126){
                    $pass_status = false;
                    break;
                }
            }
        }else $pass_status = false;
        if(!$pass_status){
            $res = redirect()->back()->with(['message' => $message, 'message_type' => 'danger'])->withInput();
            \Session::driver()->save();
            $res->send();
            exit;
        }
        /*PAC_5-2848 E*/
        if($passwordPolicy->character_type_limit == 1){
            /*PAC_5-2848 S*/
            $regex = '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])|^(?=.*?[a-z])(?=.*?[0-9])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;\'\\\\[\]])|^(?=.*?[A-Z])(?=.*?[0-9])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;\'\\\\[\]])|^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;\'\\\\[\]])/';
            /*PAC_5-2848 E*/
            $message = 'パスワードポリシーに反しています。英大文字、英小文字、数字、記号の内、3種類以上入れてください。';
       /* }else{
            $regex = '/^(?=.*[0-9])(?=.*[a-zA-Z])/';
            $message = 'パスワードは、文字と数字を含める必要があります。';*/
            if(preg_match($regex,$params['password'])){
                for($i=0; $i<strlen($params['password']); $i++){
                    if(ord($params['password'][$i]) > 126){
                        $pass_status = false;
                        break;
                    }
                }
            }else $pass_status = false;
            if(!$pass_status){
                $res = redirect()->back()->with(['message' => $message, 'message_type' => 'danger'])->withInput();
                \Session::driver()->save();
                $res->send();
                exit;
            }
        }

        if(Hash::check($params['password'], $user->password)){
            $res = redirect()->back()->with(['message' => '同じパスワードは設定できません。'
                    , 'message_type' => 'danger'])->withInput();
            \Session::driver()->save();
            $res->send();
            exit;
        }

        // check use email as password    passwordPolicy.set_mail_as_password
        if($passwordPolicy->set_mail_as_password == 1){
            $strTempUserName = explode('@',strtolower($user->email));
            $strTempPassword = strtolower($params['password']);
            if ($strTempPassword == strtolower($user->email) || $strTempUserName[0] == $strTempPassword) {
                $res = redirect()->back()->with(['message' => 'ユーザＩＤと同一のパスワードを禁止する'
                        , 'message_type' => 'danger'])->withInput();
                \Session::driver()->save();
                $res->send();
                exit;
            }
        }
        // check pass min length
        if (\strlen($params['password']) < $passwordPolicy->min_length) {
            $res = redirect()->back()->with(['message' => __('message.false.password.password_min', ['attribute' => $passwordPolicy->min_length])
                , 'message_type' => 'danger'])->withInput();
            \Session::driver()->save();
            $res->send();
            exit;
        }

        DB::beginTransaction();
        try{
            DB::table('mst_admin')->where('id', $user->id)->update(['password' => Hash::make($params['password'])]);
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            $this->assign('message', 'パスワード更新処理に失敗しました。');
        }

        $this->assign('message', '登録が完了しました。');
 
        // send email   
        MailUtils::InsertMailSendResume(
            // 送信先メールアドレス
            $user->email,
            // メールテンプレート
            MailUtils::MAIL_DICTIONARY['ADMIN_PASSWORD_CHANGED_NOTIFY']['CODE'],
            // パラメータ
            '',
            // タイプ
            AppUtils::MAIL_TYPE_ADMIN,
            // 件名
            config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.SendChangePasswordMail.subject'),
            // メールボディ
            trans('mail.SendChangePasswordMail.body')
        );

        return $this->render('SettingAdmin.ChangePasswordDone');
     }

    /**
     * update user menu_state_flg
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
     public function updateMenuStateFlg(Request $request){
         $user = \Auth::user();
         $flg = $request->get('menu_state_flg',AppUtils::ADMIN_SIMPLE_MENU_STATE_FLG);
         if (!in_array($flg,[AppUtils::ADMIN_SIMPLE_MENU_STATE_FLG,AppUtils::ADMIN_USUALLY_MENU_STATE_FLG])){
             return response()->json(['status' => false]);
         }
         DB::table('mst_admin')->where('id', $user->id)->update(['menu_state_flg' => $flg]);
         return response()->json(['status' => true]);
     }

    /**
     * PAC_5-2163  利用者情報更新画面でパスワード設定依頼を送るときメールが無効だったらモーダル表示させる
     * check  user status
     * @param Request $request
     * @return mixed
     */
     public function checkUserEmailOrStampOrStatus(Request $request,User $mUser){
         $user = \Auth::user();

         $arrUserIDs = $request->input('uids');
         if(empty($arrUserIDs)){
             return response()->json(['status' => false,'message'=>['情報の取得に失敗しました']]);
         }
         $arrUser = DB::table("mst_user")->join('mst_user_info','mst_user.id','mst_user_info.mst_user_id')
             ->select("mst_user.id",'mst_user.state_flg','mst_user_info.enable_email')
             ->where("mst_user.mst_company_id",$user->mst_company_id)->whereIn("mst_user.id",$arrUserIDs)->get();
         $arrReturnData = [
             'isEmailInvalid' => [],
             'isNoStamp' => [],
             'isStatusInvalid' => [],
             'isNoStampMaster' => [],
             'isNoStampTwo' => [],
         ];
         $arrStatusFlg = [AppUtils::STATE_INVALID,AppUtils::STATE_INVALID_NOPASSWORD];
         foreach($arrUser as $key => $item){
             if($item->enable_email == 0 ){
                 $arrReturnData['isEmailInvalid'][] = $item->id;
             }
             if(in_array($item->state_flg ,$arrStatusFlg)){
                 $arrReturnData['isStatusInvalid'][] = $item->id;
                 $arrAllStampData = (new User())->getStamps($item->id);
                 //氏名印
                 if(count($arrAllStampData['stampMaster']) == 0 ){
                     $arrReturnData['isNoStampMaster'][] = $item->id;
                 }
                 //共通印+日付印
                 if(count($arrAllStampData['stampCompany']) + count($arrAllStampData['stampDepartment']) == 0){
                     $arrReturnData['isNoStampTwo'][] = $item->id;
                 }
                 if(count($arrAllStampData['stampMaster']) + count($arrAllStampData['stampCompany']) + count($arrAllStampData['stampDepartment']) + count($arrAllStampData['stampWaitDepartment']) == 0){
                     $arrReturnData['isNoStamp'][] = $item->id;
                 }
             }
         }
         return response()->json([
             'status' => true,
             'data' => $arrReturnData,
             'message' => [
                 "情報の取得に成功しました",
             ]
         ]);
     }
     public function setUsersEmailOrStampOrStatus(Request $request){
         $user = \Auth::user();
         $arrUserIDs = $request->input('uids');
         $intType = $request->input('type');
         if(empty($arrUserIDs) || !isset($user->mst_company_id) || !in_array($intType,[1,2])){
             return response()->json(['status' => false,'message'=>['設定情報に失敗しました']]);
         }
         try{
             if($intType == 2){
                 // 便利印 無効 -> 有効　更新時
                 $company = \Illuminate\Support\Facades\DB::table('mst_company')->where('id', $user->mst_company_id)->first();
                 $totalConvenientStampArr = Company::getCompanyConvenientStampLimitCount($user->mst_company_id);
                 $userHasConvenientStamp = \Illuminate\Support\Facades\DB::table("mst_assign_stamp")
                     ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
                     ->join("mst_company","mst_user.mst_company_id","=","mst_company.id")
                     ->whereIn("mst_user.id",$arrUserIDs)
                     ->where("mst_company.id",$user->mst_company_id)
                     ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_CONVENIENT)
                     ->where('mst_assign_stamp.state_flg',AppUtils::STATE_VALID)
                     ->where('mst_user.option_flg',AppUtils::USER_NORMAL)
                     ->where('mst_user.state_flg','<>', AppUtils::STATE_VALID)
                     ->count();

                 if (in_array($company->contract_edition, [0, 1, 2]) && $company->convenient_flg == 1
                     && ($totalConvenientStampArr['userConvenientStampCount'] > $totalConvenientStampArr['companyConvenientStampLimit'] || $userHasConvenientStamp + $totalConvenientStampArr['userConvenientStampCount'] > $totalConvenientStampArr['companyConvenientStampLimit'])) {
                     return response()->json([
                         'status' => false,
                         'message' => [sprintf(__("message.warning.convenient_stamp_limit"),$totalConvenientStampArr['userConvenientStampCount'] + $userHasConvenientStamp,$totalConvenientStampArr['companyConvenientStampLimit'])],
                         'convenient_stamp_is_over' => 1,
                     ]);
                 }
                 $client = IdAppApiUtils::getAuthorizeClient();
                 if (!$client){
                     throw new \Exception("error on this");
                 }
                 foreach($arrUserIDs as $userID){
                     $arrResult = $this->handlerUsersInfo($client,$user,$userID);
                     if(false == $arrResult['status']){
                         return response()->json(['status' => false, 'message' => [$arrResult['message']]]);
                     }
                 }
             }else{
                 DB::table("mst_user_info")->whereIn("mst_user_id",$arrUserIDs)->update([
                     'enable_email' => 1,
                     'update_user' => $user->getFullName(),
                     'update_at' => Carbon::now(),
                 ]);
             }
         } catch (\Exception $e) {
             Log::error($e->getMessage() . $e->getTraceAsString());
             return response()->json(['status' => false, 'message' => ['設定情報に失敗しました']]);
         }
         return response()->json([
             'status' => true,
             'message' => [
                 "ユーザ情報の更新に成功しました。",
             ]
         ]);
     }

    /**
     *  check current user info  AND company info
     * @param $client
     * @param $user
     * @param $intUserIDs
     * @return array|bool[]
     */
    private function handlerUsersInfo($client, $user, $intUserIDs)
    {
        $objUserInfo = DB::table("mst_user")
            ->join('mst_company', 'mst_company.id', 'mst_user.mst_company_id')
            ->select("mst_user.*", "mst_company.company_name", "mst_company.system_name")
            ->where("mst_user.id", $intUserIDs)
            ->first();
        if (empty($objUserInfo)) {
            return [
                'status' => false,
                'message' => ["このユーザ情報は存在しません"],
            ];
        }
        if(AppUtils::STATE_VALID == $objUserInfo->state_flg){
            return [ 'status' => true,];
        }

        // get current company info
        $company = DB::table("mst_company")->where('id', $user->mst_company_id)->first();

        //有効ユーザーの数
        if ($company->form_user_flg){
            $valid_user_count = User::where('mst_company_id',$user->mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)
                ->where('state_flg',AppUtils::STATE_VALID)->count();
            //帳票発行機能専用ユーザ 最大数が5人
            if ($valid_user_count + 1  > AppUtils::MAX_FORM_USER_COUNT ){
                return ['status' => false, 'message' => __('message.warning.form_user_over')];
            }
        }

        DB::beginTransaction();
        try {

            if($objUserInfo->option_flg == AppUtils::USER_NORMAL){
                $boolCurrentCompanyFlg = false;
                if ($company && $company->old_contract_flg && $company->contract_edition == 1) {
                    $boolCurrentCompanyFlg = true;
                }

                // 有効ユーザー数印面チェック：旧契約形態OFF && オプションフラグがON （上限：有効ユーザー上限がオプション契約数）
                if (
                    !$boolCurrentCompanyFlg   && $company->option_contract_flg && AppUtils::STATE_VALID != $objUserInfo->state_flg
                ){

                    $mst_user_count = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)
                        ->where('option_flg', AppUtils::USER_NORMAL)
                        ->where('state_flg', AppUtils::STATE_VALID)
                        ->count();

                    if ($mst_user_count + 1 > $company->option_contract_count) {
                        DB::rollBack();
                        return ['status' => false, 'message' => [__("message.warning.user_limit", ['counts' => $mst_user_count, 'limit' => $company->option_contract_count])]];
                    }
                }

                // PAC_5-1577
                // get all user stamp total and company stamp limit
                $arrCATotal = Company::getCompanyStampLimitAndUserStampCount($user->mst_company_id);
                // user stamp total
                $intUsersStamp = $arrCATotal['intUserStampCount'];
                // company stamp limit
                $intCompanyStamp = $arrCATotal['intCompanyStampLimit'];

            }
            // update current user status
            DB::table("mst_user")->where("mst_company_id", $objUserInfo->mst_company_id)->where("id", $intUserIDs)->update([
                'state_flg' => AppUtils::STATE_VALID,
                'update_user' => $user->getFullName(),
                'update_at' => Carbon::now(),
                // 有効 -> 無効　更新時、invalid_at設定
                'invalid_at' => null,
            ]);
            if($objUserInfo->option_flg == AppUtils::USER_NORMAL){

                $arrUserAllStamp = (new User())->getStamps($intUserIDs);

                $intCurrentStampCount = $intUsersStamp + count($arrUserAllStamp['stampMaster'])
                    + count($arrUserAllStamp['stampCompany']) + count($arrUserAllStamp['stampDepartment']) + count($arrUserAllStamp['stampWaitDepartment']);
                // current user is not lived but current user want update status to lived   must count this user stamps total compare company set stamp's total
                // 印面上限チェック：
                if ($company->old_contract_flg) {
                    //旧契約形態ON　&& Standarad ：上限がイセンス契約数
                    //旧契約形態ON　&& Business、Business Pro、trial ：上限なし
                    if ($company->contract_edition == 0 && in_array($objUserInfo->state_flg, [AppUtils::STATE_INVALID, AppUtils::STATE_INVALID_NOPASSWORD])
                        && (($intUsersStamp > $intCompanyStamp) || ($intCurrentStampCount > $intCompanyStamp))) {
                        DB::rollBack();
                        return [
                            'status' => false,
                            'message' => [sprintf(__("message.warning.stamp_limit"), $intCurrentStampCount, $intCompanyStamp)],
                        ];
                    }
                } else {
                    //旧契約形態OFF　&& Standarad、Business、Business Pro ：上限がイセンス契約数
                    //旧契約形態OFF　&& trial ：上限なし
                    if (!$boolCurrentCompanyFlg && in_array($company->contract_edition, [0, 1, 2])
                        && in_array($objUserInfo->state_flg, [AppUtils::STATE_INVALID, AppUtils::STATE_INVALID_NOPASSWORD])
                        && (($intUsersStamp > $intCompanyStamp) || ($intCurrentStampCount > $intCompanyStamp))) {
                        {
                            DB::rollBack();
                            return [
                                'status' => false,
                                'message' => [sprintf(__("message.warning.stamp_limit"), $intCurrentStampCount, $intCompanyStamp)],
                            ];
                        }
                    }
                }
            }

            $arrApiUser = [
                'user_email' => $objUserInfo->email,
                "email" => strtolower($objUserInfo->email),
                "contract_app" => config('app.pac_contract_app'),
                "app_env" => config('app.pac_app_env'),
                "contract_server" => config('app.pac_contract_server'),
                "user_first_name" => $objUserInfo->given_name,
                "user_last_name" => $objUserInfo->family_name,
                "company_name" => $objUserInfo->company_name,
                "company_id" => $objUserInfo->mst_company_id,
                "status" => AppUtils::convertState(AppUtils::STATE_VALID),
                "system_name" => $objUserInfo->system_name,
                "update_user_email" => $user->email,
                'user_auth' => $objUserInfo->option_flg == AppUtils::USER_NORMAL ? AppUtils::AUTH_FLG_USER: AppUtils::AUTH_FLG_OPTION,
            ];

            $result = $client->put("users", [
                RequestOptions::JSON => $arrApiUser
            ]);

            if ($result->getStatusCode() != 200) {
                $response = json_decode((string) $result->getBody());
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => [$response->message],
                ];
            }
            DB::commit();
            return [
                'status' => true,
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage().$e->getTraceAsString());
            DB::rollBack();
            return [
                'status' => false,
                'message' => ['設定情報に失敗しました'],
            ];
        }
    }
}
