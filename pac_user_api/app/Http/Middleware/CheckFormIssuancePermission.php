<?php

namespace App\Http\Middleware;

use App\Http\Utils\AppUtils;
use Illuminate\Support\Facades\DB;
use Closure;

class CheckFormIssuancePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $login_user = $request->user();
        $hasFormIssuancePermission = DB::table('mst_company')
                        ->join('mst_user', 'mst_user.mst_company_id', '=', 'mst_company.id')
                        ->where('mst_company.id', $login_user->mst_company_id)
                        ->where('mst_user.id', $login_user->id)
                        ->where('mst_user.frm_srv_user_flg', AppUtils::STATE_VALID)
                        ->where('mst_company.frm_srv_flg', AppUtils::STATE_VALID)
                        ->count();
        if ($hasFormIssuancePermission){
            return $next($request);
        }else{
            abort(403, "明細テンプレートサービスを使用する権利がありません。");
        }
    }
}
