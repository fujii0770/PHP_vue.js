<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\DepartmentUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\GwAppApiUtils;
use App\Http\Utils\MailUtils;
use App\Http\Utils\OptionUserUtils;
use App\Http\Utils\PasswordUtils;
use App\Http\Utils\PermissionUtils;
use App\Http\Utils\StampUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Jobs\ImportReceiveUser;
use App\Models\Company;
use App\Models\CompanyStampGroups;
use App\Models\Department;
use App\Models\Position;
use App\Models\Stamp;
use App\Models\User;
use App\Models\UserInfo;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Session;

class ReceiveUserController extends Controller
{
    private $model;
    private $userInfo;
    private $company;
    private $stamp;
    private $position;
    private $password_utils;


    public function __construct(User $model, UserInfo $userInfo, Department $department, Position $position, Company $company,Stamp $stamp, PasswordUtils $password_utils)
    {
        parent::__construct();
        $this->model = $model;
        $this->userInfo = $userInfo;
        $this->company = $company;
        $this->stamp = $stamp;
        $this->position = $position;
        $this->password_utils = $password_utils;

        $this->assign('use_angular', true);
        $this->assign('show_sidebar', true);
        $this->assign('use_contain', true);
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        if (!$user->can(PermissionUtils::PERMISSION_RECEIVE_USERS_VIEW)) {
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }

        $action = $request->get('action', '');

        $limit = $request->get('limit') ? $request->get('limit') : 50;
        $users = [];
        if (!array_search($limit, array_merge(config('app.page_list_limit'), [20]))) {
            $limit = config('app.page_limit');
        }
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir') : 'desc';

        if ($action != "") {
             $users = $this->model->getList($user->mst_company_id, AppUtils::USER_RECEIVE, true, $limit);
            $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
        }

        $company = DB::table('mst_company')
            ->leftJoin('mst_limit', 'mst_company.id', '=', 'mst_limit.mst_company_id')
            ->select('mst_company.*', 'mst_limit.use_mobile_app_flg')
            ->where('mst_company.id', $user->mst_company_id)
            ->first();
        if($action != ""){
            $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
        }

        $company->domain = explode("\r\n", $company->domain);
        if (count($company->domain) == 1 ){
            $company->domain = explode("\n", $company->domain[0]);
        }
        //ドメイン名を変更する
        [$email_domain_company, $domains] = OptionUserUtils::replaceDomains($company->domain, 'wf');
        $company->domain = $domains;
        $company_domain_include_without_email = [];
        foreach($company->domain as $domain) {
            $company_domain_include_without_email[$domain] = ltrim($domain,"@");
            if ($company->without_email_flg) {
                $company_domain_include_without_email[$domain. '.scs'] = ltrim($domain, "@") . '.scs';
            }
        }

        //部署
        $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);

