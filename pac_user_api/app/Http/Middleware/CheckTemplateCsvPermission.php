<?php

namespace App\Http\Middleware;

use App\Http\Utils\AppUtils;
use Illuminate\Support\Facades\DB;
use Closure;

class CheckTemplateCsvPermission
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
        $hasTemplateCsvPermission = DB::table('mst_company')->where('id', $login_user->mst_company_id)->where('template_csv_flg', AppUtils::STATE_VALID)->count();
        $hasTemplatePermission = DB::table('mst_company')->where('id', $login_user->mst_company_id)->where('template_flg', AppUtils::STATE_VALID)->count();
        if ($hasTemplatePermission && $hasTemplateCsvPermission){
            return $next($request);
        }else{
            abort(403);
        }
    }
}
