<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Http\Utils\PermissionUtils;

class CheckShachihataAdmin
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
        $user =Auth::user();
        if ($user->hasRole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)) {
            return $next($request);
        }else{
            abort(403);
        }
        
    }
}
