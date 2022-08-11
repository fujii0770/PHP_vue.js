<?php

namespace App\Http\Controllers\API;

use App\AuditUser;
use App\Http\Controllers\Controller;
use App\Http\Utils\AppUtils;
use App\Http\Utils\ExtraAuthUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Models\MyPage;
use App\Models\PasswordPolicy;
use App\User;
use Carbon\Carbon;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class AuthController extends Controller
{

    public function __construct()
    {

    }

    public function samlLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'urlDomainId' => 'required|string'
        ]);
        if ($validator->fails()) {
            Log::debug('Login SSO failed by missing username or urlDomainId');
            return response()->json([
                'message' => 'メールアドレス、又はパスワードが正しくありません',
                'status' => 203
            ], 203);
        }

        return $this->doLogin($request, true);
    }

    /**
     * ログイン
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);
        if ($validator->fails()) {
            Log::debug('Login failed by missing username/ password');
            return response()->json([
                'message' => 'メールアドレス、又はパスワードが正しくありません',
                'status' => 203
            ], 203);
        }

        return $this->doLogin($request, false);
    }

    private function doLogin(Request $request, bool $isSSOLogin = false){
        Log::debug("ログイン時起動");

        [$isAuditUser, $company] = self::getUserCompany($request['username']);

        if(!$company || !$company->state) {
            Log::debug('Login failed by missing company or invalid state');
            return response()->json([
                'message' => 'メールアドレス、又はパスワードが正しくありません',
                'status' => 203
            ], 203);
        }
        if ($request->has('local_flg')){
            $request['clientIp'] = isset($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',',$_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $request['clientIp'];
            if ($request->get('local_flg') == 1 && $company->local_stamp_flg == 0){
                Log::debug($request['username'] . ' ローカル捺印のログイン時にログインが不可');
                return response()->json([
                    'message' => '機能の利用権限がございません。',
                    'status' => 403
                ], 403);
            }
        }

        if ($request->has('withbox_flg') && $request->get('withbox_flg') == 1 && $company->with_box_flg == 0){
            Log::debug($request['username'] . ' with boxのログイン時にログインが不可');
            return response()->json([
                'message' => '機能の利用制限がございません。',
                'status' => 403
            ], 403);
        }
        if (!$request->get('withbox_flg') && $company->with_box_flg && $company->shachihata_login_flg){
            Log::debug($request['username'] . ' 利用者のshachihata cloudへのログインを制限する');
            return response()->json([
                'message' => 'ログイン制御されています。',
                'status' => 403
            ], 403);
        }

        if (config('app.enable_sso_login') && $isSSOLogin){
            $urlDomainId = $request->get('urlDomainId');
            if ($company->login_type != AppUtils::LOGIN_TYPE_SSO || $company->url_domain_id != $urlDomainId){
                Log::debug('Login SSO failed by invalid login type or invalid url domain id');
                return response()->json([
                    'message' => 'メールアドレス、又はパスワードが正しくありません',
                    'status' => 203
                ], 203);
            }
            if ($isAuditUser){
                $user = AuditUser::where('email', $request['username'])->where('state_flg', AppUtils::STATE_VALID)->first();
                if ($user){
                    Auth::guard('web_audit')->login($user,  $request['remember']);
                }else{
                    Log::debug('Login SSO failed by invalid email or invalid state');
                    return response()->json([
                        'message' => 'メールアドレス、又はパスワードが正しくありません',
                        'status' => 203
                    ], 203);
                }
            }else{
                $user = User::where('email', $request['username'])->where('state_flg', AppUtils::STATE_VALID)->first();
                if ($user){
                    Log::debug("ssologin");

                    Auth::login($user,  $request['remember']);
                }else{
                    Log::debug('Login SSO failed by invalid email or invalid state');
                    return response()->json([
                        'message' => 'メールアドレス、又はパスワードが正しくありません',
                        'status' => 203
                    ], 203);
                }
            }
        }else{
            $credentials = ['email' => $request['username'], 'password' => $request['password'], 'state_flg' => AppUtils::STATE_VALID ];
            if ($isAuditUser){
                if (!Auth::guard('web_audit')->attempt($credentials,  $request['remember'])){
                    Log::debug('Login failed by invalid email or invalid state');
                    return response()->json([
                        'message' => 'メールアドレス、又はパスワードが正しくありません',
                        'status' => 203
                    ], 203);
                }

                $user = Auth::guard('web_audit')->user();
            }else{
                if (!Auth::attempt($credentials,  $request['remember'])){
                    Log::debug('Login failed by invalid email or invalid state');
                    return response()->json([
                        'message' => 'メールアドレス、又はパスワードが正しくありません',
                        'status' => 203
                    ], 203);
                }
                $user = $request->user();
                Log::debug("ssologin false時");
            }

            /*PAC_5-3139 SAMLユーザーログイン時の文言変更 */
            if(isset($request['checkLoginWeb']) && $request['checkLoginWeb'] && $company->login_type == AppUtils::LOGIN_TYPE_SSO){
                return response()->json([
                    'message' => 'シングルサインオン機能を利用中のため、専用のログイン画面からログインしてください。',
                    'status' => 203
                ], 203);
            }
        }

        $isIpPermitted = ExtraAuthUtils::isIpPermitted($user, $request['clientIp']);
        if (!$isIpPermitted) {
            return response()->json([
                'message' => 'アクセスが許可されていません',
                'status' => 403
            ], 403);
        }

        if ($isAuditUser) {
            $pageDisplayFirst = null;
        } else {
            if ($company->received_only_flg || $user->option_flg == AppUtils::USER_RECEIVE) {
                $pageDisplayFirst = '受信一覧';
            } elseif ($user->option_flg == AppUtils::USER_OPTION) {
                $pageDisplayFirst = 'ポータル';
            }else {
                $pageDisplayFirst = DB::table('mst_user_info')->where('mst_user_id', $user->id)->value('page_display_first');
            }
        }

        $mfaCheckResult = empty($request['from_admin']) ? ExtraAuthUtils::checkMfa($user, $request['clientIp'], $request['userAgent']) : [
            'clientIp' => $request['clientIp'],
            'userAgent' => $request['userAgent'],
            'needsMfa' => false,
        ];
        $resArray = $this->generateLoginResponseArray($user, $company, $isAuditUser, $isSSOLogin) + [
            'pageDisplayFirst' => $pageDisplayFirst,
            'mfa' => $mfaCheckResult
        ];
        $res = response()->json($resArray);

        $jar = Auth::guard('web')->getCookieJar();
        if ($jar) {
            $cookie = array_reduce($jar->getQueuedCookies(), function ($c, $e) {
                return $c || Str::startsWith($e, 'remember_web') ? $e : null;
            }, null);
            if ($cookie) {
                $res->headers->setCookie($cookie);
            }
        }

        return $res;
    }

    /**
     * リフレッシュログイン
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function recall(Request $request)
    {
        Log::debug("リフレッシュログイン起動");
        $user = Auth::guard('web')->user();
        if (!$user) {
            $user = Auth::guard('web_audit')->user();
        }

        if (!$user) {
            return response()->json([
                'recallerName' => Auth::guard('web')->getRecallerName(),
                'message' => 'ログインが許可されていません',
                'status' => 401
            ], 401);
        }

        [$isAuditUser, $company] = self::getUserCompany($user['email']);

        if(!$company || !$company->state) {
            return response()->json([
                'message' => 'ログインが許可されていません',
                'status' => 203
            ], 203);
        }

        $isIpPermitted = ExtraAuthUtils::isIpPermitted($user, $request['clientIp']);
        if (!$isIpPermitted) {
            return response()->json([
                'message' => 'アクセスが許可されていません',
                'status' => 403
            ], 403);
        }

        $mfaCheckResult = ExtraAuthUtils::checkMfa($user, $request['clientIp'], $request['userAgent']);

        $isSSOLogin = (bool) $request->input('sso_login', false);
        $resArray = $this->generateLoginResponseArray($user, $company, $isAuditUser, $isSSOLogin) + [
            'recallerName' => Auth::guard('web')->getRecallerName(),
            'mfa' => $mfaCheckResult,
        ];
        return response()->json($resArray);
    }

    /**
     * ユーザーの種類と会社情報を返す ログイン処理用
     *
     * @param string $email
     * @return array [$isAuditUser, $company] 見つからなかった場合、$company は null
     */
    private static function getUserCompany(string $email): array {
        $companyColumns = [
            'mst_company.state', 'mst_company.stamp_flg',
            'mst_company.company_name',
            'mst_company.esigned_flg', 'mst_company.guest_company_flg',
            'mst_company.guest_document_application', 'mst_company.mst_company_id',
            'mst_company.host_app_env', 'mst_company.host_contract_server',
            'mst_company.system_name', 'mst_company.contract_edition', 'mst_company.trial_flg',
            'mst_company.login_type', 'mst_company.received_only_flg',
            'mst_company.rotate_angle_flg', 'mst_company.repage_preview_flg',
            'long_term_storage_flg', 'long_term_storage_option_flg','local_stamp_flg',
            'with_box_flg','time_stamp_assign_flg','ssr.is_special_site_send_available',
            'mst_company.url_domain_id','form_user_flg','mst_company.sanitizing_flg',
            'mst_limit.shachihata_login_flg', 'mst_limit.with_box_login_flg'
        ];

        $company = DB::table('mst_company')
            ->join('mst_user','mst_user.mst_company_id','=','mst_company.id')
            ->leftjoin('special_site_receive_send_available_state as ssr','ssr.company_id','=','mst_company.id')
            ->leftjoin('mst_limit','mst_limit.mst_company_id','=','mst_company.id')
            ->select($companyColumns)
            ->where('mst_user.email', $email)
            ->where('mst_user.state_flg', '!=', AppUtils::STATE_DELETE)->first();

        if ($company) {
            return [false, $company];
        }

        $company = DB::table('mst_company')
            ->join('mst_audit','mst_audit.mst_company_id','=','mst_company.id')
            ->leftjoin('special_site_receive_send_available_state as ssr','ssr.company_id','=','mst_company.id')
            ->leftjoin('mst_limit','mst_limit.mst_company_id','=','mst_company.id')
            ->select($companyColumns)
            ->where('mst_audit.email', $email)
            ->where('mst_audit.state_flg', '!=', AppUtils::STATE_DELETE)->first();

        return [true, $company];
    }

    private function generateLoginResponseArray($user, $company, bool $isAuditUser, bool $isSSOLogin): array {
        $user->parent_company_id = $company->mst_company_id;
        $user->mst_company_name = $company->company_name;

        $user->host_app_env = $company->host_app_env;
        $user->host_contract_server = $company->host_contract_server;
        $user->system_name = $company->system_name;
        $user->contract_edition = $company->contract_edition;
        $user->trial_flg = $company->trial_flg;

        $user->received_only_flg = $company->received_only_flg;
        $user->repage_preview_flg = $company->repage_preview_flg;

        $checkLongTermFlgAll = $company->long_term_storage_flg && $company->long_term_storage_option_flg;
        if (isset($user->option_flg) && $user->option_flg != AppUtils::USER_RECEIVE){
            $user->checkLongTermFlgAll = $checkLongTermFlgAll;
            $user->checkLongTermFlgAllStampFlg = $checkLongTermFlgAll && $company->stamp_flg;
        }else{
            $user->checkLongTermFlgAll = 0;
            $user->checkLongTermFlgAllStampFlg = 0;
        }

        $user->timeStampAssignFlg = $company->time_stamp_assign_flg;//利用者側タイムスタンプ再付与機能
        $user->isGuestCompany = $company->guest_company_flg == 1;
        $user->guestCanSubscribeCircular = $company->guest_document_application == 1;
        $user->check_add_signature_time_stamp = $company->stamp_flg == 1 && $company->esigned_flg == 1;
        $user->is_special_site_send = $company->is_special_site_send_available;//特設サイト提出機能利用可能フラグ
        $user->form_user_flg = $company->form_user_flg;//帳票発行機能専用ユーザ
        $mstUserInfo = DB::table('mst_user_info')->where('mst_user_id',$user->id)->first();
        $user->rotate_angle_flg = $mstUserInfo->rotate_angle_flg;
        $user->date_stamp_config = $mstUserInfo->date_stamp_config;
        $user->department_id = $mstUserInfo->mst_department_id;
        $user->sanitizing_flg = $company->sanitizing_flg;//ダウンロードファイル無害化
        $user->time_stamp_permission = $mstUserInfo->time_stamp_permission;//タイムスタンプ発行権限
        $user->sticky_note_flg = $mstUserInfo->sticky_note_flg;//タイムスタンプ発行権限
        $needResetPass = self::isNeededResetPass($user, $isSSOLogin);
        $tokenResult = $user->createToken('Personal Access Token', [$isAuditUser?AppUtils::ACCOUNT_TYPE_AUDIT:AppUtils::ACCOUNT_TYPE_USER]);
        $branding = DB::table('branding')->where('mst_company_id', $user->mst_company_id)->first();
        $companyLimit = DB::table('mst_limit')
            ->where('mst_company_id', $user->mst_company_id)
            ->first();
        $admin = DB::table('mst_admin')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('email', $user->email)
            ->where('state_flg', AppUtils::STATE_VALID)
            ->first();

        return [
            'status' => StatusCodeUtils::HTTP_OK,
            'is_audit_user' => $isAuditUser,
            'user' => $user,
            'needResetPass' => $needResetPass,
            'token' => $needResetPass ? $this->makeTokenReset($isAuditUser?AppUtils::ACCOUNT_TYPE_AUDIT:AppUtils::ACCOUNT_TYPE_USER, $user->email) : $tokenResult->accessToken,
            'expires_in' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'branding' => $branding,
            'limit' => $companyLimit,
            'admin' => $admin ? true : false,
        ];
    }

    private static function isNeededResetPass($user, bool $isSSOLogin): bool {
        if (config('app.enable_sso_login') && $isSSOLogin) {
            return false;
        }

        $password_policy = PasswordPolicy::where('mst_company_id', $user->mst_company_id)->first();

        if ($password_policy && $password_policy->validity_period){
            if($user->password_change_date == null){
                return true;
            }else {
                $password_change_date = new \DateTime($user->password_change_date);
                $now = Carbon::now();
                $diff = $now->diffInHours($password_change_date);
                if($diff >= $password_policy->validity_period*24) {
                    return true;
                }
            }
        }

        return false;
    }

    public function makeTokenReset($accountType, $email){
        $table = $this->getTablePasswordReset($accountType);

        $token = Hash::make($email.time());
        DB::table($table)->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => new \DateTime(),
        ]);

        return Hash::make($email.$token);
    }

    public function logout(Request $request)
    {
        $isAuditUser = false;
        try {
            $user = $request->user();
            if (!$user){
                $isAuditUser = true;
                $user = Auth::guard('web_audit')->user();
            }
            if ($user){
                $user->remember_token = null;
                $user->save();
            }
        } finally {
            if ($isAuditUser){
                $request->user()->token()->revoke();
                Auth::guard('web')->logout();
            }else{
                if (Auth::guard('web_audit')->user()){
                    Auth::guard('web_audit')->user()->token()->revoke();
                }
                Auth::guard('web_audit')->logout();
            }
        }

        return [
            'message' => 'ログアウトしました。'
        ];
    }

    /**
     * ログイン
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function appLogin(Request $request)
    {
        Log::debug("appLoginからのログイン時起動");
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'メールアドレス、又はパスワードが正しくありません',
                'status' => 203
            ], 203);
        }

        $company = DB::table('mst_company')
            ->join('mst_user','mst_user.mst_company_id','=','mst_company.id')
            ->leftjoin('mst_limit','mst_limit.mst_company_id','=','mst_company.id')
            ->select('mst_company.state', 'mst_company.stamp_flg', 'mst_company.company_name', 'mst_company.esigned_flg','mst_company.guest_company_flg',
                'mst_company.guest_document_application', 'mst_company.mst_company_id', 'mst_company.host_app_env', 'mst_company.host_contract_server','mst_company.system_name',
                'mst_company.contract_edition', 'mst_company.trial_flg','mst_company.received_only_flg','mst_company.rotate_angle_flg','mst_company.repage_preview_flg',
                'mst_company.phone_app_flg','mst_company.with_box_flg', 'mst_limit.shachihata_login_flg', 'mst_limit.with_box_login_flg')
            ->where('mst_user.email',$request['username'])
            ->where('mst_user.state_flg', '!=', AppUtils::STATE_DELETE)->first();

        $isAuditUser = false;
        if (!isset($company) || !$company){
            $isAuditUser = true;
            $company = DB::table('mst_company')
                ->join('mst_audit','mst_audit.mst_company_id','=','mst_company.id')
                ->leftjoin('mst_limit','mst_limit.mst_company_id','=','mst_company.id')
                ->select('mst_company.state', 'mst_company.stamp_flg', 'mst_company.company_name', 'mst_company.esigned_flg', 'mst_company.guest_company_flg',
                    'mst_company.guest_document_application', 'mst_company.mst_company_id', 'mst_company.host_app_env', 'mst_company.host_contract_server','mst_company.system_name',
                    'mst_company.contract_edition', 'mst_company.trial_flg','mst_company.received_only_flg','mst_company.rotate_angle_flg','mst_company.repage_preview_flg',
                    'mst_company.phone_app_flg','mst_company.with_box_flg', 'mst_limit.shachihata_login_flg', 'mst_limit.with_box_login_flg')
                ->where('mst_audit.email',$request['username'])
                ->where('mst_audit.state_flg', '!=', AppUtils::STATE_DELETE)->first();
        }

        if(!isset($company) || !$company->state) {
            return response()->json([
                'message' => 'メールアドレス、又はパスワードが正しくありません',
                'status' => 203
            ], 203);
        }

        if (!$request->get('withbox_flg') && $company->with_box_flg && $company->shachihata_login_flg){
            Log::debug($request['username'] . ' 利用者のshachihata cloudへのログインを制限する');
            return response()->json([
                'message' => 'ログイン制御されています。',
                'status' => 403
            ], 403);
        }

        $credentials = ['email' => $request['username'], 'password' => $request['password'], 'state_flg' => AppUtils::STATE_VALID ];
        if ($isAuditUser){
            if (!Auth::guard('web_audit')->attempt($credentials,  $request['remember'])){
                return response()->json([
                    'message' => 'メールアドレス、又はパスワードが正しくありません',
                    'status' => 203
                ], 203);
            }

            $user = Auth::guard('web_audit')->user();
            $pageDisplayFirst = null;
        }else{
            if (!Auth::attempt($credentials,  $request['remember'])){
                return response()->json([
                    'message' => 'メールアドレス、又はパスワードが正しくありません',
                    'status' => 203
                ], 203);
            }
            $user = $request->user();
            if($company && $company->received_only_flg || $user->option_flg == AppUtils::USER_RECEIVE){
                $pageDisplayFirst = '受信一覧';
            } elseif ($user->option_flg == AppUtils::USER_OPTION) {
                $pageDisplayFirst = 'ポータル';
            }else{
                $pageDisplayFirst = DB::table('mst_user_info')->where('mst_user_id', $user->id)->value('page_display_first');
            }
        }

        //PAC_5-1611 スマホアプリからのログイン時に認証メールが飛ぶ
        $mfaCheckResult['needsMfa'] = false;
        $needResetPass = false;

        $password_policy = PasswordPolicy::where('mst_company_id', $user->mst_company_id)->first();
        if ($password_policy && $password_policy->validity_period){
            if($user->password_change_date == null){
                $needResetPass = true;
            }else {
                $password_change_date = new \DateTime($user->password_change_date);
                $now = Carbon::now();
                $diff = $now->diffInHours($password_change_date);
                if($diff >= $password_policy->validity_period*24) {
                    $needResetPass = true;
                }
            }
        }

        $tokenResult = $user->createToken('Personal Access Token', [$isAuditUser?AppUtils::ACCOUNT_TYPE_AUDIT:AppUtils::ACCOUNT_TYPE_USER]);

        $branding = DB::table('branding')->where('mst_company_id', $user->mst_company_id)->first();

        $checkAddSignatureTimeStamp = false;
        if($company->stamp_flg == 1 && $company->esigned_flg == 1){
            $checkAddSignatureTimeStamp = true;
        }
        $companyLimit = DB::table('mst_limit')
            ->where('mst_company_id', $user->mst_company_id)
            ->first();
        $isGuestCompany = false;
        if($company->guest_company_flg == 1) {
            $isGuestCompany = true;
        }
        $guestCanSubscribeCircular = false;
        if($company->guest_document_application == 1) {
            $guestCanSubscribeCircular = true;
        }
        // PAC_5-1264 かたむけて捺印をユーザー単位で非表示にできるようにする　対応　▼
        $mstUserInfo = DB::table('mst_user_info')->where('mst_user_id',$user->id)->first();
        $date_stamp_config = $mstUserInfo->date_stamp_config;
        // PAC_5-1264 かたむけて捺印をユーザー単位で非表示にできるようにする　対応　▲
        $user->department_id = $mstUserInfo->mst_department_id;
        $user->date_stamp_config = $date_stamp_config;
        $user->check_add_signature_time_stamp = $checkAddSignatureTimeStamp;
        $user->mst_company_name = $company->company_name;
        $user->isGuestCompany = $isGuestCompany;
        $user->guestCanSubscribeCircular = $guestCanSubscribeCircular;
        $user->parent_company_id = $company->mst_company_id;
        $user->host_app_env = $company->host_app_env;
        $user->host_contract_server = $company->host_contract_server;
        $user->system_name = $company->system_name;
        $user->contract_edition = $company->contract_edition;
        $user->trial_flg = $company->trial_flg;
        $user->received_only_flg = $company->received_only_flg;
        // PAC_5-1264 かたむけて捺印をユーザー単位で非表示にできるようにする　対応　▼
        //$user->rotate_angle_flg = $company->rotate_angle_flg;
        $user->rotate_angle_flg = $mstUserInfo->rotate_angle_flg;
        // PAC_5-1264 かたむけて捺印をユーザー単位で非表示にできるようにする　対応　▲
        $user->repage_preview_flg = $company->repage_preview_flg;
        $user->phone_app_flg = $company->phone_app_flg;//携帯アプリ状態
        $admin = DB::table('mst_admin')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('email', $user->email)
            ->where('state_flg', AppUtils::STATE_VALID)
            ->first();

        $res = response()->json([
            'token' => $needResetPass ? $this->makeTokenReset($isAuditUser?AppUtils::ACCOUNT_TYPE_AUDIT:AppUtils::ACCOUNT_TYPE_USER, $user->email) : $tokenResult->accessToken,
            'expires_in' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user' => $user,
            'branding' => $branding,
            'limit' => $companyLimit,
            'admin' => $admin ? true : false,
            'mfa' => $mfaCheckResult,
            'status' => StatusCodeUtils::HTTP_OK,
            'needResetPass' => $needResetPass,
            'is_audit_user' => $isAuditUser,
            'pageDisplayFirst' => $pageDisplayFirst,
        ]);

        $jar = Auth::guard('web')->getCookieJar();
        if ($jar) {
            $cookie = array_reduce($jar->getQueuedCookies(), function ($c, $e) {
                return $c || Str::startsWith($e, 'remember_web') ? $e : null;
            }, null);
            if ($cookie) {
                $res->headers->setCookie($cookie);
            }
        }

        return $res;
    }
    /**
     * リフレッシュログイン
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function appRecall(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            $user = Auth::guard('web_audit')->user();

            if (!$user) {
                return response()->json([
                    'recallerName' => Auth::guard('web')->getRecallerName(),
                    'message' => 'ログインが許可されていません',
                    'status' => 401
                ], 401);
            }
        }

        $isAuditUser = false;
        $company = DB::table('mst_company')
            ->join('mst_user','mst_user.mst_company_id','=','mst_company.id')
            ->select('mst_company.state', 'mst_company.stamp_flg', 'mst_company.esigned_flg', 'mst_company.guest_company_flg', 'mst_company.guest_document_application', 'mst_company.mst_company_id', 'mst_company.host_app_env', 'mst_company.host_contract_server','mst_company.received_only_flg','mst_company.rotate_angle_flg','mst_company.repage_preview_flg')
            ->where('mst_user.email',$user['email'])
            ->where('mst_user.state_flg', '!=', AppUtils::STATE_DELETE)->first();

        if (!$company){
            $company = DB::table('mst_company')
                ->join('mst_audit','mst_audit.mst_company_id','=','mst_company.id')
                ->select('mst_company.state', 'mst_company.stamp_flg', 'mst_company.esigned_flg', 'mst_company.guest_company_flg', 'mst_company.guest_document_application', 'mst_company.mst_company_id', 'mst_company.host_app_env', 'mst_company.host_contract_server','mst_company.received_only_flg','mst_company.rotate_angle_flg','mst_company.repage_preview_flg')
                ->where('mst_audit.email',$user['email'])
                ->where('mst_audit.state_flg', '!=', AppUtils::STATE_DELETE)->first();
            $isAuditUser = true;
        }
        if(!isset($company) || !$company || !$company->state) {
            return response()->json([
                'message' => 'ログインが許可されていません',
                'status' => 203
            ], 203);
        }

        $checkAddSignatureTimeStamp = false;
        if(isset($company) && $company->stamp_flg == 1 && $company->esigned_flg == 1){
            $checkAddSignatureTimeStamp = true;
        }
        $isGuestCompany = false;
        if(isset($company) && $company->guest_company_flg == 1) {
            $isGuestCompany = true;
        }
        $guestCanSubscribeCircular = false;
        if($company->guest_document_application == 1) {
            $guestCanSubscribeCircular = true;
        }
        $user->isGuestCompany = $isGuestCompany;
        $user->guestCanSubscribeCircular = $guestCanSubscribeCircular;
        $user->parent_company_id = $company->mst_company_id;
        $user->host_app_env = $company->host_app_env;
        $user->host_contract_server = $company->host_contract_server;
        $user->received_only_flg = $company->received_only_flg;
        // PAC_5-1264 かたむけて捺印をユーザー単位で非表示にできるようにする　対応　▼
        //$user->rotate_angle_flg = $company->rotate_angle_flg;
        $mstUserInfo = DB::table('mst_user_info')->where('mst_user_id',$user->id)->first();
        $user->rotate_angle_flg = $mstUserInfo->rotate_angle_flg;
        // PAC_5-1264 かたむけて捺印をユーザー単位で非表示にできるようにする　対応　▲
        $user->repage_preview_flg = $company->repage_preview_flg;

        $mfaCheckResult = ExtraAuthUtils::checkMfa($user, $request['clientIp'], $request['userAgent']);
        $needResetPass = false;

        $password_policy = PasswordPolicy::where('mst_company_id', $user->mst_company_id)->first();
        if ($password_policy && $password_policy->validity_period){
            if($user->password_change_date == null){
                $needResetPass = true;
            }else {
                $password_change_date = new \DateTime($user->password_change_date);
                $now = Carbon::now();
                $diff = $now->diffInHours($password_change_date);
                if($diff >= $password_policy->validity_period*24) {
                    $needResetPass = true;
                }
            }
        }

        $tokenResult = $user->createToken('Personal Access Token', [$isAuditUser?AppUtils::ACCOUNT_TYPE_AUDIT:AppUtils::ACCOUNT_TYPE_USER]);

        $branding = DB::table('branding')->where('mst_company_id', $user->mst_company_id)->first();

        $companyLimit = DB::table('mst_limit')
            ->where('mst_company_id', $user->mst_company_id)
            ->first();
        $date_stamp_config = $mstUserInfo->date_stamp_config;
        $user->date_stamp_config = $date_stamp_config;
        $user->check_add_signature_time_stamp = $checkAddSignatureTimeStamp;
        $user->department_id = $mstUserInfo->mst_department_id;

        $admin = DB::table('mst_admin')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('email', $user->email)
            ->where('state_flg', AppUtils::STATE_VALID)
            ->first();

        return response()->json([
            'token' => $needResetPass ? $this->makeTokenReset($isAuditUser?AppUtils::ACCOUNT_TYPE_AUDIT:AppUtils::ACCOUNT_TYPE_USER, $user->email) : $tokenResult->accessToken,
            'expires_in' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user' => $user,
            'branding' => $branding,
            'limit' => $companyLimit,
            'admin' => $admin ? true : false,
            'mfa' => $mfaCheckResult,
            'status' => StatusCodeUtils::HTTP_OK,
            'needResetPass' => $needResetPass,
            'recallerName' => Auth::guard('web')->getRecallerName(),
            'is_audit_user' => $isAuditUser,
        ]);
    }

}
