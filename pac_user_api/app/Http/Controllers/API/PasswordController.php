<?php
/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/8/19
 * Time: 09:55
 */

namespace App\Http\Controllers\API;


use App\Http\Utils\AppUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\StatusCodeUtils;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Http\Utils\MailUtils;
use App\Models\PasswordPolicy;
class PasswordController extends Controller
{
    var $passwordPolicy = null;

    public function checkInit(Request $request)
    {
        $email = $request->get('email');
        $hash = $request->get('token');
        $account_type = $request->get('acctype');
        $table = $this->getTablePasswordReset($account_type);
        $password_reset = DB::table($table)->where('email', $email)->orderBy('created_at', 'desc')->first();

        if (!$password_reset || !Hash::check($email . $password_reset->token, $hash)) {
            return response()->json([
                'message' => 'パスワードのリセットリンクが存在しません。',
                'status' => 203
            ], 203);
        }

        $tableAccount = $this->getTableAccount($account_type);
        if (!$user = $this->checkTimeoutEmail($password_reset, $tableAccount)) {
            return response()->json([
                'message' => 'パスワードのリセットリンクの有効期限が切れています。',
                'status' => 203
            ], 203);
        }
        return response()->json(['status' => StatusCodeUtils::HTTP_OK, 'passwordPolicy' => $this->getPolicy($user->mst_company_id)], StatusCodeUtils::HTTP_OK);
    }

    public function checkedCodeTime(Request $request)
    {
        $email = $request->get('email');
        $account_type = $request->get('acctype');

        if($account_type == AppUtils::ACCOUNT_TYPE_ADMIN){
            $table = 'admin_password_resets';
            $tableAccount = 'mst_admin';
        }elseif($account_type == AppUtils::ACCOUNT_TYPE_USER){
            $table = 'user_password_resets';
            $tableAccount = 'mst_user';
        }else{
            $table = 'audit_password_resets';
            $tableAccount = 'mst_audit';
        }

        $password_reset = DB::table($table)->where('email', $email)->orderBy('created_at', 'desc')->first();

        if (!$user = $this->checkTimeoutCode($password_reset, $tableAccount)) {
            return response()->json([
                'message' => 'パスワードのリセットコードの有効期限が切れています。',
                'status' => 203
            ], 203);
        }
        return response()->json(['status' => StatusCodeUtils::HTTP_OK, 'passwordPolicy' => $this->getPolicy($user->mst_company_id)], StatusCodeUtils::HTTP_OK);
    }

    public function checkInitOutDate(Request $request)
    {
        return $this->checkInit($request);
    }

