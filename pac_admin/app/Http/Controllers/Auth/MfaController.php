<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Http\Utils\PermissionUtils;
use App\Models\AdminLoginSituations;
use App\Models\ShachihataLoginSituations;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class MfaController extends Controller
{
    const MAX_LOGIN_ATTEMPT = 10;
    
    public function index()
    {
        if (!Session::get('Mfa.needsMfa')) {
            abort(403);
        }
        
        if (Session::get('Mfa.resend')) {
            session()->flash('resend-message', 'メールを再送信しました。');
            Session::forget('Mfa.resend');
        }

        if (!Session::has('Mfa.failCount')) {
            Session::put('Mfa.failCount', 0);
        }
        
        return view('auth.mfa.email', [
            'passwordRequired' => Session::get('viaRemember'),
            'terminate' => Session::get('Mfa.failCount') >= self::MAX_LOGIN_ATTEMPT,
        ]);
    }
    
    public function verify(Request $request)
    {
        if (!Session::get('Mfa.needsMfa') || Session::get('Mfa.failCount', 0) >= self::MAX_LOGIN_ATTEMPT) {
            abort(403);
        }
        
        $user = Auth::user();
        $passwordRequired = Session::get('viaRemember');
        $otp = $request->get('otp');
        $password = $request->get('password');
        $message = [];
        if (empty($otp)) {
            $message[] = '認証コードが入力されていません。';
        }
        if ($passwordRequired && empty($password)) {
            $message[] = 'パスワードが入力されていません。';
        }
        if (empty($message)) {
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
            if ($passwordRequired) {
                if (!Hash::check($password, $user->password)) {
                    $authFailed = true;
                }
            }
            if ($authFailed) {
                Session::put('Mfa.failCount', Session::get('Mfa.failCount', 0) + 1);
                if ($passwordRequired) {
                    $message = ['認証コードまたはパスワードが間違っています。'];
                } else {
                    $message = ['認証コードが間違っています。'];
                }
            }
        }

        if (!empty($message)) {
            return redirect('extra-auth')
                ->withInput()
                ->with('message', join('<br/>', $message));
        }

        // 認証成功
        $newLoginSituation = Session::get('Mfa.newLoginSituation');
        try {
            DB::beginTransaction();
            
            $user->last_mfa_login_at = new Carbon();
            $user->update_user = $user->getFullName();
            $user->one_time_password = null;
            $user->one_time_password_expires_at = null;
            $user->save();
            
            $loginSituations = Session::get('Mfa.loginSituations');
            if ($newLoginSituation) {
                $maxCount = config('app.mfa_login_situation_max');
                if ($loginSituations && count($loginSituations) >= $maxCount) {
                    if ($user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)) {
                        ShachihataLoginSituations::where('mst_shachihata_id', $user->id)
                            ->where('update_at', '<=', $loginSituations[$maxCount - 1]->update_at)
                            ->delete();
                    } else {
                        AdminLoginSituations::where('mst_admin_id', $user->id)
                            ->where('update_at', '<=', $loginSituations[$maxCount - 1]->update_at)
                            ->delete();
                    }
                }

                if ($user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)) {
                    $ls = new ShachihataLoginSituations();
                    $ls->mst_shachihata_id = $user->id;
                } else {
                    $ls = new AdminLoginSituations();
                    $ls->mst_admin_id = $user->id;
                }
                $ls->ip_address = $request->getClientIp();
                $ls->user_agent = $request->userAgent();
                $ls->create_user = $ls->update_user = $user->getFullName();
                $ls->save();
            } else {
                $i = Session::get('Mfa.matchedLoginSituationIndex');
                if (!is_null($i)) {
                    $loginSituations[$i]->update_at = new Carbon();
                    $loginSituations[$i]->update_user = $user->getFullName();
                    $loginSituations[$i]->save();
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        
        Session::forget('Mfa');
        Session::put('Mfa.done', true);
        return redirect('/');
    }
    
    public function resend()
    {
        if (!Session::get('Mfa.needsMfa')) {
            abort(403);
        }
        
        Session::put('Mfa.resend', true);
        return redirect('/');
    }
}
