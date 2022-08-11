<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Request;

class CheckExpTemplateActionPermission
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
        $login_user = $request->user();

        $formId = $request->route('formId');

        $user_info = DB::table('mst_user_info')
            ->where('mst_user_id', $login_user->id)
            ->first();
        if(!$user_info) {
            abort(403);
        }

        $exp_template = DB::table('frm_exp_template')
            ->where('id', $formId)
            ->where('mst_company_id', $login_user->mst_company_id)
            ->first();
        if (!$exp_template){
            abort(403);
        }

        return $next($request);
    }
}
