<?php

namespace App\Http\Middleware;

use Closure;

class CheckCircularRequestSendBackPermission extends CheckCircularPermission
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
        if ($this->checkCircularPermission($request, 'request-sendback')){
            return $next($request);
        }else{
            abort(403);
        }
    }
}
