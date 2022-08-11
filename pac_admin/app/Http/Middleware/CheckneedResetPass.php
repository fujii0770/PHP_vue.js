<?php

namespace App\Http\Middleware;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


use Closure;

class CheckneedResetPass
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
        if (Auth::check() && Session::get('admin.needResetPass')) {
            $token = Hash::make(Session::get('admin.email').time());
            DB::table('admin_password_resets')->insert([
                'email' => Session::get('admin.email'),
                'token' => $token,
                'created_at' => new \DateTime(),
            ]);

            $token = Hash::make(Session::get('admin.email').$token);
            return redirect()->to("password/init/". AppUtils::encrypt(Session::get('admin.email') . ',' . AppUtils::ACCOUNT_TYPE_ADMIN . ',' . $token, true));
        }
        return $next($request);
    }
}
