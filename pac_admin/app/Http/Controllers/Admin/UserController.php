<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Http\Utils\ApplicationAuthUtils;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\DepartmentUtils;
use App\Http\Utils\GwAppApiUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\MailUtils;
use App\Http\Utils\OperationsHistoryUtils;
use App\Http\Utils\PasswordUtils;
use App\Http\Utils\PermissionUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Jobs\ImportUsers;
use App\Http\Utils\StampUtils;
use App\Models\Admin;
use App\Models\AdminPasswordResets;
use App\Models\AssignStamp;
use App\Models\Company;
use App\Models\CompanyStampGroups;
use App\Models\Department;
use App\Models\DepartmentStamp;
use App\Models\Position;
use App\Models\Stamp;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\StampConvenientDivision;
use App\Models\UserPasswordResets;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Session;
use Illuminate\Support\Facades\DB;
use Reflector;

class UserController extends AdminController
{

    private $model;
    private $userInfo;
    private $department;
    private $position;
    private $assignStamp;
    private $company;
    private $stamp;
    private $departmentStamp;
    private $password_utils;

    public function __construct(User $model, UserInfo $userInfo, Department $department, Position $position,
                                AssignStamp $assignStamp, Company $company, Stamp $stamp, DepartmentStamp $departmentStamp, PasswordUtils $password_utils)
    {
        parent::__construct();
        $this->model = $model;
        $this->userInfo = $userInfo;
        $this->department = $department;
        $this->position = $position;
        $this->assignStamp = $assignStamp;
        $this->company = $company;
        $this->stamp = $stamp;
        $this->departmentStamp = $departmentStamp;
        $this->password_utils = $password_utils;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request){
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_USER_SETTINGS_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        // 無害化処理設定時はCSVダウンロード無効化するためのフラグ TODO 非同期化と無害化
        $sanitizing_flg = Company::where('id', $user->mst_company_id)
                                ->first()->sanitizing_flg;
        $action = $request->get('action','');
        // get list user
        // set limit to 50 for UserSetting page
        $limit = $request->get('limit') ? $request->get('limit') : 50;//config('app.page_limit');
        $users = [];
        if(!array_search($limit, array_merge(config('app.page_list_limit'),[20]))){
            $limit = config('app.page_limit');
        }
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir'): 'desc';

        if ($action != "") {
            //if($action == "export")
            //    $users = $this->model->getList($user->mst_company_id, [AppUtils::USER_NORMAL, AppUtils::USER_WITHOUT_EMAIL]);
            //else
            $users = $this->model->getList($user->mst_company_id, [AppUtils::USER_NORMAL], true, $limit);
            $orderDir = strtolower($orderDir) == "asc" ? "desc" : "asc";
        }
        $company = DB::table('mst_company')
                    ->leftJoin('mst_limit','mst_company.id','=','mst_limit.mst_company_id')
                    ->select('mst_company.*','mst_limit.use_mobile_app_flg')
                    ->where('mst_company.id', $user->mst_company_id)
                    ->first();
        $company->domain = explode("\r\n", $company->domain);
        if (count($company->domain) == 1){
            $company->domain = explode("\n", $company->domain[0]);
        }
        $email_domain_company = [];
        $company_domain_include_without_email = [];
        foreach($company->domain as $domain){
            $email_domain_company[$domain] = ltrim($domain,"@");
            $company_domain_include_without_email[$domain] = ltrim($domain,"@");
            if ($company->without_email_flg) {
                $company_domain_include_without_email[$domain. '.scs'] = ltrim($domain, "@") . '.scs';
            }
        }
        //有効ユーザーの数量
        $users_count = 0;
        if ($users){
            $users_count = count($users->filter(function ($user){
                return $user->state_flg == AppUtils::STATE_VALID;
            }));
        }

        $this->assign('email_domain_company', $email_domain_company);
        $this->assign('company_domain_include_without_email', $company_domain_include_without_email);
        $this->assign('users', $users);
        $this->assign('users_count', $users_count);

        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);

