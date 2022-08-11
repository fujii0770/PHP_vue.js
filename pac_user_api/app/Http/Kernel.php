<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \Barryvdh\Cors\HandleCors::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\SetResponseHeader::class,
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
          /*  'throttle:60,1',*/
            'bindings',

        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'JsonApiMiddleware' => \App\Http\Middleware\JsonApiMiddleware::class,
        'checkHashing' => \App\Http\Middleware\CheckHashing::class,
        'check_circular_view_permission' => \App\Http\Middleware\CheckCircularViewPermission::class,
        'check_circular_update_permission' => \App\Http\Middleware\CheckCircularUpdatePermission::class,
        'check_circular_pullback_permission' => \App\Http\Middleware\CheckCircularPullbackPermission::class,
        'check_circular_request_sendback_permission' => \App\Http\Middleware\CheckCircularRequestSendBackPermission::class,
        'check_circular_approval_sendback_permission' => \App\Http\Middleware\CheckCircularApprovalSendBackPermission::class,
        'check_multiple_circular_permission' => \App\Http\Middleware\CheckMultipleCircularPermission::class,
        'check_template_permission' => \App\Http\Middleware\CheckTemplatePermission::class,
        'check_form_issuance_permission' => \App\Http\Middleware\CheckFormIssuancePermission::class,
        'check_form_issuance_action_permission' => \App\Http\Middleware\CheckFormIssuanceActionPermission::class,
        'check_exp_template_action_permission' => \App\Http\Middleware\CheckExpTemplateActionPermission::class,
        'check_expense_permission' => \App\Http\Middleware\CheckExpensePermission::class,
        'LogOperation' => \App\Http\Middleware\LogOperation::class,
        'client.credentials' => \Laravel\Passport\Http\Middleware\CheckClientCredentials::class,
        'scopes' => \Laravel\Passport\Http\Middleware\CheckScopes::class,
        'scope' => \Laravel\Passport\Http\Middleware\CheckForAnyScope::class,
        'check_template_csv_permission' => \App\Http\Middleware\CheckTemplateCsvPermission::class,
        'cors'          => \App\Http\Middleware\Cors::class, // 追加
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}
