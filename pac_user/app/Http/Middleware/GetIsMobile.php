<?php

namespace App\Http\Middleware;

use Closure;
use Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class GetIsMobile
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

        $agent = app('agent');
        View::share(['isMobile' => $agent->isMobile()]);
        return $next($request);
    }
}
