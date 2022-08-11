<?php
/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/8/19
 * Time: 09:55
 */

namespace App\Http\Controllers;


use App\Http\Utils\AppUtils;
use App\Http\Utils\IdAppApiUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\MailUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\PasswordUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Response;

class PasswordController extends AdminController
{
    var $passwordPolicy = null;

    protected $password_utils;

    public function __construct(PasswordUtils $password_utils)
    {
        parent::__construct();

        $this->password_utils = $password_utils;
    }

    public function init($email, Request $request)
    {
        // check if there is logged user, then force logout
        if (Auth::check()) {
            // The user is logged in...
            Auth::logout();
        }
        $this->hideSideBar();

        $this->setMetaTitle("ログインパスワードの変更");

        $hash = $request->get('token');
        $password_reset = DB::table('admin_password_resets')
            ->where('email', $email)->orderBy('created_at', 'desc');

        if ($password_reset->count() <= 0) {
            $message = 'パスワードのリセットリンクが存在しません。';
            $this->assign('message', $message);
            return $this->render('auth.passwords.init_done');
        }

        $password_reset = $password_reset->first();

        if (Hash::check($email . $password_reset->token, $hash)) {
            if (!$admin = $this->checkTimeoutEmail($password_reset)) {
                $this->assign('message', 'パスワードのリセットリンクの有効期限が切れています。');
                return $this->render('auth.passwords.init_done');
            }
            $passwordPolicy = $this->getPolicy($admin->mst_company_id);
            $this->assign('email', $email);
            $this->assign('passwordPolicy', $passwordPolicy);
            return $this->render('auth.passwords.init');
        } else {
            $this->assign('message', 'パスワードのリセットリンクが存在しません。');
            return $this->render('auth.passwords.init_done');
        }
    }

