<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if(!config('app.debug')) {
            Paginator::currentPathResolver(function () {
                return str_replace('http://', 'https://', $this->app['request']->url());
            });
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if(!config('app.debug')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
