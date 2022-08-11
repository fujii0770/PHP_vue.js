<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),
    'enable_log_operation' => env('ENABLE_LOG_OPERATION', true),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'tap'    => [App\Logging\CustomizeFormatter::class],
            'level' => 'debug',
            'days' => 30,
        ],

        'cron-daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel-cron.log'),
            'tap'    => [App\Logging\CustomizeFormatter::class],
            'level' => 'debug',
            'days' => 14,
        ],

        'trial-daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel-trial.log'),
            'level' => 'debug',
            'days' => 30,
        ],

        'import-csv-daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel-import-csv.log'),
            'level' => 'debug',
            'days' => 30,
        ],

        'logstash' => [
            'driver' => 'daily',
            'tap' => [App\Logging\CustomFilenames::class],
            'path' => storage_path('logs/logstash.log'),
            'level' => 'debug',
            'days' => 2,
        ],
        'sync-company-to-gw' => [
            'driver' => 'daily',
            'path' => storage_path('logs/sync-company-to-gw.log'),
            'level' => 'debug',
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],
    ],

];
