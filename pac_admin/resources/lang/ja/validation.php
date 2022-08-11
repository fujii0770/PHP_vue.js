<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'not_special_char'           => ':attributeは文字「"\'`!$%^@＃」を除外しなければなりません。',
    'accepted'             => ':attributeを承認してください。',
    'active_url'           => ':attributeには有効なURLを指定してください。',
    'after'                => ':attributeには:date以降の日付を指定してください。',
    'after_or_equal'       => ':attributeには:dateかそれ以降の日付を指定してください。',
    'alpha'                => ':attributeには英字のみからなる文字列を指定してください。',
    'alpha_dash'           => ':attributeには英数字・ハイフン・アンダースコアのみからなる文字列を指定してください。',
    'alpha_num'            => ':attributeには英数字のみからなる文字列を指定してください。',
    'array'                => ':attributeには配列を指定してください。',
    'before'               => ':attributeには:date以前の日付を指定してください。',
    'before_or_equal'      => ':attributeには:dateかそれ以前の日付を指定してください。',
    'between'              => [
        'numeric' => ':attributeには:min〜:maxまでの数値を指定してください。',
        'file'    => ':attributeには:min〜:max KBのファイルを指定してください。',
        'string'  => ':attributeには:min〜:max文字の文字列を指定してください。',
        'array'   => ':attributeには:min〜:max個の要素を持つ配列を指定してください。',
    ],
    'boolean'              => ':attributeには真偽値を指定してください。',
    'confirmed'            => ':attributeが確認用の値と一致しません。',
    'date'                 => ':attributeには正しい形式の日付を指定してください。',
    'date_format'          => '":format"という形式の日付を指定してください。',
    'different'            => ':attributeには:otherとは異なる値を指定してください。',
    'digits'               => ':attributeには:digits桁の数値を指定してください。',
    'digits_between'       => ':attributeには:min〜:max桁の数値を指定してください。',
    'dimensions'           => ':attributeの画像サイズが不正です。',
    'distinct'             => '指定された:attributeは既に存在しています。',
    'email'                => ':attributeには正しい形式のメールアドレスを指定してください。',
    'exists'               => '指定された:attributeは存在しません。',
    'file'                 => ':attributeにはファイルを指定してください。',
    'filled'               => ':attributeには空でない値を指定してください。',
    'gt'                   => [
        'numeric' => ':attributeには、:valueより大きな値を指定してください。',
        'file'    => ':attributeには、:value kBより大きなファイルを指定してください。',
        'string'  => ':attributeは、:value文字より長く指定してください。',
        'array'   => ':attributeには、:value個より多くのアイテムを指定してください。',
    ],
    'gte'                  => [
        'numeric' => ':attributeには、:value以上の値を指定してください。',
        'file'    => ':attributeには、:value kB以上のファイルを指定してください。',
        'string'  => ':attributeは、:value文字以上で指定してください。',
        'array'   => ':attributeには、:value個以上のアイテムを指定してください。'
    ],
    'image'                => ':attributeには画像ファイルを指定してください。',
    'in'                   => ':attributeには:valuesのうちいずれかの値を指定してください。',
    'in_array'             => ':attributeが:otherに含まれていません。',
    'integer'              => ':attributeには整数を指定してください。',
    'ip'                   => ':attributeには正しい形式のIPアドレスを指定してください。',
    'ipv4'                 => ':attributeには正しい形式のIPv4アドレスを指定してください。',
    'ipv6'                 => ':attributeには正しい形式のIPv6アドレスを指定してください。',
    'json'                 => ':attributeには正しい形式のJSON文字列を指定してください。',
    'lt'                   => [
        'numeric' => ':attributeには、:valueより小さな値を指定してください。',
        'file'    => ':attributeには、:value kBより小さなファイルを指定してください。',
        'string'  => ':attributeは、:value文字より短く指定してください。',
        'array'   => ':attributeには、:value個より少ないアイテムを指定してください。',
    ],
    'lte'                  => [
        'numeric' => ':attributeには、:value以下の値を指定してください。',
        'file'    => ':attributeには、:value kB以下のファイルを指定してください。',
        'string'  => ':attributeは、:value文字以下で指定してください。',
        'array'   => ':attributeには、:value個以下のアイテムを指定してください。',
    ],
    'max'                  => [
        'numeric' => ':attributeには、:max以下の数字を指定してください。',
        'file'    => ':attributeには、:max kB以下のファイルを指定してください。',
        'string'  => ':attributeは、:max文字以下で指定してください。',
        'array'   => ':attributeは:max個以下指定してください。',
    ],
    'mimes'                => ':attributeには:valuesのうちいずれかの形式のファイルを指定してください。',
    'mimetypes'            => ':attributeには:valuesのうちいずれかの形式のファイルを指定してください。',
    'min'                  => [
        'numeric' => ':attributeには:min以上の数値を指定してください。',
        'file'    => ':attributeには:min KB以上のファイルを指定してください。',
        'string'  => ':attributeには:min文字以上の文字列を指定してください。',
        'array'   => ':attributeには:min個以上の要素を持つ配列を指定してください。',
    ],
    'not_in'               => ':attributeには:valuesのうちいずれとも異なる値を指定してください。',
    'not_regex'            => 'The :attribute format is invalid.',
    'numeric'              => ':attributeには数値を指定してください。',
    'present'              => ':attributeには現在時刻を指定してください。',
    'regex'                => '正しい形式の:attributeを指定してください。',
    'required'             => ':attributeは必須です。',
    'required_if'          => ':otherが:valueの時:attributeは必須です。',
    'required_unless'      => ':otherが:values以外の時:attributeは必須です。',
    'required_with'        => ':valuesのうちいずれかが指定された時:attributeは必須です。',
    'required_with_all'    => ':valuesのうちすべてが指定された時:attributeは必須です。',
    'required_without'     => ':valuesのうちいずれかがが指定されなかった時:attributeは必須です。',
    'required_without_all' => ':valuesのうちすべてが指定されなかった時:attributeは必須です。',
    'same'                 => ':attributeが:otherと一致しません。',
    'size'                 => [
        'numeric' => ':attributeには:sizeを指定してください。',
        'file'    => ':attributeには:size KBのファイルを指定してください。',
        'string'  => ':attributeには:size文字の文字列を指定してください。',
        'array'   => ':attributeには:size個の要素を持つ配列を指定してください。',
    ],
    'string'               => ':attributeには文字列を指定してください。',
    'timezone'             => ':attributeには正しい形式のタイムゾーンを指定してください。',
    'unique'               => 'その:attributeはすでに使われています。',
    'uploaded'             => ':attributeのアップロードに失敗しました。',
    'url'                  => ':attributeには正しい形式のURLを指定してください。',
    'starts_with' => ':attributeは、次のいずれかで始まる必要があります。:values',
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'validity_period' => [
            'required' => '0 以上の数値で入力してください',
        ],
        'min_length'=> [
            'required' => '4文字以上14文字以下の数値で入力してください',
        ],
        'password_mail_validity_days' => [
            // PAC_5-1970 パスワードメールの有効期限を変更する Start
            'required' => '1～14の数値で入力してください',
            // PAC_5-1970 End
        ],
        'domain' => [
            'starts_with' => '登録できるドメインは、次のいずれかで始まる必要があります。@',
        ],
        'url_domain_id' => [
            'regex' => 'URLドメイン識別:半角英数で入力してください'
        ],
        'user_max_limit'=>[
            'max'=>'利用上限人数上限を超えています。',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'email' =>'メールアドレス',
        'auth_email' => '認証コード送信先メールアドレス',
        'email_auth_flg' => 'メール認証',
        'mfa_type' => '多要素認証',
        'mfa_flg' => '多要素認証',
        'email_auth_dest_flg' => '認証コード送信先',
        'api_apps' => 'APIの使用',
        'name' => '名称',
        'ip_address' => 'IPアドレス',
        'subnet_mask' => 'サブネットマスク',
        'domain' => '登録できるドメイン',
        'upper_limit' => '登録可能印面数',
        'max_usable_capacity' => '長期保管ディスク使用容量',
        'contract_edition' => '契約Edition',
        'system_name'=> 'システム名称',
        'auto_save_days'=> '自動保存日数',
        'auto_save'=> '文書の長期保管',
        'given_name' => '名',
        'family_name' => '姓',
        'state_flg' => '状態',
        'url_domain_id'=>'URLドメイン識別',
        'saml_unique'=>'SAML識別子',
        'assigned_company' => '配属現場名',
        'Regulations_work_start_time' => '規定出勤時間',
        'Regulations_work_end_time'	 => '規定終業時間',
        'Overtime_unit' => '残業発生時間単位',
        'break_time' => '休憩時間',
        'phone_number' => '電話番号（外線）',
        'postal_code' => '郵便番号',
        "fax_number" => 'FAX番号',
        "notification_email" => '通知先メールアドレス',
        "old_contract_flg" => '旧契約形態',
        'max_requests' =>'送信可能回数',
        'max_document_size' =>'ファイルサイズ',
        'use_storage_percent' =>'容量通知',
        'max_keep_days' =>'保存日数',
        'delete_informed_days_ago' =>'削除予告日数',
        'long_term_storage_percent' =>'長期保管ディスク容量通知',
        'dl_request_limit' =>'保有可能ダウンロード要求数',
        'dl_request_limit_per_one_hour' =>'一時間当たりのダウンロード要求可能な回数数',
        'maxBbsCount' =>'掲示板容量',
        'maxScheduleCount' =>'スケジューラー容量',
        'dl_max_keep_days' =>'ダウンロード最大保存期間',
        'dl_after_proc' =>'ダウンロード後削除',
        'dl_after_keep_days' =>'ダウンロード後保存期間',
        'dl_file_total_size_limit' =>'ダウンロードファイル容量',
        'max_ip_address_count' =>'IPアドレス制限登録件数',
        'max_viwer_count' =>'閲覧ユーザー登録件数',
        'sanitize_request_limit' =>'無害化ファイル要求上限',
        'max_attachment_size' =>'添付ファイル容量',
        'max_total_attachment_size' =>'添付ファイル合計容量',
        'max_attachment_count' =>'添付ファイル数',
        'template_size_limit' =>'テンプレートサイズ上限',
        'exp_template_size_limit' =>'Export用ﾃﾝﾌﾟﾚｰﾄｻｲｽﾞ上限',
        'max_template_file' =>'登録テンプレート上限',
        'exp_max_template_file' =>'Export用登録可能ﾃﾝﾌﾟﾚｰﾄ上限',
        'convenient_upper_limit' => '便利印契約数',
        /*PAC_5-1807 S*/
        'bbs_max_attachment_size' =>'添付ファイル容量',
        'bbs_max_total_attachment_size' =>'添付ファイル合計容量',
        'bbs_max_attachment_count' =>'添付ファイル数',
        /*PAC_5-1807 E*/
        'select_id' =>'選択ID',
        'contract_edition_name' =>'契約Edition',
        'auto_save_num'=>'長期保管の件数',
        'max_frm_document' => '帳票発行文書数上限',
        'contract_type'=>'契約種別',
        'user_max_limit'=>'利用上限人数',
        'domain'=>'ドメイン',
        'storage_max_limit'=>'保管サイズ上限',
        // PAC 5 2663
        'chat_role_flg' => 'ササッとTalkロール設定',
        'status' => '状況',
        /*PAC_5-3018 S*/
        'phone_number_mobile'=>'電話番号（携帯）',
        'phone_number_extension'=>'電話番号（内線）',
        /*PAC_5-3018 E*/
        'receive_plan_flg' => '受信専用プラン',
        'remark_message' => '備考',
        'to_do_list_flg'=>'ToDoリストフラグ',
        'to_do_list_limit_flg'=>'ToDoリスト人数制限',
        'to_do_list_buy_count'=>'ToDoリストの購入数',
        'remark_message' => '備考',
        'purpose_name'=>'目的の名称',
        'wtsm_name'=>'用途の名称',
        'wtsm_describe'=>'用途の説明',
        'num_people_describe'=>'人数入力の説明',
        'detail_describe'=>'詳細入力の説明',
        'account_name'=>'勘定科目の名称',
        'describe'=>'説明',
        'remarks'=>'備考',
        'sub_account_name'=>'補助科目',
        'memo'=>'備考',
        'rec_date'=>'計上日',
        'debit_rec_dept'=>'借方 計上部門',
        'debit_account'=>'借方 勘定科目',
        'debit_subaccount'=>'借方 補助科目',
        'debit_amount'=>'借方 金額',
        'debit_tax_div'=>'借方 税区分',
        'debit_tax_rate'=>'借方 税率',
        'debit_tax'=>'借方 税額',
        'credit_dept'=>'貸方 計上部門',
        'credit_account'=>'貸方 勘定科目',
        'credit_subaccount'=>'貸方 補助科目',
        'credit_amount'=>'貸方 金額',
        'credit_tax_div'=>'貸方 税区分',
        'credit_tax_rate'=>'貸方 税率',
        'credit_tax'=>'貸方 税額',
    ],

];
