<?php

namespace App\Http\Middleware;

use App\Http\Utils\PermissionUtils;
use App\Models\AdminLoginSituations;
use App\Models\Company;
use App\Models\ShachihataLoginSituations;
use App\ShachihataAdmin;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CheckMfa
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
        if (Auth::check() && !(Session::has('Mfa.needsMfa') || Session::has('Mfa.done'))) {
            $user = Auth::user();
            if ($user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)) {
                $shachi = ShachihataAdmin::findOrFail($user->id);
                $company = (object)['mfa_flg' => $shachi->email_auth_flg];
            } else {
                $company = Company::findOrFail($user->mst_company_id);
            }
            $limit = DB::table('mst_limit')->select('mfa_login_timing_flg','mfa_interval_hours')
                ->where('mst_company_id', $user->mst_company_id)->first();


            if (!$company->mfa_flg || !$user->email_auth_flg) {
                Session::put('Mfa.needsMfa', false);
                return $next($request);
            }
            
            if (!$user->last_mfa_login_at) {
                // 初回MFA
                Session::put('Mfa.needsMfa', true);
                Session::put('Mfa.newLoginSituation', true);
                return $next($request);
            }

            if ($user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)) {
                $items = ShachihataLoginSituations::where('mst_shachihata_id', $user->id)
                    ->orderBy('update_at', 'desc')
                    ->limit(config('app.mfa_login_situation_max'))
                    ->get();
            } else {
                $items = AdminLoginSituations::where('mst_admin_id', $user->id)
                    ->orderBy('update_at', 'desc')
                    ->limit(config('app.mfa_login_situation_max'))
                    ->get();
            }
            
            $ip = $request->getClientIp();
            $ua = $request->userAgent();
            $found = false;
            foreach ($items as $i => $item) {
                if ($item->ip_address == $ip && $item->user_agent == $ua) {
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
                if($limit->mfa_login_timing_flg){
                    if ($last_mfa_login_at->lt($now->subHours($limit->mfa_interval_hours))) {
                        $needsMfa = true;
                    }
                }else{
                    //PAC_5-1421 通知メール認証＋ログイン状態保持の時リダイレクトが二回飛ぶ　対策暫定対応
                    $needsMfa = true;
                }
            }

            Session::put('Mfa.needsMfa', $needsMfa);
            if ($needsMfa) {
                Session::put('Mfa.newLoginSituation', $newLoginSituation);
                if ($found && isset($i)) {
                    Session::put('Mfa.matchedLoginSituationIndex', $i);
                }
                if (count($items) > 0) {
                    Session::put('Mfa.loginSituations', $items);
                }
            } else {
                if (isset($item)) {
                    $item->update_at = new Carbon();
                    $item->update_user = $user->getFullName();
                    $item->save();
                }
                Session::put('Mfa.done', true);
            }
        }
        
        return $next($request);
    }
}
