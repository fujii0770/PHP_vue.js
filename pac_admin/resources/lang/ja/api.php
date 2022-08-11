<?php

return [

    // 通知
    'notice' => [],

    //情報
    'info' => [],

    // 警告
    'warning' => [
        'not_permission_access' => 'アクセス許可がありません！',
        'not_detected' => ':attributeが見つかりません',
    ],

    //成功
    'success' => [


    ],

    //失敗
    'fail' => [
        'system_error' => 'システムエラーが発生しました。',
        'api_authentication' => 'API認証に失敗しました。',
        'value_not_exists' => '指定した:columnが存在しません。( :value )',
        'request_parameter_is_missing' => 'リクエストパラメータが不足しています。{:parameter_name}',
        'exclusive' => '他の処理によりデータが変更または削除されています。（排他制御）',
        'data_not_found' => 'データが見つかりませんでした。',
        'chat' => [
            'pacid_api_call' => "統合ID-APIの呼び出しに失敗しました。(:uri , code :code)",
            'exservice_api_call' => ":serviceの呼び出しに失敗しました。(:uri , code :code)"
        ],
    ],

    //項目
    'columns' => [
        'company' => '企業',
        'pac_id_api' => '統合ID-API',
        'chat_server_api' => 'チャットサーバーAPI',
        'chat'=> [
            'system_name' => 'ササッとTalk',
            'site_name' => 'ササッとTalk - :tenant',
        ],
    ]
];
