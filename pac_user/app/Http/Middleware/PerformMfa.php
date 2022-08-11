<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class PerformMfa
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
        $mfa = Session::get('mfa');
        if (!empty($mfa->needsMfa)) {
            return redirect('extra-auth');
        }
        
        return $next($request);
    }
}
