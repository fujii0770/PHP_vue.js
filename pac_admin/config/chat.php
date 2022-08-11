<?php

/**
 * チャット用
 */

return [
    // ECSおよびECRアクセス用アクセスキー
    'aws_key' => env('CHAT_AWS_ACCESS_KEY_ID'),
    // ECSおよびECRアクセス用パスワード
    'aws_secret' => env('CHAT_AWS_SECRET_ACCESS_KEY'),
    // ECSおよびECRアクセス用リージョン
    'aws_region' => env('CHAT_AWS_DEFAULT_REGION', "ap-northeast-1"),
    // ECSコンテナエージェントにAWS API呼び出しを行う権限を付与するタスク実行ロールのARN
    'aws_execution_role_arn' => env('CHAT_AWS_TASKROLE_ARN'),
    // タスクのコンテナが引き受けることができるIAMロール
    'aws_task_role_arn' => env('CHAT_AWS_TASKROLE_ARN'),
    // コンテナに設定するロググループ {key}=テナントキー  旧：CHAT_AWS_LOGS_GROUP
    'aws_tenant_logs_group' => env('CHAT_AWS_TENANT_LOGS_GROUP', "ecs-logs"),
    // コンテナに設定するログドライバー
    "aws_tenant_log_driver" => env('CHAT_AWS_TENANT_LOGS_DRIVER', "awslogs"),
    // コンテナに設定するログのリージョン
    "aws_tenant_logs_region" => env('CHAT_AWS_TENANT_LOGS_REGION', "ap-northeast-1"),
    // コンテナに設定する awslogs-stream-prefix の値
    "aws_tenant_logs_stream_prefix" => env('CHAT_AWS_TENANT_LOGS_STREAM_PREFIX', "stalk"),
    // コンテナに設定するメモリ量
    "aws_tenant_memory_reservation" => env('CHAT_AWS_TENANT_MEMORY_RESERVATION', 500),

    // S3のバケットとルート
    'aws_s3_bucket_root' => env('CHAT_AWS_S3_BUCKET_ROOT'),
    // S3アクセスキー
    'aws_s3_key' => env('CHAT_AWS_S3_ACCESS_KEY_ID'),
    // S3シークレットキー
    'aws_s3_secret' => env('CHAT_AWS_S3_SECRET_ACCESS_KEY'),
    // S3リージョン
    'aws_s3_region' => env('CHAT_AWS_S3_REGION', "ap-northeast-1"),
    // チャットサーバーに設定する管理ユーザー名
    'default_admin_username' => env('CHAT_DEFAULT_ADMIN_USERNAME', "admin01"),
    // チャットサーバーの管理ユーザパスワード
    'default_admin_password' => env('CHAT_DEFAULT_ADMIN_PASSWORD', "pass@1234"),
    // チャットサーバーの管理ユーザーメアド
    'default_admin_email' => env('CHAT_DEFAULT_ADMIN_EMAIL'),

    // チャットサーバーへのログイン最大再試行回数
    'login_retries' => env('CHAT_LOGIN_RETRIES', 10),
    // チャットサーバーへのログイン再試行待機秒数
    'login_sleep_seconds' => env('CHAT_LOGIN_SLEEP_SECONDS', 5),
    // チャットサーバーからの初期化依頼受付用RESTサービスのパス
    'rest_path_for_init' => "/api/chat/initServer",

    // チャットサーバー上のテナント管理者用ロール名
    'tenant_admin_role_name' => env('CHAT_TENANT_ADMIN_ROLENAME', ""),
    // チャットサーバー上のテナントユーザー用ロール名
    'tenant_user_role_name' => env('CHAT_TENANT_USER_ROLENAME', "user"),

    // MONGO DB の OPLOG のDB名とパラメータ
    "mongo_oplog_db" => env('CHAT_MONGO_OPLOG_DB', "local?replSet=rs01"),
    // MONGO DB の URLに追加するパラメータ
    "mongo_option" => env('CHAT_MONGO_OPTION', "replSet=rs01"),

    // SMTPプロトコル "smtp" or "smtps"
    "smtp_protocol" => env('CHAT_SMTP_PROTOCOL', "smtp"),
    // SMTPホスト名 "smtp.example.com"
    "smtp_host" => env('CHAT_SMTP_HOST'),
    // SMTPポート番号
    "smtp_port" => env('CHAT_SMTP_PORT', 465),
    // TLSを無視？
    "smtp_ignore_tls" => false,
    // SMTP POOL
    "smtp_pool" => true,
    // SMTPユーザー名
    "smtp_username" => env('CHAT_SMTP_USERNAME'),
    // SMTPパスワード
    "smtp_password" => env('CHAT_SMTP_PASSWORD'),
    // SMTP送信メールアドレス
    "smtp_from" => env('CHAT_SMTP_FROM_EMAIL'),
];
