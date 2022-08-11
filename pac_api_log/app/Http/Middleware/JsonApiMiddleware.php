<?php

namespace App\Http\Middleware;

use Closure;
use Log;

class JsonApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    const PARSED_METHODS = [
        'POST', 'PUT', 'PATCH'
    ];

    public function handle($request, Closure $next)
    {
        if (in_array($request->getMethod(), self::PARSED_METHODS)) {
            $content = json_decode($request->getContent(), true);
            if (is_array($content)){
                $request->merge(json_decode($request->getContent(), true));
            }else{
                $request->merge([$content]);
            }
        }
        return $next($request);
    }
}
