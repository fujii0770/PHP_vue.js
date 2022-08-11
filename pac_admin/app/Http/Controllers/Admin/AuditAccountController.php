<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\MailUtils;
use App\Http\Utils\PermissionUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use DB;
use App\Http\Utils\IdAppApiUtils;
use Illuminate\Support\Str;
use GuzzleHttp\RequestOptions;
use Session;
use Illuminate\Support\Facades\Hash;

class AuditAccountController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        $limit = $request->get('limit') ? $request->get('limit') : config('app.page_limit');

        $audits = [];
        if(!array_search($limit, config('app.page_list_limit'))){
            $limit = config('app.page_limit');
        }
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir'): 'desc';

        $audits = DB::table('mst_audit')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('state_flg','!=',AppUtils::STATE_DELETE)
            ->paginate($limit)->appends(request()->except('_token'));

        $company = DB::table('mst_company')
                ->leftJoin('mst_limit','mst_company.id','=','mst_limit.mst_company_id')
                ->select('mst_company.*','mst_limit.use_mobile_app_flg')
                ->where('mst_company.id', $user->mst_company_id)
                ->first();

        $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";

        $password_policy = DB::table('password_policy')->where('mst_company_id', $user->mst_company_id)->first();

        $this->assign('audits', $audits);
        $this->assign('company', $company);
        $this->assign('passwordPolicy', $password_policy);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);
        $this->assign('allow_create', $user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_CREATE));
        $this->assign('allow_update', $user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_UPDATE));
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));

        $this->setMetaTitle("監査用アカウント設定");
        return $this->render('SettingAuditAccount.index');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        $item_user = $request->get('item');

        $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
        $apiUser = [
            "email"=> strtolower($item_user['email']),
            "contract_app"=> config('app.pac_contract_app'),
            "app_env"=> config('app.pac_app_env'),
            "contract_server"=> config('app.pac_contract_server'),
            "user_auth"=> AppUtils::AUTH_FLG_AUDIT,
            "user_first_name"=> $item_user['account_name'],
//            "user_last_name"=> $item_user['account_name'],
            "company_name"=> $company?$company->company_name:'',
            "company_id"=> $company?$company->id:0,
            "status"=> AppUtils::convertState($item_user['state_flg']),
            "pasword_change_date" => Carbon::now(),
            "system_name"=>$company?$company->system_name:'',
        ];
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client){
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        Session::flash('audit_email', $item_user['email']);
        Session::flash('audit_name', $item_user['account_name']);
        Session::flash('audit_expiration_date', $item_user['expiration_date']);

        DB::beginTransaction();
        try{
            $item_user['email'] = strtolower($item_user['email']);
            $item = DB::table('mst_audit')->insert([
                'mst_company_id' => $user->mst_company_id,
                'login_id' => Str::uuid()->toString(),
                'account_name' => $item_user['account_name'],
                'password' => $item_user['password'] ? Hash::make($item_user['password']) : null,
                'email' => $item_user['email'],
                'state_flg' => $item_user['state_flg'],
                'expiration_date' => $item_user['expiration_date'],
                'create_at' => Carbon::now(),
                'create_user' => $user->getFullName(),

            ]);
            Log::debug("Call ID App Api to create audit account");
            $apiUser['create_user_email'] = $user->email;
            $result = $client->post("users",[
                RequestOptions::JSON => $apiUser
            ]);

            if($result->getStatusCode() != 200) {
                DB::rollBack();
                Log::warning("Call ID App Api to create audit account failed. Response Body ".$result->getBody());
                $response = json_decode((string) $result->getBody());
                return response()->json(['status' => false,
                    'message' => [$response->message],
                    'errors' => isset($response->errors)?$response->errors:[]
                ]);
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
        }

        return response()->json(['status' => true, 'id' => $item['id'],
            'message' => [__('message.success.create_audit')]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = \Auth::user();
        $item = DB::table('mst_audit')->where('mst_company_id',$user->mst_company_id)->find($id);
        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        $item->password = null;
        return response()->json(['status' => true, 'item' => $item]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = \Auth::user();
        $item_user = $request->get('item');
        $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
        $item = DB::table('mst_audit')->where('id', $id)->first();
        if($item->mst_company_id != $user->mst_company_id){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        $apiUser = [
            "user_email" => $item->email,
            "email"=> strtolower($item_user['email']),
            "contract_app"=> config('app.pac_contract_app'),
            "app_env"=> config('app.pac_app_env'),
            "contract_server"=> config('app.pac_contract_server'),
            "user_auth"=> AppUtils::AUTH_FLG_AUDIT,
            "user_first_name"=> $item_user['account_name'],
//            "user_last_name"=> $item_user['family_name'],
            "company_name"=> $company?$company->company_name:'',
            "company_id"=> $company?$company->id:0,
            "status"=> AppUtils::convertState($item_user['state_flg']),
            "system_name"=>$company?$company->system_name:'',
        ];
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client){
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }

        $item_user['email'] = strtolower($item_user['email']);
        $apiUser['update_user_email'] = $user->email;

        $result = $client->put("users",[
            RequestOptions::JSON => $apiUser
        ]);
        if($result->getStatusCode() == 200) {
            $item->update_user = $user->getFullName();
        }else{
            Log::warning("Call ID App Api to update audit account failed. Response Body ".$result->getBody());
            $response = json_decode((string) $result->getBody());
            return response()->json(['status' => false,
                'message' => [$response->message],
                'errors' => $response->errors
            ]);
        }
        Session::flash('audit_email', $item_user['email']);
        Session::flash('audit_name', $item_user['account_name']);
        Session::flash('audit_expiration_date', $item_user['expiration_date']);

        DB::beginTransaction();
        try{
            $update = [
                'account_name' => $item_user['account_name'],
                'email' => $item_user['email'],
                'state_flg' => $item_user['state_flg'],
                'expiration_date' => $item_user['expiration_date'],
                'update_at' => Carbon::now(),
                'update_user' => $user->getFullName(),
            ];
            if($item_user['password']) {
                $update['password'] = Hash::make($item_user['password']);
                $update['password_change_date'] = Carbon::now();
            }
            DB::table('mst_audit')->where('id', $id)
                ->update($update);
            // ユーザ無効時、rememberToken削除
            if ($item_user['state_flg'] == AppUtils::STATE_INVALID) {
                CommonUtils::rememberTokenClean($id,'mst_audit');
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true, 'id' => $item->id,
            'message' => [__('message.success.update_audit')]
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
//    public function destroy($id)
//    {
//        $user = \Auth::user();
//        if(!$user->can(PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_DELETE)){
//            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
//        }
//        $item = DB::table('mst_audit')->where('mst_company_id',$user->mst_company_id)->find($id);
//
//        if(!$item){
//            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
//        }
//
//        $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
//        Session::flash('audit_email', $item->email);
//        $apiUser = [
//            "email"=> $item->email,
//            "contract_app"=> config('app.pac_contract_app'),
//            "app_env"=> config('app.pac_app_env'),
//            "contract_server"=> config('app.pac_contract_server'),
//            "user_auth"=> AppUtils::AUTH_FLG_AUDIT,
//            "user_first_name"=> $item->account_name,
////            "user_last_name"=> $item->account_name,
//            "company_name"=> $company?$company->company_name:'',
//            "status"=> AppUtils::convertState(AppUtils::STATE_DELETE),
//            "update_user_email" => $user->email,
//            "user_email" => $item->email,
//            "company_id"=> $company?$company->id:0,
//            "system_name"=>$company?$company->system_name:'',
//        ];
//
//        $client = IdAppApiUtils::getAuthorizeClient();
//        if (!$client){
//            //TODO message
//            return response()->json(['status' => false,
//                'message' => ['Cannot connect to ID App']
//            ]);
//        }
//        Log::debug("Call ID App Api to disable audit account $item->email");
//        $result = $client->put("users",[
//            RequestOptions::JSON => $apiUser
//        ]);
//
//        if($result->getStatusCode() == StatusCodeUtils::HTTP_OK) {
//            try{
//                DB::beginTransaction();
//                DB::table('mst_audit')->where('id', $id)
//                    ->update([
//                        'email' => $item->email . '.del',
//                        'state_flg' => AppUtils::STATE_DELETE,
//                        'update_at' => Carbon::now(),
//                        'update_user' => $user->getFullName(),
//                    ]);
//                // ユーザ削除時、rememberToken削除
//                CommonUtils::rememberTokenClean($id,'mst_audit');
//                DB::commit();
//            }catch(\Exception $e){
//                DB::rollBack();
//            }
//        }else{
//            Log::warning("Call ID App Api to disable company audit account. Response Body ".$result->getBody());
//            $response = json_decode((string) $result->getBody());
//            return response()->json(['status' => false,
//                'message' => [$response->message]
//            ]);
//        }
//
//        return response()->json(['status' => true]);
//    }
    public function resetpass(Request $request)
    {
        $user = \Auth::user();
        $cids = $request->get('cids',[]);
        $items = [];
        if(count($cids)){
            $items = DB::table('mst_audit')
                    ->whereIn('id', $cids)
                    ->where('mst_company_id',$user->mst_company_id)
                    ->get();
        }

        if(!count($items)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $emails = [];

        foreach($items as $item){
            $this->sendMailResetPassword(AppUtils::ACCOUNT_TYPE_AUDIT, $item->email, config('app.url_app_user'));
            $emails[] = $item->email;
        }
        Session::flash('emails', $emails);
        return response()->json(['status' => true,
            'message' => [__('message.success.reset_password_audit')]
        ]);
    }

    public function sendLoginUrl(Request $request)
    {
        $user = \Auth::user();
        $cids = $request->get('cids',[]);
        $items = [];
        if(count($cids)){
            $items = DB::table('mst_audit')
                    ->whereIn('id', $cids)
                    ->where('mst_company_id',$user->mst_company_id)
                    ->get();
        }

        if(!count($items)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        // check login type of logged company
        $company = DB::table('mst_company')
                    ->where('id', $user->mst_company_id)
                    ->select('login_type', 'url_domain_id')
                    ->first();
        $emails = [];
        if ($company && $company->login_type == AppUtils::LOGIN_TYPE_SSO){

            $data = ['url_domain_id' => $company->url_domain_id];
            foreach ($items as $item) {
                MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                    $item->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['USER_REGISTRATION_COMPLETE_NOTIFY']['CODE'],
                    // パラメータ
                    json_encode($data,JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_ADMIN,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.UserRegistrationCompleteMail.subject'),
                    // メールボディ
                    trans('mail.UserRegistrationCompleteMail.body',$data)
                );
                $emails[] = $item->email;
            }
            Session::flash('emails', $emails);
            return response()->json(['status' => true,
                'message' => [__('指定したメールアドレスにログインURLを送信しました。'), __('メールのリンクからログインが可能です。')]
            ]);
        }
    }

    /**
     * 監査用アカウント一括削除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletes(Request $request)
    {
        try {
            $user = \Auth::user();
            if (!$user->can(PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_DELETE)) {
                return response()->json(['status' => false, 'message' => [__('message.warning.not_permission_access')]]);
            }
            $cids = $request->get('cids', []);
            $items = [];
            if (count($cids)) {
                $items = DB::table('mst_audit')->whereIn('id', $cids)->where('mst_company_id', $user->mst_company_id)->get();
            }
            if (!count($items)) {
                return response()->json(['status' => false, 'message' => [__('message.warning.not_permission_access')]]);
            }
            $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
            $emails = [];
            foreach ($items as $item){
                $emails[]=$item->email;
            }
            Session::flash('audit_email', $emails);
            $listID = [];
            $listEmail = [];
            $listName = [];
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            foreach ($items as $item) {
                $apiUser = [
                    "email" => $item->email,
                    "contract_app" => config('app.pac_contract_app'),
                    "app_env" => config('app.pac_app_env'),
                    "contract_server" => config('app.pac_contract_server'),
                    "user_auth" => AppUtils::AUTH_FLG_AUDIT,
                    "user_first_name" => $item->account_name,
                    "company_name" => $company ? $company->company_name : '',
                    "status" => AppUtils::STATE_INVALID,
                    "update_user_email" => $user->email,
                    "user_email" => $item->email,
                    "company_id" => $company ? $company->id : 0,
                    "system_name" => $company ? $company->system_name : '',
                ];
                Log::debug("Call ID App Api to disable audit account $item->email");
                $result = $client->put("users", [
                    RequestOptions::JSON => $apiUser
                ]);
                DB::beginTransaction();
                DB::table('mst_audit')
                    ->where('email', $item->email)
                    ->where('mst_company_id', $user->mst_company_id)
                    ->update([
                        'email' => $item->email . '.del',
                        'state_flg' => AppUtils::STATE_DELETE,
                        'update_at' => Carbon::now(),
                        'update_user' => $user->getFullName(),
                    ]);
                // ユーザ削除時、rememberToken削除
                DB::table('mst_audit')->whereIn('id', $cids)
                    ->where('remember_token', '!=', '')
                    ->update(['remember_token' => '']);
                if ($result->getStatusCode() != StatusCodeUtils::HTTP_OK) {
                    DB::rollBack();
                    Log::error("Call ID App Api to disable audit account failed. Response Body " . $result->getBody());
                    $response = json_decode((string)$result->getBody());
                    return response()->json(['status' => false, 'message' => [$response->message]]);
                }
                DB::commit();
                $listID[] = $item->id;
                $listEmail[] = $item->email;
                $listName[] = $item->account_name;
            }

            Session::flash('id', $listID);
            Session::flash('email', $listEmail);
            Session::flash('name', $listName);

            return response()->json(['status' => true,'message' => [__('message.success.delete_select_audit')]]);
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
        }
    }
}
