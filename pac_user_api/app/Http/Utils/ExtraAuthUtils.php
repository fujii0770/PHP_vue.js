<?php


namespace App\Http\Utils;


use App\Models\User;
use App\Models\UserLoginSituations;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Http\Utils\AppUtils;
use App\Http\Utils\MailUtils;
use Illuminate\Support\Facades\Cache;

class ExtraAuthUtils
{
    public static function isIpPermitted($user, $clientIp)
    {
        // IP制限要否チェック
        $needsIpRestriction = false;
        $permitted = false;
        if (!Session::exists('IpRestriction.ip_restriction_flg')) {
            $company = DB::table('mst_company')->find($user->mst_company_id);
            $needsIpRestriction = $company->ip_restriction_flg;
            Session::put('IpRestriction.ip_restriction_flg', $needsIpRestriction);
            Session::put('IpRestriction.permit_unregistered_ip_flg', $company->permit_unregistered_ip_flg);
        } else {
            $needsIpRestriction = Session::get('IpRestriction.ip_restriction_flg');
            $permitted = Session::get('IpRestriction.permitted');
        }
        if (!$needsIpRestriction || $permitted) {
            return true;
        }

        // IP制限リスト取得
        $ipList = [];
        if (Session::exists('IpRestrictionList')) {
            $ipList = Session::get('IpRestrictionList');
        } else {
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                Log::error("統合ID APIクライアント取得失敗");
                return false;
            }
            $params = [
                'company_id' => $user->mst_company_id,
                'contract_app' => config('app.edition_flg'),
                'app_env' => config('app.server_env'),
                'contract_server' => config('app.server_flg'),
            ];
            $result = $client->post('ip_restrictions/list', [
                RequestOptions::JSON => $params
            ]);
            if ($result->getStatusCode() != StatusCodeUtils::HTTP_OK) {
                Log::error("IP制限リスト取得失敗: " . $result->getBody());
                return false;
            }

            $ipList =array_map(function($val){
                if (strpos($val['ip_address'],'/')){
                    $info=explode("/",$val['ip_address']);
                    $long=ip2long($info[0]);
                    $bit=32-$info[1];
                    $start=0xffffffff << $bit;
                    $end=0xffffffff >> $info[1];
                    $firstIP=long2ip(sprintf('%u',($long & $start)));
                    $lastIP=long2ip(sprintf('%u',($long | $end)));
                }elseif (strpos($val['ip_address'], '*') !== false) {
                    $firstIP = str_replace('*', '0', $val['ip_address']);
                    $lastIP  = str_replace('*', '255', $val['ip_address']);
                }else{
                    $firstIP = $val['ip_address'];
                    $lastIP  = $val['ip_address'];
                }
                return [
                    'firstIP'=>$firstIP,
                    'lastIP'=>$lastIP
                ];
            },json_decode($result->getBody(), true)['data']);
            Session::put('IpRestrictionList', $ipList);
        }

        // IP制限チェック
        if (!empty($ipList)) {
            $ip = ip2long($clientIp);
            $result = collect($ipList)->some(function ($val) use($clientIp){
                return self::checkIP($clientIp,$val['firstIP'],$val['lastIP']);
            });

            if (!$result) {
                if (!Session::get('IpRestriction.permit_unregistered_ip_flg')) {
                    Log::warning("IPアドレス制限違反 user_id: " . $user->id . ", IP: " . $clientIp);
                    //Auth::logout();
                    return false;
                } else {
                    Session::put('IpRestriction.permitted', true);
                    $admin = DB::table('mst_admin')->where('mst_company_id', $user->mst_company_id)
                        ->where('role_flg', 1)->where('state_flg', 1)->first();
                    if ($admin) {
                        $json_arr = ['user' => $user->email, 'ipAddress' => $clientIp];
                        // IP制限_ログイン通知
                        MailUtils::InsertMailSendResume(
                            // 送信先メールアドレス
                            $admin->email,
                            // メールテンプレート
                            MailUtils::MAIL_DICTIONARY['USER_IP_RESTRICTION_ALERT']['CODE'],
                            // パラメータ
                            json_encode($json_arr,JSON_UNESCAPED_UNICODE),
                            // タイプ
                            AppUtils::MAIL_TYPE_USER,
                            // 件名
                            config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.SendIpRestrictionMail.subject'),
                            // メールボディ
                            trans('mail.SendIpRestrictionMail.body', $json_arr)
                        );
                    }
                }
            }
        }

