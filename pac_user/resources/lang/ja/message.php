<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    // 通知
    'notice' => [],

    //情報
    'info' => [],

    // 警告
    'warning' => [
        'not_permission_access' => 'アクセス許可がありません！',
        'attachment_request' =>[
            'upload_attachment_size_max' => 'アップロードできる合計のファイルサイズは :file_max_attachment_size MB 以内です',
        ],
        'disk_mail_file' =>[
            'upload_size_max' => 'アップロードできるファイルサイズは :max_size MB 以内です',
        ],
    ],

    //成功
    'success' => [
        'save_date' => '日付印設定を更新しました。',
    ],

    //失敗
    'false' => [
        'save_company_stamp' => '共通印名称更新処理に失敗しました。',
        'attachment_request' => [
            'get_data' =>'添付ファイルの取得に失敗しました',
            'delete_attachment'=>'添付ファイル削除処理に失敗しました。',
            'file_attribute' => 'ファイル属性の取得に失敗しました。も一度やり直してください',
            'file_upload' => 'アップロード処理に失敗しました。',
        ],
    ],

];
