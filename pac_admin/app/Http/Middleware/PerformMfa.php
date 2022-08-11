<?php

namespace App\Http\Middleware;

use App\CompanyAdmin;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use App\ShachihataAdmin;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Http\Utils\MailUtils;

class PerformMfa
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Session::get('Mfa.needsMfa')) {
            $user = Auth::user();
            
            // OTP作成
            $otp = random_int(100000, 999999); // TODO: もうちょっと工夫する
            $otpExpires = (new Carbon())->addMinutes(10);

            try {
                DB::beginTransaction();
            
                $user->one_time_password = Hash::make($otp);
                $user->one_time_password_expires_at = $otpExpires;
                $user->save();
                
                // メール送信
                $to = $user->email_auth_dest_flg ? $user->auth_email : $user->email;
                if (!empty($to)) {
                    $company = DB::table('mst_company')->select('company_name')->where('id',$user->mst_company_id)->first();
                    $data = [
                        'otp' => $otp,
                        'otpExpires' => $otpExpires->format('Y/m/d H:i'),
                    ];
                    $data['user_id'] = strpos($user->email, '@') === false ? $user->email : '';
                    $data['company_name'] = strpos($user->email, '@') === false && $company ? $company->company_name : '';

                    //認証コードの発行
                    MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                        $to,
                        // メールテンプレート
                        MailUtils::MAIL_DICTIONARY['ADMIN_MFA_CODE_RELEASE']['CODE'],
                        // パラメータ
                        json_encode($data,JSON_UNESCAPED_UNICODE),
                        // タイプ
                        AppUtils::MAIL_TYPE_ADMIN,
                        // 件名
                        config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.SendMfaMail.subject'),
                        // メールボディ
                        trans('mail.SendMfaMail.body', $data)
                    );
                }

                DB::commit();
            } catch (\Exception $e) {
                Log::error('ワンタイムパスワード送信失敗');
                DB::rollBack();
                throw $e;
            }
            
            return redirect('extra-auth');
        }

        return $next($request); 
    }
}
