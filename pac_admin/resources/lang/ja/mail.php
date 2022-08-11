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

    'SendExportDepartmentAlert' => [
        'subject' => '部署CSVダウンロード',
        'body' => ':adminName 様 \r\n
                   Shachihata Cloudの部署情報ダウンロードファイルの作成が完了しましたので、ご連絡致します。'
    ],

    'trialDuplicateErrorMail' => [
        'subject' => 'ShachihataCloudトライアル重複登録エラー',
        'body' => 'このメールは自動送信されたメールです。\r\n
            以下の内容でトライアルがありましたが、開始できませんでした。\r\n
            --------------------------------------------------------------------------\r\n
            申込日時 :  :nowdete \r\n
            対象商品 : Shachihata Cloud トライアル\r\n
            会社名 : :nickname \r\n
            会社名フリガナ : :kananame \r\n
            :agcodetext
            :agcouponcodetext
            電話番号 : :telno \r\n
            業種 : :industry \r\n
            部署・役職名 : :group :post \r\n
            お名前 : :familyname :givenname \r\n
            お名前フリガナ: :familynameKana :givennameKana \r\n
            担当者の電話番号： :telno2 \r\n
            メールアドレス : :email \r\n
            あなたのお立場をお聞かせください : :position \r\n\r\n
            :success_users
            :failed_users
            パソコン決裁Cloudのご利用状況 : :is_inuse \r\n
            解決したい課題 : :task \r\n
            導入予定時期 : :pretiming \r\n
            ご意見・ご要望 : :question \r\n
            印鑑数 : :maxstamps \r\n
            利用ドメイン : :domains \r\n \r\n
            メルマガ配信希望 : :mailMagazine \r\n
            --------------------------------------------------------------------------'
    ],

    'trialSuccessMail' => [
        'subject' => '利用者初期パスワードのお知らせ',
        'body' => 'Shachihata Cloud30日間トライアルにお申込いただき\r\n
        誠にありがとうございます。\r\n
        初期パスワードを発行いたしましたので24時間以内にログインをお試しください。\r\n\r\n
        パスワード：:password \r\n\r\n
          また、ログイン後の右上のアイコンより、管理者アカウントへログインしていただけます。\r\n
          ※初期パスワードは同じです。\r\n\r\n
          ▼ 「初回設定マニュアル」\r\n
          https://help.dstmp.com/help/firstmanual-b/\r\n
          ▼ 管理者向けヘルプサイト\r\n
          https://help.dstmp.com/scloud/business/admin/\r\n
          ▼ 利用者向けヘルプサイト\r\n
          https://help.dstmp.com/scloud/business/user/\r\n
          ▼ ヘルプサイト\r\n
          https://help.dstmp.com/\r\n\r\n
          今回のトライアルは下記の内容で承りました。\r\n
          --------------------------------------------------------------------------\r\n
          対象商品 : Shachihata Cloud トライアル\r\n
          会社名 : :nickname \r\n
          会社名フリガナ : :kananame \r\n
          電話番号 : :telno \r\n
          業種 : :industry \r\n
          部署・役職名 : :group  :post \r\n
          お名前 : :familyname  :givenname \r\n
          お名前フリガナ： :familynameKana  :givennameKana \r\n
          担当者の電話番号： :telno2 \r\n
          メールアドレス : :email \r\n
          あなたのお立場をお聞かせください : :position \r\n
          :success_users
          :failed_users
          パソコン決裁Cloudのご利用状況 : :is_inuse \r\n
          解決したい課題 : :task \r\n
          導入予定時期 : :pretiming \r\n
          印鑑数 : :maxstamps \r\n
          利用ドメイン : :domains \r\n
          メルマガ配信希望 : :mailMagazine \r\n
          --------------------------------------------------------------------------\r\n\r\n
        '
    ],

    'email_reset_link_admin' => [
        'subject' => '初期パスワードのお知らせ',
        'body' => 'いつもShachihata Cloudをご利用いただきありがとうございます。\r\n\r\n
            ご利用の管理者アカウントに対して、初期パスワードを発行いたしました。\r\n
           パスワード： :password \r\n\r\n
            お客様がこのリクエストを行っていない場合、このままこのメールを削除してください。\r\n
            他人が不正にアカウントにアクセスしていると思われる場合は、\r\n
                   Shachihata Cloudの設定ページで、ただちにパスワードを変更してください。'
    ],
    'email_reset_link_audit' => [
        'subject' => '初期パスワードのお知らせ',
        'body' => 'いつもShachihata Cloudをご利用いただきありがとうございます。\r\n\r\n
            ご利用のShachihata Cloudアカウントに対して、初期パスワードを発行いたしました。\r\n
            パスワード設定画面に移動して、新しいパスワードを設定してください。\r\n\r\n
           パスワード： :password \r\n\r\n
            お客様がこのリクエストを行っていない場合、このままこのメールを削除してください。\r\n
            他人が不正にアカウントにアクセスしていると思われる場合は、\r\n
                   Shachihata Cloudの設定ページで、ただちにパスワードを変更してください。'
    ],
    'email_reset_link_user' => [
        'subject' => '初期パスワードのお知らせ',
        'body' => '対象企業：:company_name\r\n
            対象ID：:user_id\r\n\r\n
            いつもShachihata Cloudをご利用いただきありがとうございます。\r\n\r\n
            ご利用のShachihata Cloudアカウントに対して、初期パスワードを発行いたしました。\r\n
           パスワード： :password \r\n\r\n
            お客様がこのリクエストを行っていない場合、このままこのメールを削除してください。\r\n
            他人が不正にアカウントにアクセスしていると思われる場合は、\r\n
                   Shachihata Cloudの設定ページで、ただちにパスワードを変更してください。'
    ],
    'email_reset_link_simple_user' => [
        'subject' => '初期パスワードのお知らせ',
        'body' => 'Shachihata Cloudをご利用いただきありがとうございます。\r\n\r\n
            管理者より、お客様はShachihata Cloudの利用者として登録されました。\r\n\r\n
            パスワード： :password \r\n\r\n
            お客様がこのリクエストを行っていない場合、このままこのメールを削除してください。\r\n
            他者が不正にアカウントにアクセスしていると思われる場合は、\r\n
            Shachihata Cloudの設定ページで、ただちにパスワードを変更してください。'
    ],

    'SendMailDeleteCircular' => [
        'subject' => '回覧削除のお知らせ',
        'body' => '管理者により以下の回覧が削除されました。\r\n
                   ※申請日時 - 件名(ファイル名)\r\n\r\n
                   ・:deleteTime - :title (:fileName)'
    ],

    'SendChangePasswordMail' => [
        'subject' => 'パスワードの設定が完了しました',
        'body' => 'いつもShachihata Cloudをご利用いただきありがとうございます。\r\n
                 ご利用の管理者アカウントに新しいパスワードを設定しました。'
    ],

    'SendMailAssignCompanyStamp' => [
        'subject' => '共通印登録完了のお知らせ',
        'body' => 'Shachihata Cloudをご利用いただきありがとうございます。\r\n
    お申し込みいただいておりました共通印の準備が整いました。\r\n
                   Shachihata Cloudの管理者サイトにログインし、共通印設定画面でご確認ください。'
    ],

    'SendMfaMail' => [
        'subject' => '認証コードの発行',
        'body' => 'いつもShachihata Cloudをご利用いただきありがとうございます。\r\n
        :otp \r\n
        この認証コードをログイン画面に入力してください。\r\n
        この認証コードの有効期限は :otpExpires  です。\r\n
                   お客様がこのリクエストを行っていない場合、貴社の管理者までお問い合わせください。\r\n '
    ],

    'SendIpRestrictionMail' => [
        'subject' => 'ログイン通知',
        'body' => 'いつもShachihata Cloudをご利用いただきありがとうございます。\r\n
        ご利用のShachihata Cloudに対して、登録外のIPアドレスからログインが行われました。\r\n
            IPアドレス：:ipAddress \r\n
                        ユーザー：:user \r\n\r\n '
    ],

    'SendMailAlertLongTermStorage' => [
        'subject' => '利用状況に関するお知らせ',
        'body' => 'まもなく、利用可能な長期保管ディスク容量をオーバーします。\r\n
                   不要になった回覧文書を削除してください。\r\n\r\n
    使用ディスク容量：\r\n
        :current_long_term_storage_size\r\n\r\n
      ディスク使用率：\r\n
      :current_long_term_storage_percent\r\n\r\n
                   ※この電子メールの内容を他の人と共有しないでください。\r\n '
    ],

    'SendAdminPasswordMail' => [
        'subject' => '初期パスワード発行のお知らせ',
        'body' => 'いつもShachihata Cloudをご利用いただきありがとうございます。\r\n
	ご利用のShachihata Cloud管理者アカウントに対して、初期パスワードを発行しました。\r\n\r\n
	    パスワード：:password \r\n\r\n
	    お客様がこのリクエストを行っていない場合、このままこのメールを削除してください。\r\n
	    他人が不正にアカウントにアクセスしていると思われる場合は、\r\n
                   Shachihata Cloudの設定ページで、ただちにパスワードを変更してください。'
    ],

    'SendDepartmentStampActivateSuccessMail' => [
        'subject' => '部署名入り日付印登録完了のお知らせ',
        'body' => 'いつもShachihata Cloudをご利用いただきありがとうございます。\r\n
                   お申し込みいただいておりました。:userNameさんの部署名入り日付印の準備が整いました。'
    ],

    'SendDepartmentStampActivateFailedMail' => [
        'subject' => '部署名入り日付印登録失敗　:date',
        'body' => 'システム管理者様\r\n\r\n
                   部署名入り日付印の有効化処理において、DB更新エラーが発生しました。\r\n
                   ------------------\r\n
                   失敗した印面IDは以下の通りです。\r\n
                   ------------------\r\n
        :stampIds \r\n
                   统计した印面IDは以下の通りです。\r\n
                   ------------------\r\n
        成功総数：:successCount 件\r\n
                   失敗総数：:failureCount 件\r\n
                   ------------------'
    ],

    'batchHistoryMailSend' => [
        'subject' => '【Shachihata Cloud 】:env バッチ結果',
        'body' => 'Shachihata 様 \r\n\r\n
                   Shachihata Cloudのバッチ（:batch_date分）を実施完了しましたので、ご連絡致します。 \r\n\r\n
                   <table><tr><td>コマンド</td><td>実施日</td><td>状態</td><td>開始時刻</td><td>終了時刻</td><td>実行時間</td></tr>
                   :batch_histories
                   </table>'
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
    'send_timestamps_count_less_remind_mail' => [
        'subject' => 'Shachihata Cloudタイムスタンプの利用通知',
        'body' => ':company_name\r\n
        :admin_name様\r\n\r\n
        いつもShachihata Cloudをご利用いただきありがとうございます。\r\n
        ご利用のShachihata Cloudに対して\r\n
        タイムスタンプの残り回数が:timestamps_count回以下になりました。\r\n\r\n
        契約数を契約サイトにてご確認お願いいたします。\r\n
        :cloud_link'
    ],
    'send_timestamps_count_upper_limit_remind_mail' => [
        'subject' => 'Shachihata Cloudタイムスタンプの利用通知',
        'body' => ':company_name\r\n
        :admin_name様\r\n\r\n
        いつもShachihata Cloudをご利用いただきありがとうございます。\r\n
        ご利用のShachihata Cloudに対して\r\n
        タイムスタンプ発行数が上限に達しました。\r\n\r\n
        契約数を契約サイトにてご確認お願いいたします。\r\n
        また、追加購入の場合は契約サイトからご購入ください。\r\n
        :cloud_link'
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
];