    public function postInit($email, Request $request)
    {
        $this->hideSideBar();
        $this->setMetaTitle("ログインパスワードの変更");
        try {
            $params = $request->all();
            $validator = Validator::make($params, [
                'email' => 'required',
                'password' => 'required|max:32|confirmed',
                'password_confirmation' => 'required|max:32',
                'token' => 'required',
            ]);
            if ($validator->fails()) {
                $message = $validator->messages();
                $message_all = $message->all();

                $res = redirect()->back()->with("errors", $message)->with(['message' => implode('<br/>', $message_all), 'message_type' => 'danger'])->withInput();
                \Session::driver()->save();
                $res->send();
                exit;
            }

            // validation.regex
            $pass_status = true;
            $regex = '/^(?=.*[0-9])(?=.*[a-zA-Z])/';
            if (preg_match($regex, $params['password'])) {
                for ($i = 0; $i < strlen($params['password']); $i++) {
                    if (ord($params['password'][$i]) > 126) {
                        $pass_status = false;
                        break;
                    }
                }
            } else $pass_status = false;
            if (!$pass_status) {
                $res = redirect()->back()->with(['message' => __('validation.regex', ['attribute' => 'password']), 'message_type' => 'danger'])->withInput();
                \Session::driver()->save();
                $res->send();
                exit;
            }

            $hash = $params['token'];
            $password_reset = DB::table('admin_password_resets')
                ->where('email', $email)->orderBy('created_at', 'desc');

            if ($password_reset->count() <= 0) {
                $this->assign('message', 'パスワードのリセットリンクが存在しません。');
                return $this->render('auth.passwords.init_done');
            }

            $password_reset = $password_reset->first();

            if (Hash::check($email . $password_reset->token, $hash)) {
                if (!$admin = $this->checkTimeoutEmail($password_reset)) {
                    $this->assign('message', 'パスワードのリセットリンクの有効期限が切れています。');
                    return $this->render('auth.passwords.init_done');
                }

                $passwordPolicy = $this->getPolicy($admin->mst_company_id);

                // check pass min length
                if (\strlen($params['password']) < $passwordPolicy->min_length) {
                    $res = redirect()->back()->with(['message' => __('message.false.password.password_min', ['attribute' => $passwordPolicy->min_length])
                        , 'message_type' => 'danger'])->withInput();
                    \Session::driver()->save();
                    $res->send();
                    exit;
                }

                // check password same last time    passwordPolicy.enable_password
                if ($passwordPolicy->enable_password == 0) {
                    if (Hash::check($params['password'], $admin->password)) {
                        $res = redirect()->back()->with(['message' => '前回と同じパスワードは使用できません。'
                            , 'message_type' => 'danger'])->withInput();
                        \Session::driver()->save();
                        $res->send();
                        exit;
                    }
                }

                // check use email as password    passwordPolicy.set_mail_as_password
                if ($passwordPolicy->set_mail_as_password == 1) {
                    $strTempUserName = explode('@',strtolower($email));
                    $strTempPassword = strtolower($params['password']);
                    if ($strTempPassword == strtolower($email) || $strTempUserName[0] == $strTempPassword) {
                        $res = redirect()->back()->with(['message' => 'ユーザＩＤと同一のパスワードを禁止する'
                            , 'message_type' => 'danger'])->withInput();
                        \Session::driver()->save();
                        $res->send();
                        exit;
                    }
                }

                DB::beginTransaction();
                DB::table('admin_password_resets')
                    ->where('email', $email)
                    ->delete();
                $update = [
                    'password' => Hash::make($params['password']),
                    'password_change_date' => Carbon::now()
                ];
                if ($admin->state_flg == 0) {
                    $update['state_flg'] = AppUtils::STATE_VALID;

                    $admin = DB::table('mst_admin')->join('mst_company', 'mst_company.id', '=', 'mst_admin.mst_company_id')->select(['mst_admin.*', 'mst_company.company_name', 'mst_company.system_name'])->where('email', $email)->first();
                    $apiUser = [
                        "email" => $email,
                        "contract_app" => config('app.pac_contract_app'),
                        "app_env" => config('app.pac_app_env'),
                        "contract_server" => config('app.pac_contract_server'),
                        "user_auth" => AppUtils::AUTH_FLG_ADMIN,
                        "user_first_name" => $admin->given_name,
                        "user_last_name" => $admin->family_name,
                        "company_name" => $admin->company_name,
                        "company_id" => $admin->mst_company_id,
                        "status" => AppUtils::convertState(AppUtils::STATE_VALID),
                        "user_email" => $email,
                        "update_user_email" => $email,
                        "system_name" => $admin->system_name
                    ];
                    $client = IdAppApiUtils::getAuthorizeClient();
                    if (!$client) {
                        DB::rollBack();
                        Log::warning("Cannot connect to ID App");
                        $this->assign('message', 'パスワードのリセットのエラーが発生しました。');
                        return $this->render('auth.passwords.init_done');
                    }

                    $result = $client->put("users", [
                        RequestOptions::JSON => $apiUser
                    ]);
                    if ($result->getStatusCode() != 200) {
                        DB::rollBack();
                        Log::warning("Call ID App Api to update company admin failed. Response Body " . $result->getBody());

                        $this->assign('message', 'パスワードのリセットのエラーが発生しました。');
                        return $this->render('auth.passwords.init_done');
                    }
                }
                DB::table('mst_admin')->where('email', $email)->update($update);
                DB::commit();

                Session::flush();
                // check if there is logged user, then force logout
                if (Auth::check()) {
                    // The user is logged in...
                    Auth::logout();
                }

                $this->assign('message', '登録が完了しました。');

                // パスワード変更完了通知メールが送られてくる
                MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                    $email,
                    // メールテンプレート
                     MailUtils::MAIL_DICTIONARY['ADMIN_PASSWORD_CHANGED_NOTIFY']['CODE'],
                    // パラメータ
                    '',
                    // タイプ
                    AppUtils::MAIL_TYPE_ADMIN,
                    // 件名
                    config('app.mail_environment_prefix') . config('mail.subject_shachihata_cloud_admin') . ' パスワードの設定が完了しました',
                    // メールボディ
                    trans('mail.SendChangePasswordMail.body')
                );

                return $this->render('auth.passwords.init_done');

            } else {
                $this->assign('message', 'パスワードのリセットリンクが存在しません。');
                return $this->render('auth.passwords.init_done');
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            $this->assign('message', 'パスワードのリセットのエラーが発生しました。');
            return $this->render('auth.passwords.init_done');
        }
    }

