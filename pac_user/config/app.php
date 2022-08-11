<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),
    'management_api_env' => env("MANAGEMENT_API_ENV",''),
    'app_server_env' => env("APP_SERVER_ENV",'1'),
    'pac_contract_server' => env("PAC_CONTRACT_SERVER",0),
    'edition_flg' => env("EDITION_FLG",'1'),
    'server_api' => ['host' =>env('API_HOST', 'https://3.112.131.230/app-api/'),
        'base_url' =>env('API_BASE_URL', 'api/v1'),
        'client_id' =>env('API_CLIENT_ID', 1),
        'client_secret' =>env('API_CLIENT_SECRET', 1)],
    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'audit_home_screen' => env('AUDIT_HOME_SCREEN', 'document-search'),

    'asset_url' => env('ASSET_URL', null),

    'api_host' => env('API_HOST','http://localhost'),

    'api_host_domain' => env('API_HOST_DOMAIN','http://localhost'),

    'api_base' => env('API_BASE','/api/v1/'),

    'stamp_api_host' => env('STAMP_API_HOST','http://localhost'),

    'stamp_api_base' => env('STAMP_API_BASE','/api/'),

    'google_analytics_id' => env('GA_ID',''),

    'unauthenticated_redirect_url' => env('UNAUTHENTICATED_REDIRECT_URL','http://localhost'),

    'unfound_approval_url' => env('UNFOUND_APPROVAL_URL','https://estamp.dstmp.com/app/site/approval/'),

    'enable_self_login' => env('ENABLE_SELF_LOGIN',false),

    'enable_sso_login' => env('ENABLE_SSO_LOGIN',true),

    'enable_sso_slo' => env('ENABLE_SSO_SLO',false),

    'saml_url_prefix' => env('SAML_URL_PREFIX', 'sso'),

    'dummy_login' => env('DUMMY_LOGIN',false),

    'app_server_env' => env("APP_SERVER_ENV",'1'),//env_flg
    'pac_contract_server' => env("PAC_CONTRACT_SERVER",0),//server_flg
    'edition_flg' => env("EDITION_FLG",'1'),//edition_flg

    'aes256_pass' => env('AES256_PASS'),

    'guzzle_timeout' => env('GUZZLE_TIMEOUT', 30),

    'guzzle_connect_timeout' => env('GUZZLE_CONNECT_TIMEOUT', 30),

    'libreoffice_location' => env('LIBREOFFICE_LOCATION', '/tmp/.config/libreoffice/4/user'),
    'libreoffice_user_count' => env('LIBREOFFICE_USER_COUNT', 30),

    'survey_url' => env('SURVEY_URL'),
    'gw_domain' => env('GW_DOMAIN',null),

    'office_convert_api_host' => env('OFFICE_CONVERT_API_HOST', 'http://localhost'),
    'office_convert_api_base' => env('OFFICE_CONVERT_API_BASE', '/api/'),
    'app_lgwan_flg' => env('APP_LGWAN_PRIVATE_FLG', 0),
    'stamp_lgwan_public_url' => env('STAMP_LGWAN_PUBLIC_URL', ''),

    'page_break_api_host' => env('PAGE_BREAK_API_HOST','http://localhost'),
    'page_break_api_base' => env('PAGE_BREAK_API_BASE','/api/'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Asia/Tokyo',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'ja',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        Intervention\Image\ImageServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,

        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

        Intervention\Image\ImageServiceProvider::class,

        Jenssegers\Agent\AgentServiceProvider::class


    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
        'ImageService' => Intervention\Image\Facades\Image::class,
        'Aacotroneo\Saml2\Saml2ServiceProvider' => App\Saml\Saml2ServiceProvider::class,
        'Aacotroneo\Saml2\Http\Controllers\Saml2Controller' => App\Saml\Saml2Controller::class,
        'Agent' => Jenssegers\Agent\Facades\Agent::class

    ],

];
