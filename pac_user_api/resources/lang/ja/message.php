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
    'info' => [
        'auto_storage' => [
            'local_request' => '本環境BOX自動保存リクエストがあります。{company_id=:company_id, circular_id=:circular_id}',
        ],
    ],

    // 警告
    'warning' => [
        'not_permission_access' => 'アクセス許可がありません！',
        'userCheck' => 'ユーザ有効チェック失敗。email：:email, env：:env, server：:server',
        'download_request' => [
            'download_file_max' => 'ダウンロード要求が最大件数に達しています。時間を置いてから実行してください。',
            'download_size_max' => 'ダウンロード容量が最大に達しています。ダウンロードまたは削除をしてから実行してください。',
            'order_size_max' => '容量上限を超えた予約です。選択項目を変更してください。',
            'req_file_max' => '保存中のダウンロードフォルダ数が上限に達したため、ダウンロードできません。<br/>ダウンロードフォルダを削除してから実行してください。',
            'sanitize_req_max' => 'ダウンロードファイルの無害化要求が最大件数に達しています。時間を置いてから実行してください。',
        ],
        'doc＿not_exist' => '回覧文書を見つかりません。',
        'attachment_request' => [
            'upload_attachment_count_max' => 'アップロードできる合計のファイルの総数は :max_attachment_count 以内です',
            'upload_attachment_size_max' => 'アップロードできる合計のファイルサイズは :max_total_attachment_size GB以内です',
            'not_exist' => '添付ファイル存在しませんまたは削除しました。',
            'storage_upper_limit' => 'このファイルはアップロードできません。データ容量(:size)を超えています。',
            'upload_attachment_upper_limit' => 'アップロードできる合計のファイルサイズは :file_max_attachment_size MB 以内です。',
        ],
        'disk_mail' => [
            'access_code' => 'セキュリティコードが正しくありません。',
            'expiration_date' => 'ダウンロードリンクの有効期限が切れています。',
            'download_limit' => 'ダウンロード回数の上限を超えています。',
            'not_exit' => '送信者によってダウンロードリンクが削除されました。',
        ],
        'form_issuance' => [
            'no_template' => 'テンプレート情報が空です。',
        ],
    ],

    //成功
    'success' => [
        'reset_pass_was_send_mail' => '指定したメールアドレスに、初期パスワードの通知メールを送信しました。',
        'userCheck' => 'ユーザ有効チェック処理に成功しました。',
        'download_request' => [
            'get_data' => '送信文書の取得処理に成功しました。',
            'file_delete' => '文書削除処理に成功しました。',
            'download_file' => 'ダウンロード処理に成功しました。',
            're_order' => 'ダウンロードを再予約しました。',
            'download_ordered' => 'ダウンロードを予約しました。<br/>  :attribute',
            'download_ordered_sanitizing' => 'ダウンロードを予約しました。',
            'sanitizing_update' => '無害化状態を更新しました。',
        ],
        'update_circular_status' => '回覧更新処理に成功しました。',
        'data_get' => ':attributeの取得処理に成功しました。',
        'doc_histories_get' => '回覧文書と履歴の取得処理に成功しました。',
        'attachment_request' => [
            'get_data' => '添付ファイルの取得に成功しました',
            'store_attachment' => '添付ファイル処理に成功しました。',
            'delete_attachment' => '添付ファイル削除処理に成功しました。',
            'download_attachment' => '添付ファイルのダウンロードに成功しました。',
        ],
        'create_circular_company' => '企業取得に成功しました',
        'long_term_save' => '回覧の長期保管をできました。',
        'disk_mail' => [
            'upload' => 'ファイル削除処理に成功しました。',
            'get' => 'ファイルメール便取得処理に成功しました。',
            'send' => 'ファイルメール便送信処理に成功しました。',
            'delete' => 'ファイル削除処理に成功しました。',
            'download' => 'ファイルダウンロード処理に成功しました。',
            'getTemplate' => 'テンプレート取得処理に成功しました。',
            'updateTemplate' => 'テンプレート更新処理に成功しました。',
            'sendAgain' => 'ファイルメール便再送信処理に成功しました。',
        ],
        'approval_update' => '一括承認しました。',
        'submission_state_update' => '差戻しました。',
        'long_term_folder_get' => 'フォルダの取得処理に成功しました。',
        'long_term_folder_move' => '文書の移動処理に成功しました。',
        'favorite_update' => 'お気に入りの編集に成功しました。',
        'favorite_item_sort' => '回覧順の編集に成功しました。',
        'favorite_item_delete' => '回覧順の編集削除に成功しました。',
        'to_do_list' => [
            'add' => ':attributeが正常に追加されました。',
            'update' => ':attributeが正常に更新されました。',
            'delete' => ':attributeが正常に削除されました。',
            'done' => 'タスク完了操作は正常に処理されました。',
            'revoke' => 'タスクを元に戻す操作は成功しました。',
            'detail' => '正常に取得された:attribute。',
            'get_scheduler_data' => 'ToDoリストはスケジューラデータを正常に取得します。',
            'add_scheduler' => 'ToDoリストスケジューラが正常に追加されました。',
            'update_scheduler' => 'ToDoリストスケジューラが正常に更新されました。',
            'delete_scheduler' => 'ToDoリストスケジューラが正常に削除されました。',
        ],
        'hr_mail_setting' => [
            'get' => '勤怠連絡メール設定情報取得処理に成功しました。',
            'update' => '勤怠連絡メール設定が正常に更新されました。',
            'send' => '勤怠連絡送信に成功しました。',
        ],
    ],

    //失敗
    'false' => [
        'save_company_stamp' => '共通印名称更新処理に失敗しました。',
        'userCheck' => 'ユーザ有効チェックエラーが発生。email：:email',
        'api_authentication' => 'API認証失敗しました。',
        'form_issuance' => [
            'template_code' => '明細テンプレートコードを存在しません。',
            'csv_upload' => 'CSVのアップロードに失敗しました。CSVファイルであるかご確認ください。',
            'template_disabled' => '無効明細テンプレートをインポートできない。',
            'template_complete' => '完了保存以外明細テンプレートをインポートできない。',
            'template_other' => '他社登録する明細テンプレートをインポートできない。',
            'text_size' => '追加テキスト内容に30桁を超えています。',
            'max_frm_document' => 'CSVで作成する文書数により今月の発行文書数が上限に達します。',
            'over_max_frm_document' => '作成可能な文書の最大件数(:max_frm_document件)を越えたため処理中止します。',
            'template_code_input' => ' 明細テンプレートの設定に失敗しました。<br/>明細テンプレートコードを入力してください。',
            'template_code_used' => ' 明細テンプレートの設定に失敗しました。<br/>明細テンプレートコードはすでに使われています。',
            'template_code_check' => ' 明細テンプレートの設定に失敗しました。<br/>英語文字または数字のみご入力してください。',
        ],
        'download_request' => [
            'get_data' => 'ダウンロードファイル取得に失敗しました。も一度直してください',
            'file_delete' => '文書削除処理に失敗しました。',
            'limit_setting_get' => 'ダウンロード制限設定がされていないため予約できません。管理者に問い合わせください。',
            'file_detail_get' => '文書情報を取得できませんでした',
            'download_ordered' => 'ダウンロードを予約出来ませんでした。<br/>:attribute',
            'download_ordered_sanitizing' => 'ダウンロードファイル数が上限に達したため、一部ダウンロードできません。<br/>:attribute',
            'zip_create' => '( Zipファイル生成に失敗しました。 )',
            'compress_e' => '圧縮処理に失敗しました。[ E :attribute | :path ]',
            'compress_n' => '圧縮処理に失敗しました。[ N :attribute | :path ]',
            'doc_histories_request' => '回覧:attribute<br/>の履歴を取得することが失敗です。',
            'login' => 'ログイン情報を確認出来ませんでした。',
            'method' => 'ダウンロードの予約が出来ませんでした。:attribute',
            'download_period' => 'ダウンロードファイルの有効期限が切れています。',
            'sanitizing_update' => '無害化状態更新に失敗しました。',
        ],
        'update_circular_status' => '回覧更新処理に失敗しました。',
        'doc_histories_get' => '回覧文書と履歴の取得処理に失敗しました。 :attribute',
        'invalidUser' => '利用者が削除、もしくは無効になっています。',
        'auth_client' => 'ユーザ有効チェックエラーが発生。email：:email',
        'auto_storage' => [
            'circular_not_exist' => 'BOX自動保存：指定された回覧文書は存在しません。',
            'no_settings' => 'BOX自動保存：企業設定取得失敗しました。{company_id=:company_id}',
            'bad_request' => 'BOX自動保存：他環境にリクエスト連携失敗しました。{circular_id=:circular_id}',
            'token' => 'BOX自動保存：トークン取得失敗しました。{company_id=:company_id}',
            'create_folder' => 'BOX自動保存：フォルダの作成に失敗しました。{circular_id=:circular_id}',
            'create_documents' => 'BOX自動保存：連携文書作成に失敗しました。{circular_id=:circular_id}',
            'send_document' => 'BOX自動保存：文書連携に失敗しました。{circular_id=:circular_id}',
        ],
        'attachment_request' => [
            'get_data' => '添付ファイルの取得に失敗しました',
            'store_attachment' => '添付ファイル処理に失敗しました。',
            'delete_attachment' => '添付ファイル削除処理に失敗しました。',
            'chang_confidential_flg' => '添付ファイルの「社外秘に設定」更新処理に失敗しました。',
            'download_attachment' => 'ファイルのダウンロードに失敗しました',
            'check_fail' => 'ウイルスチェックに失敗しました。',
            'is_checking' => 'ウイルスチェックが完了していないため、ダウンロードができません。しばらくお待ちください。',
        ],
        'create_circular_company' => '企業取得に失敗しました',
        'long_term_save' => '回覧の長期保管をできませんでした',
        'disk_mail' => [
            'upload' => 'ファイルアップロード処理に失敗しました。',
            'delete' => 'ファイル削除処理に失敗しました。',
            'get' => 'ファイルメール便取得処理に失敗しました。',
            'send' => 'ファイルメール便送信処理に失敗しました。',
            'download' => 'ファイルダウンロード処理に失敗しました。',
            'getTemplate' => 'テンプレート取得処理に失敗しました。',
            'updateTemplate' => 'テンプレート更新処理に失敗しました。',
            'sendAgain' => 'ファイルメール便再送信処理に失敗しました。',
        ],
        'long_term_folder_get' => 'フォルダの取得処理に失敗しました。',
        'long_term_folder_move' => '文書の移動処理に失敗しました。',
        'system_error' => 'システムエラー発生しました。',
        'skip_before_node_has_handler' => '未承認の操作があります。',
        'skip_handler_error' => 'スキップ操作が失敗しました。',
        "sendAllUserNotExists" => '利用者为空：利用者を追加してください',
        "long_term_folder_permission" => 'このフォルダを表示する権限がありません。',
        'favorite_update' => 'お気に入りの編集に失敗しました。',
        'favorite_item_sort' => '回覧順の編集に失敗しました。',
        'favorite_item_delete' => '回覧順の編集削除に失敗しました。',
        'to_do_list' => [
            'list_not_exist' => 'このやることリストは存在しません。',
            'task_not_exist' => 'タスクが存在しません。',
            'circular_not_exist' => '文書が存在しません。',
            'get_scheduler_data' => 'ToDoリストがスケジューラデータの取得に失敗しました。',
            'add_scheduler' => 'ToDoリストスケジューラの追加に失敗しました。',
            'update_scheduler' => 'ToDoリストスケジューラの更新に失敗しました。',
            'delete_scheduler' => 'ToDoリストスケジューラの削除に失敗しました。',
            'done_circular' => 'ToDoリスト文書タスクステータスの更新に失敗しました。',
        ],
        'gw' => [
            'failed' => '大変申し訳ございません。アクセス集中の為、データの取得、または更新に失敗しました。<br/>お手数をおかけしますが、時間を置いてから再度お試しください。',
            'closed' => '大変申し訳ございません。アクセス集中の為、データの取得、または更新に失敗しました。<br/>お手数をおかけしますが、時間を置いてから再度お試しください。',
        ],
        'hr_mail_setting' => [
            'get' => '勤怠連絡メール設定情報取得処理に失敗しました。',
            'update' => '勤怠連絡メール設定更新処理に失敗しました。',
            'contact_count_min' => '勤怠連絡設定-連絡先を見つかりません。',
            'contact_count_max' => '勤怠連絡設定-連絡先は最大5件まで。',
            'send' => '勤怠連絡送信に失敗しました。',
        ],
    ],

];
