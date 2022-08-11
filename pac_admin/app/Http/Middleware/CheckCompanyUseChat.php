<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Utils\AppUtils;

class CheckCompanyUseChat
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
        if (Auth::check()) {
            $user = Auth::user();
            $companyUser = DB::table('mst_company')
                ->where('mst_company.id', $user->mst_company_id)
                ->where('mst_company.chat_flg', AppUtils::MST_COMPANY_CHAT_FLG_USING)
                ->select('mst_company.chat_flg')
                ->first();
            if ($companyUser) {
                return $next($request);
            }
        }
        return abort(403);
    }
}
