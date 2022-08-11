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
    'slogan' => env('APP_SLOGAN', env('APP_NAME', 'Laravel')),
    'url_app_user' => env('URL_APP_USER'),
    'page_limit' => env('PAGE_LIMIT', 10),
    'page_list_limit' => array_combine(explode(",", env('PAGE_LIST_LIMIT', "10,50,100")), explode(",", env('PAGE_LIST_LIMIT', "10,50,100"))),
    'id_app_api_host' => env('ID_APP_API_HOST', ''),
    'id_app_api_base_url' => env('ID_APP_API_BASE_URL', ''),
    'id_app_api_client_id' => env('ID_APP_API_CLIENT_ID', ''),
    'id_app_api_client_secret' => env('ID_APP_API_CLIENT_SECRET', ''),
    'stamp_api_base_url' => env('STAMP_API_BASE_URL', ''),
    'department_stamp_api_url' => env('DEPARTMENT_STAMP_API_URL', ''),
    's3_storage_root_folder' =>env('STORAGE_ROOT_FOLDER','long_term_document'),
    's3_login_root_folder' =>env('LOGIN_ROOT_FOLDER',''), // PAC_5-2089 マスター画面からログイン画面の画像・テキストを変換

    'pac_app_env' => env('PAC_APP_ENV', 0),//env_flg
    'pac_contract_app' => env('PAC_CONTRACT_APP', 1),//edition_flg
    'pac_contract_server' => env('PAC_CONTRACT_SERVER', 0),//server_flg

    'constraints_max_requests' => env('CONSTRAINTS_MAX_REQUESTS', 1),
    'constraints_max_doccument_size' => env('CONSTRAINTS_MAX_DOCUMENT_SIZE', 8),
    //'constraints_user_storage_size' => env('CONSTRAINTS_USER_STORAGE_SIZE', 3),
    'constraints_use_storage_percent' => env('CONSTRAINTS_USE_STORAGE_PERCENT', 4),
    'constraints_max_keep_day' => env('CONSTRAINTS_MAX_KEEP_DAYS', 5),
    'constraints_delete_informed_day' => env('CONSTRAINTS_DELETE_INFORMED_DAY', 6),
    'constraints_long_term_storage_percent' => env('CONSTRAINTS_LONG_TERM_STORAGE_PERCENT', 90),
    'constraints_max_ip_address_count' => env('CONSTRAINTS_MAX_IP_ADDRESS_COUNT', 100),

    'constraints_dl_max_keep_days' => env('CONSTRAINTS_DL_MAX_KEEP_DAYS', 30),
    'constraints_dl_after_proc' => env('CONSTRAINTS_DL_MAX_AFTER_PROC', 0),
    'constraints_dl_after_keep_days' => env('CONSTRAINTS_DL_AFTER_KEEP_DAYS', 0),
    'constraints_dl_request_limit' => env('CONSTRAINTS_DL_REQUEST_LIMIT', 5),
    'constraints_dl_request_limit_per_one_hour' => env('CONSTRAINTS_DL_REQUEST_LIMIT_PER_ONE_HOUR', 0),
    'constraints_dl_file_total_size_limit' => env('CONSTRAINTS_DL_FILE_TOTAL_SIZE_LIMIT', 1024 * 1024 * 1024 * 1024),
    'constraints_max_viwer_count' => env('CONSTRAINTS_MAX_VIWER_COUNT', 10),
    'constraints_sanitize_request_limit' => env('CONSTRAINTS_SANITIZE_REQUEST_LIMIT', 10),
    'constraints_max_frm_document' => env('CONSTRAINTS_MAX_FRM_DOCUMENT', 100),
    'constraints_max_attachment_size' => env('CONSTRAINTS_MAX_ATTACHMENT_SIZE', 500),
    'constraints_max_total_attachment_size' => env('CONSTRAINTS_MAX_TOTAL_ATTACHMENT_SIZE', 5),
    'constraints_max_attachment_count' => env('CONSTRAINTS_MAX_ATTACHMENT_COUNT', 10),

    'unauthenticated_redirect_url' => env('UNAUTHENTICATED_REDIRECT_URL','http://localhost'),

    'mfa_interval_days' => env('MFA_INTERVAL_DAYS', 30),
    'mfa_login_situation_max' => env('mfa_login_situation_max', 3),
    'mail_environment_prefix' => env('MAIL_ENVIRONMENT_PREFIX', ''),
    'enable_self_login' => env('ENABLE_SELF_LOGIN',false),
    'enable_sso_login' => env('ENABLE_SSO_LOGIN',false),

    'help_url_first_time_set' => env('HELP_URL_FIRST_TIME_SET', 'https://help.dstmp.com/first-manual-bizpro-detail/?postId=3157'),
    'help_url_user_registration' => env('HELP_URL_USER_REGISTRATION', 'https://help.dstmp.com/help-bizpro-detail/?postId=3360'),
    'help_url_common_mark_setting' => env('HELP_URL_COMMON_MARK_SETTING', 'https://help.dstmp.com/help-bizpro-detail/?postId=3402'),

    // PDF処理API
    'stamp_api_host' => env('STAMP_API_HOST','http://localhost'),
    'stamp_api_base' => env('STAMP_API_BASE','/api/'),

    'sync_import_csv_limit' => env('SYNC_IMPORT_CSV_LIMIT', 5), // 同時にCSV取込制御数

    'url_contract' => env('URL_CONTRACT'),

    'survey_url' => env('SURVEY_URL'),

    'guzzle_timeout' => env('GUZZLE_TIMEOUT', 30),
    'guzzle_connect_timeout' => env('GUZZLE_CONNECT_TIMEOUT', 30),

    // 企業毎の利用量集計
    'per_operation_history_size' => env('PER_OPERATION_HISTORY_SIZE', 272), // 履歴ごとの容量(B)
    'per_mail_size' => env('PER_MAIL_SIZE', 1479), // メールごとの容量(B)
    'per_schedule_size' => env('PER_SCHEDULE_SIZE', 300), // スケジュールレコードごとの容量(B)
    'use_storage_base' => env('USE_STORAGE_BASE', 1.3), // 使用容量ベース

    'gw_domain' => env('GW_DOMAIN',''),

    'gw_use' => env('GW_USE',''),

    'gw_batch_token' => env('GW_BATCH_TOKEN','xxx'),

    // update csv path
    'update_path' => 'update/',
    // create csv path
    'create_path' => 'create/',
    //sftp update csv path
    'sftp_update_path' => env('SFTP_UPDATEDATA_PATH','upload/'),

    //受信専用プラン
    'receive_plan_api_host' =>env('RECEIVE_PLAN_API_HOST','https://dstmp-order.dev.onestop-i.co.jp'),
    'receive_plan_access_code' =>env('RECEIVE_PLAN_ACCESS_CODE','2ZDJfd0897dkd34thio49gj4'),

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
    'server_list' => env('SERVER_LIST', '00,01,02,03,10,100'),
    // key : env_flg + server_flg
    'server_env_api' => [
        '00' => [
            'host' => env('PAC_AWS_API_HOST', 'https://3.112.131.230/app-api/'),
            'base_url' => env('PAC_AWS_API_BASE_URL', 'api/v1'),
            'client_id' => env('PAC_AWS_API_CLIENT_ID', 1),
            'client_secret' => env('PAC_AWS_API_CLIENT_SECRET', 1)
        ],
        '01' => [
            'host' => env('PAC_AWS1_API_HOST', 'https://3.112.131.230/app-api/'),
            'base_url' => env('PAC_AWS1_API_BASE_URL', 'api/v1'),
            'client_id' => env('PAC_AWS1_API_CLIENT_ID', 1),
            'client_secret' => env('PAC_AWS1_API_CLIENT_SECRET', 1)
        ],
        '02' => [
            'host' => env('PAC_AWS2_API_HOST', 'https://3.112.131.230/app-api/'),
            'base_url' => env('PAC_AWS2_API_BASE_URL', 'api/v1'),
            'client_id' => env('PAC_AWS2_API_CLIENT_ID', 1),
            'client_secret' => env('PAC_AWS2_API_CLIENT_SECRET', 1)
        ],
        '03' => [
            'host' => env('PAC_AWS3_API_HOST', 'https://3.112.131.230/app-api/'),
            'base_url' => env('PAC_AWS3_API_BASE_URL', 'api/v1'),
            'client_id' => env('PAC_AWS3_API_CLIENT_ID', 1),
            'client_secret' => env('PAC_AWS3_API_CLIENT_SECRET', 1)
        ],
        '04' => [
            'host' => env('PAC_AWS4_API_HOST', 'https://3.112.131.230/app-api/'),
            'base_url' => env('PAC_AWS4_API_BASE_URL', 'api/v1'),
            'client_id' => env('PAC_AWS4_API_CLIENT_ID', 1),
            'client_secret' => env('PAC_AWS4_API_CLIENT_SECRET', 1)
        ],
        '05' => [
            'host' => env('PAC_AWS5_API_HOST', 'https://3.112.131.230/app-api/'),
            'base_url' => env('PAC_AWS5_API_BASE_URL', 'api/v1'),
            'client_id' => env('PAC_AWS5_API_CLIENT_ID', 1),
            'client_secret' => env('PAC_AWS5_API_CLIENT_SECRET', 1)
        ],
        '06' => [
            'host' => env('PAC_AWS6_API_HOST', 'https://3.112.131.230/app-api/'),
            'base_url' => env('PAC_AWS6_API_BASE_URL', 'api/v1'),
            'client_id' => env('PAC_AWS6_API_CLIENT_ID', 1),
            'client_secret' => env('PAC_AWS6_API_CLIENT_SECRET', 1)
        ],
        '07' => [
            'host' => env('PAC_AWS7_API_HOST', 'https://3.112.131.230/app-api/'),
            'base_url' => env('PAC_AWS7_API_BASE_URL', 'api/v1'),
            'client_id' => env('PAC_AWS7_API_CLIENT_ID', 1),
            'client_secret' => env('PAC_AWS7_API_CLIENT_SECRET', 1)
        ],
        '10' => [
            'host' => env('PAC_K5_API_HOST', 'https://133.162.146.120/app-api/'),
            'base_url' => env('PAC_K5_API_BASE_URL', 'api/v1'),
            'client_id' => env('PAC_K5_API_CLIENT_ID', 1),
            'client_secret' => env('PAC_K5_API_CLIENT_SECRET', 1)
        ],
        '0100' => [
            'host' => env('PAC_AWS100_API_HOST', 'https://3.112.131.230/app-api/'),
            'base_url' => env('PAC_AWS100_API_BASE_URL', 'api/v1'),
            'client_id' => env('PAC_AWS100_API_CLIENT_ID', 1),
            'client_secret' => env('PAC_AWS100_API_CLIENT_SECRET', 1)
        ]
    ],
    'current_edition_api' => [
        '0' => ['host' =>env('CURRENT_AWS_API_HOST', 'https://3.112.131.230/app-api/'),
            'base_url' =>env('CURRENT_AWS_API_BASE_URL', 'api/v1'),
            'client_id' =>env('CURRENT_AWS_API_CLIENT_ID', 1),
            'client_secret' =>env('CURRENT_AWS_API_CLIENT_SECRET', 1)],
        '1' => ['host' =>env('CURRENT_K5_API_HOST', 'https://133.162.146.120/app-api/'),
            'base_url' =>env('CURRENT_AWS_API_BASE_URL', 'api/v1'),
            'client_id' =>env('CURRENT_K5_API_CLIENT_ID', 1),
            'client_secret' =>env('CURRENT_K5_API_CLIENT_SECRET', 1)]
    ],
    'saml_url_prefix' => env('SAML_URL_PREFIX', 'sso'),
    'fujitsu_company_id' => env('FUJITSU_COMPANY_ID',0),
    'dummy_login' => env('DUMMY_LOGIN',false),
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

    'asset_url' => env('ASSET_URL', null),
    'office_convert_api_host' => env('OFFICE_CONVERT_API_HOST', 'http://localhost'),
    'office_convert_api_base' => env('OFFICE_CONVERT_API_BASE', '/api/'),
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
    'max_request' => env('CONSTRAINTS_MAX_REQUESTS'),

    'aes256_pass' => env('AES256_PASS'),
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
        Ipunkt\LaravelAnalytics\AnalyticsServiceProvider::class,
        Jenssegers\Agent\AgentServiceProvider::class,
        Barryvdh\Snappy\ServiceProvider::class,
        Omniphx\Forrest\Providers\Laravel\ForrestServiceProvider::class
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
        'PermissionUtils' => App\Http\Utils\PermissionUtils::class,
        'CommonUtils' => App\Http\Utils\CommonUtils::class,
        'Analytics' => Ipunkt\LaravelAnalytics\AnalyticsFacade::class,
        'Aacotroneo\Saml2\Saml2ServiceProvider' => App\Saml\Saml2ServiceProvider::class,
        'Aacotroneo\Saml2\Http\Controllers\Saml2Controller' => App\Saml\Saml2Controller::class,
        'Agent' => Jenssegers\Agent\Facades\Agent::class,
        'PDF' => Barryvdh\Snappy\Facades\SnappyPdf::class,
        'SnappyImage' => Barryvdh\Snappy\Facades\SnappyImage::class,
        'Forrest' => Omniphx\Forrest\Providers\Laravel\Facades\Forrest::class,

    ],

        /*
    |--------------------------------------------------------------------------
    | Download State Confirm Form's Reload Interval
    |--------------------------------------------------------------------------
    */

    'reload_interval' => 600000,

    /*
    |--------------------------------------------------------------------------
    | Whether it is an LGWAN server
    |--------------------------------------------------------------------------
    */

    'app_lgwan_flg' => env('APP_LGWAN_PRIVATE_FLG', false),

    's3_storage_root_folder' =>env('STORAGE_ROOT_FOLDER','long_term_document'),
    's3_storage_attachment_root_folder' =>env('STORAGE_ATTACHMENT_ROOT_FOLDER','long_term_attachment'),
    'k5_storage_attachment_root_folder' =>env('STORAGE_K5_ATTACHMENT_ROOT_FOLDER','attachment_document'),
    // PAC_5-2691
    'long_term_back_attachment_folder_pre' => env("LONG_TERM_BACK_ATTACHMENT_FOLDER_PRE",'long_term_back_attachment'),


    'timestamp_order_url' => env('TIMESTAMP_ORDER_URL', 'https://dstmp-order.shachihata.com/mypage'),
    /*
    |--------------------------------------------------------------------------
    | 特設サイトAPI
    |--------------------------------------------------------------------------
    */
    'special_app_api_host' => env('SPECIAL_APP_API_HOST', 'https://3.112.41.105/app-api/'),
    'special_app_api_base_url' => env('SPECIAL_APP_API_BASE_URL', 'api/v1'),
    'special_app_api_client_id' => env('SPECIAL_APP_API_CLIENT_ID', '1'),
    'special_app_api_client_secret' => env('SPECIAL_APP_API_CLIENT_SECRET', '1'),
];
