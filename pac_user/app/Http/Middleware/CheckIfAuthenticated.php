<?php

namespace App\Http\Middleware;

use App\Utils\AppUtils;
use App\Utils\UserApiUtils;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CheckIfAuthenticated
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
        $token = $request->bearerToken();
        if (Session::has('needResetPass')) {
            $isAuditUser = Session::get('is_audit_user', false);
            if ($isAuditUser) {
                return redirect()->to("password/init/" . AppUtils::encrypt(Session::get('username') . ',' . AppUtils::ACCOUNT_TYPE_AUDIT . ',' . Session::get('tokenResetPass'), true));
            } else {
                return redirect()->to("password/init/" . AppUtils::encrypt(Session::get('username') . ',' . AppUtils::ACCOUNT_TYPE_USER . ',' . Session::get('tokenResetPass'), true));
            }
        }
        // PAC_5-1488 クラウドストレージを追加する Start
        if (Session::has('hashUser') && in_array($request->route()->uri, ['uploadExternal', 'externalCallbackDone'])){
            return $next($request);
        }
        // PAC_5-1488 End
        if (!$token) {
            if (Session::has('accessToken')) {
                $token = Session::get('accessToken');
            } else {
                throw new AuthenticationException(
                    'Unauthenticated.', [], $this->redirectTo($request)
                );
            }
        }

        if (!Session::has($token)) {
            Log::debug("Check token from API");
            $client = UserApiUtils::getAuthorizedApiClient($token);

            $result = $client->get("user", []);
            if ($result->getStatusCode() == 200) {
                $resultLogin = json_decode((string)$result->getBody());

                Session::put($token, $resultLogin);
            } else {
                throw new AuthenticationException(
                    'Unauthenticated.', [], $this->redirectTo($request)
                );
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
