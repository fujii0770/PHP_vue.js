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

    'id_app_api_host' => env('ID_APP_API_HOST', ''),
    'id_app_api_base_url' => env('ID_APP_API_BASE_URL', ''),
    'id_app_api_client_id' => env('ID_APP_API_CLIENT_ID', ''),
    'id_app_api_client_secret' => env('ID_APP_API_CLIENT_SECRET', ''),

    'mfa_interval_days' => env('MFA_INTERVAL_DAYS', 30),
    'mfa_login_situation_max' => env('mfa_login_situation_max', 3),
    'mail_environment_prefix' => env('MAIL_ENVIRONMENT_PREFIX', ''),
    's3_storage_root_folder' =>env('STORAGE_ROOT_FOLDER','long_term_document'),
    's3_storage_attachment_root_folder' =>env('STORAGE_ATTACHMENT_ROOT_FOLDER','long_term_attachment'),
    's3_storage_root_folder_bbs' =>env('STORAGE_ROOT_FOLDER_BBS','bulletin_board_file'),
    's3_storage_root_folder_expense_settlement' =>env('STORAGE_ROOT_FOLDER_ES','expense_settlement_file'),
    'k5_storage_attachment_root_folder' =>env('STORAGE_K5_ATTACHMENT_ROOT_FOLDER','attachment_document'),
    's3_storage_form_template_folder' =>env('STORAGE_FORM_TEMPLATE_FOLDER','form_template'),
    's3_storage_form_template_folder_type' =>env('STORAGE_FORM_TEMPLATE_FOLDER_TYPE','template'),
    's3_storage_form_import_folder_type' =>env('STORAGE_FORM_IMPORT_FOLDER_TYPE','imp'),
    's3_storage_exp_template_folder_type' =>env('STORAGE_EXP_TEMPLATE_FOLDER_TYPE','exp'),

    'long_term_back_attachment_folder_pre' => env("LONG_TERM_BACK_ATTACHMENT_FOLDER_PRE",'long_term_back_attachment'),

    's3_storage_template_file_name_prefix' =>env('STORAGE_TEMPLATE_FILE_NAME_PREFIX','form_template_'),
    's3_storage_exp_template_file_name_prefix' =>env('STORAGE_EXP_TEMPLATE_FILE_NAME_PREFIX','form_exp_template_'),
    's3_storage_exp_template_file_out_name_prefix' =>env('STORAGE_EXP_TEMPLATE_FILE_OUT_NAME_PREFIX','form_exp_template_out_'),
    's3_storage_import_file_name_prefix' =>env('STORAGE_IMPORT_FILE_NAME_PREFIX','form_imp_'),

    's3_imprintservice_root_folder' =>env('STORAGE_FORM_TEMPLATE_FOLDER','imprintservice'),
    's3_form_template_folder' =>env('STORAGE_FORM_TEMPLATE_FOLDER','form_template'),
    's3_form_template_folder_type' =>env('STORAGE_FORM_TEMPLATE_FOLDER_TYPE','template'),
    's3_form_import_folder_type' =>env('STORAGE_FORM_IMPORT_FOLDER_TYPE','imp'),
    'formissuance_import_limit' =>env('FORMISSUANCE_IMPORT_LIMIT', 100),
    's3_form_csv_import' =>env('STORAGE_FORM_CSV_IMPORT_FOLDER', 'csv_import'),

    'libreoffice_location' => env('LIBREOFFICE_LOCATION', '/tmp/.config/libreoffice/4/user'),

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

    /*
    |--------------------------------------------------------------------------
    | Server Application Environment
    |--------------------------------------------------------------------------
    |
    |
    */

    'server_env' => env('APP_SERVER_ENV', 0),  //env_flg
    'edition_flg' => env('APP_EDITION_FLV', 1), //edition_flg
    'server_flg' => env('APP_SERVER_FLG', 0), //server_flg

    //ログインURL：key : edition_flg + env_flg + server_flg
    'server_app_env_url' => [
        '000' => env('PAC_APP_AWS_ENV_URL', 'https://3.112.131.230/app/'), //現行AWSログインURL
        '100' => env('NE_APP_AWS_ENV_URL', 'https://3.112.131.230/app/'), //新エディションAWSログインURL-aap1
        '101' => env('NE_APP_AWS2_ENV_URL', 'https://3.112.131.230/app/'), //新エディションAWSログインURL-aap2
        '102' => env('NE_APP_AWS3_ENV_URL', 'https://3.112.131.230/app/'), //新エディションAWSログインURL-aap3
        '103' => env('NE_APP_AWS4_ENV_URL', 'https://3.112.131.230/app/'), //新エディションAWSログインURL-aap4
        '104' => env('NE_APP_AWS5_ENV_URL', 'https://3.112.131.230/app/'), //新エディションAWSログインURL-aap5
        '105' => env('NE_APP_AWS6_ENV_URL', 'https://3.112.131.230/app/'), //新エディションAWSログインURL-aap6
        '106' => env('NE_APP_AWS7_ENV_URL', 'https://3.112.131.230/app/'), //新エディションAWSログインURL-aap7
        '107' => env('NE_APP_AWS8_ENV_URL', 'https://3.112.131.230/app/'), //新エディションAWSログインURL-aap8
        '010' => env('PAC_APP_K5_ENV_URL', 'https://133.162.146.120/app/'), //現行K5ログインURL
        '110' => env('NE_APP_K5_ENV_URL', 'https://3.112.131.230/app/'),//新エディションK5ログインURL
        '10100' => env('NE_APP_AWS100_ENV_URL', 'https://3.112.131.230/app/'), //新エディションAWSログインURL-lgapp
    ],

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
        '0' => [
            'host' => env('CURRENT_AWS_API_HOST', 'https://3.112.131.230/app-api/'),
            'base_url' => env('CURRENT_AWS_API_BASE_URL', 'api/v1'),
            'client_id' => env('CURRENT_AWS_API_CLIENT_ID', 1),
            'client_secret' => env('CURRENT_AWS_API_CLIENT_SECRET', 1)
        ],
        '1' => [
            'host' => env('CURRENT_K5_API_HOST', 'https://133.162.146.120/app-api/'),
            'base_url' => env('CURRENT_AWS_API_BASE_URL', 'api/v1'),
            'client_id' => env('CURRENT_K5_API_CLIENT_ID', 1),
            'client_secret' => env('CURRENT_K5_API_CLIENT_SECRET', 1)
        ]
    ],

    'create_users_csv_path' => env('CREATE_USERS_CSV_PATH',''),
    'enable_push_notification' => env('ENABLE_PUSH_NOTIFICATION',false),
    'fujitsu_company_id' => env('FUJITSU_COMPANY_ID',0),

    'guzzle_timeout' => env('GUZZLE_TIMEOUT', 30),
    'guzzle_connect_timeout' => env('GUZZLE_CONNECT_TIMEOUT', 30),

    'enable_sso_login' => env('ENABLE_SSO_LOGIN',true),

    'saml_url_prefix' => env('SAML_URL_PREFIX', 'sso'),

    'app_lgwan_flg' => env('APP_LGWAN_PRIVATE_FLG', false), // サーバがLGWAN環境下かどうか


    'add_timestamp_start_time' => env('ADD_TIMESTAMP_START_TIME_DEFAUT', '0 2 * * *'),
    'add_timestamp_end_time' => env('ADD_TIMESTAMP_END_TIME_DEFAUT', '05:00'),

    // 企業毎の利用量集計
    'notice_over_storage_percent' => env('NOTICE_OVER_STORAGE_PERCENT', 90), // 90％になったタイミングでメール通知
    'use_storage_base' => env('USE_STORAGE_BASE', 1.3), // 使用容量ベース

    'max_issu_export_count' => env('MAX_ISSU_EXPORT_COUNT', 100), //帳票一覧 Export最大件数

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
    'old_app_url' => env('OLD_APP_URL', 'http://localhost'),
    'new_app_url' => env('NEW_APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    'bizcard_show_url' => env('BIZCARD_SHOW_URL', 'http://localhost'),

    'stamp_api_host' => env('STAMP_API_HOST','http://localhost'),

    'stamp_api_base' => env('STAMP_API_BASE','/api/'),

    'ocr_api_host' => env('OCR_API_HOST', 'http://localhost'),

    'office_convert_api_host' => env('OFFICE_CONVERT_API_HOST', 'http://localhost'),
    'office_convert_api_base' => env('OFFICE_CONVERT_API_BASE', '/api/'),

    'gw_domain' => env('GW_DOMAIN',''),

    'gw_use' => env('GW_USE',''),

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
    'circular_approval_url' => env('CIRCULAR_APPROVAL_URL', 'http://localhost'),
    'circular_approval_lgwan_public_url' => env('CIRCULAR_APPROVAL_LGWAN_PUBLIC_URL', ''),

    'Regulations_work_start_time_default' => env("WORK_START_TIME_DEFAULT", '09:00'),
    'Regulations_work_end_time_default' => env("WORK_START_END_DEFAULT", '18:00'),
    'overtime_unit_default' => env("OVERTIME_UNIT_DEFAULT", 15),
    'break_time_default' => env("BREAK_TIME_DEFAULT", 60),

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

    'aes256_pass' => env('AES256_PASS'),

    'cipher' => 'AES-256-CBC',

    'stamp_api_base_url' => env('STAMP_API_BASE_URL'),

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
        LaravelFCM\FCMServiceProvider::class,

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
        Barryvdh\Cors\ServiceProvider::class,
		Barryvdh\Snappy\ServiceProvider::class,
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
        'Image' => Intervention\Image\Facades\Image::class,
		'PDF' => Barryvdh\Snappy\Facades\SnappyPdf::class,
		'SnappyImage' => Barryvdh\Snappy\Facades\SnappyImage::class,
        'Laravel\Passport\Guards\TokenGuard' => App\Auth\TokenGuard::class,
        'FCM'      => LaravelFCM\Facades\FCM::class,

    ],

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
