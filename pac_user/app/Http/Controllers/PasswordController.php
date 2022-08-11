<?php
/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/8/19
 * Time: 09:55
 */

namespace App\Http\Controllers;


use App\Utils\AppUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Utils\UserApiUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendChangePasswordMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use App\Utils\UserLoginUtils;


class PasswordController extends Controller
{
    /**
     * パスワード変更メール(単一システムログイン画面)
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sendReentryMail(Request $request)
    {
        try {
            $email = $request->get('email');

            $envClient = UserApiUtils::getApiClient();
            if ($envClient) {
                $chekSamlEnabled = $envClient->post("passwords/checkEmailSamlEnabledCompanies", [
                    RequestOptions::JSON => [
                        'email' => $email,
                    ],
                ]);

                if ($chekSamlEnabled->getStatusCode() == 200) {
                    $resData = json_decode((string)$chekSamlEnabled->getBody());
                    if($resData && isset($resData->data)){
                        if($resData->data->login_type != UserLoginUtils::LOGIN_TYPE_SSO){
                            $response = $envClient->post("passwords/sendReentryMail", [
                                RequestOptions::JSON => [
                                    'email' => $email,
                                    'user_auth' => 1,
                                    'url' => substr(config('app.url'), 0, strpos(config('app.url'), '/', 8) + 1)
                                ],
                            ]);
                            if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                                Log::error('Call sendReentryMail failed');
                                Log::error($response->getBody());
                            }
                        }else{
                            return $this->render('auth.passwords.reentrySaml');
                        }
                    }else{
                        Log::warning('Call checkEmailSamlEnabledCompanies get response false for email '.$email);
                        Log::warning($chekSamlEnabled->getBody());
                        return $this->render('auth.passwords.reentryOk');
                    }
                }else{
                    Log::error('Call checkEmailSamlEnabledCompanies failed');
                    Log::error($chekSamlEnabled->getBody());
                    return $this->render('auth.passwords.reentryError');
                }
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
        }
        return $this->render('auth.passwords.reentryOk');
    }

    /**
     * メールから「パスワードを設定」ボタンを押下
     * @param $token
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function passwordInit($token)
    {
        //パラメーター取得
        $params = explode(',', AppUtils::decrypt($token, true));
        $email = count($params) > 0 ? $params[0] : '';
        $type = count($params) > 1 ? $params[1] : '';
        $hash = count($params) > 2 ? $params[2] : '';
        $client = UserApiUtils::getApiClient();

        $result = $client->post("passwords/init/checkInit", [
            RequestOptions::JSON => [
                'token' => $hash,
                'email' => $email,
                'acctype' => $type
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
     * コード入力後からのパスワード再設定画面表示
     * @param $token
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function loadPasswordChangePage($token)
    {
        //パラメーター取得
        $params = explode(',', AppUtils::decrypt($token, true));
        $email = count($params) > 0 ? $params[0] : '';
        $type = count($params) > 1 ? $params[1] : '';
        $hash = count($params) > 2 ? $params[2] : '';
        $client = UserApiUtils::getApiClient();

        $result = $client->post("passwords/init/code/checkedCodeTime", [
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
     * コード入力画面から「送信」ボタンを押下
     * @param $token
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPasswordChangeUrl(Request $request)
    {
        $client = UserApiUtils::getApiClient();
        $code = $request->code;
        $email = $request->email;

        // 変数は$messageで帰ってこれる
        $result = $client->post("code/codeCheckedUser", [
            RequestOptions::JSON => [
                'code' => $code,
                'email' => $email,
                'user_auth' => 1,
                'url' => substr(config('app.url'), 0, strpos(config('app.url'), '/', 8) + 1)
            ]
        ]);
        $resultCheck = \json_decode((string)$result->getBody(NULL));

        // ここで帰ってきた値(ハッシュ)をURLの最後に乗せて移動させる
        if ($result->getStatusCode() == 200) {
            $this->assign('code', $code);
            $this->assign('email', $email);
            return Response::json(['link_reset' => $resultCheck->link_reset],200);
        } elseif($result->getStatusCode() == 204) {
            return Response::json(['message' => '時間切れ'],204);
        } elseif($result->getStatusCode() == 408) {
            return Response::json(['message' => $resultCheck->message],201);
        } else {
            return Response::json(['message' => $resultCheck->message],203);
        }
    }

    /**
     * ログインパスワードの変更画面「更新」ボタンを押下
     * @param $token
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function passwordPostInit($token, Request $request)
    {
        try {
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

            //パラメーター取得
            $user_params = explode(',', AppUtils::decrypt($token, true));
            $email = count($user_params) > 0 ? $user_params[0] : '';
            $type = count($user_params) > 1 ? $user_params[1] : '';
            $hash = count($user_params) > 2 ? $user_params[2] : '';

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
            } else {
                $pass_status = false;
            }
            if (!$pass_status) {
                $res = redirect()->back()->with(['message' => __('validation.regex', ['attribute' => 'password']), 'message_type' => 'danger'])->withInput();
                \Session::driver()->save();
                $res->send();
                exit;
            }

            $client = UserApiUtils::getApiClient();
            $result = $client->post("passwords/init/setPassword", [
                RequestOptions::JSON => [
                    'email' => $email,
                    'token' => $hash,
                    'password' => $request->get('password'),
                    'acctype' => $type,
                ]
            ]);

            if ($result->getStatusCode() == 200) {
                // パスワード変更完了通知メールが送られてくる
                $result_mail = $client->post("passwords/init/sendFinishMail", [
                    RequestOptions::JSON => [
                        'email' => $email,
                        'token' => $hash,
                    ]
                ]);
                if ($result_mail->getStatusCode() == 200) {
                    Session::flush();
                    $this->assign('message', '登録が完了しました。');
                } else {
                    $message = \json_decode((string)$result->getBody());
                    $this->assign('message', $message->message);
                }
                // ADD PAC_5-357 End
                return $this->render('auth.passwords.init_done');

            } else if ($result->getStatusCode() == 409) {
                $info = \json_decode((string)$result->getBody());
                $res = redirect()->back()->with(['message' => $info->message])->withInput();
                \Session::driver()->save();
                $res->send();
                exit;
            } else {
                $message = \json_decode((string)$result->getBody());
                $this->assign('message', $message->message);
                return $this->render('auth.passwords.init_done');
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            $this->assign('message', 'パスワードのリセットのエラーが発生しました。');
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
        try {
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

            //パラメーター取得
            $user_params = explode(',', AppUtils::decrypt($token, true));
            $email = count($user_params) > 0 ? $user_params[0] : '';
            $type = count($user_params) > 1 ? $user_params[1] : '';
            $hash = count($user_params) > 2 ? $user_params[2] : '';
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
            } else {
                $pass_status = false;
            }
            if (!$pass_status) {
                $res = redirect()->back()->with(['message' => __('validation.regex', ['attribute' => 'password']), 'message_type' => 'danger'])->withInput();
                \Session::driver()->save();
                $res->send();
                exit;
            }

            $client = UserApiUtils::getApiClient();
            $result = $client->post("passwords/init/code/setPassword", [
                RequestOptions::JSON => [
                    'email' => $email,
                    'token' => $hash,
                    'password' => $request->get('password'),
                    'acctype' => $type,
                ]
            ]);

            if ($result->getStatusCode() == 200) {
                Session::flush();
                $this->assign('message', '登録が完了しました。');
                return $this->render('auth.passwords.init_done');
            } else if ($result->getStatusCode() == 409) {
                $info = \json_decode((string)$result->getBody());
                $res = redirect()->back()->with(['message' => $info->message])->withInput();
                \Session::driver()->save();
                $res->send();
                exit;
            } else {
                $message = \json_decode((string)$result->getBody());
                $this->assign('message', $message->message);
                return $this->render('auth.passwords.init_done');
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            $this->assign('message', 'パスワードのリセットのエラーが発生しました。');
            return $this->render('auth.passwords.init_done');
        }
    }
}
