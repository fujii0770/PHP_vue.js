<?php

namespace App\Http\Middleware;

use Closure;

class CheckCircularApprovalSendBackPermission extends CheckCircularPermission
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
        if ($this->checkCircularPermission($request, 'approval-sendback')){
            return $next($request);
        }else{
            abort(403);
        }
    }
}
