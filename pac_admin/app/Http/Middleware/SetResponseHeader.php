<?php

namespace App\Http\Middleware;

use Closure;

class SetResponseHeader
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
        $response = $next($request);
        $response->headers->set('cache-control', ['private', 'no-store', 'no-cache', 'must-revalidate', 'max-age=0']);

        return $response;
    }
}