    public function setPassword(Request $request)
    {
        try {
            $params = $request->all();

            $email = $params['email'];
            $hash = $params['token'];
            $account_type = isset($params['acctype']) ? $params['acctype'] : '';
            $table_password_resets = $this->getTablePasswordReset($account_type);

            $password_reset = DB::table($table_password_resets)->where('email', $email)->orderBy('created_at', 'desc')->first();

            if (!$password_reset || !Hash::check($email . $password_reset->token, $hash)) {
                return response()->json([
                    'message' => 'パスワードのリセットリンクが存在しません。',
                    'status' => 203
                ], 203);
            }

            $table = $this->getTableAccount($account_type);
            if (!$user = $this->checkTimeoutEmail($password_reset, $table)) {
                return response()->json([
                    'message' => 'パスワードのリセットリンクの有効期限が切れています。',
                    'status' => 203
                ], 203);
            }

            $passwordPolicy = $this->getPolicy($user->mst_company_id);

            // check pass min length
            if (\strlen($params['password']) < $passwordPolicy->min_length) {
                return response()->json([
                    'message' => __('validation.min.string', ['attribute' => 'パスワード', 'min' => $passwordPolicy->min_length]),
                    'status' => 409
                ], 409);
            }

            // check password same last time    passwordPolicy.enable_password
            if ($passwordPolicy->enable_password == 0) {
                if (Hash::check($params['password'], $user->password)) {
                    return response()->json([
                        'message' => '前回と同じパスワードは使用できません。',
                        'status' => 409
                    ], 409);
                }
            }

            // check use email as password    passwordPolicy.set_mail_as_password
            if ($passwordPolicy->set_mail_as_password == 1) {
                $strTempUserName = explode('@',strtolower($email));
                $strTempPassword = strtolower($params['password']);
                if ($strTempPassword == strtolower($email) || $strTempUserName[0] == $strTempPassword) {
                    return response()->json([
                        'message' => 'ユーザＩＤと同一のパスワードを禁止する',
                        'status' => 409
                    ], 409);
                }
            }

            DB::beginTransaction();

            $update = [
                'password' => Hash::make($params['password']),
                'password_change_date' => Carbon::now()
            ];
            if ($user->state_flg == 0) {

                $user = DB::table($table)->join('mst_company', 'mst_company.id', '=', "$table.mst_company_id")->select(["$table.*", 'mst_company.company_name', 'mst_company.system_name'])->where('email', $email)->first();

                $apiUser = [
                    "user_email" => $email,
                    "email" => strtolower($email),
                    "contract_app" => config('app.edition_flg'),
                    "app_env" => config('app.server_env'),
                    "contract_server" => config('app.server_flg'),
                    "user_auth" => strpos($email,'@') === false ? AppUtils::ACCOUNT_TYPE_OPTION : AppUtils::AUTH_FLG_USER,
                    "user_first_name" => $user->given_name,
                    "user_last_name" => $user->family_name,
                    "company_name" => $user->company_name,
                    "company_id" => $user->mst_company_id,
                    "status" => 1,
                    "system_name" => $user->system_name,
                ];
                $client = IdAppApiUtils::getAuthorizeClient();
                if (!$client) {
                    Log::warning("Cannot connect to ID App");

                    return response()->json([
                        'message' => 'パスワードのリセットリンクが存在しません。',
                        'status' => 203
                    ], 203);
                }
                $apiUser['update_user_email'] = $user->email;
                $result = $client->put("users", [
                    RequestOptions::JSON => $apiUser
                ]);
                if ($result->getStatusCode() != StatusCodeUtils::HTTP_OK) {
                    Log::warning("Call ID App Api to update company user failed. Response Body " . $result->getBody());

                    return response()->json([
                        'message' => 'パスワードのリセットリンクが存在しません。',
                        'status' => 203
                    ], 203);
                }
            }

            DB::table($table)->where('email', $email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->update($update);
            DB::table($table_password_resets)->where('email', $email)->delete();

            DB::commit();

            return response()->json([
                'message' => '登録が完了しました。',
                'status' => StatusCodeUtils::HTTP_OK
            ], StatusCodeUtils::HTTP_OK);


        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            // print_r($ex->getMessage());exit;
            return response()->json([
                'message' => 'パスワードのリセットのエラーが発生しました。',
                'status' => 203
            ], 203);
        }
    }

    public function setPasswordForCode(Request $request)
    {
        try {
            $params = $request->all();

            $email = $params['email'];
            $hash = $params['token'];
            $account_type = isset($params['acctype']) ? $params['acctype'] : '';
            $table_password_resets = $this->getTablePasswordReset($account_type);

            $password_reset = DB::table($table_password_resets)->where('email', $email)->orderBy('created_at', 'desc')->first();

            if (!$password_reset->code) {
                return response()->json([
                    'message' => 'パスワードのリセットリンクが存在しません。',
                    'status' => 203
                ], 203);
            }

            $table = $this->getTableAccount($account_type);
            if (!$user = $this->checkTimeoutCode($password_reset, $table)) {
                return response()->json([
                    'message' => 'パスワードのリセットコードの有効期限が切れています。',
                    'status' => 203
                ], 203);
            }

            $passwordPolicy = $this->getPolicy($user->mst_company_id);

            // check pass min length
            if (\strlen($params['password']) < $passwordPolicy->min_length) {
                return response()->json([
                    'message' => __('validation.min.string', ['attribute' => 'パスワード', 'min' => $passwordPolicy->min_length]),
                    'status' => 409
                ], 409);
            }

            // check password same last time    passwordPolicy.enable_password
            if ($passwordPolicy->enable_password == 0) {
                if (Hash::check($params['password'], $user->password)) {
                    return response()->json([
                        'message' => '前回と同じパスワードは使用できません。',
                        'status' => 409
                    ], 409);
                }
            }

            // check use email as password    passwordPolicy.set_mail_as_password
            if ($passwordPolicy->set_mail_as_password == 1) {
                $strTempUserName = explode('@',strtolower($email));
                $strTempPassword = strtolower($params['password']);
                if ($strTempPassword == strtolower($email) || $strTempUserName[0] == $strTempPassword) {
                    return response()->json([
                        'message' => 'ユーザＩＤと同一のパスワードを禁止する',
                        'status' => 409
                    ], 409);
                }
            }

            DB::beginTransaction();

            $update = [
                'password' => Hash::make($params['password']),
                'password_change_date' => Carbon::now()
            ];
            if ($user->state_flg == 0) {
                $update['state_flg'] = AppUtils::STATE_VALID;

                $user = DB::table($table)->join('mst_company', 'mst_company.id', '=', "$table.mst_company_id")->select(["$table.*", 'mst_company.company_name', 'mst_company.system_name'])->where('email', $email)->first();

                $apiUser = [
                    "user_email" => $email,
                    "email" => strtolower($email),
                    "contract_app" => config('app.edition_flg'),
                    "app_env" => config('app.server_env'),
                    "contract_server" => config('app.server_flg'),
                    "user_auth" => AppUtils::AUTH_FLG_USER,
                    "user_first_name" => $user->given_name,
                    "user_last_name" => $user->family_name,
                    "company_name" => $user->company_name,
                    "company_id" => $user->mst_company_id,
                    "status" => 0,
                    "system_name" => $user->system_name,
                ];
                $client = IdAppApiUtils::getAuthorizeClient();
                if (!$client) {
                    Log::warning("Cannot connect to ID App");

                    return response()->json([
                        'message' => 'パスワードのリセットリンクが存在しません。',
                        'status' => 203
                    ], 203);
                }
                $apiUser['update_user_email'] = $user->email;
                $result = $client->put("users", [
                    RequestOptions::JSON => $apiUser
                ]);
                if ($result->getStatusCode() != StatusCodeUtils::HTTP_OK) {
                    Log::warning("Call ID App Api to update company user failed. Response Body " . $result->getBody());

                    return response()->json([
                        'message' => 'パスワードのリセットリンクが存在しません。',
                        'status' => 203
                    ], 203);
                }
            }

            DB::table($table)->where('email', $email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->update($update);
            DB::table($table_password_resets)->where('email', $email)->delete();

            DB::commit();

            return response()->json([
                'message' => '登録が完了しました。',
                'status' => StatusCodeUtils::HTTP_OK
            ], StatusCodeUtils::HTTP_OK);


        } catch (\Exception $ex) {
            DB::rollBack();
            // print_r($ex->getMessage());exit;
            $test = $ex->getMessage();
            return response()->json([
                'message' => $test,
                'status' => 203
            ], 203);
        }
    }

    /**
     * パスワードリセット
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        try {
            $params = $request->all();

            $email = $params['email']; //メールアドレス
            $user_auth = $params['user_auth']; //ユーザー権限 1:利用者　2:管理者

            if ($user_auth == 1) {
                $user = DB::table('mst_user')->where('email', $email)->where('state_flg', AppUtils::STATE_VALID)->first();
            } else if ($user_auth == 2) {
                $user = DB::table('mst_admin')->where('email', $email)->where('state_flg', AppUtils::STATE_VALID)->first();
            } else {
                return response()->json([
                    'message' => 'ユーザー権限設定不正。',
                    'status' => 409
                ], 409);
            }
            if (!$user) {
                return response()->json([
                    'message' => 'ユーザーが見つかりません。',
                    'status' => 409
                ], 409);
            }

            $update = [
                'password' => Hash::make($params['password']),
                'password_change_date' => \Date("Y-m-d")
            ];

            if ($user_auth == 1) {
                DB::table('mst_user')->where('email', $email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->update($update);
            } else {
                DB::table('mst_admin')->where('email', $email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->update($update);
            }

            return response()->json([
                'message' => 'パスワードのリセット完了しました。',
                'status' => StatusCodeUtils::HTTP_OK
            ], StatusCodeUtils::HTTP_OK);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json([
                'message' => 'パスワードのリセットのエラーが発生しました。',
                'status' => 203
            ], 203);
        }
    }

    /**
     * パスワード変更完了通知メールが送られてくる
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendFinishMail(Request $request)
    {
        try {
            $params = $request->all();

            $email = $params['email']; //メールアドレス
            // 利用者:パスワード設定完了通知

            // check if the company of email is using SAML Login
            $company = DB::table('mst_company')->join('mst_user', 'mst_user.mst_company_id', '=', 'mst_company.id')
                ->where('mst_user.email', $email)
                ->select('mst_company.login_type', 'mst_company.url_domain_id', 'mst_company.company_name',
                    'mst_user.notification_email')->first();

            $data = [
                'url_domain_id' => $company && ($company->login_type == AppUtils::LOGIN_TYPE_SSO) ? $company->url_domain_id : ''
            ];

            $data['user_id'] = strrpos($email, '.wf') == strlen($email) - 3 || strrpos($email, '.gw') == strlen($email) - 3 ? $email : '';
            $data['company_name'] = strrpos($email, '.wf') == strlen($email) - 3 || strrpos($email, '.gw') == strlen($email) - 3 && $company ? $company->company_name : '';

            MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                $email,
                // メールテンプレート
                MailUtils::MAIL_DICTIONARY['USER_PASSWORD_CHANGED_NOTIFY']['CODE'],
                // パラメータ
                json_encode($data,JSON_UNESCAPED_UNICODE),
                // タイプ
                AppUtils::MAIL_TYPE_USER,
                // 件名
                config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.SendFinishMail.subject'),
                // メールボディ
                trans('mail.SendFinishMail.body',$data), AppUtils::MAIL_STATE_WAIT, AppUtils::MAIL_SEND_DEFAULT_TIMES,
                // // オプション利用者の受信メールアドレス
                $company->notification_email
            );
            return response()->json([
                'message' => 'パスワード変更完了通知メールが送信完了しました。',
                'status' => StatusCodeUtils::HTTP_OK
            ], StatusCodeUtils::HTTP_OK);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json([
                'message' => 'パスワード変更完了通知メールが送信のエラーが発生しました。',
                'status' => 203
            ], 203);
        }
    }

    /**
     * パスワード変更メールが送られてくる(新エディションのログイン画面)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function sendReentryMail(Request $request)
    {
        try {
            DB::beginTransaction();
            $email = trim($request->get('email'));
            $user_auth = $request->get('user_auth');
            $url = $request->get('url');

            if (!$email) {
                return response()->json(['status' => false, 'message' => 'メールアドレス存在しません。']);
            }
            $token = Hash::make($email . time());
            $data = [];
            $notification_email = '';
            if ($user_auth == AppUtils::AUTH_FLG_ADMIN) {
                $user = DB::table('mst_admin')->where('email', $email)->where('state_flg', '<>', AppUtils::STATE_DELETE)->first();
                $table = 'admin_password_resets';
                $data['account_type'] = AppUtils::ACCOUNT_TYPE_ADMIN;
            } else if ($user_auth == AppUtils::AUTH_FLG_USER) {
                $user = DB::table('mst_user')->where('email', $email)->where('state_flg', '<>', AppUtils::STATE_DELETE)->first();
                if (!$user) {
                    $user_auth = AppUtils::AUTH_FLG_AUDIT;
                }
                $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();

                $table = 'user_password_resets';
                $data['account_type'] = $user->option_flg == AppUtils::USER_OPTION ? AppUtils::ACCOUNT_TYPE_OPTION : AppUtils::ACCOUNT_TYPE_USER;
                $data['user_id'] = $data['account_type'] == AppUtils::ACCOUNT_TYPE_OPTION ? $email : '';
                $data['company_name'] = $data['account_type'] == AppUtils::ACCOUNT_TYPE_OPTION && $company ? trim($company->company_name) : '';
                $notification_email = $data['account_type'] == AppUtils::ACCOUNT_TYPE_OPTION && $company ? $user->notification_email : '';
            } else {
                $user = null;
            }

            if ($user_auth == AppUtils::AUTH_FLG_AUDIT) {
                $user = DB::table('mst_audit')->where('email', $email)->where('state_flg', AppUtils::STATE_VALID)->first();
                $table = 'audit_password_resets';
                $data['account_type'] = AppUtils::ACCOUNT_TYPE_AUDIT;
            }

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'メールアドレス存在しません。']);
            }

            DB::table($table)->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => new \DateTime(),
            ]);

            $data['email'] = $email;
            $data['token'] = Hash::make($email . $token);
            $data['link_root'] = $url;

            $policy = PasswordPolicy::where('mst_company_id', $user->mst_company_id)->first();
            if ($policy && $policy->password_mail_validity_days === 0) {
                $data['time_out'] = '無期限';
            } else {
                // PAC_5-1970 パスワードメールの有効期限を変更する Start
                $data['time_out'] = date("Y/m/d H:i", time() + 86400 * ($policy->password_mail_validity_days ?? 7));
                // PAC_5-1970 End
            }

            $hash = AppUtils::encrypt($email . ',' . $data['account_type'] . ',' . $data['token'] . ',' . config('app.server_flg'), true);
            // トライアルユーザーに初期パスワード付与
            $pass = $this->getPassword();
            $data['password'] = $pass[0];

            $subject_app = trans('mail.prefix.user');
            if ($data['account_type'] == 'user' || $data['account_type'] == AppUtils::ACCOUNT_TYPE_OPTION) {
                $code = MailUtils::MAIL_DICTIONARY['USER_PASSWORD_SET_REQUEST']['CODE'];
                $mail_type = AppUtils::MAIL_TYPE_USER;
                $mst_table = 'mst_user';
            } else if ($data['account_type'] == 'audit') {
                $code = MailUtils::MAIL_DICTIONARY['AUDIT_PASSWORD_SET_REQUEST']['CODE'];
                $mail_type = AppUtils::MAIL_TYPE_AUDIT;
                $mst_table = 'mst_audit';
            } else {
                $subject_app = trans('mail.prefix.admin');
                $code = MailUtils::MAIL_DICTIONARY['ADMIN_PASSWORD_SET_REQUEST']['CODE'];
                $mail_type = AppUtils::MAIL_TYPE_ADMIN;
                $mst_table = 'mst_admin';
            }

            //初期パスワードの通知
            $resume_id = MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                $email,
                // メールテンプレート
                $code,
                // パラメータ
                json_encode($data,JSON_UNESCAPED_UNICODE),
                // タイプ
                $mail_type,
                // 件名
                config('app.mail_environment_prefix') . $subject_app . trans('mail.email_reset_link.subject'),
                // メールボディ
                trans('mail.email_reset_link.body', $data),AppUtils::MAIL_STATE_WAIT, AppUtils::MAIL_SEND_DEFAULT_TIMES,
                $notification_email
            );

            if(!$resume_id){
                DB::rollBack();
                Log::error('send reentry email failed:'.$email);
                return response()->json([
                    'message' => 'パスワード変更メールが送信のエラーが発生しました。',
                    'status' => false
                ], 500);
            }
            if(!empty($data['password']) && $pass[1]){
                DB::table($mst_table)
                    ->where('email',strtolower($email))
                    ->where('state_flg', '!=', AppUtils::STATE_DELETE)
                    ->update([
                        'password' => $pass[1],
                        'password_change_date' => Carbon::now(),
                    ]);
            }
            DB::commit();
            return response()->json(['status' => true,
                'message' => [__('message.success.reset_pass_was_send_mail')]
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json([
                'message' => 'パスワード変更メールが送信のエラーが発生しました。',
                'status' => false
            ], 500);
        }
    }

    public function chekSamlEnabledCompanies(Request $request)
    {
        try {
            $email = trim($request->get('email'));
            $user_audit = null;

            if (!$email) {
                return response()->json(['status' => false, 'message' => 'メールアドレス存在しません。']);
            }

            $user = DB::table('mst_user')
                        ->join('mst_company', 'mst_user.mst_company_id', '=', 'mst_company.id')
                        ->where('mst_user.email', $email)
                        ->select('mst_company.login_type')->first();

            if (!$user) {
                $user_audit = AppUtils::AUTH_FLG_AUDIT;
            }

            if ($user_audit) {
                $user = DB::table('mst_audit')
                            ->join('mst_company', 'mst_audit.mst_company_id', '=', 'mst_company.id')
                            ->where('mst_audit.email', $email)
                            ->select('mst_company.login_type')->first();
            }

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'メールアドレス存在しません。']);
            }

            return response()->json([
                'status' => true,
                'data' => $user
            ]);

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json([
                'message' => 'パスワード変更メールが送信のエラーが発生しました。',
                'status' => false
            ], 500);
        }
    }

    /**
     * パスワード変更のトークンチェック(新エディションのログイン画面)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkReentryHash(Request $request)
    {
        try {
            $email = $request->get('email');
            $hash = $request->get('token');
            $type = $request->get('type');
            $table = $type == AppUtils::AUTH_FLG_AUDIT ? 'audit_password_resets' : ($type == AppUtils::AUTH_FLG_USER || $type == AppUtils::AUTH_FLG_OPTION ? 'user_password_resets' : 'admin_password_resets');
            $password_reset = DB::table($table)->where('email', $email)->orderBy('created_at', 'desc')->first();

            if (!$password_reset || !Hash::check($email . $password_reset->token, $hash)) {
                return response()->json([
                    'message' => 'パスワードのリセットリンクが存在しません。',
                    'status' => false
                ], 203);
            }

            $tableAccount = $type == AppUtils::AUTH_FLG_AUDIT ? 'mst_audit' : ($type == AppUtils::AUTH_FLG_USER || $type == AppUtils::AUTH_FLG_OPTION ? 'mst_user' : 'mst_admin');
            if (!$user = $this->checkTimeoutReentryHash($password_reset, $tableAccount)) {
                return response()->json([
                    'message' => 'パスワードのリセットリンクの有効期限が切れています。',
                    'status' => false
                ], 406);
            }
            $passwordPolicy = $this->getPolicy($user->mst_company_id);
            return response()->json([
                'status' => true,
                'passwordPolicy' => $passwordPolicy
            ], StatusCodeUtils::HTTP_OK);

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json([
                'message' => 'パスワードのリセットリンクがチェックのエラーが発生しました。',
                'status' => false
            ], 500);
        }
    }

    /**
     * パスワード変更(新エディション ログイン画面用)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetLoginPassword(Request $request)
    {
        try {
            $params = $request->all();

            $email = $params['email'];
            $hash = $params['token'];
            $type = $params['type'];
            $table_password_resets = $type == AppUtils::AUTH_FLG_AUDIT ? 'audit_password_resets' : ($type == AppUtils::AUTH_FLG_USER || $type == AppUtils::AUTH_FLG_OPTION ? 'user_password_resets' : 'admin_password_resets');

            $password_reset = DB::table($table_password_resets)->where('email', $email)->orderBy('created_at', 'desc')->first();

            if (!$password_reset || !Hash::check($email . $password_reset->token, $hash)) {
                return response()->json([
                    'message' => 'パスワードのリセットリンクが存在しません。',
                    'status' => false
                ], 203);
            }


            $table = $type == AppUtils::AUTH_FLG_AUDIT ? 'mst_audit' : ($type == AppUtils::AUTH_FLG_USER || $type == AppUtils::AUTH_FLG_OPTION ? 'mst_user' : ($type == AppUtils::AUTH_FLG_ADMIN ? 'mst_admin' : 'mst_shachihata'));

            if (!$user = $this->checkTimeoutReentryHash($password_reset, $table)) {
                return response()->json([
                    'message' => 'パスワードのリセットリンクの有効期限が切れています。',
                    'status' => false
                ], 406);
            }

            $passwordPolicy = $this->getPolicy($user->mst_company_id);

            // check pass min length
            if (\strlen($params['password']) < $passwordPolicy->min_length) {
                return response()->json([
                    'message' => __('validation.min.string', ['attribute' => 'パスワード', 'min' => $passwordPolicy->min_length]),
                    'status' => false,
                    'passwordPolicy' => $passwordPolicy
                ], 409);
            }

            // check password same last time    passwordPolicy.enable_password
            if ($passwordPolicy->enable_password == 0) {
                if (Hash::check($params['password'], $user->password)) {
                    return response()->json([
                        'message' => '前回と同じパスワードは使用できません。',
                        'status' => false,
                        'passwordPolicy' => $passwordPolicy
                    ], 409);
                }
            }

            // check use email as password    passwordPolicy.set_mail_as_password
            if ($passwordPolicy->set_mail_as_password == 1) {
                $strTempUserName = explode('@',strtolower($email));
                $strTempPassword = strtolower($params['password']);
                if ($strTempPassword == strtolower($email) || $strTempUserName[0] == $strTempPassword) {
                    return response()->json([
                        'message' => 'ユーザＩＤと同一のパスワードを禁止する',
                        'status' => false,
                        'passwordPolicy' => $passwordPolicy
                    ], 409);
                }
            }

            DB::beginTransaction();

            $update = [
                'password' => Hash::make($params['password']),
                'password_change_date' => Carbon::now()
            ];

            if ($user->state_flg == 0) {
                $update['state_flg'] = 1;
            }

            DB::table($table)->where('email', $email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->update($update);
            DB::table($table_password_resets)->where('email', $email)->delete();

            DB::commit();

            $subject_app = trans('mail.prefix.user');
            $mailData = [];
            $notification_email = '';
            if ($type == AppUtils::AUTH_FLG_USER || $type == AppUtils::AUTH_FLG_AUDIT || $type == AppUtils::AUTH_FLG_OPTION ) {
                $code = MailUtils::MAIL_DICTIONARY['USER_PASSWORD_CHANGED_NOTIFY']['CODE'];

                $company = DB::table('mst_company')->join('mst_user', 'mst_user.mst_company_id', '=', 'mst_company.id')
                    ->where('mst_user.email', $email)
                    ->select('mst_company.login_type', 'mst_company.url_domain_id', 'mst_company.company_name', 'mst_user.notification_email')
                    ->first();

                $mailData = [
                    'url_domain_id' => $company&&($company->login_type==AppUtils::LOGIN_TYPE_SSO)?$company->url_domain_id:''
                ];

                $mail_type = $type == AppUtils::AUTH_FLG_USER ? AppUtils::MAIL_TYPE_USER : AppUtils::MAIL_TYPE_AUDIT;
                $mailData['user_id'] = strrpos($email, '.wf') == strlen($email) - 3 || strrpos($email, '.gw') == strlen($email) - 3 ? $email : '';
                $mailData['company_name'] =strrpos($email, '.wf') == strlen($email) - 3 || strrpos($email, '.gw') == strlen($email) - 3 && $company ? $company->company_name : '';
                $notification_email = strrpos($email, '.wf') == strlen($email) - 3 || strrpos($email, '.gw') == strlen($email) - 3 && $company ? $company->notification_email : '';

            } else {
                $subject_app = trans('mail.prefix.admin');
                $code = MailUtils::MAIL_DICTIONARY['ADMIN_PASSWORD_CHANGED_NOTIFY']['CODE'];
                $mail_type = AppUtils::MAIL_TYPE_ADMIN;
            }
            // パスワード設定完了通知
            MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                $email,
                // メールテンプレート
                $code,
                // パラメータ
                json_encode($mailData,JSON_UNESCAPED_UNICODE),
                // タイプ
                $mail_type,
                // 件名
                config('app.mail_environment_prefix') . $subject_app . trans('mail.SendFinishMail.subject'),
                // メールボディ
                trans('mail.SendFinishMail.body', $mailData),AppUtils::MAIL_STATE_WAIT,AppUtils::MAIL_SEND_DEFAULT_TIMES,
                $notification_email
            );

            return response()->json([
                'message' => '登録が完了しました。',
                'status' => true
            ], StatusCodeUtils::HTTP_OK);

        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            // print_r($ex->getMessage());exit;
            return response()->json([
                'message' => 'パスワードのリセットのエラーが発生しました。',
                'status' => false
            ], 500);
        }
    }

    public function before_login(Request $request)
    {
        // URLを受け取る。
        $url = $request->request->all();
        $url = $url[0];
        // URLから会社の「url_domain_id」を取得。
        $domain_id = strrchr($url, "/");
        $domain_id = substr($domain_id, 1);
        // 取得した「url_domain_id」を使いDBから情報を取得。
        $domain = DB::table('mst_company')->select('without_email_flg')->where('url_domain_id', $domain_id)->first();
        // 対象の「url_domain_id」が登録されているか判断。
        if(!empty($domain)){
            // ログインタイプが設定されているか判断。
            if($domain->without_email_flg == 1){
                return 1;
            }
            return 0;
        }else{
            return 0;
        }
        return 0;
    }

    public function codeCheckedUser(Request $request)
    {
        $code = $request->get('code');
        $email = $request->get('email');
        $user_auth = $request->get('user_auth');
        $url = $request->get('url');
        $table = 'mst_user';
        $time_now = Carbon::now();

        $code_change = DB::table('password_resets_policy')->where('email', $email)->first();
        // 入力情報の「code」を使いDBから対象の情報を取得。
        $resetdata = DB::table('user_password_resets')->where('email', $email)->where('code', $code)->first();

        if ($code_change && $code_change->password_change_flg >= 5 && Carbon::parse($code_change->last_update_at)->addMinutes(3) > $time_now){
            return response()->json(['status' => 408, 'message' => 'パスワード設定コード試行が規定回数を超えました。'], 408);
        }

        if(!$resetdata){
            $this->handleCheckCodeError($email);
            return response()->json([
                'message' => 'パスワードのリセットリンクが存在しません',
                'status' => 203
            ], 203);
        }
        if (!$user = $this->checkTimeoutCode($resetdata, $table)) {
            $this->handleCheckCodeError($email);
            return response()->json([
                'message' => 'パスワードのリセットコードの有効期限が切れています。',
                'status' => false
            ], 204);
        }
        $token = Hash::make($email . time());
        $data = [];

        if ($user_auth == AppUtils::AUTH_FLG_USER) {
            $user = DB::table('mst_user')->where('email', $email)->where('state_flg', '<>', AppUtils::STATE_DELETE)->first();
            if (!$user) {
                $user_auth = AppUtils::AUTH_FLG_AUDIT;
                $user = DB::table('mst_audit')->where('email', $email)->where('state_flg', AppUtils::STATE_VALID)->first();
                $data['account_type'] = AppUtils::ACCOUNT_TYPE_AUDIT;
            }
            $data['account_type'] = AppUtils::ACCOUNT_TYPE_USER;
        } else {
            $user = null;
        }
        $data['token'] = Hash::make($email . $token);

        $policy = PasswordPolicy::where('mst_company_id', $user->mst_company_id)->first();

        if ($policy && $policy->password_mail_validity_days === 0) {
            $data['time_out'] = '無期限';
        } else {
            // PAC_5-1970 パスワードメールの有効期限を変更する Start
            $data['time_out'] = date("Y/m/d H:i", time() + 86400 * ($policy->password_mail_validity_days ?? 7));
            // PAC_5-1970 End
        }
        if ($code_change) {
            DB::table('password_resets_policy')->where('email', $email)->delete();
        }

        $hash = AppUtils::encrypt($email . ',' . $data['account_type'] . ',' . $data['token'] . ',' . config('app.server_flg'), true);
        $link_reset = rtrim($url, '/') . '/app' . '/password/init/code/' . $hash;

        return response()->json([
            'link_reset' => $link_reset,
            'status' => true
        ], 200);
    }

    public function codeCheckedAdmin(Request $request)
    {
        $code = $request->get('code');
        $user_auth = $request->get('user_auth');
        $url = $request->get('url');
        $table = 'mst_admin';

        // 入力情報の「code」を使いDBから対象の情報を取得。
        $resetdata = DB::table('admin_password_resets')->where('code', $code)->first();
        $email = $resetdata->email;
        $codeInDB = $resetdata->code;

        if (!$codeInDB) {
            return response()->json([
                'message' => 'パスワード設定コードが存在しません。',
                'status' => false
            ], 203);
        }

        if (!$user = $this->checkTimeoutCode($resetdata, $table)) {
            return response()->json([
                'message' => 'パスワードのリセットコードの有効期限が切れています。',
                'status' => false
            ], 204);
        }

        // 最新設定方式はコードではなく
        $password_reset = DB::table('admin_password_resets')->where('email', $email)->orderBy('created_at', 'desc')->first();
        if (!$password_reset->code) {
            return response()->json([
                'message' => 'パスワードのリセットリンクが存在しません',
                'status' => 409
            ], 409);
        }

        $token = Hash::make($email . time());
        $data = [];

        if ($user_auth == AppUtils::AUTH_FLG_ADMIN) {
            $user = DB::table('mst_admin')->where('email', $email)->where('state_flg', '<>', AppUtils::STATE_DELETE)->first();
            $data['account_type'] = AppUtils::ACCOUNT_TYPE_ADMIN;
        } else if ($user_auth == AppUtils::AUTH_FLG_USER) {
            $user = DB::table('mst_user')->where('email', $email)->where('state_flg', '<>', AppUtils::STATE_DELETE)->first();
            if (!$user) {
                $user_auth = AppUtils::AUTH_FLG_AUDIT;
            }
            $data['account_type'] = AppUtils::ACCOUNT_TYPE_USER;
        } else {
            $user = null;
        }

        if ($user_auth == AppUtils::AUTH_FLG_AUDIT) {
            $user = DB::table('mst_audit')->where('email', $email)->where('state_flg', AppUtils::STATE_VALID)->first();
            $table = 'audit_password_resets';
            $data['account_type'] = AppUtils::ACCOUNT_TYPE_AUDIT;
        }

        $data['token'] = Hash::make($email . $token);

        $policy = PasswordPolicy::where('mst_company_id', $user->mst_company_id)->first();
        if ($policy && $policy->password_mail_validity_days === 0) {
            $data['time_out'] = '無期限';
        } else {
            // PAC_5-1970 パスワードメールの有効期限を変更する Start
            $data['time_out'] = date("Y/m/d H:i", time() + 86400 * ($policy->password_mail_validity_days ?? 7));
            // PAC_5-1970 End
        }

        $hash = AppUtils::encrypt($email . ',' . $data['account_type'] . ',' . $data['token'] . ',' . config('app.server_flg'), true);
        $link_reset = rtrim($url, '/') . (($user_auth == AppUtils::AUTH_FLG_USER || $user_auth == AppUtils::AUTH_FLG_AUDIT) ? '/app' : '/admin') . '/password/init/code/' . $hash;


        return response()->json([
            'link_reset' => $link_reset,
            'status' => true
        ], 200);
    }

    /**
     * コードチャックエラー
     * @param $email
     * @return void
     */
    private function handleCheckCodeError($email)
    {
        $code_change = DB::table('password_resets_policy')->where('email', $email)->first();
        DB::table('password_resets_policy')->where('email', $email)->updateOrInsert(
            [
                'email' => $email
            ], [
                'email' => $email,
                'type_flg' => AppUtils::AUTH_FLG_USER,
                'password_change_flg' => $code_change ? $code_change->password_change_flg + 1 : 1,
                'last_update_at' => Carbon::now()
            ]);
    }

    /**
     * パスワードのリセットリンクの有効期限チェック(新エディション ログイン画面用)
     * @param $password_reset
     * @param $table
     * @return bool
     */
    private function checkTimeoutReentryHash($password_reset, $table)
    {
        $user = DB::table($table)->where('email', $password_reset->email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->first();
        if (!$user) return false;
        $passwordPolicy = $this->getPolicy($user->mst_company_id);
        if (!$passwordPolicy || $passwordPolicy->password_mail_validity_days == 0) return $user;

        $time_out = \strtotime($password_reset->created_at) + $passwordPolicy->password_mail_validity_days * 86400;
        if (\time() > $time_out) return false;

        return $user;
    }

    private function checkTimeoutEmail($password_reset, $table)
    {
        $user = DB::table($table)->where('email', $password_reset->email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->first();
        if (!$user) return false;
        $passwordPolicy = $this->getPolicy($user->mst_company_id);
        if (!$passwordPolicy || $passwordPolicy->password_mail_validity_days == 0) return $user;

        $time_out = \strtotime($password_reset->created_at) + $passwordPolicy->password_mail_validity_days * 86400;
        if (\time() > $time_out) return false;

        return $user;
    }

    private function checkTimeoutCode($password_reset, $table)
    {
        $user = DB::table($table)->where('email', $password_reset->email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->first();
        if (!$user) return false;
        $passwordPolicy = $this->getPolicy($user->mst_company_id);
        if (!$passwordPolicy || $passwordPolicy->password_mail_validity_days == 0) return $user;

        $time_out = \strtotime($password_reset->created_at) + $passwordPolicy->password_mail_validity_days * 259200;
        if (\time() > $time_out) return false;

        return $user;
    }

    private function getPolicy($mst_company_id)
    {
        if ($this->passwordPolicy != null and $this->passwordPolicy->mst_company_id == $mst_company_id) return $this->passwordPolicy;

        $this->passwordPolicy = DB::table('password_policy')->where('mst_company_id', $mst_company_id)->first();
        if ($this->passwordPolicy == null) {
            $this->passwordPolicy = new \stdClass();
            $this->passwordPolicy->mst_company_id = $mst_company_id;
            $this->passwordPolicy->min_length = 4;
            // PAC_5-1970 パスワードメールの有効期限を変更する Start
            $this->passwordPolicy->password_mail_validity_days = 7;
            // PAC_5-1970 End
            $this->passwordPolicy->enable_password = 1;
            $this->passwordPolicy->validity_period = 0;
            $this->passwordPolicy->character_type_limit = 0;
            $this->passwordPolicy->set_mail_as_password = 0;
        }
        return $this->passwordPolicy;
    }
    /**
     * 初期パスワード取得
     * @return mixed
     */
    private function getPassword()
    {
        // トライアルユーザーに初期パスワード付与
        $randStr = str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnopqrstuvwxyz123456789');
        $pass[0] = substr($randStr, 0, 8);
        $pass[1] = Hash::make($pass[0]);
        return $pass;
    }
}
