<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Utils\UserApiUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Utils\OperationsHistoryUtils;
use App\Utils\AppUtils;

class MfaController extends Controller
{
    const MAX_LOGIN_ATTEMPT = 10;
    
    public function index()
    {
        $mfa = Session::get('mfa');
        if (empty($mfa->needsMfa)) {
            abort(403);
        }
        
        if ($mfa->type == 1) {
            if (!empty($mfa->resend)) {
                session()->flash('resend-message', 'メールを再送信しました。');
                unset($mfa->resend);
            } elseif (!empty($mfa->resendError)) {
                session()->flash('message', 'メールの再送信に失敗しました。時間をおいて再度お試しください。');
                unset($mfa->resendError);
            }

            if (!isset($mfa->failCount)) {
                $mfa->failCount = 0;
            }
            
            return view('auth.mfa.email', [
                'passwordRequired' => Session::get('viaRemember'),
                'terminate' => $mfa->failCount >= self::MAX_LOGIN_ATTEMPT,
            ]);
        } elseif ($mfa->type == 2) {
            return view('auth.mfa.qr-code', [
                'token' => $mfa->otp->otp
            ]);
        } else {
            abort(500);
        }
    }
    
    public function verify(Request $request)
    {
        $mfa = Session::get('mfa');
        if (empty($mfa->needsMfa) || (isset($mfa->failCount) && $mfa->failCount >= self::MAX_LOGIN_ATTEMPT)) {
            abort(403);
        }
        
        $passwordRequired = Session::get('viaRemember');
        $otp = $request->get('otp');
        $password = $request->get('password');
        $message = [];
        if (strlen($otp) == 0) {
            $message[] = '認証コードが入力されていません。';
        }
        if ($passwordRequired && strlen($password) == 0) {
            $message[] = 'パスワードが入力されていません。';
        }

        $client = UserApiUtils::getAuthorizedApiClient(Session::get('accessToken'));
        if (empty($message)) {
            $param = [
                'mfa' => $mfa,
                'otp' => $otp
            ];
            if ($passwordRequired) {
                $param['password'] = $password;
            }

            $result = $client->post("mfa/authByEmail", [
                RequestOptions::JSON => $param
            ]);
            if ($result->getStatusCode() != 200) {
                $data = json_decode((string)$result->getBody());
                $message[] = $data->message;
                $mfa->failCount++;
            }
        }

        // operation_history
        $logParam = [
            'auth_flg'=>AppUtils::AUTH_FLG_USER,
            'mst_display_id'=>OperationsHistoryUtils::LOG_INFO['Mfa']['verify'][0],
            'mst_operation_id'=>OperationsHistoryUtils::LOG_INFO['Mfa']['verify'][1],
        ];

        if (!empty($message)) {
            // Log
            $logParam['result'] = 1;
            $logParam['detail_info'] = OperationsHistoryUtils::LOG_INFO['Mfa']['verify'][3];

            $result = $client->post("store-log", [
                RequestOptions::JSON => $logParam
            ]);

            return redirect('extra-auth')
                ->withInput()
                ->with('message', join('<br/>', $message));
        }else{
            // Log
            $logParam['result'] = 0;
            $logParam['detail_info'] = OperationsHistoryUtils::LOG_INFO['Mfa']['verify'][2];

            $result = $client->post("store-log", [
                RequestOptions::JSON => $logParam
            ]);
        }
        
        Session::forget('mfa');
        return redirect(config("app.url"));
    }
    
    public function pollQrCodeAuthStatus(Request $request)
    {
        $mfa = Session::get('mfa');
        if (empty($mfa->needsMfa)) {
            abort(403);
        }
        
        $client = UserApiUtils::getAuthorizedApiClient(Session::get('accessToken'));
        $result = $client->post("mfa/checkQrCodeAuth", [
            RequestOptions::JSON => ['mfa' => $mfa]
        ]);
        if ($result->getStatusCode() == 200) {
            Session::forget('mfa');
        }
        return response()->json([
            'status' => $result->getStatusCode()
        ]);
    }

    public function resend()
    {
        $mfa = Session::get('mfa');
        if (empty($mfa->needsMfa)) {
            abort(403);
        }
        
        $client = UserApiUtils::getAuthorizedApiClient(Session::get('accessToken'));
        $result = $client->post("mfa/resendAuthMail", [
            RequestOptions::JSON => []
        ]);

        // operation_history
        $logParam = [
            'auth_flg'=>AppUtils::AUTH_FLG_USER,
            'mst_display_id'=>OperationsHistoryUtils::LOG_INFO['Mfa']['resend'][0],
            'mst_operation_id'=>OperationsHistoryUtils::LOG_INFO['Mfa']['resend'][1],
        ];
        
        if ($result->getStatusCode() == 200) {
            $mfa->resend = true;

            // Log
            $logParam['result'] = 0;
            $logParam['detail_info'] = OperationsHistoryUtils::LOG_INFO['Mfa']['resend'][2];
        } else {
            $mfa->resendError = true;

            // Log
            $logParam['result'] = 1;
            $logParam['detail_info'] = OperationsHistoryUtils::LOG_INFO['Mfa']['resend'][3];
        }

		// PAC_5-2095 LOGs
//        $result = $client->post("store-log", [
//            RequestOptions::JSON => $logParam
//        ]);

        return redirect('/');
    }
}
