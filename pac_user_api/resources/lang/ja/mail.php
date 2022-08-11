<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Password Reset Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by the password broker for a password update attempt
    | has failed, such as for an invalid token or invalid new password.
    |
    */

    'prefix' => [
        'user' => '[Shachihata Cloud] ',
        'admin' => '[Shachihata Cloud：管理者] '
    ],

    'circular_user_template' => [
        'subject' => ':title - :author_user さんの回覧文書が届いています',
        'body' => ':receiver_name さん：\r\n\r\n
        :creator_name さんから以下の回覧文書が届いています。\r\n\r\n
        件名　　　：:mail_name\r\n
        メッセージ：:text\r\n
        ファイル名：:filenamestext\r\n\r\n
    :circular_approval_url_text
    Shachihata Cloudを利用して、回覧文書を確認・捺印することができます。\r\n
        回覧文書へのリンクをクリックするとShachihata Cloudの画面に移動します。\r\n\r\n
    ※この電子メールの内容を他の人と共有しないでください。\r\n\r\n
    この電子メールに記載された文書へのリンクを用いて、\r\n
        Shachihata Cloud上の文書にアクセスが可能です。\r\n
            他の人に見られることがないように、この電子メールの転送、\r\n
                   およびリンクの転記は控えてください。'
    ],

    'SendAccessCodeNoticeMail' => [
        'subject' => '回覧文書:title－アクセスコードのお知らせ',
        'body' => '回覧文書:titleのアクセスコードを通知します。\r\n
    アクセスコード：\r\n
                       :access_code'
    ],

    'circular_has_ended_template' => [
        'subject' => ':title - 文書の回覧が終了しました',
        'body' => ':receiver_name さん：\r\n\r\n
        以下の文書の回覧が終了しました。\r\n\r\n
        件名　　　：:mail_name\r\n
        メッセージ：:last_updated_text\r\n
        ファイル名：:filenamestext\r\n
        作成者　　：:author_email\r\n
        最終更新者：:last_updated_email\r\n\r\n
    :circular_approval_url_text
    Shachihata Cloudを利用して、回覧文書を確認・捺印することができます。\r\n
        文書をクリックするとShachihata Cloudの画面に移動します。\r\n\r\n
         この電子メールの内容をほかの人と共有しないでください\r\n
    メールに記載された文書へのリンクを用いて、\r\n
         Shachihata Cloudの文書にアクセスが可能です。\r\n
         他の人に見られることがないように、メールの転送、\r\n
                   および文書へのリンクの転記は控えてください。'
    ],

    'circular_pullback_template' => [
        'subject' => ':title - 回覧文書が撤回されました',
        'body' => ':receiver_name さん：\r\n\r\n
    以下の文書の回覧が:user_name さんに引戻しされました。\r\n\r\n
    件名　　　：:mail_name\r\n
    ファイル名：:docstext\r\n
    コメント　：:pullback_remark',
    ],

    'circular_user_request_sendback_template' => [
        'subject' => ':title - :user さんの差戻し依頼が届いています',
        'body' => ':receiver_name さん：\r\n\r\n
    :return_requester さんから以下の回覧文書の差戻し依頼が届いています。\r\n\r\n
    件名　　　：:mail_name\r\n
    メッセージ：:text\r\n
    ファイル名：:filenamestext\r\n\r\n
    :circular_approval_url_text
    Shachihata Cloudを利用して、回覧文書を確認・捺印することができます。\r\n
            文書をクリックするとShachihata Cloudの画面に移動します。 \r\n\r\n
              この電子メールの内容をほかの人と共有しないでください\r\n
            メールに記載された文書へのリンクを用いて、\r\n
                Shachihata Cloudの文書にアクセスが可能です。\r\n
                他の人に見られることがないように、メールの転送、\r\n
                および文書へのリンクの転記は控えてください。',
    ],

    'circular_user_viewed_template' => [
        'subject' => ':title - :user_email さんが回覧文書を閲覧しました',
        'body' => ':receiver_name さん：\r\n\r\n
    :browsing_user さんが以下の回覧文書を閲覧しました。\r\n\r\n
    件名　　　：:mail_name\r\n
    ファイル名：:filenamestext\r\n
    作成者　　：:author_email\r\n
    最終更新者：:last_updated_email\r\n\r\n
            Shachihata Cloudを利用して、回覧文書を確認・捺印することができます。\r\n
            文書をクリックするとShachihata Cloudの画面に移動します。\r\n\r\n
              この電子メールの内容をほかの人と共有しないでください\r\n
                   メールに記載された文書へのリンクを用いて、Shachihata Cloudの文書にアクセスが可能です。\r\n
                   他の人に見られることがないように、メールの転送、および文書へのリンクの転記は控えてください。',
    ],

    'circular_user_sendback_template' => [
        'subject' => ':title - 回覧文書が差戻しされました',
        'body' => ':receiver_name さん：\r\n\r\n
   :return_user さんから以下の回覧文書が差戻しされました。\r\n\r\n
   件名　　　：:mail_name\r\n
   メッセージ：:text\r\n
   ファイル名：:filenamestext\r\n
   作成者　　：:author_email\r\n
   最終更新者：:last_updated_email\r\n\r\n
    :circular_approval_url_text
               Shachihata Cloudを利用して、回覧文書を確認・捺印することができます。\r\n
            文書をクリックするとShachihata Cloudの画面に移動します。\r\n\r\n
              この電子メールの内容をほかの人と共有しないでください\r\n
                   メールに記載された文書へのリンクを用いて、Shachihata Cloudの文書にアクセスが可能です。\r\n
                   他の人に見られることがないように、メールの転送、および文書へのリンクの転記は控えてください。',
    ],

    'SendFinishMail' => [
        'subject' => 'パスワードの設定が完了しました',
        'body' => '対象企業：:company_name\r\n
            対象ID：:user_id\r\n\r\n
            いつもShachihata Cloudをご利用いただきありがとうございます。\r\n
      ご利用のアカウントに新しいパスワードを設定しました。\r\n ',
    ],

    'circular_notify_template' => [
        'subject' => ':title - :creator_nameさんの回覧文書が届いています（再送）',
        'body' => ':receiver_name さん：\r\n\r\n
    :creator_name さんから以下の回覧文書が届いています（再送）。\r\n\r\n
    件名　　　：:mail_name\r\n
    メッセージ：:text\r\n
    ファイル名：:docstext\r\n\r\n
    :circular_approval_url_text
          Shachihata Cloudを利用して、回覧文書を確認・捺印することができます。\r\n
           文書をクリックするとShachihata Cloudの画面に移動します。\r\n\r\n
                  この電子メールの内容をほかの人と共有しないでください\r\n
            メールに記載された文書へのリンクを用いて、Shachihata Cloudの文書にアクセスが可能です。\r\n
                   他の人に見られることがないように、メールの転送、および文書へのリンクの転記は控えてください。'
    ],

    'SendMailAlertFileExpired' => [
        'subject' => 'ファイル保存期間のお知らせ',
        'body' => 'まもなく、以下の回覧文書の保存期間が終了いたします。\r\n
    期間終了後は表示・保存ができなくなりますので、ご注意ください。\r\n\r\n
    対象の回覧文書：\r\n
    :circularDocuments\r\n\r\n
                     ※この電子メールの内容を他の人と共有しないでください。'
    ],

    'SendMailAlertDiskQuota' => [
        'subject' => '利用状況に関するお知らせ',
        'body' => 'まもなく、利用可能なディスク容量をオーバーします。\r\n
      不要になった回覧文書を削除してください。\r\n\r\n
      使用ディスク容量：\r\n
      :current_storage_sizeMB \r\n\r\n
      ディスク使用率：\r\n
      :current_storage_percent% \r\n\r\n
                  ※この電子メールの内容を他の人と共有しないでください。\r\n '
    ],

    'circular_user_renotify_template' => [
        'subject' => ':title - :creator_nameさんの回覧文書が届いています（再送）',
        'body' => ':receiver_name さん：\r\n\r\n
    :creator_name さんから以下の回覧文書が届いています。\r\n\r\n
    件名　　　：:mail_name\r\n
    メッセージ：:text\r\n
    ファイル名：:filenamestext\r\n\r\n
    :circular_approval_url_text
        Shachihata Cloudを利用して、回覧文書を確認・捺印することができます。\r\n
            回覧文書へのリンクをクリックするとShachihata Cloudの画面に移動します。\r\n\r\n
            ※この電子メールの内容を他の人と共有しないでください。\r\n
                   この電子メールに記載された文書へのリンクを用いて、Shachihata Cloud上の文書にアクセスが可能です。\r\n
                 他の人に見られることがないように、この電子メールの転送、\r\n
                   およびリンクの転記は控えてください。'
    ],

    'SendIpRestrictionMail' => [
        'subject' => 'ログイン通知',
        'body' => 'いつもShachihata Cloudをご利用いただきありがとうございます。\r\n
           ご利用のShachihata Cloudに対して、登録外のIPアドレスからログインが行われました。\r\n
            IPアドレス：:ipAddress \r\n
                     ユーザー：:user \r\n '
    ],

    'SendMfaMail' => [
        'subject' => '認証コードの発行',
        'body' => '対象企業：:company_name\r\n
            対象ID：:user_id\r\n\r\n
            いつもShachihata Cloudをご利用いただきありがとうございます。\r\n
           :otp \r\n
           この認証コードをログイン画面に入力してください。\r\n
           この認証コードの有効期限は :otpExpires  です。<\r\n
                   お客様がこのリクエストを行っていない場合、貴社の管理者までお問い合わせください。\r\n '
    ],

    'circular_updated_notify_template' => [
        'subject' => ':title - 回覧文書が更新されました',
        'body' => ':receiver_nameさん：\r\n\r\n
    以下の回覧文書が更新されました。\r\n\r\n
    件名　　　：:mail_name\r\n
    メッセージ：:last_updated_text\r\n
    ファイル名：:filenamestext\r\n
    作成者　　：:author_email\r\n
    最終更新者：:last_updated_email\r\n\r\n
        Shachihata Cloudを利用して、回覧文書を確認・捺印することができます。\r\n
            文書をクリックするとShachihata Cloudの画面に移動します。\r\n\r\n
            この電子メールの内容をほかの人と共有しないでください\r\n
                   メールに記載された文書へのリンクを用いて、Shachihata Cloudの文書にアクセスが可能です。\r\n
                   他の人に見られることがないように、メールの転送、および文書へのリンクの転記は控えてください。'
    ],

    'email_reset_link' => [
        'subject' => '初期パスワードのお知らせ',
        'body' => '対象企業：:company_name\r\n
            対象ID：:user_id\r\n\r\n
            いつもShachihata Cloudをご利用いただきありがとうございます。\r\n\r\n
            ご利用のShachihata Cloudアカウントに対して、初期パスワードを発行いたしました。\r\n
           パスワード: :password \r\n\r\n
            お客様がこのリクエストを行っていない場合、このままこのメールを削除してください。\r\n
            他人が不正にアカウントにアクセスしていると思われる場合は、\r\n
                   Shachihata Cloudの設定ページで、ただちにパスワードを変更してください。'
    ],

    'UserRegistrationCompleteMail' => [
        'subject' => 'ユーザ登録が完了しました',
        'body' => 'いつもShachihata Cloudをご利用いただきありがとうございます。\r\n
                   ご利用のアカウントのユーザ登録が完了しました。\r\n '
    ],

    'SendDownloadReserveCompletedMail' => [
        'subject' => 'ダウンロードファイルの作成完了のお知らせ',
        'body' => 'ダウンロードファイルの準備が完了しました。\r\n
            ダウンロード期限内にファイルのダウンロードをお願い致します。\r\n\r\n
            ファイル名：:file_name \r\n
            ダウンロード期限：:dl_period',
    ],

    'SendBoxRefreshTokenUpdateFailedMail' => [
        'subject' => 'BOX自動保管の更新トークン期限切れのお知らせ',
        'body' => '※このメールは自動的に配信しております。\r\n
                   :admin_name  様\r\n\r\n
                   Shachihata Cloudカスタマーサポートです。\r\n
                   この度は、弊社サービスをご利用頂き大変ありがとうございます。\r\n\r\n
                   BOX自動保管に関するトークンの自動更新が失敗しました。\r\n
                   下記のリンクから管理者としてログインを行い、\r\n
                   「BOX自動保管」メニューから再度設定を行ってください。\r\n
                   https://app.shachihata.com/admin\r\n\r\n
                   BOX自動保管の設定方法\r\n
                   https://help.dstmp.com/help/box-enabled-auto-storage/',
    ],

    'SendCsvSuccessMail' => [
        'subject' => '情報取得リクエスト（正常終了）',
        'body' => '下記の情報取得リクエストが正常終了しました。\r\n
                   ファイルは、:file_path を参照してください。\r\n\r\n
                   [処理番号] :id \r\n
                   [コマンド] :command \r\n
                   [受付時間] :request_datetime \r\n
                   [実行開始時間] :execution_start_datetime \r\n
                   [実行終了時間] :execution_end_datetime \r\n',
    ],

    'SendCsvFailedMail' => [
        'subject' => '情報取得リクエスト（失敗）',
        'body' => '下記の情報取得リクエストが失敗しました。\r\n\r\n
                   [処理番号] :id \r\n
                   [コマンド] :command \r\n
                   [受付時間] :request_datetime \r\n
                   [実行開始時間] :execution_start_datetime \r\n
                   [実行開始時間] :execution_start_datetime \r\n
                   [メッセージ] :message1 \r\n',
    ],
    'SendDiskFileMail' => [
        'subject' => ':name さん（:email） からファイルメール便が届いています',
        'body' => ':name さん（:email） からファイルメール便が届いています。\r\n\r\n
        件名　　　：:title\r\n
        メッセージ：:mail_text\r\n
        ファイル名：:file_names_text\r\n
        ダウンロードリンク：:download_link\r\n'
    ],
    'SendDiskFileAccessCodeMail' => [
        'subject' => 'ファイルメール便 セキュリティコードのお知らせ',
        'body' => ':title　のセキュリティコード通知します。\r\n\r\n
        セキュリティコード：:access_code\r\n'
    ],
    'circular_skip_template' => [
        'subject' => ':title - 回覧文書のスキップ通知',
        'body' => ':receiver_name さん：\r\n\r\n
    以下の文書をスキップされました。\r\n\r\n
    件名　　　：:mail_name\r\n
    ファイル名：:docstext',
    ],

    'hr_work_report_sendback_template' => [
        'subject' => ':title - 勤務表が差戻しされました',
        'body' => ':receiver_name さん：\r\n\r\n
        :return_user さんから以下の勤務表が差戻しされました。\r\n\r\n
        勤務月　　　：:mail_name\r\n
        作成者　　：:author_email\r\n
        最終更新者：:last_updated_email\r\n\r\n
        この電子メールの内容をほかの人と共有しないでください\r\n
        メールに記載された文書へのリンクを用いて、Shachihata Cloudの文書にアクセスが可能です。\r\n
        他の人に見られることがないように、メールの転送、および文書へのリンクの転記は控えてください。',
    ],
    
    'to_do_list_deadline_notice' => [
        'subject' => ':task_typeタスク「:title」は締め切り日に達しました。',
        'body' => ':task_typeタスク「:title」は締め切り日に達しました。\r\n\r\n
        タイトル\r\n
        :title \r\n\r\n
        期限日\r\n
        :deadline \r\n\r\n
        優先度\r\n
        :important \r\n\r\n
        詳細\r\n
        :content \r\n\r\n',
    ],

    'SendHrWorkNotice' => [
        'subject' => '勤怠連絡',
        'body' => '[送信者]: :name\r\n
        [送信者メールアドレス]: :email\r\n\r\n
        :text\r\n\r\n
        :signature',
    ],
    'SendWorkDetailSubmissionMail' => [
        'subject' => '勤務表提出',
        'body' => ':admin_name さん\r\n
        :user_name さんから勤務表が提出されました。\r\n\r\n
        勤務月： :working_month\r\n',
    ],
    'SendApprovalWorkDetailMail' => [
        'subject' => '勤務表承認',
        'body' => ':user_name さん\r\n
        :admin_name さんから勤務表が承認されました。\r\n\r\n
        勤務月： :working_month\r\n',
    ],
    'SendRemandWorkDetailMail' => [
        'subject' => '勤務表差戻し',
        'body' => ':user_name さん\r\n
        :admin_name さんから勤務表が差戻しされました。\r\n\r\n
        勤務月： :working_month\r\n',
    ],
];