        $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);

        //役職
        $listPosition = $this->position
            ->select('id' , 'position_name as text' , 'position_name as sort_name')
            ->where('state',1)
            ->where('mst_company_id',$user->mst_company_id)
            ->get()
            ->map(function ($sort_name) {
                $sort_name->sort_name = str_replace(\App\Http\Utils\AppUtils::STR_KANJI, \App\Http\Utils\AppUtils::STR_SUUJI, $sort_name->sort_name);

                return $sort_name;
            })
            ->keyBy('id')
            ->sortBy('sort_name')
            ->toArray();

        $this->assign('listDepartmentDetail', $listDepartmentDetail);
        $this->assign('listDepartmentTree', $listDepartmentTree);
        $this->assign('listPosition', $listPosition);
        $this->assign('listPositionObj', json_encode($listPosition, JSON_FORCE_OBJECT));

        $this->assign('email_domain_company', $email_domain_company);
        $this->assign('company_domain_include_without_email', $company_domain_include_without_email);
        $this->assign('users', $users);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);
        $this->assign('company', $company);

        $this->assign('allow_create', $user->can(PermissionUtils::PERMISSION_RECEIVE_USERS_CREATE));
        $this->assign('allow_update', $user->can(PermissionUtils::PERMISSION_RECEIVE_USERS_UPDATE));
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);
        $this->setMetaTitle("受信専用利用者");
        return $this->render('SettingReceiveUser.index');
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
        if (!$user->can(PermissionUtils::PERMISSION_RECEIVE_USERS_CREATE)) {
            return response()->json(['status' => false, 'message' => [__('message.warning.not_permission_access')]]);
        }

        $item_user = $request->get('item');
        $customAttributes = [
            'email' => 'ユーザーID',
        ];
        if(isset($item_user['without_email_flg']) && $item_user['without_email_flg']){
            $item_user['email'] = $item_user['email'].'.scs';
            unset($item_user['notification_email']);
            $validator = Validator::make($item_user, $this->model->rules("", false, true, true), [], $customAttributes);
        } else {
            $validator = Validator::make($item_user, $this->model->rules("", false, true), [], $customAttributes);
        }
        $userInfoRules = $this->userInfo->rules('', true);
        $infoValidator = Validator::make($item_user['info'], $userInfoRules);

        if ($validator->fails() || $infoValidator->fails()) {
            $message = $validator->messages()->merge($infoValidator->messages());
            $message_all = $message->all();
            return response()->json(['status' => false, 'message' => $message_all]);
        }

        $company = $this->company->where('id', $user->mst_company_id)->first();

        $apiUser = [
            "email" => strtolower($item_user['email']),
            "contract_app" => config('app.pac_contract_app'),
            "app_env" => config('app.pac_app_env'),
            "contract_server" => config('app.pac_contract_server'),
            "user_auth" => AppUtils::AUTH_FLG_RECEIVE,
            "user_first_name" => $item_user['given_name'],
            "user_last_name" => $item_user['family_name'],
            "company_name" => $company ? $company->company_name : '',
            "company_id" => $company ? $company->id : 0,
            "status" => AppUtils::convertState($item_user['state_flg']),
            "system_name" => $company ? $company->system_name : '',
        ];

        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            return response()->json(['status' => false, 'message' => ['Cannot connect to ID App']]);
        }
        if(!isset($item_user['without_email_flg'])){
            $item_user['notification_email'] = strtolower($item_user['notification_email']);
        }
        $item_user['option_flg'] = AppUtils::USER_RECEIVE;
        $item = new $this->model;
        $item->fill($item_user);
        $item->mst_company_id = $user->mst_company_id;
        $item->login_id = Str::uuid()->toString();
        $item->system_id = 0;
        $item->amount = 0;
        $item->password = "";
        $item->create_user = $user->getFullName();

        $item_info = new $this->userInfo;
        $item_info->fill($item_user['info']);
        $item_info->approval_request_flg = 1;
        // 企業設定と同じように設定する
        $item_info->browsed_notice_flg = 0;
        $item_info->update_notice_flg = 0;
        $item_info->create_user = $user->getFullName();
        // 追加部署と役職
        if($item_info->mst_department_id == "null") {
            $item_info->mst_department_id = null;
        }
        if($item_info->mst_position_id == "null") {
            $item_info->mst_position_id = null;
        }

        if($item_info->mst_department_id_1 == "null") {
            $item_info->mst_department_id_1 = null;
        }
        if($item_info->mst_department_id_2 == "null") {
            $item_info->mst_department_id_2 = null;
        }
        if($item_info->mst_position_id_1 == "null") {
            $item_info->mst_position_id_1 = null;
        }
        if($item_info->mst_position_id_2 == "null") {
            $item_info->mst_position_id_2 = null;
        }


        DB::beginTransaction();
        try {
            $item->save();
            $item_info->mst_user_id = $item->id;
            $item_info->date_stamp_config = 1;
            $item_info->save();

            Log::debug("Call ID App Api to create company user");
            $apiUser['create_user_email'] = $user->email;
            $result = $client->post("users", [
                RequestOptions::JSON => $apiUser
            ]);

            if ($result->getStatusCode() != 200) {
                DB::rollBack();
                Log::warning("Call ID App Api to create company user failed. Response Body " . $result->getBody());
                $response = json_decode((string)$result->getBody());
                return response()->json(['status' => false,
                    'message' => [$response->message],
                    'errors' => isset($response->errors) ? $response->errors : []
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
        }

        return response()->json(['status' => true, 'id' => $item->id, 'info_id' => $item_info->id, 'message' => [__('message.success.create_user')]
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
        $company = $this->company->where('id', $user->mst_company_id)->first();
        $department_stamp_flg = $company->department_stamp_flg;
        $company_enable_email = $company->enable_email;
        $item = $this->model->where('mst_company_id', $user->mst_company_id)->find($id);
        $user_info = $this->userInfo->where('mst_user_id', $id)->select('enable_email', 'email_format')->first();

        if (!$item) {
            return response()->json(['status' => false, 'message' => [__('message.warning.not_permission_access')]]);
        }

        $listGroup = CompanyStampGroups::join('mst_company_stamp_groups_admin',function($query)use($user){
                $query->on('mst_company_stamp_groups.id','mst_company_stamp_groups_admin.group_id')
                    ->where('mst_company_stamp_groups_admin.mst_admin_id',$user->id);
                })
            ->where('mst_company_id','=', $user->mst_company_id)
            ->select(['mst_company_stamp_groups.id as id','mst_company_stamp_groups.group_name as group_name'])
            ->get();

        $item->info;
        $item->stamps = $item->getStamps($item->id, $user->id);
        $item->department_stamp_flg = $department_stamp_flg;
        $item->contract_edition = $company->contract_edition;
        $item->company_enable_email = $company_enable_email;
        $item->user_enable_email = $user_info->enable_email;
        $item->user_email_format = $user_info->email_format;
        $item->company_stamp_flg = $company->stamp_flg;
        $item->passwordStatus = $item->password == "" ? 0 : 1;
        $admin_id = $user->id;
        $items = $item->toArray();

        return response()->json(['status' => true, 'item' => $items, 'listGroup' => $listGroup, 'admin_id' => $admin_id, 'company' => $company]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = \Auth::user();
        if (!$user->can(PermissionUtils::PERMISSION_RECEIVE_USERS_UPDATE)) {
            return response()->json(['status' => false, 'message' => [__('message.warning.not_permission_access')]]);
        }
        $item_user = $request->get('item');
        $item_info = $this->userInfo->find($item_user['info']['id']);
        if ($item_info) {
            $checkCompanyUserInfo = DB::table('mst_user')
                ->where('id', $item_info->mst_user_id)
                ->where('mst_company_id', $user->mst_company_id)
                ->first();
        }
        $item = $this->model->where('mst_company_id', $user->mst_company_id)->find($id);
        if (!$item or !$item_info or !$checkCompanyUserInfo) {
            return response()->json(['status' => false, 'message' => [__('message.warning.not_permission_access')]]);
        }
        if(isset($item_user['without_email_flg']) && $item_user['without_email_flg']){
            unset($item_user['notification_email']);
            $validator = Validator::make($item_user, $this->model->rules($id, false, true, true));
        }else {
            $validator = Validator::make($item_user, $this->model->rules($id, false, true));
        }

        $userInfoRules = $this->userInfo->rules('', true);
        $infoValidator = Validator::make($item_user['info'], $userInfoRules);
        if ($validator->fails() || $infoValidator->fails()) {
            $message = $validator->messages()->merge($infoValidator->messages());

            $message_all = $message->all();
            return response()->json(['status' => false, 'message' => $message_all]);
        }

        $company = $this->company->where('id', $user->mst_company_id)->first();

        $apiUser = [
            "user_email" => $item->email,
            "email" => strtolower($item_user['email']),
            "contract_app" => config('app.pac_contract_app'),
            "app_env" => config('app.pac_app_env'),
            "contract_server" => config('app.pac_contract_server'),
            "user_auth" => AppUtils::AUTH_FLG_RECEIVE,
            "user_first_name" => $item_user['given_name'],
            "user_last_name" => $item_user['family_name'],
            "company_name" => $company ? $company->company_name : '',
            "company_id" => $company ? $company->id : 0,
            "status" => AppUtils::convertState($item_user['state_flg']),
            "system_name" => $company ? $company->system_name : '',
        ];

        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            return response()->json(['status' => false, 'message' => ['Cannot connect to ID App']]);
        }

        // 有効 -> 無効　更新時、invalid_at設定
        if ($item->state_flg == AppUtils::STATE_VALID && ($item_user['state_flg'] == AppUtils::STATE_INVALID || $item_user['state_flg'] == AppUtils::STATE_INVALID_NOPASSWORD)) {
            $item_user['invalid_at'] = Carbon::now();
        } elseif ($item_user['state_flg'] == AppUtils::STATE_VALID) {
            $item_user['invalid_at'] = null;
        }

        $item_user['email'] = strtolower($item_user['email']);
        $item->fill($item_user);

        $apiUser['update_user_email'] = $user->email;
        $result = $client->put("users", [
            RequestOptions::JSON => $apiUser
        ]);
        if ($result->getStatusCode() == 200) {
            $item->update_user = $user->getFullName();
        } else {
            Log::warning("Call ID App Api to update company user failed. Response Body " . $result->getBody());
            $response = json_decode((string)$result->getBody());
            return response()->json(['status' => false,
                'message' => [$response->message],
            ]);
        }

        $item_info->fill($item_user['info']);
        $item_info->update_user = $user->getFullName();
        // 追加部署と役職
        if($item_info->mst_department_id == "null") {
            $item_info->mst_department_id = null;
        }
        if($item_info->mst_position_id == "null") {
            $item_info->mst_position_id = null;
        }
        // PAC_5-1599 追加部署と役職 Start
        if($item_info->mst_department_id_1 == "null") {
            $item_info->mst_department_id_1 = null;
        }
        if($item_info->mst_department_id_2 == "null") {
            $item_info->mst_department_id_2 = null;
        }
        if($item_info->mst_position_id_1 == "null") {
            $item_info->mst_position_id_1 = null;
        }
        if($item_info->mst_position_id_2 == "null") {
            $item_info->mst_position_id_2 = null;
        }


        DB::beginTransaction();
        try {

            $item->save();
            if ($item->state_flg==AppUtils::STATE_INVALID){
                DB::table('app_role_users')->where('mst_user_id',$item->id)->delete();
                DB::table('mst_application_users')->where('mst_user_id',$item->id)->delete();
            }
            $item_info->mst_user_id = $item->id;
            $item_info->save();
            // ユーザ無効時、rememberToken削除
            if ($item->state_flg == AppUtils::STATE_INVALID) {
                CommonUtils::rememberTokenClean($item->id, 'mst_user');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
        }
        return response()->json(['status' => true, 'id' => $item->id, 'info_id' => $item_info->id, 'message' => [__('message.success.update_user')]]);
    }

    /**
     * 利用者削除(単人)
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = \Auth::user();
        if (!$user->can(PermissionUtils::PERMISSION_RECEIVE_USERS_DELETE)) {
            return response()->json(['status' => false, 'message' => [__('message.warning.not_permission_access')]]);
        }

        $item = $this->model->find($id);
        if (!$item || $item->mst_company_id != $user->mst_company_id) {
            return response()->json(['status' => false, 'message' => [__('message.warning.not_permission_access')]]);
        }

        $item->state_flg = AppUtils::STATE_DELETE;
        $item->delete_at = Carbon::now();
        $company = $this->company->where('id', $user->mst_company_id)->first();
        $apiUser = [
            "email" => $item->email,
            "contract_app" => config('app.pac_contract_app'),
            "app_env" => config('app.pac_app_env'),
            "contract_server" => config('app.pac_contract_server'),
            "user_auth" => AppUtils::AUTH_FLG_RECEIVE,
            "user_first_name" => $item->given_name,
            "user_last_name" => $item->family_name,
            "company_name" => $company ? $company->company_name : '',
            "status" => AppUtils::convertState($item->state_flg),
            'update_user_email' => $user->email,
            'user_email' => $item->email,
            "company_id" => $company ? $company->id : 0,
            "system_name" => $company ? $company->system_name : '',
        ];

        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            return response()->json(['status' => false, 'message' => ['Cannot connect to ID App']]);
        }

        // PAC_5-3112 GW・CalDAV側の利用者情報も削除する Start
        $gw_use = config('app.gw_use');
        $gw_domain = config('app.gw_domain');
        if($gw_use == 1 && $gw_domain) {
            Log::debug("Call Gw Api to delete company user $item->email");
            $gw_result = GwAppApiUtils::userDelete($id, $item->email, $company ? $company->id : 0);
            if (!$gw_result) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => [__('message.false.api.del_gw_user')]]);
            }
        }
        // PAC_5-3112 End

        Log::debug("Call ID App Api to disable company user $item->email");
        $result = $client->put("users", [
            RequestOptions::JSON => $apiUser
        ]);
        $item->notification_email = $item->notification_email . '.del';
        $item->email = $item->email . '.del';
        if ($result->getStatusCode() == 200) {
            try {
                DB::beginTransaction();
                $item->save();
                DB::table('app_role_users')->where('mst_user_id',$id)->delete();
                DB::table('mst_application_users')->where('mst_user_id',$id)->delete();
                // ユーザ削除時、rememberToken削除
                CommonUtils::rememberTokenClean($id, 'mst_user');
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }
        } else {
            Log::warning("Call ID App Api to disable company user failed. Response Body " . $result->getBody());
            $response = json_decode((string)$result->getBody());

            return response()->json(['status' => false, 'message' => [$response->message]]);
        }

        return response()->json(['status' => true]);
    }

    /**
     * 利用者一括削除
     * @param Request $request
     */
    public function deletes(Request $request){
        $user = \Auth::user();
        if (!$user->can(PermissionUtils::PERMISSION_OPTION_USERS_DELETE)) {
            return response()->json(['status' => false, 'message' => [__('message.not_permission_access')]]);
        }

        $cids = $request->get('cids', []);
        if (!$cids) {
            return response()->json(['status' => false, 'message' => [__('message.not_permission_access')]]);
        }

        $mstUsers = DB::table('mst_user')
            ->whereIn('id', $cids)
            ->get();

        foreach ($mstUsers as $mstUser) {
            if ($mstUser->mst_company_id != $user->mst_company_id) {
                return response()->json(['status' => false, 'message' => [__('message.not_permission_access')]]);
            }
        }

        $company = $this->company->where('id', $user->mst_company_id)->first();

        try {
            $gw_use     = config('app.gw_use');
            $gw_domain  = config('app.gw_domain');
            //APIからtokenを取得
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                return response()->json(['status' => false, 'message' => ['Cannot connect to ID App']]);
            }

            foreach ($mstUsers as $mstUser) {
                //IDM(mst_user)更新用項目設定
                $mstUser->state_flg = AppUtils::STATE_DELETE;
                $apiUser = [
                    "email" => $mstUser->email,
                    "contract_app" => config('app.pac_contract_app'),
                    "app_env" => config('app.pac_app_env'),
                    "contract_server" => config('app.pac_contract_server'),
                    "user_auth" => AppUtils::AUTH_FLG_RECEIVE,
                    "user_first_name" => $mstUser->given_name,
                    "user_last_name" => $mstUser->family_name,
                    "company_name" => $company ? $company->company_name : '',
                    "status" => AppUtils::convertState($mstUser->state_flg),
                    'update_user_email' => $user->email,
                    'user_email' => $mstUser->email,
                    "company_id" => $company ? $company->id : 0,
                    "system_name" => $company ? $company->system_name : ''
                ];

                DB::beginTransaction();
                DB::table('mst_user')->where('id', $mstUser->id)->update([
                    'notification_email' => $mstUser->notification_email . '.del',
                    'email' => $mstUser->email . '.del',
                    'state_flg' => AppUtils::STATE_DELETE,
                    'delete_at' => Carbon::now(),
                    'update_user' => $user->getFullName(),
                    'update_at' => Carbon::now()]);

                DB::table('app_role_users')->where('mst_user_id',$mstUser->id)->delete();
                DB::table('mst_application_users')->where('mst_user_id',$mstUser->id)->delete();

                // ユーザ削除時、rememberToken削除
                CommonUtils::rememberTokenClean($mstUser->id, 'mst_user');

                // PAC_5-3112 GW・CalDAV側の利用者情報も削除する Start
                if($gw_use == 1 && $gw_domain) {
                    Log::debug("Call Gw Api to delete company user $mstUser->email");
                    $gw_result = GwAppApiUtils::userDelete($mstUser->id, $mstUser->email, $company ? $company->id : 0);
                    if (!$gw_result) {
                        DB::rollBack();
                        return response()->json(['status' => false, 'message' => [__('message.false.api.del_gw_user')]]);
                    }
                }
                // PAC_5-3112 End

                Log::debug("Call ID App Api to disable company user $mstUser->email");

                $result = $client->put("users", [
                    RequestOptions::JSON => $apiUser
                ]);

                if ($result->getStatusCode() == 200) {
                    DB::commit();
                    Log::debug("Disable company user $mstUser->email succeed ");
                } else {
                    DB::rollBack();
                    Log::warning("Call ID App Api to disable company user failed. Response Body " . $result->getBody());
                    $response = json_decode((string)$result->getBody());
                    return response()->json(['status' => false,
                        'message' => [$response->message]
                    ]);
                }
            }
            return response()->json(['status' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
        }
    }

    /**
     * ログインURL送信
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendLoginUrl(Request $request)
    {
        $user = \Auth::user();

        $cids = $request->get('cids', []);
        $items = [];
        if (count($cids)) {
            $items = DB::table('mst_user')
                ->whereIn('id', $cids)
                ->where('mst_company_id', $user->mst_company_id)
                ->get();
        }
        if (!count($items)) {
            return response()->json(['status' => false, 'message' => [__('message.warning.not_permission_access')]]);
        }

        // check login type of logged company
        $company = DB::table('mst_company')
            ->where('id', $user->mst_company_id)
            ->select('login_type', 'url_domain_id')
            ->first();
        $emails = [];
        if ($company && $company->login_type == AppUtils::LOGIN_TYPE_SSO) {
            $data = ['url_domain_id' => $company->url_domain_id];
            foreach ($items as $item) {
                MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                    $item->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['USER_REGISTRATION_COMPLETE_NOTIFY']['CODE'],
                    // パラメータ
                    json_encode($data, JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_ADMIN,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.UserRegistrationCompleteMail.subject'),
                    // メールボディ
                    trans('mail.UserRegistrationCompleteMail.body', $data)
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
     * 氏名印検索
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNameStamps(Request $request){

        $user = \Auth::user();
        $name = $request->get('name');
        $name = mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', $name);
        $name = mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $name);

        $isFullSizeName = AppUtils::jpn_zenkaku_only($name);

        if (!$isFullSizeName){
            return response()->json(['status' => false, 'message' => ['全角文字を入力してください。']]);
        }

        // search map
        // mst_stamp_synonyms_mapに検索、存在する場合、リスト取得、存在しない場合、自身利用
        $synonymsList  =   DB::table('mst_stamp_synonyms_map')
            ->where('origin', $name)
            ->select('origin','synonym')
            ->get();

        $nameList = array();
        if(count($synonymsList)){
            foreach ($synonymsList as $synonyms) {
                $nameList[] = $synonyms->synonym;
            }
        }else{
            $nameList[] = $name;
        }
        foreach ($nameList as $name) {
            // search in table
            $stamps = $this->stamp->leftJoin('mst_assign_stamp', function($join){
                $join->on('mst_assign_stamp.stamp_id', '=', 'mst_stamp.id');
                $join->on('mst_assign_stamp.stamp_flg', '=', DB::raw(AppUtils::STAMP_FLG_NORMAL));
            })
                ->where('mst_stamp.stamp_name', $name)
                ->whereNull('mst_assign_stamp.id')
                ->select(['mst_stamp.stamp_division', 'mst_stamp.font'])->get();
            $missingStamps = [0 => [0,1,2]];
            foreach ($stamps as $stamp){
                unset($missingStamps[$stamp->stamp_division][$stamp->font]);
            }
            if(count($missingStamps)){
                // call api generate
                try{
                    $arrInsert = [];
                    foreach($missingStamps as $stamp_division => $fonts){ // stamp_division
                        foreach($fonts as $font){ // font
                            $stamp = AppUtils::searchStamp($name, $stamp_division, $font);
                            if(is_numeric($stamp)){
                                return response()->json(['status' => false, 'message' => ["その印面は作成できません（code = ".$stamp.")"]]);
                            }else{
                                // ハンコ解像度調整
                                $stamp->contents = StampUtils::stampClarity(base64_decode($stamp->contents));

                                $arrInsert[] = [
                                    'stamp_name' => $name,
                                    'stamp_division' => $stamp_division,
                                    'font' => $font,
                                    'stamp_image' => $stamp->contents,
                                    'width' => floatval($stamp->realWidth) * 100, 'height' => floatval($stamp->realHeight) * 100,
                                    'date_x' => $stamp->datex, 'date_y' => $stamp->datey,
                                    'date_width' => $stamp->datew, 'date_height' => $stamp->dateh,
                                    'create_user' => $user->getFullName(), 'serial' => ''
                                ];
                            }
                        }
                    }
                    DB::beginTransaction();
                    $this->stamp->insert($arrInsert);
                    $insertedStamps = $this->stamp->where('serial', '')->select('id')->get();
                    foreach ($insertedStamps as $insertedStamp){
                        $this->stamp->where('id', $insertedStamp->id)->update(['serial' => AppUtils::generateStampSerial(AppUtils::STAMP_FLG_NORMAL, $insertedStamp->id)]);
                    }
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
                }
            }
        }
        $stamps = $this->stamp->whereIn('id', function($query) use ($nameList) {
            $query->from('mst_stamp')->leftJoin('mst_assign_stamp', function($join){
                $join->on('mst_assign_stamp.stamp_id', '=', 'mst_stamp.id');
                $join->on('mst_assign_stamp.stamp_flg', '=', DB::raw(AppUtils::STAMP_FLG_NORMAL));
            })
                ->selectRaw('MIN(mst_stamp.id)')
                ->where('mst_stamp.stamp_division',0)
                ->whereIn('mst_stamp.stamp_name', $nameList)
                ->whereNull('mst_assign_stamp.id')
                ->groupBy(['mst_stamp.stamp_name', 'mst_stamp.stamp_division', 'mst_stamp.font']);
        })
            ->orderBy('stamp_division')->orderBy('stamp_name','desc')->orderBy('font')->get();

        return response()->json(['status' => true, 'items' => $stamps]);
    }

    /**
     * 受信専用利用者 印面登録
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeNameStamps(Request $request)
    {
        $user = \Auth::user();

        if(!$user->can(PermissionUtils::PERMISSION_RECEIVE_USERS_UPDATE)){
            return response()->json(['status' => false,'message' => [__('管理者情報取得処理に失敗しました。')]]);
        }

        $stamps        = $request->get('stamps');
        $mst_user_id   = $request->get('mst_user_id');
        $stamp_flg     = $request->get('stamp_flg');
        $time_stamp_permission = $request->get('time_stamp_permission');
        $state_flg     = $request->has('state_flg') ? $request->get('state_flg') : AppUtils::STATE_VALID;
        $mst_admin_id  = $user->id;  // 管理者ID

        $itemUser      = $this->model->find($mst_user_id);
        if(!$itemUser || $itemUser->mst_company_id != $user->mst_company_id){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')], 'error' => $itemUser]);
        }

        if(!$stamps OR !count($stamps)){
            return response()->json(['status' => false,'message' => [__('管理者情報取得処理に失敗しました。')]]);
        }

        DB::beginTransaction();

        try{
            // 利用者状態再判定
            $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();


            $UserStamps = $itemUser->getStamps($mst_user_id);

            $updateFlg = false;
            // 利用者無効時(パスワード設定前含む)、有効に更新
            if($itemUser->state_flg == 0 || $itemUser->state_flg == 9){
                $itemUser->state_flg = 1;
                $updateFlg = true;
            }

            if($updateFlg){
                $client = IdAppApiUtils::getAuthorizeClient();
                if (!$client){
                    return response()->json(['status' => false,
                        'message' => ['Cannot connect to ID App'],
                        'is_over' => 0,
                    ]);
                }
                $apiUser = [
                    "user_email" => $itemUser->email,
                    "email"=> strtolower($itemUser->email),
                    "contract_app"=> config('app.pac_contract_app'),
                    "app_env"=> config('app.pac_app_env'),
                    "contract_server"=> config('app.pac_contract_server'),
                    "user_auth"=> AppUtils::AUTH_FLG_RECEIVE,
                    "user_first_name"=> $itemUser->given_name,
                    "user_last_name"=> $itemUser->family_name,
                    "company_name"=> $company?$company->company_name:'',
                    "company_id"=> $company?$company->id:0,
                    "status"=> AppUtils::convertState($itemUser->state_flg),
                    "system_name"=> $company?$company->system_name:'',
                    "update_user_email"=> $user->email,
                ];

                $itemUser->save();

                Log::debug("Call ID App Api to create company receiver user");

                $result = $client->put("users",[
                    RequestOptions::JSON => $apiUser
                ]);

                if($result->getStatusCode() != 200) {
                    DB::rollBack();
                    Log::warning("Call ID App Api to create company user failed. Response Body ".$result->getBody());
                    $response = json_decode((string) $result->getBody());
                    return response()->json(['status' => false,
                        'message' => [$response->message],
                        'errors' => isset($response->errors)?$response->errors:[],
                        'is_over' => 0,
                    ]);
                }
            }

            $arrInsert = [];
            foreach($stamps as & $stamp_id){
                $arrInsert[] = [
                    'stamp_id' => $stamp_id, 'mst_user_id' => $mst_user_id, 'display_no' => 0, 'state_flg' => $state_flg,
                    'stamp_flg' => $stamp_flg, 'create_user' => $user->getFullName(), 'time_stamp_permission' => $time_stamp_permission,
                    'mst_admin_id' => $mst_admin_id];
            }
            DB::table('mst_assign_stamp')->insert($arrInsert);

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        $UserStamps = $itemUser->getStamps($mst_user_id);

        $stampAssign = DB::table('mst_stamp')->whereIn('id',$stamps)
                ->select('stamp_division','id')
            ->get()->toArray();


        return response()->json(['status' => true,'message' => [__('印面割当を行いました。')],
            'userAssign' => ['email' => $itemUser->email, 'name' => $itemUser->family_name . $itemUser->given_name],
            'stampAssign' => $stampAssign,
            'stamps'=> $UserStamps,
            'item'=> $itemUser,]);
    }

    /**
     * パスワード設定コード一括表示
     *
     * @param Request $request
     * @return void
     */
    public function showPasswordList(Request $request){
        $user = \Auth::user();
        $user_ids = $request->get('cids', []);

        $users = $this->model->where('mst_company_id', $user->mst_company_id)
            ->whereIn('id', $user_ids)
            ->where('option_flg',AppUtils::USER_RECEIVE)
            ->where('without_email_flg', AppUtils::WITHOUT_EMAIL_T)
            ->where('state_flg', '!=', AppUtils::STATE_DELETE)
            ->get();
        $user_list = [];
        foreach ($users as $key => $user) {
            $password_code = $this->password_utils->createUserPasswordSettingCode($user->id);
            $user_list[] = [
                "email"         => $user->email,
                "family_name"   => $user->family_name,
                "given_name"    => $user->given_name,
                "password_code" => $password_code
            ];
        }
        return response()->json(['status' => true, 'user_list' => $user_list]);
    }
        /**
     * 初期パスワード設定
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function resetPass(Request $request)
    {
        $user = \Auth::user();
        if (!$user->can(PermissionUtils::PERMISSION_RECEIVE_USERS_CREATE) && !$user->can(PermissionUtils::PERMISSION_RECEIVE_USERS_UPDATE)) {
            return response()->json(['status' => false, 'message' => [__('message.not_permission_access')]]);
        }
        $cids = $request->get('cids',[]);
        $items = [];
        $company = $this->company->where('id', $user->mst_company_id)->first();
        if(count($cids)){
            $items = DB::table('mst_user')
                ->whereIn('id', $cids)
                ->where('mst_company_id',$user->mst_company_id)
                ->get();
        }
        if(!count($items)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }


        $company_name = $company ? trim($company->company_name) : '';

        $emails = [];

        foreach($items as $item){
            if($item->option_flg == AppUtils::USER_RECEIVE && $item->without_email_flg == AppUtils::WITHOUT_EMAIL_F){
                $this->sendMailResetPassword(AppUtils::AUTH_FLG_RECEIVE, $item->email, config('app.url_app_user'), $item->notification_email, $company_name);
            }elseif($item->without_email_flg == AppUtils::WITHOUT_EMAIL_T){
                //メールアドレス無し設定、パスワード設定依頼リンク送信不要
            }else{
                $this->sendMailResetPassword(AppUtils::ACCOUNT_TYPE_USER, $item->email, config('app.url_app_user'));
            }
            $emails[] = $item->email;
        }
        Session::flash('emails', $emails);
        return response()->json(['status' => true,
            'message' => [__('message.success.reset_pass_was_send_mail')]
        ]);
    }

    /**
     * 受信専用利用者 csv取込
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = \Auth::user();
            if(!$user->can(PermissionUtils::PERMISSION_RECEIVE_USERS_CREATE))
                return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);

            // 待機中レーコド確認
            // PAC_5-2133 Start import_type
            $item = DB::table('csv_import_list')
                ->where('company_id', $user->mst_company_id)
                ->where('result', 2)
                ->where('import_type', AppUtils::STATE_IMPORT_CSV_RECEIVE_USER)
                ->first();
            // PAC_5-2133 End
            if ($item) {
                return response()->json(['status' => false, 'message' => '現在、CSV取込を行っております。しばらくお待ちください']);
            }
            if (!$request->hasFile('file')) {
                return response()->json(['status' => false, 'message' => 'CSV取込失敗しました。時間をおいて再度お試しください。']);
            }

            // ファイル保存
            $file = $request->file('file');
            $without_email_import_flg = $request->get('without_email_import_flg',0);
            $file_path = storage_path('import_csv/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . $user->mst_company_id . $user->id . time());
            if (!is_dir($file_path)) {
                mkdir($file_path, 0755, true);
            }
            copy($file, $file_path . '/' . $file->getClientOriginalName());
            $path = $file_path . '/' . $file->getClientOriginalName();

            // data取得
            $csv_data = array_map('str_getcsv', file($path)); // doc csv
            $str = file_get_contents($file);
            $code = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5', 'SJIS'));
            if ($code == 'CP936' || $code == 'SJIS' || $code == 'SJIS-win') {
                $csv_data = CommonUtils::convertCode('SJIS-win', 'UTF-8', $csv_data);
            }

            // csv取込履歴追加
            $id = DB::table('csv_import_list')->insertGetId([
                'company_id' => $user->mst_company_id,
                'user_id' => $user->id,
                'name' => $file->getClientOriginalName(),
                'success_num' => 0,
                'failed_num' => 0,
                'total_num' => 0,
                'result' => 2,
                'create_at' => Carbon::now(),
                'file_path' => $path,
                'file_data' => json_encode($csv_data),
                'import_type' => $without_email_import_flg ? AppUtils::STATE_IMPORT_CSV_WITHOUT_EMAIL_RECEIVE_USER : AppUtils::STATE_IMPORT_CSV_RECEIVE_USER,
            ]);
            $this->dispatch(new ImportReceiveUser($id));
            return response()->json(['status' => true, 'message' => 'CSV取込を受付しました。']);
        } catch (\Exception $e) {
            Log::channel('import-csv-daily')->error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => 'CSV取込失敗しました。時間をおいて再度お試しください。']);
        }
    }

}