        $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);

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

        // PAC_5-983 BEGIN
        // 上位部署の情報を取得する
        $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);
        // PAC_5-983 END

        $this->assign('listDepartmentDetail', $listDepartmentDetail);
        $this->assign('listDepartmentTree', $listDepartmentTree);
        $this->assign('listPosition', $listPosition);
        // PAC_5-1599 追加部署と役職 Start
        $this->assign('listPositionObj', json_encode($listPosition, JSON_FORCE_OBJECT));
        // PAC_5-1599 End
        $this->assign('company', $company);
        $this->assign('sanitizing_flg', $sanitizing_flg);

        $this->assign('allow_create', $user->can(PermissionUtils::PERMISSION_USER_SETTINGS_CREATE));
        $this->assign('allow_update', $user->can(PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE));

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        $this->setMetaTitle("利用者設定");

        return $this->render('SettingUser.index');
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
        if(!$user->can(PermissionUtils::PERMISSION_USER_SETTINGS_CREATE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        $item_user = $request->get('item');

        //メールアドレス無し設定
        if(isset($item_user['without_email_flg']) && $item_user['without_email_flg']){
            $item_user['email'] = $item_user['email'].'.scs';
        }

        $validator = Validator::make($item_user, $this->model->rules());

        $userInfoRules = $this->userInfo->rules();
        if($item_user['info']['mfa_type'] == 1){
            $userInfoRules['auth_email'] = ($item_user['info']['email_auth_dest_flg'] ? 'required|' : 'nullable|'). $userInfoRules['auth_email'];
        }
        $infoValidator = Validator::make($item_user['info'], $userInfoRules);
        if ($validator->fails() || $infoValidator->fails())
        {
            $message = $validator->messages()->merge($infoValidator->messages());
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        $company = $this->company->where('id', $user->mst_company_id)->first();

        if ($company->form_user_flg){
            //有効ユーザーの数
            $valid_user_count = User::where('mst_company_id',$user->mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)
                ->where('state_flg',AppUtils::STATE_VALID)->count();
            //帳票発行機能専用ユーザ 最大数が5人
            if ($valid_user_count + 1  > AppUtils::MAX_FORM_USER_COUNT ){
                return response()->json(['status' => false, 'message' => __('message.warning.form_user_over'), 'form_user_over' => 1]);
            }
        }

        $apiUser = [
            "email"=> strtolower($item_user['email']),
            "contract_app"=> config('app.pac_contract_app'),
            "app_env"=> config('app.pac_app_env'),
            "contract_server"=> config('app.pac_contract_server'),
            "user_auth"=> AppUtils::AUTH_FLG_USER,
            "user_first_name"=> $item_user['given_name'],
            "user_last_name"=> $item_user['family_name'],
            "company_name"=> $company?$company->company_name:'',
            "company_id"=> $company?$company->id:0,
            "status"=> AppUtils::convertState($item_user['state_flg']),
            "system_name"=> $company?$company->system_name:'',
        ];
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client){
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }

        $item_user['email'] = strtolower($item_user['email']);
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
        $item_info->browsed_notice_flg = $company?$company->view_notification_email_flg:0;
        $item_info->update_notice_flg = $company?$company->updated_notification_email_flg:0;
        $item_info->create_user = $user->getFullName();

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
        // PAC_5-1599 End
        if(!isset($item_info->page_display_first) || empty($item_info->page_display_first)) {
            $item_info->page_display_first = "ポータル";
        }

        if(!isset($item_info->circular_info_first) || empty($item_info->circular_info_first)) {
            $item_info->circular_info_first = "印鑑";
        }

        DB::beginTransaction();
        try{
            $item->save();
            $item_info->mst_user_id = $item->id;
            $item_info->save();

            Log::debug("Call ID App Api to create company user");
            $apiUser['create_user_email'] = $user->email;
            $result = $client->post("users",[
                RequestOptions::JSON => $apiUser
            ]);

            if($result->getStatusCode() != 200) {
                DB::rollBack();
                Log::warning("Call ID App Api to create company user failed. Response Body ".$result->getBody());
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
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true, 'id' => $item->id, 'info_id' => $item_info->id,
            'message' => [__('message.success.create_user'), __('message.success.send_mail_set_pass')]
        ]);
    }

    /**
     * 利用者情報更新
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function update($id, Request $request){
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        Log::debug('update');
        $item_user = $request->get('item');
        $item = $this->model->where('mst_company_id',$user->mst_company_id)->find($id);
        $item_info = $this->userInfo->find($item_user['info']['id']);
        Log::debug('item_info');
        Log::debug($item_info);
        $company = $this->company->where('id', $user->mst_company_id)->first();

        if ($company->form_user_flg){
            //有効ユーザーの数
            $valid_user_count = User::where('mst_company_id',$user->mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)
                ->where('state_flg',AppUtils::STATE_VALID)->count();
            //帳票発行機能専用ユーザ 最大数が5人
            if ($item_user['state_flg'] == AppUtils::STATE_VALID && in_array($item->state_flg,[AppUtils::STATE_INVALID_NOPASSWORD,AppUtils::STATE_INVALID])
                && $valid_user_count + 1  > AppUtils::MAX_FORM_USER_COUNT ){
                return response()->json(['status' => false, 'message' => __('message.warning.form_user_over'), 'form_user_over' => 1]);
            }
        }

        if($item_info){
            $checkCompanyUserInfo = DB::table('mst_user')
                                ->where('id',$item_info->mst_user_id)
                                ->where('mst_company_id',$user->mst_company_id)
                                ->first();
        }
        if(!$item OR !$item_info OR !$checkCompanyUserInfo){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $validator = Validator::make($item_user, $this->model->rules($id));

        $userInfoRules = $this->userInfo->rules();
        if($item_user['info']['mfa_type'] == 1){
            $userInfoRules['auth_email'] = ($item_user['info']['email_auth_dest_flg'] ? 'required|' : 'nullable|'). $userInfoRules['auth_email'];
        }
        $infoValidator = Validator::make($item_user['info'], $userInfoRules);
        if ($validator->fails() || $infoValidator->fails())
        {
            $message = $validator->messages()->merge($infoValidator->messages());

            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        $boolCurrentCompanyFlg = false;
        if ($company && $company->old_contract_flg && $company->contract_edition == 1){
            $boolCurrentCompanyFlg = true;
        }

        // 有効ユーザー数印面チェック：旧契約形態OFF && オプションフラグがON （上限：有効ユーザー上限がオプション契約数）
        if (!$boolCurrentCompanyFlg && $company && !$company->old_contract_flg && $company->option_contract_flg && $item_user['state_flg'] == AppUtils::STATE_VALID && $item->state_flg != AppUtils::STATE_VALID) {
            $mst_user_count = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)->where('state_flg', AppUtils::STATE_VALID)->count();
            if ($mst_user_count + 1 > $company->option_contract_count) {
                return response()->json(['status' => false, 'message' => [__("message.warning.user_limit", ['counts' => $mst_user_count, 'limit' => $company->option_contract_count])]]);
            }
        }

        //PAC_5-2476
        //旧契約形態ON　&& Bussiness : ユーザー数上限がライセンス契約数
        if ($company->old_contract_flg) {
            $mst_user_count = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)->where('state_flg', AppUtils::STATE_VALID)->count();
            if ($company->contract_edition == 1 && $mst_user_count + 1 > $company->upper_limit && $item->state_flg != AppUtils::STATE_VALID) {
                return response()->json(['status' => false, 'message' => [__("message.warning.user_limit_response", ['counts' => $mst_user_count, 'limit' => $company->upper_limit])]]);
            }
        }

        $apiUser = [
            "user_email" => $item->email,
            "email"=> strtolower($item_user['email']),
            "contract_app"=> config('app.pac_contract_app'),
            "app_env"=> config('app.pac_app_env'),
            "contract_server"=> config('app.pac_contract_server'),
            "user_auth"=> AppUtils::AUTH_FLG_USER,
            "user_first_name"=> $item_user['given_name'],
            "user_last_name"=> $item_user['family_name'],
            "company_name"=> $company?$company->company_name:'',
            "company_id"=> $company?$company->id:0,
            "status"=> AppUtils::convertState($item_user['state_flg']),
            "system_name"=> $company?$company->system_name:'',
        ];
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client){
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }

        // PAC_5-1577
        // get all user stamp total and company stamp limit
        $arrCATotal = Company::getCompanyStampLimitAndUserStampCount($user->mst_company_id);
        // user stamp total
        $intUsersStamp = $arrCATotal['intUserStampCount'];
        // company stamp limit
        $intCompanyStamp = $arrCATotal['intCompanyStampLimit'];

        // 有効 -> 無効　更新時、invalid_at設定
        if($item->state_flg == AppUtils::STATE_VALID && ($item_user['state_flg'] == AppUtils::STATE_INVALID || $item_user['state_flg'] == AppUtils::STATE_INVALID_NOPASSWORD)){
            $item_user['invalid_at'] = Carbon::now();
        }elseif ($item_user['state_flg'] == AppUtils::STATE_VALID){
            $item_user['invalid_at'] = null;
        }
        $intCurrentStampCount = $intUsersStamp + count($item_user['stamps']['stampMaster']) + count($item_user['stamps']['stampCompany']) + count($item_user['stamps']['stampDepartment']) + count($item_user['stamps']['stampWaitDepartment']);
        // current user is not lived but current user want update status to lived   must count this user stamps total compare company set stamp's total
        // 印面上限チェック：
        $stamp_is_over = 0;
        $over_message = [
            'stamp_over' => '',
            'convenient_stamp_over' => ''
        ];
        if ($company->old_contract_flg) {
            //旧契約形態ON　&& Standarad ：上限がイセンス契約数
            //旧契約形態ON　&& Business、Business Pro、trial ：上限なし
            if ($company->contract_edition == 0 && in_array($item->state_flg, [AppUtils::STATE_INVALID, AppUtils::STATE_INVALID_NOPASSWORD])
                && $item_user['state_flg'] == AppUtils::STATE_VALID && (($intUsersStamp > $intCompanyStamp) || ($intCurrentStampCount > $intCompanyStamp))) {
                $stamp_is_over = 1;
                $over_message['stamp_over'] = sprintf(__("message.warning.stamp_limit"), $intCurrentStampCount, $intCompanyStamp);
            }
        } else {
            //旧契約形態OFF　&& Standarad、Business、Business Pro ：上限がイセンス契約数
            //旧契約形態OFF　&& trial ：上限なし
            if (!$boolCurrentCompanyFlg && in_array($company->contract_edition, [0, 1, 2]) && in_array($item->state_flg, [AppUtils::STATE_INVALID, AppUtils::STATE_INVALID_NOPASSWORD]) && $item_user['state_flg'] == AppUtils::STATE_VALID
                && (($intUsersStamp > $intCompanyStamp) || ($intCurrentStampCount > $intCompanyStamp))) {
                {
                    $stamp_is_over = 1;
                    $over_message['stamp_over'] = sprintf(__("message.warning.stamp_limit"), $intCurrentStampCount, $intCompanyStamp);
                }
            }
        }

        //無効 -> 有効　更新時  便利印
        $convenient_stamp_is_over = 0;
        if ($item_info->option_flg == AppUtils::USER_NORMAL) {
            $totalConvenientStampArr = Company::getCompanyConvenientStampLimitCount($item->mst_company_id);
            $userHasConvenientStamp = DB::table("mst_assign_stamp")
                ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
                ->join("mst_company","mst_user.mst_company_id","=","mst_company.id")
                ->where("mst_user.id",$item->id)
                ->where("mst_company.id",$item->mst_company_id)
                ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_CONVENIENT)
                ->where('mst_assign_stamp.state_flg',AppUtils::STATE_VALID)
                ->where('mst_user.option_flg',AppUtils::USER_NORMAL)
                ->count();
            if (in_array($item->state_flg, [AppUtils::STATE_INVALID, AppUtils::STATE_INVALID_NOPASSWORD])
                && $item_user['state_flg'] == AppUtils::STATE_VALID && in_array($company->contract_edition, [0, 1, 2]) && $company->convenient_flg == 1
                && ($totalConvenientStampArr['userConvenientStampCount'] > $totalConvenientStampArr['companyConvenientStampLimit'] || $userHasConvenientStamp + $totalConvenientStampArr['userConvenientStampCount'] > $totalConvenientStampArr['companyConvenientStampLimit'])) {
                $convenient_stamp_is_over = 1;
                $over_message['convenient_stamp_over'] = sprintf(__("message.warning.convenient_stamp_limit"),$totalConvenientStampArr['userConvenientStampCount']+$userHasConvenientStamp,$totalConvenientStampArr['companyConvenientStampLimit']);
            }
        }
        if ($stamp_is_over === 1 || $convenient_stamp_is_over === 1) {
            return response()->json([
                'status' => false,
                'message' => $over_message,
                'is_over' => $stamp_is_over,
                'convenient_stamp_is_over' => $convenient_stamp_is_over,
            ]);
        }

        $item_user['email'] = strtolower($item_user['email']);
        $item->fill($item_user);

        $apiUser['update_user_email'] = $user->email;
        $result = $client->put("users",[
            RequestOptions::JSON => $apiUser
        ]);
        if($result->getStatusCode() == 200) {
            $item->update_user = $user->getFullName();
        }else{
            Log::warning("Call ID App Api to update company user failed. Response Body ".$result->getBody());
            $response = json_decode((string) $result->getBody());
            return response()->json(['status' => false,
                'message' => [$response->message],
                //'errors' => $response->errors
            ]);
        }

        $item_info->fill($item_user['info']);
        $item_info->update_user = $user->getFullName();

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
        // PAC_5-1599 End
        // PAC_5-1264 ▼
        // 個人の権限が取り下げられた場合、設定された印鑑の角度が0に復旧
        if ($item_info->rotate_angle_flg == 0){
            $item_info->default_rotate_angle = 0;
        }
        // PAC_5-1264 ▲
        DB::beginTransaction();
        try {
            $item->save();
            /*PAC_5-2458 S*/
            if ($item->state_flg==AppUtils::STATE_INVALID){
                DB::table('app_role_users')->where('mst_user_id',$id)->delete();
                DB::table('mst_application_users')->where('mst_user_id',$id)->delete();
            }
            if ($item->state_flg==AppUtils::STATE_VALID && $company->contract_edition == AppUtils::CONTRACT_EDITION_TRIAL){
                ApplicationAuthUtils::appUserUpdate($company->id,AppUtils::GW_APPLICATION_ID_FAQ_BOARD,$item->id);
            }
            /*PAC_5-2458 E*/
            $item_info->mst_user_id = $item->id;
            $item_info->save();

            // ユーザ無効時、rememberToken削除
            if ($item->state_flg == AppUtils::STATE_INVALID) {
                CommonUtils::rememberTokenClean($item->id, 'mst_user');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        // get all user stamp total and company stamp limit
        $arrCATotal = Company::getCompanyStampLimitAndUserStampCount($user->mst_company_id);
        // current company is not free and user's stamp total  > company limit
        $intBoolIsOver = ($arrCATotal['intUserStampCount'] > $arrCATotal['intCompanyStampLimit'] && $company->contract_edition != 3) ? 1 : 0 ;
        Session::put('stamp_is_over',$intBoolIsOver);
        // is_over params just let html's addStampButton  can not  add
        return response()->json(['status' => true, 'id' => $item->id, 'info_id' => $item_info->id, 'is_over' => 0 ,
            'message' => [__('message.success.update_user')]
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
        $company = $this->company
            ->leftJoin('mst_limit','mst_company.id','=','mst_limit.mst_company_id')
            ->select('mst_company.*','mst_limit.time_stamp_permission as company_time_stamp_permission')
            ->where('mst_company.id', $user->mst_company_id)
            ->first();
        $department_stamp_flg = $company->department_stamp_flg;
        $company_enable_email = $company->enable_email;
        $item = $this->model->where('mst_company_id',$user->mst_company_id)->find($id);
        $user_info = $this->userInfo->where('mst_user_id',$id)->select('enable_email', 'email_format')->first();

        Log::debug('show');
        //Log::debug($request->all());

        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $listGroup = CompanyStampGroups::
            join('mst_company_stamp_groups_admin',function($query)use($user){
                $query->on('mst_company_stamp_groups.id','mst_company_stamp_groups_admin.group_id')
                        ->where('mst_company_stamp_groups_admin.mst_admin_id',$user->id);
        })
            ->where('mst_company_id','=', $user->mst_company_id)
            ->select(['mst_company_stamp_groups.id as id','mst_company_stamp_groups.group_name as group_name'])
            ->get();

        $item->info;
        $item->stamps = $item->getStamps($item->id,$user->id);
        $item->department_stamp_flg = $department_stamp_flg;
        $item->contract_edition = $company->contract_edition;
        $item->company_enable_email = $company_enable_email;
        $item->user_enable_email = $user_info->enable_email;
        $item->user_email_format = $user_info->email_format;
        $item->company_stamp_flg = $company->stamp_flg;
        $item->company_time_stamp_permission = $company->company_time_stamp_permission;
        $item->passwordStatus = $item->password==""?0:1;
        $item->default_stamp_flg = $company->default_stamp_flg;
        $admin_id = $user->id;
        // $item->passwordStatus = $item->password==""?"未設定":"設定済";

        // PAC_5-1055 BEGIN 利用者設定の部分で日付が表示されるようにしてほしい
        if (!empty($item->stamps['stampCompany'])) {
            $items = $item->toArray();
            $stampCompanys = $item->stamps['stampCompany']->toArray();
            foreach ($stampCompanys as &$stamps) {
                // 共通印時間を追加する
                if ( $stamps['stamp_company']['stamp_division'] !== 0){
                    $stamps['stamp_company']['stamp_image'] = StampUtils::companyStampWithDateArr($stamps['stamp_company'], $user->mst_company_id);
                }
            }
            $items['stamps']['stampCompany'] = $stampCompanys;
        }
        // PAC_5-1055 END

        if (!empty($items['stamps']['convenientStamp'])){
            $convenientStamps = $items['stamps']['convenientStamp'];
            foreach ($convenientStamps as $key => $stamp){
                if ($stamp['stamp_date_flg'] !== 0){
                    $convenientStamps[$key]['stamp_image'] = StampUtils::companyStampWithDate($stamp, $user->mst_company_id);
                }
            }
            $items['stamps']['convenientStamp'] = $convenientStamps;
        }

        // PAC_5-1577
        // 印面上限チェック：
        $arrCATotal = Company::getCompanyStampLimitAndUserStampCount($user->mst_company_id);
        $intOverStatus = 0;
        if ($company->old_contract_flg) {
            $mst_user_count = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)->where('state_flg', AppUtils::STATE_VALID)->count();
            //旧契約形態ON　&& Standarad ：上限がイセンス契約数
            //旧契約形態ON　&& Business、Business Pro、trial ：上限なし
            if ($company->contract_edition == 0 && $arrCATotal['intUserStampCount'] > $arrCATotal['intCompanyStampLimit']) {
                $intOverStatus = 1;
            }
            //PAC_5-2476
            //旧契約形態ON　&& Bussiness : ユーザー数上限がライセンス契約数
            if ($company->contract_edition == 1 && $mst_user_count > $company->upper_limit) {
                $intOverStatus = 1;
                $items['stamp_is_over_message'] = $intOverStatus ? sprintf(__("message.warning.user_limit_biz"),$mst_user_count,$arrCATotal['intCompanyStampLimit']):'';
            }
        } else {
            //旧契約形態OFF　&& Standarad、Business、Business Pro ：上限がイセンス契約数
            //旧契約形態OFF　&& trial ：上限なし
            if (in_array($company->contract_edition, [0, 1, 2]) &&$arrCATotal['intUserStampCount'] > $arrCATotal['intCompanyStampLimit']) {
                $intOverStatus = 1;
            }
        }

        // 便利印
        $totalConvenientStampArr = Company::getCompanyConvenientStampLimitCount($user->mst_company_id);
        $convenientStampIsOver = 0;
        if (in_array($company->contract_edition, [0, 1, 2]) && $company->convenient_flg == 1 && $totalConvenientStampArr['userConvenientStampCount'] > $totalConvenientStampArr['companyConvenientStampLimit']) {
            $convenientStampIsOver = 1;
        }

        // get all user stamp total and company stamp limit

        // stamp is over ? message : ''
        $items['stamp_is_over'] = $intOverStatus;
        $items['convenient_stamp_is_over'] = $convenientStampIsOver;
        $items['convenient_stamp_is_over_message'] = $convenientStampIsOver ? sprintf(__("message.warning.convenient_stamp_limit"),$totalConvenientStampArr['userConvenientStampCount'],$totalConvenientStampArr['companyConvenientStampLimit']):'';

        $divisionList = StampConvenientDivision::where('del_flg',0)->get();
        // PAC_5-2332 add S
        $one='';
        foreach ($divisionList as $key=>$val){
            if($val->id==4){
                $one=$val;
                unset($divisionList[$key]);
                $divisionList[]=$one;
            }
        }
        // PAC_5-2332 E
        return response()->json(['status' => true, 'item' => $items,'listGroup'=>$listGroup, 'admin_id'=>$admin_id,'company'=>$company,'divisionList'=>$divisionList]);
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
            ->where('option_flg', AppUtils::USER_NORMAL)
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
     * 利用者削除(単人)
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_USER_SETTINGS_DELETE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        $item = $this->model->find($id);

        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        if($item->mst_company_id != $user->mst_company_id){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $item->state_flg = AppUtils::STATE_DELETE;
        $item->delete_at = Carbon::now();
        $company = $this->company->where('id', $user->mst_company_id)->first();
        $apiUser = [
            "email" => $item->email,
            "contract_app" => config('app.pac_contract_app'),
            "app_env" => config('app.pac_app_env'),
            "contract_server" => config('app.pac_contract_server'),
            "user_auth" => AppUtils::AUTH_FLG_USER,
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
        if (!$client){
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
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
        $result = $client->put("users",[
            RequestOptions::JSON => $apiUser
        ]);
        $item->email = $item->email . '.del';
        if($result->getStatusCode() == 200) {
            try{
                DB::beginTransaction();
                $item->save();

                AssignStamp::where('mst_user_id', $id)->update(['state_flg' => AppUtils::STATE_INVALID, 'delete_at' => Carbon::now() ]);
                /*PAC_5-2458 S*/
                DB::table('app_role_users')->where('mst_user_id',$id)->delete();
                DB::table('mst_application_users')->where('mst_user_id',$id)->delete();
                /*PAC_5-2458 E*/
                // ユーザ削除時、rememberToken削除
                CommonUtils::rememberTokenClean($id,'mst_user');
                $info=OperationsHistoryUtils::LOG_INFO['User']['destroy'];
                $log_info = [
                    [
                        'auth_flg' => OperationsHistoryUtils::HISTORY_FLG_ADMIN,
                        'mst_display_id' => $info[0],
                        'mst_operation_id' => $info[1],
                        'result' => 0,
                        'detail_info' => "＜{$item->email}＞の利用者情報の削除に成功しました。",
                        'ip_address' => request()->server->get('HTTP_X_FORWARDED_FOR') ? request()->server->get('HTTP_X_FORWARDED_FOR'):request()->getClientIp(),
                        'create_at' => date("Y-m-d H:i:s"),
                    ]
                ];
                OperationsHistoryUtils::storeRecordsToCurrentEnv($log_info,$user->id);
                DB::commit();
            }catch(\Exception $e){
                DB::rollBack();
            }
        }else{
            Log::warning("Call ID App Api to disable company user failed. Response Body ".$result->getBody());
            $response = json_decode((string) $result->getBody());
            return response()->json(['status' => false,
                'message' => [$response->message]
            ]);
        }

        return response()->json(['status' => true]);
    }

    public function import(Request $request)
    {
        try {

            $user = \Auth::user();
            // 待機中レーコド確認
            // PAC_5-2133 Start import_type
            $item = DB::table('csv_import_list')
                ->where('company_id', $user->mst_company_id)
                ->where('result', 2)
                ->where('import_type', AppUtils::STATE_IMPORT_CSV_USER)
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
                'import_type' => $without_email_import_flg ? AppUtils::STATE_IMPORT_CSV_WITHOUT_EMAIL_USER : AppUtils::STATE_IMPORT_CSV_USER,
            ]);
            $this->dispatch(new ImportUsers($id));
            return response()->json(['status' => true, 'message' => 'CSV取込を受付しました。']);
        } catch (\Exception $e) {
            Log::channel('import-csv-daily')->error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => 'CSV取込失敗しました。時間をおいて再度お試しください。']);
        }
    }

    public function searchStamp(Request $request){
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

        // mst_stamp insert
        foreach ($nameList as $name) {
            // search in table
            $stamps = $this->stamp->leftJoin('mst_assign_stamp', function($join){
                $join->on('mst_assign_stamp.stamp_id', '=', 'mst_stamp.id');
                $join->on('mst_assign_stamp.stamp_flg', '=', DB::raw(AppUtils::STAMP_FLG_NORMAL));
            })
                ->where('mst_stamp.stamp_name', $name)
                ->whereNull('mst_assign_stamp.id')
                ->select(['mst_stamp.stamp_division', 'mst_stamp.font'])->get();

            $missingStamps = [0 => [0,1,2],1 => [0,1,2]];
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

                                $arrInsert[] = ['stamp_name' => $name, 'stamp_division' => $stamp_division, 'font' => $font,
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
                                ->whereIn('mst_stamp.stamp_name', $nameList)
                                ->whereNull('mst_assign_stamp.id')
                                ->groupBy(['mst_stamp.stamp_name', 'mst_stamp.stamp_division', 'mst_stamp.font']);
                        })
                ->orderBy('stamp_division')->orderBy('stamp_name','desc')->orderBy('font')->get();

        return response()->json(['status' => true, 'items' => $stamps]);
    }

    /**
     * 利用者一括削除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletes(Request $request)
    {
        $user = \Auth::user();
        if (!$user->can(PermissionUtils::PERMISSION_USER_SETTINGS_DELETE)) {
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
            $gw_use = config('app.gw_use');
            $gw_domain = config('app.gw_domain');
            //APIからtokenを取得
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }

            foreach ($mstUsers as $mstUser) {
                //IDM(mst_user)更新用項目設定
                $mstUser->state_flg = AppUtils::STATE_DELETE;
                $apiUser = [
                    "email" => $mstUser->email,
                    "contract_app" => config('app.pac_contract_app'),
                    "app_env" => config('app.pac_app_env'),
                    "contract_server" => config('app.pac_contract_server'),
                    "user_auth" => 1,
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
                    'email' => $mstUser->email . '.del',
                    'state_flg' => AppUtils::STATE_DELETE,
                    'delete_at' => Carbon::now(),
                    'update_user' => $user->getFullName(),
                    'update_at' => Carbon::now()]);
                AssignStamp::where('mst_user_id', $mstUser->id)->update(['state_flg' => AppUtils::STATE_INVALID, 'delete_at' => Carbon::now(), 'update_user' => $user->getFullName(), 'update_at' => Carbon::now()]);
                /*PAC_5-2458 S*/
                DB::table('app_role_users')->where('mst_user_id',$mstUser->id)->delete();
                DB::table('mst_application_users')->where('mst_user_id',$mstUser->id)->delete();
                /*PAC_5-2458 E*/
                // ユーザ削除時、rememberToken削除
                CommonUtils::rememberTokenClean($mstUser->id,'mst_user');

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
                    $info=OperationsHistoryUtils::LOG_INFO['User']['deletes'];
                    $log_info = [
                        [
                            'auth_flg' => OperationsHistoryUtils::HISTORY_FLG_ADMIN,
                            'mst_display_id' => $info[0],
                            'mst_operation_id' => $info[1],
                            'result' => 0,
                            'detail_info' => "＜{$mstUser->email}＞の利用者情報の削除に成功しました。",
                            'ip_address' => $request->server->get('HTTP_X_FORWARDED_FOR') ? $request->server->get('HTTP_X_FORWARDED_FOR'):$request->getClientIp(),
                            'create_at' => date("Y-m-d H:i:s"),
                        ]
                    ];
                    OperationsHistoryUtils::storeRecordsToCurrentEnv($log_info,$user->id);
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

    public function resetpass(Request $request)
    {
        $user = \Auth::user();
        if (!$user->can(PermissionUtils::PERMISSION_USER_SETTINGS_CREATE) && !$user->can(PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE) && !$user->can(PermissionUtils::PERMISSION_OPTION_USERS_CREATE) && !$user->can(PermissionUtils::PERMISSION_OPTION_USERS_UPDATE)) {
            return response()->json(['status' => false, 'message' => [__('message.not_permission_access')]]);
        }
        $cids = $request->get('cids',[]);
        $items = [];
        $company = $this->company->where('id', $user->mst_company_id)->first();
        $invalid_user_count = DB::table('mst_user')->whereIn('id',$cids)->where('state_flg','!=',AppUtils::STATE_VALID)->count();
        if ($company && !$company->old_contract_flg && $company->option_contract_flg && $invalid_user_count > 0) {
            $mst_user_count = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)->where('state_flg', AppUtils::STATE_VALID)->count();
            if ($mst_user_count + $invalid_user_count > $company->option_contract_count) {
                return response()->json(['status' => false, 'message' => [__("message.warning.user_limit", ['counts' => $mst_user_count + $invalid_user_count, 'limit' => $company->option_contract_count])]]);
            }
        }
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
            if($item->option_flg == AppUtils::USER_OPTION && $item->without_email_flg == AppUtils::WITHOUT_EMAIL_F){
                $this->sendMailResetPassword(AppUtils::ACCOUNT_TYPE_OPTION, $item->email, config('app.url_app_user'), $item->notification_email, $company_name);
            }elseif($item->without_email_flg == AppUtils::WITHOUT_EMAIL_T && $item->option_flg == AppUtils::USER_OPTION){
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

    public function sendLoginUrl(Request $request)
    {
        $user = \Auth::user();

        $cids = $request->get('cids',[]);
        $items = [];
        if(count($cids)){
            $items = DB::table('mst_user')
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
            foreach($items as $item){
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

    public function getDepartmentStamp(Request $request){
        $user = \Auth::user();

        $param = [];
        // 印面種類(XGL-15/XGFD-21)
        $param['shohincd']     = $request->input('info.stamp_type');
        // レイアウト
        $param['ptn']          = $request->input('info.stamp_layout');
        // 書体(鯱旗楷書体W5/鯱旗古印体W5/鯱旗行書体W5)
        $param['font']         = AppUtils::STAMP_FONT_VALUE[$request->input('info.font')];
        // 色
        $param['color']        = $request->input('info.color');
        // 枠名（レイアウト毎に固定）(XGL15-E.drw/XG21-E.drw)
        $param['waku']         = isset(AppUtils::STAMP_WAKU[$param['shohincd']])?AppUtils::STAMP_WAKU[$param['shohincd']]:"";
        // 企業用ID　企業認証、企業別サービスを行うためのID
        $param['bizid']        = AppUtils::STAMP_BIZID;
        // 画像のサイズ
        $param['imgsize']      = AppUtils::STAMP_SIZE[$param['shohincd']];
        $param['fname']        = Date("YmdHis");
        // 文字化け判定用文字列（固定)
        $param['garbled']      = AppUtils::STAMP_GARBLED;

        $face_up1 = $face_up2 = $face_down1 = $face_down2 = "";

        $req_face_up = $request->input('info.face_up') ? $request->input('info.face_up') : "　";
        $req_face_up1 = $request->input('info.face_up1') ? $request->input('info.face_up1') : "　";
        $req_face_up2 = $request->input('info.face_up2') ? $request->input('info.face_up2') : "　";
        $req_face_down = $request->input('info.face_down') ? $request->input('info.face_down') : "　";
        $req_face_down1 = $request->input('info.face_down1') ? $request->input('info.face_down1') : "　";
        $req_face_down2 = $request->input('info.face_down2') ? $request->input('info.face_down2') : "　";

        switch($param['ptn']){
            case 'E0{0}1':
                // XGL-15 上下１行（子付き）
                $face_up1 = $param['item1'] = $req_face_up;
                $face_down1 = $param['item2'] = $req_face_down;
                $face_down2 = $param['item3'] = $req_face_down1;
                $length = mb_strlen($param['item2']);

                $length = $length>3?3:$length;
                $param['ptn'] = "E0".$length."1";
                break;
            case 'E101':
                // 上下１行 XGL-15/XGFD-21
                $face_up1 = $param['item1'] = $req_face_up;
                $face_down1 = $param['item2'] = $req_face_down;
                break;
            case 'E102':
                // XGFD-21 下２行
                $face_up1 = $param['item1'] = $req_face_up1;
                $face_down1 = $param['item2'] = $req_face_down1;
                $face_down2 = $param['item3'] = $req_face_down2;
                break;
            case 'E201':
                // XGFD-21 上２行
                $face_up1 = $param['item1'] = $req_face_up1;
                $face_up2 = $param['item2'] = $req_face_up2;
                $face_down1 = $param['item3'] = $req_face_down1;
                break;
            case 'E202':
                // XGFD-21 上下２行
                $face_up1 = $param['item1'] = $req_face_up1;
                $face_up2 = $param['item2'] = $req_face_up2;
                $face_down1 = $param['item3'] = $req_face_down1;
                $face_down2 = $param['item4'] = $req_face_down2;
                break;
        }

        // dd(['form_params' => $param]);
        $client = new Client([  'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'], 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout') ]);
        try{
            $result = $client->post(config('app.department_stamp_api_url'), ['form_params' => $param]);
            if($result->getStatusCode() == 200) {
                $stamp = (string) $result->getBody();

                $result = $client->get($stamp);
                if($result->getStatusCode() == 200) {
                    $img_str = (string) $result->getBody();
                    $im = new \Imagick();
                    $im->readImageBlob($img_str);
                    $im->setImageResolution(72,72);
                    $im->setImageFormat("png");
                    $imgBuff = $im->getimageblob();

                    // ハンコ解像度調整
                    $stamp_image = StampUtils::stampClarity($imgBuff);

                    $stamp = [
                        'pribt_type' => $param['shohincd'],
                        'font' => $request->input('info.font'),
                        'stamp_image' => $stamp_image,
                        'layout' => $param['ptn'],
                        'color' => $param['color'],
                        'face_up1' => $face_up1,
                        'face_up2' => $face_up2,
                        'face_down1' => $face_down1,
                        'face_down2' => $face_down2,
                        'width' => $param['imgsize'] * AppUtils::PX_TO_MICROMET/3,
                        'height' => $param['imgsize'] * AppUtils::PX_TO_MICROMET/3,
                        'real_width' => $param['imgsize'] * AppUtils::PX_TO_MICROMET/3,
                        'real_height' => $param['imgsize'] * AppUtils::PX_TO_MICROMET/3,
                        'date_x'      => AppUtils::STAMP_DATE_X[$param['shohincd']],
                        'date_y'      => AppUtils::STAMP_DATE_Y[$param['shohincd']],
                        'date_width'  => AppUtils::STAMP_DATE_WIDTH[$param['shohincd']],
                        'date_height' => AppUtils::STAMP_DATE_HEIGHT[$param['shohincd']],
                        'state' => AppUtils::STATE_VALID,
                        'serial'=>''
                    ];

                    DB::beginTransaction();
                    $departmentStamp = new $this->departmentStamp;
                    $departmentStamp->fill($stamp);
                    $departmentStamp->save();

                    $departmentStamp->serial = AppUtils::generateStampSerial(AppUtils::STAMP_FLG_DEPARTMENT, $departmentStamp->id);
                    $departmentStamp->save();
                    DB::commit();

                    $stamp['id'] = $departmentStamp->id;

                    return response()->json(['status' => true, 'stamp' => $stamp]);
                }else{
                    Log::warning("Get stamp response body: ".$result->getBody());
                    return response()->json(['status' => false, 'message' => ["Something errors"]]);
                }
            }else {
                Log::warning("Create stamp response body: ".$result->getBody());
                return response()->json(['status' => false, 'message' => ["Something errors"]]);
            }

        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }

    /**
     * 部署捺印が生成されたときに情報を取得します
     * @param Request $request 情報
     */
    public function getDepartmentStampInfo (Request $request){
        try {
            // 捺印id
            $id = $request->input('info');
            // 捺印情報取得
            $stampInfo = DB::table('department_stamp')
                ->where('id', $id)
                ->first();
            return response()->json(['status' => true, 'stamp' => $stampInfo]);
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }

    /**
     * get current company stamp is full ? or  not full
     * @param Request $request  select_type val==1 then ( >= company.upper_limit)   val==2 then  (> company.upper_limit)
     * @return mixed is_over  1 : 0;   1 === is full    0 == not full
     */
    public function getStampOverStatus(Request $request){
        $user = \Auth::user();
        $intType = $request->input("select_type",1); //2:ユーザー登録 1:ユーザー更新
        $company = $this->company->where('id', $user->mst_company_id)->first();
        $intOverStatus = 0;
        $strMessage = '';
        $arrCUTotal = Company::getCompanyStampLimitAndUserStampCount($user->mst_company_id);

        // 印面上限チェック：
        if ($company->old_contract_flg) {
            //旧契約形態ON　&& Standarad ：上限がイセンス契約数
            //旧契約形態ON　&& Business、Business Pro、trial ：上限なし
            if ($company->contract_edition == 0 && $arrCUTotal['intUserStampCount'] >= $arrCUTotal['intCompanyStampLimit']) {
                $intOverStatus = 1;
            }
            //Businessエディション＋旧契約形態フラグON → 契約数を有効利用者数が超えないように制御する
            if ($company->contract_edition == AppUtils::CONTRACT_EDITION_BUSINESS){
                $mst_user_count = DB::table('mst_user')
                    ->where('mst_company_id', $user->mst_company_id)
                    ->where('option_flg',AppUtils::USER_NORMAL)
                    ->where('state_flg', AppUtils::STATE_VALID)
                    ->count();
                if ( $mst_user_count + 1 > $company->upper_limit) {
                    return response()->json(['status' => true,'is_over' => 1, 'message' => [__("message.warning.user_limit_response", ['counts' => $mst_user_count, 'limit' => $company->upper_limit])]]);
                }
            }
        } else {
            //旧契約形態OFF　&& Standarad、Business、Business Pro ：上限がイセンス契約数
            //旧契約形態OFF　&& trial ：上限なし
            if (in_array($company->contract_edition, [0, 1, 2]) &&$arrCUTotal['intUserStampCount'] >= $arrCUTotal['intCompanyStampLimit']) {
                $intOverStatus = 1;
            }
        }

        if($intOverStatus == 1 ){
            if($company->contract_edition != 3 && $intType == 2){
                Session::put('stamp_is_over',$intOverStatus);
            }
            $strMessage = sprintf(__("message.warning.stamp_limit"),$arrCUTotal['intUserStampCount'],$arrCUTotal['intCompanyStampLimit']);
        }
        return response()->json(['status' => true, 'is_over' => $intOverStatus, 'message' => $strMessage ]);
    }

    /**
     * find this user all stamp total + all user stamp total  > company upper_limit
     * @param Request $request
     * @return mixed
     */
    public function findCurrentUserStampIsOk(Request $request){
        $user = \Auth::user();
        $intUserID = $request->input("mst_user_id");
        $company = $this->company->where('id', $user->mst_company_id)->first();
        $intStampTotal = Company::getCompanyStampCount($user->mst_company_id);
        $arrAllStampData = (new User())->getStamps($intUserID);
        $intIsOver = 0;
        $arrCurrentUser = User::where("id",$intUserID)->first();
        $over_message = [
            'stamp_over' => '',
            'convenient_stamp_over' => '',
        ];
        $intCurrentStampTotal = $intStampTotal + count($arrAllStampData['stampMaster']) + count($arrAllStampData['stampCompany']) + count($arrAllStampData['stampDepartment']) + count($arrAllStampData['stampWaitDepartment']);

        // 印面上限チェック：
        if ($company->old_contract_flg) {
            //旧契約形態ON　&& Standarad ：上限がイセンス契約数
            //旧契約形態ON　&& Business、Business Pro、trial ：上限なし
            if ($company->contract_edition == 0 && $arrCurrentUser->state_flg != AppUtils::STATE_VALID && $intCurrentStampTotal > $company->upper_limit) {
                $intIsOver = 1;
                $over_message['stamp_over'] = sprintf(__("message.warning.stamp_limit"), $intCurrentStampTotal, $company->upper_limit);
            }
        } else {
            //旧契約形態OFF　&& Standarad、Business、Business Pro ：上限がイセンス契約数
            //旧契約形態OFF　&& trial ：上限なし
            if (in_array($company->contract_edition, [0, 1, 2]) && $arrCurrentUser->state_flg != AppUtils::STATE_VALID && $intCurrentStampTotal > $company->upper_limit) {
                $intIsOver = 1;
                $over_message['stamp_over'] = sprintf(__("message.warning.stamp_limit"), $intCurrentStampTotal, $company->upper_limit);
            }
        }

        //無効 -> 有効　更新時  便利印
        $convenient_stamp_is_over = 0;
        if ($arrCurrentUser->option_flg == AppUtils::USER_NORMAL) {
            $totalConvenientStampArr = Company::getCompanyConvenientStampLimitCount($company->id);
            $userHasConvenientStamp = DB::table("mst_assign_stamp")
                ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
                ->join("mst_company","mst_user.mst_company_id","=","mst_company.id")
                ->where("mst_user.id",$arrCurrentUser->id)
                ->where("mst_company.id",$company->id)
                ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_CONVENIENT)
                ->where('mst_assign_stamp.state_flg',AppUtils::STATE_VALID)
                ->where('mst_user.option_flg',AppUtils::USER_NORMAL)
                ->count();
            if ($arrCurrentUser->state_flg != AppUtils::STATE_VALID && in_array($company->contract_edition, [0, 1, 2]) && $company->convenient_flg == 1
                && ($totalConvenientStampArr['userConvenientStampCount'] > $totalConvenientStampArr['companyConvenientStampLimit'] || $userHasConvenientStamp + $totalConvenientStampArr['userConvenientStampCount'] > $totalConvenientStampArr['companyConvenientStampLimit'])) {
                $convenient_stamp_is_over = 1;
                $over_message['convenient_stamp_over'] = sprintf(__("message.warning.convenient_stamp_limit"),$totalConvenientStampArr['userConvenientStampCount']+$userHasConvenientStamp,$totalConvenientStampArr['companyConvenientStampLimit']);
    }
        }

        return  response()->json(['status' => true, 'is_over' => $intIsOver, 'convenient_stamp_is_over' => $convenient_stamp_is_over, 'message' => $over_message]);
    }

    /**
     * zipcode Cover address
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAddress(Request $request)
    {
        $code = $request->get('zipcode');
        $client = new Client();
        $res = $client->request("get", "https://zipcloud.ibsnet.co.jp/api/search?zipcode=" . $code);
        return response()->json(json_decode($res->getBody(), true));
    }
}
