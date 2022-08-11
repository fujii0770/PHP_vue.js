<?php

namespace App\Http\Middleware;

use App\Utils\UserApiUtils;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Response;

class CheckHashing
{

    /**
     * PAC_5-1488 クラウドストレージを追加する
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $request['usingHash'] = isset($request['usingHash']) && ((string)$request['usingHash'] === 'true' || (string)$request['usingHash'] === '1');
        if (!$request['usingHash']) {
            throw new AuthenticationException(
                'Unauthenticated.', [], $this->redirectTo($request)
            );
        } else {
            $userApiClient = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if(!$userApiClient){
                return Response::json(['status' => false, 'message' => '', 'data' => null], \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }
            $result = $userApiClient->get("userByHashing");
            if ($result->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                Log::warning("userByHashing response body: " . $result->getBody());
                return Response::json(['status' => false, 'message' => '権限がありません', 'data' => null], \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }
        }
        return $next($request);
    }
    
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return config('app.unauthenticated_redirect_url');
        }
    }
}