    /**
     * メールから「パスワードを設定」ボタンを押下
     * @param $token
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function passwordInit($token)
    {
        if (Auth::check()) {
            Auth::logout();
        }
        $this->hideSideBar();
        $this->setMetaTitle("ログインパスワードの変更");

        //パラメーター取得
        $params = explode(',', AppUtils::decrypt($token, true));
        $email = count($params) > 0 ? $params[0] : '';
        $hash = count($params) > 2 ? $params[2] : '';

        //リセットリンクが存在チェック
        $password_reset = DB::table('admin_password_resets')->where('email', $email)->orderBy('created_at', 'desc')->first();
        if (!$password_reset || !Hash::check($email . $password_reset->token, $hash)) {
            $this->assign('message', 'パスワードのリセットリンクが存在しません。');
            return $this->render('auth.passwords.init_done');
        }

        // 有効期限チェック
        if (!$admin = $this->checkTimeoutEmail($password_reset)) {
            $this->assign('message', 'パスワードのリセットリンクの有効期限が切れています。');
            return $this->render('auth.passwords.init_done');
        }

        // 企業パスワード設定制限取得
        $passwordPolicy = $this->getPolicy($admin->mst_company_id);
        $this->assign('email', $email);
        $this->assign('passwordPolicy', $passwordPolicy);
        return $this->render('auth.passwords.init');
    }

    /**
     * ログインパスワードの変更画面「更新」ボタンを押下
     * @param $token
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function passwordPostInit($token, Request $request)
    {
        $this->hideSideBar();
        $this->setMetaTitle("ログインパスワードの変更");
        try {
            // パラメーターチェック
            $params = $request->all();
            $validator = Validator::make($params, [
                'email' => 'required',
                'password' => 'required|max:32|confirmed',
                'password_confirmation' => 'required|max:32',
            ]);

            if ($validator->fails()) {
                $message = $validator->messages();
                $message_all = $message->all();
                $res = redirect()->back()->with("errors", $message)->with(['message' => implode('<br/>', $message_all), 'message_type' => 'danger'])->withInput();
                \Session::driver()->save();
                $res->send();
                exit;
            }

            // パラメーター解析
            $token_params = explode(',', AppUtils::decrypt($token, true));
            $email = count($token_params) > 0 ? $token_params[0] : '';
            $hash = count($token_params) > 2 ? $token_params[2] : '';

            //パスワード強度チェック
            $pass_status = true;
            $regex = '/^(?=.*[0-9])(?=.*[a-zA-Z])/';
            if (preg_match($regex, $params['password'])) {
                for ($i = 0; $i < strlen($params['password']); $i++) {
                    if (ord($params['password'][$i]) > 126) {
                        $pass_status = false;
                        break;
                    }
                }
            } else {
                $pass_status = false;
            }
            if (!$pass_status) {
                $res = redirect()->back()->with(['message' => __('validation.regex', ['attribute' => 'password']), 'message_type' => 'danger'])->withInput();
                \Session::driver()->save();
                $res->send();
                exit;
            }

            $password_reset = DB::table('admin_password_resets')->where('email', $email)->orderBy('created_at', 'desc')->first();

            if (!$password_reset || !Hash::check($email . $password_reset->token, $hash)) {
                $this->assign('message', 'パスワードのリセットリンクが存在しません。');
                return $this->render('auth.passwords.init_done');
            }

            if (!$admin = $this->checkTimeoutEmail($password_reset)) {
                $this->assign('message', 'パスワードのリセットリンクの有効期限が切れています。');
                return $this->render('auth.passwords.init_done');
            }

            $passwordPolicy = $this->getPolicy($admin->mst_company_id);

            // check pass min length
            if (\strlen($params['password']) < $passwordPolicy->min_length) {
                $res = redirect()->back()->with(['message' => __('message.false.password.password_min', ['attribute' => $passwordPolicy->min_length])
                    , 'message_type' => 'danger'])->withInput();
                \Session::driver()->save();
                $res->send();
                exit;
            }

            // check password same last time    passwordPolicy.enable_password
            if ($passwordPolicy->enable_password == 0) {
                if (Hash::check($params['password'], $admin->password)) {
                    $res = redirect()->back()->with(['message' => '前回と同じパスワードは使用できません。'
                        , 'message_type' => 'danger'])->withInput();
                    \Session::driver()->save();
                    $res->send();
                    exit;
                }
            }

            // check use email as password    passwordPolicy.set_mail_as_password
            if ($passwordPolicy->set_mail_as_password == 1) {
                $strTempUserName = explode('@',strtolower($email));
                $strTempPassword = strtolower($params['password']);
                if ($strTempPassword == strtolower($email) || $strTempUserName[0] == $strTempPassword) {
                    $res = redirect()->back()->with(['message' => 'ユーザＩＤと同一のパスワードを禁止する'
                        , 'message_type' => 'danger'])->withInput();
                    \Session::driver()->save();
                    $res->send();
                    exit;
                }
            }

            DB::beginTransaction();
            DB::table('admin_password_resets')->where('email', $email)->delete();
            $update = [
                'password' => Hash::make($params['password']),
                'password_change_date' => Carbon::now()
            ];
            if ($admin->state_flg == 0) {
                $update['state_flg'] = AppUtils::STATE_VALID;

                $admin = DB::table('mst_admin')->join('mst_company', 'mst_company.id', '=', 'mst_admin.mst_company_id')->select(['mst_admin.*', 'mst_company.company_name', 'mst_company.system_name'])->where('email', $email)->first();
                $apiUser = [
                    "email" => $email,
                    "contract_app" => config('app.pac_contract_app'),
                    "app_env" => config('app.pac_app_env'),
                    "contract_server" => config('app.pac_contract_server'),
                    "user_auth" => AppUtils::AUTH_FLG_ADMIN,
                    "user_first_name" => $admin->given_name,
                    "user_last_name" => $admin->family_name,
                    "company_name" => $admin->company_name,
                    "company_id" => $admin->mst_company_id,
                    "status" => AppUtils::convertState(AppUtils::STATE_VALID),
                    "user_email" => $email,
                    "update_user_email" => $email,
                    "system_name" => $admin->system_name
                ];
                $client = IdAppApiUtils::getAuthorizeClient();
                if (!$client) {
                    DB::rollBack();
                    Log::warning("Cannot connect to ID App");
                    $this->assign('message', 'パスワードのリセットのエラーが発生しました。');
                    return $this->render('auth.passwords.init_done');
                }

                $result = $client->put("users", [
                    RequestOptions::JSON => $apiUser
                ]);
                if ($result->getStatusCode() != 200) {
                    DB::rollBack();
                    Log::warning("Call ID App Api to update company admin failed. Response Body " . $result->getBody());

                    $this->assign('message', 'パスワードのリセットのエラーが発生しました。');
                    return $this->render('auth.passwords.init_done');
                }
            }
            DB::table('mst_admin')->where('email', $email)->update($update);

            // 管理者:パスワード設定完了通知
            MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                $email,
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
            DB::commit();

            Session::flush();
            if (Auth::check()) {
                Auth::logout();
            }

            $this->assign('message', '登録が完了しました。');
            return $this->render('auth.passwords.init_done');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            $this->assign('message', 'パスワードのリセットのエラーが発生しました。');
            return $this->render('auth.passwords.init_done');
        }
    }

    public function getPasswordCodeUser(Request $request)
    {
        $code = $this->password_utils->createUserPasswordSettingCode($request->code_user_id);
        // コードを画面に返す。
        return response()->json($code);
    }

    public function getPasswordCodeAdmin(Request $request)
    {
        $code = $this->password_utils->createAdminPasswordSettingCode($request->code_admin_id);
        // コードを画面に返す。
        return response()->json($code);
    }

    public function getPasswordChangeUrl(Request $request)
    {
        $code = $request->code;
        $app_env = config('app.pac_app_env');
        $app_server = config('app.pac_contract_server');
        $envClient = EnvApiUtils::getAuthorizeClient($app_env,$app_server);

        // 変数は$messageで帰ってこれる
        $result = $envClient->post("code/codeCheckedAdmin", [
            RequestOptions::JSON => [
                'code' => $code,
                'user_auth' => 2,
                'url' => substr(config('app.url'),0,strpos(config('app.url'),'/',8)+1)
            ]
        ]);
        $resultCheck = \json_decode((string)$result->getBody(NULL));

        // URLをjsonで返す
        if ($result->getStatusCode() == 200) {
            $this->assign('code', $code);
            return Response::json(['link_reset' => $resultCheck->link_reset],200);
        } elseif($result->getStatusCode() == 204) {
            return Response::json(['message' => '時間切れ'],204);
        } elseif($result->getStatusCode() == 409) {
            return Response::json(['message' => $resultCheck->message],202);
        } else {
            return Response::json(['message' => $resultCheck->message],203);
        }
    }

    public function loadPasswordChangePage($token)
    {
        //パラメーター取得
        $params = explode(',', AppUtils::decrypt($token, true));
        $email = count($params) > 0 ? $params[0] : '';
        $type = count($params) > 1 ? $params[1] : '';
        $hash = count($params) > 2 ? $params[2] : '';
        $app_env = config('app.pac_app_env');
        $app_server = config('app.pac_contract_server');
        $envClient = EnvApiUtils::getAuthorizeClient($app_env,$app_server);

        $result = $envClient->post("passwords/init/code/checkedCodeTime", [
            RequestOptions::JSON => [
                'email' => $email,
                'acctype' => $type,
            ]
        ]);

        $resultCheck = \json_decode((string)$result->getBody());

        if ($result->getStatusCode() == 200) {
            $this->assign('passwordPolicy', $resultCheck->passwordPolicy);
            $this->assign('email', $email);
            return $this->render('auth.passwords.init');
        } else {
            $this->assign('message', $resultCheck);
            return $this->render('auth.passwords.init_done');
        }
    }

    /**
     * コード用URLからログインパスワードの変更画面「更新」ボタンを押下
     * @param $token
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function updatePassword($token, Request $request)
    {
        $this->hideSideBar();
        $this->setMetaTitle("ログインパスワードの変更");

        try {
            // パラメーターチェック
            $params = $request->all();
            $validator = Validator::make($params, [
                'email' => 'required',
                'password' => 'required|max:32|confirmed',
                'password_confirmation' => 'required|max:32',
            ]);

            if ($validator->fails()) {
                $message = $validator->messages();
                $message_all = $message->all();
                $res = redirect()->back()->with("errors", $message)->with(['message' => implode('<br/>', $message_all), 'message_type' => 'danger'])->withInput();
                \Session::driver()->save();
                $res->send();
                exit;
            }

            // パラメーター解析
            $token_params = explode(',', AppUtils::decrypt($token, true));
            $email = count($token_params) > 0 ? $token_params[0] : '';
            $hash = count($token_params) > 2 ? $token_params[2] : '';

            //パスワード強度チェック
            $pass_status = true;
            $regex = '/^(?=.*[0-9])(?=.*[a-zA-Z])/';
            if (preg_match($regex, $params['password'])) {
                for ($i = 0; $i < strlen($params['password']); $i++) {
                    if (ord($params['password'][$i]) > 126) {
                        $pass_status = false;
                        break;
                    }
                }
            } else {
                $pass_status = false;
            }
            if (!$pass_status) {
                $res = redirect()->back()->with(['message' => __('validation.regex', ['attribute' => 'password']), 'message_type' => 'danger'])->withInput();
                \Session::driver()->save();
                $res->send();
                exit;
            }

            $password_reset = DB::table('admin_password_resets')->where('email', $email)->orderBy('created_at', 'desc')->first();

            if (!$password_reset->code) {
                $this->assign('message', 'パスワードのリセットリンクが存在しません');
                return $this->render('auth.passwords.init_done');
            }

            if (!$admin = $this->checkTimeoutCode($password_reset)) {
                $this->assign('message', 'パスワードのリセットコードの有効期限が切れています。');
                return $this->render('auth.passwords.init_done');
            }

            $passwordPolicy = $this->getPolicy($admin->mst_company_id);

            // check pass min length
            if (\strlen($params['password']) < $passwordPolicy->min_length) {
                $res = redirect()->back()->with(['message' => __('message.false.password.password_min', ['attribute' => $passwordPolicy->min_length])
                    , 'message_type' => 'danger'])->withInput();
                \Session::driver()->save();
                $res->send();
                exit;
            }

            // check password same last time    passwordPolicy.enable_password
            if ($passwordPolicy->enable_password == 0) {
                if (Hash::check($params['password'], $admin->password)) {
                    $res = redirect()->back()->with(['message' => '前回と同じパスワードは使用できません。'
                        , 'message_type' => 'danger'])->withInput();
                    \Session::driver()->save();
                    $res->send();
                    exit;
                }
            }

            // check use email as password    passwordPolicy.set_mail_as_password
            if ($passwordPolicy->set_mail_as_password == 1) {
                $strTempUserName = explode('@',strtolower($email));
                $strTempPassword = strtolower($params['password']);
                if ($strTempPassword == strtolower($email) || $strTempUserName[0] == $strTempPassword) {
                    $res = redirect()->back()->with(['message' => 'ユーザＩＤと同一のパスワードを禁止する'
                        , 'message_type' => 'danger'])->withInput();
                    \Session::driver()->save();
                    $res->send();
                    exit;
                }
            }

            DB::beginTransaction();
            DB::table('admin_password_resets')->where('email', $email)->delete();
            $update = [
                'password' => Hash::make($params['password']),
                'password_change_date' => Carbon::now()
            ];
            if ($admin->state_flg == 0) {
                $update['state_flg'] = AppUtils::STATE_VALID;

                $admin = DB::table('mst_admin')->join('mst_company', 'mst_company.id', '=', 'mst_admin.mst_company_id')->select(['mst_admin.*', 'mst_company.company_name', 'mst_company.system_name'])->where('email', $email)->first();
                $apiUser = [
                    "email" => $email,
                    "contract_app" => config('app.pac_contract_app'),
                    "app_env" => config('app.pac_app_env'),
                    "contract_server" => config('app.pac_contract_server'),
                    "user_auth" => AppUtils::AUTH_FLG_ADMIN,
                    "user_first_name" => $admin->given_name,
                    "user_last_name" => $admin->family_name,
                    "company_name" => $admin->company_name,
                    "company_id" => $admin->mst_company_id,
                    "status" => AppUtils::convertState(AppUtils::STATE_VALID),
                    "user_email" => $email,
                    "update_user_email" => $email,
                    "system_name" => $admin->system_name
                ];
                $client = IdAppApiUtils::getAuthorizeClient();
                if (!$client) {
                    DB::rollBack();
                    Log::warning("Cannot connect to ID App");
                    $this->assign('message', 'パスワードのリセットのエラーが発生しました。');
                    return $this->render('auth.passwords.init_done');
                }

                $result = $client->put("users", [
                    RequestOptions::JSON => $apiUser
                ]);
                if ($result->getStatusCode() != 200) {
                    DB::rollBack();
                    Log::warning("Call ID App Api to update company admin failed. Response Body " . $result->getBody());

                    $this->assign('message', 'パスワードのリセットのエラーが発生しました。');
                    return $this->render('auth.passwords.init_done');
                }
            }
            DB::table('mst_admin')->where('email', $email)->update($update);
            DB::commit();

            Session::flush();
            if (Auth::check()) {
                Auth::logout();
            }

            $this->assign('message', '登録が完了しました。');
            return $this->render('auth.passwords.init_done');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            $this->assign('message', 'パスワードのリセットのエラーが発生しました。');
            return $this->render('auth.passwords.init_done');
        }
    }
    /**
     * パスワード変更メール(単一システムログイン画面)
     */
    public function sendReentryMail(Request $request)
    {
        $this->hideSideBar();
        try {
            $email = $request->get('email');
            $user = DB::table('mst_admin')->where('email', $email)->where('state_flg', AppUtils::STATE_VALID)->first();
            if (!$user) {
                $user = DB::table('mst_shachihata')->where('email', $email)->first();
                if (!$user) {
                    return $this->render('auth.passwords.reentryOk');
                }
            }
            if ($user) {
                $app_env = config('app.pac_app_env');
                $app_server = config('app.pac_contract_server');
                $envClient = EnvApiUtils::getAuthorizeClient($app_env, $app_server);
                if ($envClient) {
                    $response = $envClient->post("passwords/sendReentryMail", [
                        RequestOptions::JSON => [
                            'email' => $email,
                            'user_auth' => 2,
                            'url' => substr(config('app.url'), 0, strpos(config('app.url'), '/', 8) + 1)
                        ],
                    ]);
                    if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                        Log::error('Call sendReentryMail failed');
                        Log::error($response->getBody());
                    }
                }
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
        }
        return $this->render('auth.passwords.reentryOk');
    }

    private function checkTimeoutEmail($password_reset)
    {
        $admin = DB::table('mst_admin')->where('email', $password_reset->email)->first();
        if (!$admin) return false;
        $passwordPolicy = $this->getPolicy($admin->mst_company_id);
        if (!$passwordPolicy || $passwordPolicy->password_mail_validity_days == 0) return $admin;

        $time_out = \strtotime($password_reset->created_at) + $passwordPolicy->password_mail_validity_days * 86400;
        if (\time() > $time_out) return false;

        return $admin;
    }

    private function checkTimeoutCode($password_reset)
    {
        $admin = DB::table('mst_admin')->where('email', $password_reset->email)->first();
        if (!$admin) return false;
        $passwordPolicy = $this->getPolicy($admin->mst_company_id);
        if (!$passwordPolicy || $passwordPolicy->password_mail_validity_days == 0) return $admin;

        $time_out = \strtotime($password_reset->created_at) + $passwordPolicy->password_mail_validity_days * 259200;
        if (\time() > $time_out) return false;

        return $admin;
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
        }
        $this->assign('passwordPolicy', $this->passwordPolicy);

        return $this->passwordPolicy;
    }
}