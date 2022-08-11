<?php

namespace App\Http\Middleware;

use Closure;

class CheckCircularUpdatePermission extends CheckCircularPermission
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
        if ($this->checkCircularPermission($request, 'update')){
            return $next($request);
        }else{
            abort(403);
        }
    }
}
