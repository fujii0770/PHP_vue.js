<?php

namespace App\Http\Middleware;

use App\CompanyAdmin;
use App\Http\Utils\AppUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\PermissionUtils;
use App\Models\Company;
use Closure;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Http\Utils\MailUtils;
use Carbon\Carbon;
use DB;

class CheckIpRestriction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string $order
     * @return mixed
     */
    public function handle($request, Closure $next, $order = null)
    {
        if ($order == 'postCheck') {
            $res = $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)) {
                if ($order == 'postCheck') {
                    return $res;
                } else {
                    return $next($request);
                }
            }

            // IP制限要否チェック
            $needsIpRestriction = false;
            $permitted = false;
            if (!$request->session()->exists('IpRestriction.ip_restriction_flg')) {
                $company = Company::findOrFail($user->mst_company_id);
                $needsIpRestriction = $company->ip_restriction_flg;
                $request->session()->put('IpRestriction.ip_restriction_flg', $needsIpRestriction);
                $request->session()->put('IpRestriction.permit_unregistered_ip_flg', $company->permit_unregistered_ip_flg);
            } else {
                $needsIpRestriction = $request->session()->get('IpRestriction.ip_restriction_flg');
                $permitted = $request->session()->get('IpRestriction.permitted');
            }
            if (!$needsIpRestriction || $permitted) {
                if ($order == 'postCheck') {
                    return $res;
                } else {
                    return $next($request);
                }
            }

            // IP制限リスト取得
            $ipList = [];
            if ($request->session()->exists('IpRestrictionList')) {
                $ipList = $request->session()->get('IpRestrictionList');
            } else {
                $client = IdAppApiUtils::getAuthorizeClient();
                if (!$client) {
                    Log::error("統合ID APIクライアント取得失敗");
                    Auth::logout();
                    abort(500);
                }
                $params = [
                    'company_id' => $user->mst_company_id,
                    'contract_app' => config('app.pac_contract_app'),
                    'app_env' => config('app.pac_app_env'),
                    'contract_server'=> config('app.pac_contract_server'),
                ];
                $result = $client->post('ip_restrictions/list', [
                    RequestOptions::JSON => $params
                ]);
                if ($result->getStatusCode() != 200) {
                    Log::error("IP制限リスト取得失敗: " . $result->getBody());
                    Auth::logout();
                    abort(500);
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
                $request->session()->put('IpRestrictionList', $ipList);
            }


            // IP制限チェック
            if (!empty($ipList)) {
                $clientIp = $request->getClientIp();
                if (!$clientIp) {
                    Log::error("クライアントIPアドレス取得不可");
                    Auth::logout();
                    abort(500);
                }


                $result = collect($ipList)->some(function ($val) use($clientIp){
                    return $this->checkIP($clientIp,$val['firstIP'],$val['lastIP']);
                });

                if (!$result) {
                    if (!$request->session()->get('IpRestriction.permit_unregistered_ip_flg')) {
                        Log::warning("IPアドレス制限違反 user_id: " . $user->id . ", IP: " . $clientIp);
                        Auth::logout();
                        abort(403, 'アクセスが許可されていません');
                    } else {
                        $request->session()->put('IpRestriction.permitted', true);
                        $admin = CompanyAdmin::where('mst_company_id', $user->mst_company_id)
                            ->where('role_flg', 1)->where('state_flg', 1)->first();
                        if ($admin) {
                            $data = ["ipAddress" => $clientIp, "user" => $user->email];

                            MailUtils::InsertMailSendResume(
                                // 送信先メールアドレス
                                $admin->email,
                                // メールテンプレート
                                MailUtils::MAIL_DICTIONARY['ADMIN_IP_RESTRICTION_ALERT']['CODE'],
                                // パラメータ
                                json_encode($data,JSON_UNESCAPED_UNICODE),
                                // タイプ
                                AppUtils::MAIL_TYPE_ADMIN,
                                // 件名
                                config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.SendIpRestrictionMail.subject'),
                                // メールボディ
                                trans('mail.SendIpRestrictionMail.body',$data)
                            );
                        }
                    }
                }
            }
        }

        if ($order == 'postCheck') {
            return $res;
        } else {
            return $next($request);
        }
    }

    public function checkIP($clientIP,$startIP,$endIP){
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
