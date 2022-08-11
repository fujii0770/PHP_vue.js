<?php

namespace App\Providers;

use App\Http\Utils\AppUtils;
use App\Models\Passport\AuthCode;
use App\Models\Passport\Client;
use App\Models\Passport\PersonalAccessClient;
use App\Models\Passport\Token;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
         'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::tokensCan([
            AppUtils::ACCOUNT_TYPE_USER => 'Normal user',
            AppUtils::ACCOUNT_TYPE_AUDIT => 'Audit user',
        ]);

        Passport::routes(function ($router) {
            $router->forAccessTokens();
        });
    }
}