        return true;
    }

    public static function checkMfa($user, $clientIp, $userAgent)
    {
        $company = DB::table('mst_company')->find($user->mst_company_id);
        $userInfo = DB::table('mst_user_info')->where('mst_user_id', $user->id)->first();
        $limit = DB::table('mst_limit')->select('mfa_login_timing_flg','mfa_interval_hours')->where('mst_company_id', $user->mst_company_id)->first();

        $result = [
          'clientIp' => $clientIp,
          'userAgent' => $userAgent
        ];

        if (!$company->mfa_flg || !isset($userInfo->mfa_type) || !$userInfo->mfa_type) {
            $result['needsMfa'] = false;
            return $result;
        }

        if (!$user->last_mfa_login_at) {
            // 多要素認証初回利用の処理
            if ($userInfo->mfa_type == 1) {
                ExtraAuthUtils::sendOtpMail($user, $userInfo);
            } elseif ($userInfo->mfa_type == 2) {
                $result['otp'] = ExtraAuthUtils::generateQrCodeToken($user);
            } else {
                throw new \Exception('invalid mfa_type: '. $user->mfa_type);
            }
            $result['needsMfa'] = true;
            $result['type'] = $userInfo->mfa_type;
            $result['newLoginSituation'] = true;
            return $result;
        }
        $maxSituationCount = config('app.mfa_login_situation_max');
        $items = UserLoginSituations::where('mst_user_id', $user->id)
            ->orderBy('update_at', 'desc')
            ->limit($maxSituationCount)
            ->get();

        $found = false;
        foreach ($items as $i => $item) {
            if ($item->ip_address == $clientIp && $item->user_agent == $userAgent) {
                $found = true;
                break;
            }
        }
        $needsMfa = false;
        $newLoginSituation = false;
        if (!$found) {
            $needsMfa = true;
            $newLoginSituation = true;
        } else {
            $now = new Carbon();
            $last_mfa_login_at = new Carbon($user->last_mfa_login_at);
            //PAC_5-1421　ログイン要求タイミングで分岐
            if($limit->mfa_login_timing_flg){
                if ($last_mfa_login_at->lt($now->subHours($limit->mfa_interval_hours))) {
                    $needsMfa = true;
                    $newLoginSituation = false;
                }
            }else{
            //PAC_5-1421 通知メール認証＋ログイン状態保持の時リダイレクトが二回飛ぶ　対策暫定対応
                $needsMfa = true;
                $newLoginSituation = false;
            }
        }

        // 結果作成
        if ($needsMfa) {
            $result['needsMfa'] = true;
            $result['type'] = $userInfo->mfa_type;
            $result['newLoginSituation'] = $newLoginSituation;
            if ($found && isset($item)) {
                $result['matchedLoginSituationId'] = $item->id;
            }
            if ($newLoginSituation && count($items) >= $maxSituationCount) {
                $result['loginSituationIdToBeDeleted'] = $items[$maxSituationCount-1]->id;
            }
        } else {
            $result['needsMfa'] = false;
            if (isset($item)) {
                $item->update_at = new Carbon();
                $item->update_user = $user->getFullName();
                $item->save();
            }
        }

        // OTP生成
        if ($needsMfa) {
            if ($userInfo->mfa_type == 1) {
                ExtraAuthUtils::sendOtpMail($user, $userInfo);
            } elseif ($userInfo->mfa_type == 2) {
                $result['otp'] = ExtraAuthUtils::generateQrCodeToken($user);
            } else {
                throw new \Exception('invalid mfa_type: ' . $user->mfa_type);
            }
        }

        return $result;
    }

    public static function resendAuthMail($user)
    {
        $userInfo = DB::table('mst_user_info')->where('mst_user_id', $user->id)->first();
        // PAC_5-2095 再送信ログが1回分表示される
        Cache::store('database')->forget($user->email.'-OtpKey');
        return ExtraAuthUtils::sendOtpMail($user, $userInfo);
    }

    private static function sendOtpMail($user, $userInfo)
    {
        $otp = random_int(100000, 999999);
        $otpHash = Hash::make($otp);
        $otpExpires = (new Carbon())->addMinutes(10);
        $stringCacheData =  Cache::store('database')->get($user->email.'-OtpKey');
        if($stringCacheData){
            $arrData =  json_decode($stringCacheData,true);
            return [
                'otp' => $arrData['otp'],
                'otpExpires' => $arrData['otpExpires']
            ];
        }
        try {
            DB::beginTransaction();

            $user->one_time_password = $otpHash;
            $user->one_time_password_expires_at = $otpExpires;
            $user->save();
            $json_arr = [];
            $to = $userInfo->email_auth_dest_flg ? $userInfo->auth_email : $user->email;
            $notification_email = strpos($user->email, '@') === false ? $user->notification_email : '';
            $json_arr['user_id'] = strpos($user->email, '@') === false ? $user->email : '';
            $company = DB::table('mst_company')->where('mst_company_id', $user->mst_company_id)->first();
            $json_arr['company_name'] = strpos($user->email, '@') === false && $company ? $company->company_name : '';

            if (!empty($to)) {
                $json_arr['otp'] = $otp;
                $json_arr['otpExpires'] = $otpExpires->format('Y/m/d H:i');
                //利用者:認証コードの発行
                MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                    $to,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['USER_MFA_CODE_RELEASE']['CODE'],
                    // パラメータ
                    json_encode($json_arr,JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_USER,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.SendMfaMail.subject'),
                    // メールボディ
                    trans('mail.SendMfaMail.body', $json_arr),AppUtils::MAIL_STATE_WAIT ,AppUtils::MAIL_SEND_DEFAULT_TIMES,
                    $notification_email
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            Log::error('ワンタイムパスワード送信失敗');
            DB::rollBack();
            throw $e;
        }
        $arrReturnData = [
            'otp' => $otpHash,
            'otpExpires' => $otpExpires
        ];
        Cache::store('database')->put($user->email.'-OtpKey',json_encode($arrReturnData),4);
        return [
            'otp' => $otpHash,
            'otpExpires' => $otpExpires
        ];
    }

    private static function generateQrCodeToken($user)
    {
        $otp = Str::random(60);
        $otpHash = Hash::make($otp);
        $otpExpires = (new Carbon())->addMinutes(3);

        try {
            DB::beginTransaction();
            DB::table('mst_user')->where('id', $user->id)->update(['one_time_password' => $otpHash, 'one_time_password_expires_at' => $otpExpires, 'one_time_password_confirmed' => 0]);
            DB::commit();

            $user->one_time_password = $otpHash;
            $user->one_time_password_expires_at = $otpExpires;
            $user->one_time_password_confirmed = 0;
        } catch (\Exception $e) {
            Log::error('QRコード用トークン生成失敗');
            DB::rollBack();
            throw $e;
        }

        return [
            'otp' => $otp,
            'otpExpires' => $otpExpires
        ];
    }
    public static function checkIP($clientIP,$startIP,$endIP){
        $clientIP = bindec(decbin(ip2long($clientIP)));
        $startIP = bindec(decbin(ip2long($startIP)));
        $endIP = bindec(decbin(ip2long($endIP)));
        if($startIP <= $clientIP && $clientIP <= $endIP){
            return true;
        }else{
            return false;
        }
    }
}
