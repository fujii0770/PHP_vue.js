<?php

namespace App\Http\Middleware;

use App\Http\Utils\AppUtils;
use Illuminate\Support\Facades\DB;
use Closure;

class CheckExpensePermission
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
        $user = $request->user();
        $hasExpensePermissionUser = $user->expense_flg;
        $hasExpensePermission = DB::table('mst_company')->where(['id' => $user->mst_company_id,
            'expense_flg' => AppUtils::STATE_VALID
            ])->first();

        if ($hasExpensePermission && $hasExpensePermissionUser == AppUtils::STATE_VALID){
            return $next($request);
        }else{
            abort(403);
        }
    }
}
