<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Utils\ExtraAuthUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Models\UserLoginSituations;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MfaController extends Controller
{
    
    public function authByEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mfa' => 'required',
            'otp' => 'required',
            'password' => 'sometimes|required'
        ]);
        $otp = $request->get('otp');
        $password = $request->get('password');
        if ($validator->fails()) {
            if (strlen($password) == 0) {
                $message = '認証コードが間違っています。';
            } else {
                $message = '認証コードまたはパスワードが間違っています。';
            }
            return response()->json([
                'message' => $message,
                'status' => 401
            ], 401);
        }
        
        $user = $request->user();
        $authFailed = false;
        if (!Hash::check($otp, $user->one_time_password)) {
            $authFailed = true;
        } else {
            $now = new Carbon();
            $otpExpires = new Carbon($user->one_time_password_expires_at);
            if ($otpExpires->lt($now)) {
                $authFailed = true;
            }
        }
        if (!$authFailed && strlen($password) > 0) {
            if (!Hash::check($password, $user->password)) {
                $authFailed = true;
            }
        }
        
        if ($authFailed) {
            if (strlen($password) == 0) {
                $message = '認証コードが間違っています。';
            } else {
                $message = '認証コードまたはパスワードが間違っています。';
            }
            return response()->json([
                'message' => $message,
                'status' => 401
            ], 401);
        } else {
            $mfa = $request->get('mfa');
            try {
                $this->finalize($user, $mfa);
            } catch(\Exception $e) {
                Log::error($e->getMessage());
                return response()->json([
                    'message' => '内部エラーが発生しました。',
                    'status' => 500
                ], 500);
            }
            return response()->json([
                'message' => 'メール認証成功',
                'status' => StatusCodeUtils::HTTP_OK
            ], StatusCodeUtils::HTTP_OK);
        }
    }
    
    public function authByQrCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
        ]);
        $otp = $request->get('otp');
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->toArray(),
                'status' => 401
            ], 401);
        }
        
        try {
            DB::beginTransaction();
            
            $user = $request->user();
            $authFailed = false;
            if (!Hash::check($otp, $user->one_time_password)) {
                $authFailed = true;
            } else {
                $now = new Carbon();
                $otpExpires = new Carbon($user->one_time_password_expires_at);
                if ($otpExpires->lt($now)) {
                    $authFailed = true;
                }
            }
            
            if ($authFailed) {
                $user->one_time_password = null;
                $user->one_time_password_expires_at = null;
                $user->one_time_password_confirmed = null;
                $user->save();
                DB::commit();

                return response()->json([
                    'message' => 'QRコード認証失敗',
                    'status' => 401
                ], 401);
            } else {
                $user->one_time_password_confirmed = 1;
                $user->save();
                DB::commit();

                return response()->json([
                    'message' => 'QRコード認証成功',
                    'status' => StatusCodeUtils::HTTP_OK
                ], StatusCodeUtils::HTTP_OK);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json([
                'message' => '内部エラーが発生しました。',
                'status' => 500
            ], 500);
        }
    }

    public function checkQrCodeAuth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mfa' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->toArray(),
                'status' => 500
            ], 500);
        }
        
        $user = $request->user();
        if (is_null($user->one_time_password_confirmed)) {
            return response()->json([
                'message' => 'auth session not started.',
                'status' => 403
            ], 403);
        } elseif ($user->one_time_password_confirmed == 0) {
            $otpExpires = new Carbon($user->one_time_password_expires_at);
            if ($otpExpires->lt(new Carbon())) {
                try {
                    DB::beginTransaction();
                    $user->one_time_password = null;
                    $user->one_time_password_expires_at = null;
                    $user->one_time_password_confirmed = null;
                    $user->save();
                    DB::commit();
                    return response()->json([
                        'message' => 'auth timed out.',
                        'status' => 401
                    ], 401);
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                    DB::rollBack();
                    return response()->json([
                        'message' => 'internal error.',
                        'status' => 500
                    ], 500);
                }
            } else {
                return response()->json([
                    'message' => 'waiting confirmation.',
                    'status' => 203
                ], 203);
            }
        } elseif ($user->one_time_password_confirmed == 1) {
            try {
                $this->finalize($user, $request->get('mfa'));
                return response()->json([
                    'message' => 'auth succeeded.',
                    'status' => StatusCodeUtils::HTTP_OK
                ], StatusCodeUtils::HTTP_OK);
            } catch(\Exception $e) {
                Log::error($e->getMessage());
                return response()->json([
                    'message' => 'internal error.',
                    'status' => 500
                ], 500);
            }
        }
        
        return response()->json([
            'message' => 'invalid auth state.',
            'status' => 500
        ], 500);
    }
    
    private function finalize($user, $mfa)
    {
        Log::debug(print_r($mfa, true));
        
        $newLoginSituation = $mfa['newLoginSituation'] ?? null;
        $matchedLoginSituationId = $mfa['matchedLoginSituationId'] ?? null;
        $loginSituationIdToBeDeleted = $mfa['loginSituationIdToBeDeleted'] ?? null;
                
        try {
            DB::beginTransaction();
            
            $user->last_mfa_login_at = new Carbon();
            $user->update_user = $user->getFullName();
            $user->one_time_password = null;
            $user->one_time_password_expires_at = null;
            $user->one_time_password_confirmed = null;
            $user->save();
            
            if ($newLoginSituation) {
                if ($loginSituationIdToBeDeleted) {
                    DB::table('user_login_situations')
                        ->where('mst_user_id', $user->id)
                        ->whereRaw('update_at <= (select t.update_at from (select update_at from user_login_situations where id = ?) t)'
                            , [$loginSituationIdToBeDeleted])
                        ->delete();
                }
                
                $ls = new UserLoginSituations();
                $ls->mst_user_id = $user->id;
                $ls->ip_address = $mfa['clientIp'];
                $ls->user_agent = $mfa['userAgent'];
                $ls->create_user = $ls->update_user = $user->getFullName();
                $ls->save();
            } else {
                if ($matchedLoginSituationId) {
                    DB::table('user_login_situations')
                        ->where('id', $matchedLoginSituationId)
                        ->update([
                            'update_at' => new Carbon(),
                            'update_user' => $user->getFullName()
                        ]);
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function resendAuthMail(Request $request)
    {
        $user = $request->user();
        if ($user) {
            ExtraAuthUtils::resendAuthMail($user);
            return response()->json([
                'message' => '認証メールを再送信しました。',
                'status' => StatusCodeUtils::HTTP_OK,
            ]);
        }

        return response()->json([
            'message' => '認証メールの再送信に失敗しました。',
            'status' => 500,
        ], 500);
    }
}
