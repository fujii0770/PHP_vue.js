<?php

namespace App\Http\Middleware;

use App\Http\Utils\AppUtils;
use Illuminate\Support\Facades\DB;
use Closure;

class CheckTemplatePermission
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
        $hasTemplatePermission = DB::table('mst_company')->where('id', $login_user->mst_company_id)->where('template_flg', AppUtils::STATE_VALID)->count();
        $specialSite = DB::table('special_site_receive_send_available_state')->where('company_id', $login_user->mst_company_id)->where('is_special_site_send_available', 1)->count();

        if ($hasTemplatePermission || $specialSite){
            return $next($request);
        }else{
            abort(403);
        }
    }
}
