<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class RefererLogging
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $referer = $request->headers->get('referer');
        Log::debug('The current SAML request is: '.$request->url());
        if ($referer){
            Log::debug('The SAML request came from the Referer URL (IDP): '.$referer);
        }else{
            Log::debug('The SAML request came without the Referer URL (IDP)');
        }

        return $next($request);
    }
}
