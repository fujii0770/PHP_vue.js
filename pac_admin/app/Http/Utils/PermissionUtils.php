<?php

/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/3/19
 * Time: 10:22
 */
namespace App\Http\Utils;

class PermissionUtils
{
    const PERMISSION_USAGE_SITUATION_VIEW = 'admin.view_usage_situation';
    const PERMISSION_ADMINISTRATOR_HISTORY_VIEW = 'admin.view_administrator_operation_history';
    const PERMISSION_USER_HISTORY_VIEW = 'admin.view_user_operation_history';
    const PERMISSION_USER_API_HISTORY_VIEW = 'admin.view_user_api_call_history';

    const PERMISSION_BRANDING_SETTINGS_VIEW = 'admin.view_branding_settings';
    const PERMISSION_AUTHORITY_DEFAULT_SETTING_VIEW = 'admin.view_administrator_authority_default_value_setting';
    const PERMISSION_PASSWORD_POLICY_SETTINGS_VIEW = 'admin.view_password_policy_settings';
    const PERMISSION_DATE_STAMP_SETTING_VIEW = 'admin.view_date_stamp_setting';
    const PERMISSION_LIMIT_SETTING_VIEW = 'admin.view_limit_setting';
    const PERMISSION_BRANDING_SETTINGS_UPDATE = 'admin.update_branding_settings';
    const PERMISSION_AUTHORITY_DEFAULT_SETTING_UPDATE = 'admin.update_administrator_authority_default_value_setting';
    const PERMISSION_PASSWORD_POLICY_SETTINGS_UPDATE = 'admin.update_password_policy_settings';
    const PERMISSION_DATE_STAMP_SETTING_UPDATE = 'admin.update_date_stamp_setting';
    const PERMISSION_LIMIT_SETTING_UPDATE = 'admin.update_limit_setting';
    const PERMISSION_COMMON_MARK_SETTING_VIEW = 'admin.view_common_mark_setting';
    const PERMISSION_COMMON_MARK_SETTING_CREATE = 'admin.create_common_mark_setting';
    const PERMISSION_COMMON_MARK_SETTING_UPDATE = 'admin.update_common_mark_setting';
    const PERMISSION_COMMON_MARK_SETTING_DELETE = 'admin.delete_common_mark_setting';
    // PAC_5-955 生成権限と削除権限を作成
    const PERMISSION_IP_RESTRICTION_SETTING_VIEW = 'admin.view_ip_restriction_setting';
    const PERMISSION_IP_RESTRICTION_SETTING_CREATE = 'admin.create_ip_restriction_setting';
    const PERMISSION_IP_RESTRICTION_SETTING_UPDATE = 'admin.update_ip_restriction_setting';
    const PERMISSION_IP_RESTRICTION_SETTING_DELETE = 'admin.delete_ip_restriction_setting';

    const PERMISSION_CERTIFICATE_SETTING_SETTING_VIEW = 'admin.view_certificate_setting_setting';
    const PERMISSION_CERTIFICATE_SETTING_SETTING_CREATE = 'admin.create_certificate_setting_setting';
    const PERMISSION_CERTIFICATE_SETTING_SETTING_UPDATE = 'admin.update_certificate_setting_setting';
    const PERMISSION_CERTIFICATE_SETTING_SETTING_DELETE = 'admin.delete_certificate_setting_setting';
    const PERMISSION_PROTECTION_SETTING_VIEW = 'admin.view_protection_setting';
    const PERMISSION_PROTECTION_SETTING_UPDATE = 'admin.update_protection_setting';

    const PERMISSION_ADMINISTRATOR_SETTINGS_VIEW = 'admin.view_administrator_settings';
    const PERMISSION_ADMINISTRATOR_SETTINGS_CREATE = 'admin.create_administrator_settings';
    const PERMISSION_ADMINISTRATOR_SETTINGS_UPDATE = 'admin.update_administrator_settings';
     //　PAC_5-407　管理者削除権限を追加
    const PERMISSION_ADMINISTRATOR_SETTINGS_DELETE = 'admin.delete_administrator_settings';

    const PERMISSION_ADMINISTRATOR_SETTINGS_GROUP_SETTING_VIEW = 'admin.view_stamp_group_setting';
    const PERMISSION_ADMINISTRATOR_SETTINGS_GROUP_SETTING_UPDATE = 'admin.update_stamp_group_setting';

    const PERMISSION_USER_SETTINGS_VIEW = 'admin.view_user_settings';
    const PERMISSION_USER_SETTINGS_CREATE = 'admin.create_user_settings';
    const PERMISSION_USER_SETTINGS_UPDATE = 'admin.update_user_settings';
    const PERMISSION_USER_SETTINGS_DELETE = 'admin.delete_user_settings';
    const PERMISSION_COMMON_SEAL_ASSIGNMENT_VIEW = 'admin.view_common_seal_assignment';
    const PERMISSION_COMMON_SEAL_ASSIGNMENT_UPDATE = 'admin.update_common_seal_assignment';
    const PERMISSION_AUDIT_ACCOUNT_SETTING_VIEW = 'admin.view_audit_account_settings';
    const PERMISSION_AUDIT_ACCOUNT_SETTING_UPDATE = 'admin.update_audit_account_settings';
    const PERMISSION_AUDIT_ACCOUNT_SETTING_CREATE = 'admin.create_audit_account_settings';
    const PERMISSION_AUDIT_ACCOUNT_SETTING_DELETE = 'admin.delete_audit_account_settings';

    const PERMISSION_MASTER_SYNC_SETTING_VIEW = 'admin.view_master_sync_setting';
    const PERMISSION_MASTER_SYNC_SETTING_UPDATE = 'admin.update_master_sync_setting';

    const PERMISSION_COMMON_ADDRESS_BOOK_VIEW = 'admin.view_common_address_book';
    const PERMISSION_COMMON_ADDRESS_BOOK_CREATE = 'admin.create_common_address_book';
    const PERMISSION_COMMON_ADDRESS_BOOK_UPDATE = 'admin.update_common_address_book';
    const PERMISSION_COMMON_ADDRESS_BOOK_DELETE = 'admin.delete_common_address_book';
    const PERMISSION_APPROVAL_ROUTE_VIEW= 'admin.view_approval_route';
    const PERMISSION_APPROVAL_ROUTE_CREATE = 'admin.create_approval_route';
    const PERMISSION_APPROVAL_ROUTE_UPDATE = 'admin.update_approval_route';
    const PERMISSION_DEPARTMENT_TITLE_VIEW = 'admin.view_department_title';
    const PERMISSION_DEPARTMENT_TITLE_CREATE= 'admin.create_department_title';
    const PERMISSION_DEPARTMENT_TITLE_UPDATE = 'admin.update_department_title';
    const PERMISSION_DEPARTMENT_TITLE_DELETE = 'admin.delete_department_title';
    const PERMISSION_CIRCULATION_LIST_VIEW = 'admin.view_circulation_list';
    const PERMISSION_CIRCULATION_LIST_DELETE = 'admin.delete_circulation_list';

    //承認ルート
    const PERMISSION_TEMPLATE_ROUTE_VIEW = 'admin.view_template_route';
    const PERMISSION_TEMPLATE_ROUTE_CREATE= 'admin.create_template_route';
    const PERMISSION_TEMPLATE_ROUTE_UPDATE = 'admin.update_template_route';

    const PERMISSION_COMPANY_SETTING = 'admin.company_setting';
    const PERMISSION_GENERAL_USER_SETTING = 'admin.general_user_setting';
    const PERMISSION_CONSTRAINT_SETTING = 'admin.constraint_setting';
    const PERMISSION_MAIL_HISTORY = 'admin.mail_history';

    const PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_VIEW = 'admin.view_long_term_storage_index_setting';
    const PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_CREATE = 'admin.create_long_term_storage_index_setting';
    const PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_UPDATE = 'admin.update_long_term_storage_index_setting';
    const PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_DELETE = 'admin.delete_long_term_storage_index_setting';

    const PERMISSION_LONG_TERM_FOLDER_VIEW = 'admin.view_long_term_folder';
    const PERMISSION_LONG_TERM_FOLDER_CREATE = 'admin.create_long_term_folder';
    const PERMISSION_LONG_TERM_FOLDER_UPDATE = 'admin.update_long_term_folder';
    const PERMISSION_LONG_TERM_FOLDER_DELETE = 'admin.delete_long_term_folder';

    const PERMISSION_LONG_TERM_STORAGE_SETTING_VIEW = 'admin.view_long_term_storage_settings';
    const PERMISSION_LONG_TERM_STORAGE_SETTING_UPDATE = 'admin.update_long_term_storage_settings';

    const PERMISSION_BOX_ENABLED_AUTO_STORAGE_SETTING_VIEW = 'admin.view_box_enabled_auto_storage_settings';
    const PERMISSION_BOX_ENABLED_AUTO_STORAGE_SETTING_UPDATE = 'admin.update_box_enabled_auto_storage_settings';

    const ROLE_SHACHIHATA_ADMIN = 'shachihata_admin';
    const ROLE_COMPANY_MANAGER = 'company_manager';
    const ROLE_COMPANY_NORMAL_ADMIN = 'company_normal_admin';

    const PERMISSION_APP_USE_SETTING_VIEW = 'admin.view_app_use_setting';
    const PERMISSION_APP_USE_SETTING_UPDATE = 'admin.update_app_use_setting';

    const PERMISSION_APP_ROLE_SETTING_VIEW = 'admin.view_app_role_setting';
    const PERMISSION_APP_ROLE_SETTING_CREATE = 'admin.create_app_role_setting';
    const PERMISSION_APP_ROLE_SETTING_UPDATE = 'admin.update_app_role_setting';
    const PERMISSION_APP_ROLE_SETTING_DELETE = 'admin.delete_app_role_setting';

    const PERMISSION_FACILITY_SETTING_VIEW = 'admin.view_facility_setting';
    const PERMISSION_FACILITY_SETTING_CREATE = 'admin.create_facility_setting';
    const PERMISSION_FACILITY_SETTING_UPDATE = 'admin.update_facility_setting';
    const PERMISSION_FACILITY_SETTING_DELETE = 'admin.delete_facility_setting';
    // PAC_14-61
    const PERMISSION_COLORCATEGORY_SETTING_VIEW = 'admin.view_colorcategory_setting';
    const PERMISSION_COLORCATEGORY_SETTING_CREATE = 'admin.create_colorcategory_setting';
    const PERMISSION_COLORCATEGORY_SETTING_UPDATE = 'admin.update_colorcategory_setting';
    const PERMISSION_COLORCATEGORY_SETTING_DELETE = 'admin.delete_colorcategory_setting';

    const PERMISSION_SCHEDULE_LIST_SETTING_VIEW = 'admin.view_schedule_list_setting';
    const PERMISSION_SCHEDULE_LIST_SETTING_CREATE = 'admin.create_schedule_list_setting';
    const PERMISSION_SCHEDULE_LIST_SETTING_UPDATE = 'admin.update_schedule_list_setting';
    const PERMISSION_SCHEDULE_LIST_SETTING_DELETE = 'admin.delete_schedule_list_setting';

    const PERMISSION_WORK_CONFIRM_SETTING_VIEW = 'admin.view_work_confirm_setting';
    const PERMISSION_WORK_CONFIRM_SETTING_CREATE = 'admin.create_work_confirm_setting';
    const PERMISSION_WORK_CONFIRM_SETTING_UPDATE = 'admin.update_work_confirm_setting';
    const PERMISSION_WORK_CONFIRM_SETTING_DELETE = 'admin.delete_work_confirm_setting';


    //PAC_5-2546
    const PERMISSION_HR_ADMIN_SETTING_VIEW = 'admin.view_hr_admin_setting';
    const PERMISSION_HR_ADMIN_SETTING_CREATE = 'admin.create_hr_admin_setting';
    const PERMISSION_HR_ADMIN_SETTING_UPDATE = 'admin.update_hr_admin_setting';
    const PERMISSION_HR_ADMIN_SETTING_DELETE = 'admin.delete_hr_admin_setting';
    //PAC_5-2546

    //PAC_5_3190-3191
    const PERMISSION_HR_WORKING_HOUR_VIEW = 'admin.view_hr_working_hour';
    const PERMISSION_HR_WORKING_HOUR_CREATE = 'admin.create_hr_working_hour';
    const PERMISSION_HR_WORKING_HOUR_UPDATE = 'admin.update_hr_working_hour';
    const PERMISSION_HR_WORKING_HOUR_DELETE = 'admin.delete_hr_working_hour';
    //PAC_5_3190-3191

    const PERMISSION_HR_USER_SETTING_VIEW = 'admin.view_hr_user_setting';
    const PERMISSION_HR_USER_SETTING_CREATE = 'admin.create_hr_user_setting';
    const PERMISSION_HR_USER_SETTING_UPDATE = 'admin.update_hr_user_setting';
    const PERMISSION_HR_USER_SETTING_DELETE = 'admin.delete_hr_user_setting';

    const PERMISSION_DAILY_REPORT_SETTING_VIEW = 'admin.view_daily_report_setting';
    const PERMISSION_DAILY_REPORT_SETTING_CREATE = 'admin.create_hr_user_setting';
    const PERMISSION_DAILY_REPORT_SETTING_UPDATE = 'admin.update_daily_report_setting';
    const PERMISSION_DAILY_REPORT_SETTING_DELETE = 'admin.delete_hr_user_setting';

    const PERMISSION_OPTION_USERS_VIEW = 'admin.view_option_users';
    const PERMISSION_OPTION_USERS_UPDATE = 'admin.update_option_users';
    const PERMISSION_OPTION_USERS_CREATE = 'admin.create_option_users';
    const PERMISSION_OPTION_USERS_DELETE = 'admin.delete_option_users';
    //PAC_5-1902
    const PERMISSION_SPECIAL_SITE_UPLOAD_VIEW = 'admin.view_special_site_upload';
    const PERMISSION_SPECIAL_SITE_UPLOAD_CREATE = 'admin.create_special_site_upload';
    const PERMISSION_SPECIAL_SITE_UPLOAD_UPDATE = 'admin.update_special_site_upload';
    const PERMISSION_SPECIAL_SITE_UPLOAD_DELETE = 'admin.delete_special_site_upload';
    const PERMISSION_SPECIAL_SITE_RECEIVE_VIEW = 'admin.view_special_site_receive';
    const PERMISSION_SPECIAL_SITE_RECEIVE_UPDATE = 'admin.update_special_site_receive';
    const PERMISSION_SPECIAL_SITE_SEND_VIEW = 'admin.view_special_site_send';
    const PERMISSION_SPECIAL_SITE_SEND_UPDATE = 'admin.update_special_site_send';
    //PAC_5-1902

    const PERMISSION_RECEIVE_USERS_VIEW = 'admin.view_receive_users';
    const PERMISSION_RECEIVE_USERS_UPDATE = 'admin.update_receive_users';
    const PERMISSION_RECEIVE_USERS_CREATE = 'admin.create_receive_users';
    const PERMISSION_RECEIVE_USERS_DELETE = 'admin.delete_receive_users';
    const PERMISSION_ADMIN_ISSUANCE_SETTING_VIEW = 'admin.view_admin_issuance_setting';
    const PERMISSION_ADMIN_ISSUANCE_SETTING_CREATE = 'admin.create_admin_issuance_setting';
    const PERMISSION_ADMIN_ISSUANCE_SETTING_UPDATE = 'admin.update_admin_issuance_setting';
    const PERMISSION_ADMIN_ISSUANCE_SETTING_DELETE = 'admin.delete_admin_issuance_setting';
    const PERMISSION_FRM_INDEX_SETTING_VIEW = 'admin.view_frm_index_setting';
    const PERMISSION_FRM_INDEX_SETTING_CREATE = 'admin.create_frm_index_setting';
    const PERMISSION_FRM_INDEX_SETTING_UPDATE = 'admin.update_frm_index_setting';
    const PERMISSION_FRM_INDEX_SETTING_DELETE = 'admin.delete_frm_index_setting';

    const PERMISSION_DISPATCHAREA_SETTING_VIEW = 'admin.view_dispatcharea_setting';
    const PERMISSION_DISPATCHAREA_SETTING_CREATE = 'admin.create_dispatcharea_setting';
    const PERMISSION_DISPATCHAREA_SETTING_UPDATE = 'admin.update_dispatcharea_setting';
    const PERMISSION_DISPATCHAREA_SETTING_DELETE = 'admin.delete_dispatcharea_setting';

    const PERMISSION_ADMIN_TIMECARD_SETTING_VIEW = 'admin.view_timecard_setting';
    const PERMISSION_ADMIN_TIMECARD_SETTING_CREATE = 'admin.create_timecard_setting';
    const PERMISSION_ADMIN_TIMECARD_SETTING_UPDATE = 'admin.update_timecard_setting';
    const PERMISSION_ADMIN_TIMECARD_SETTING_DELETE = 'admin.delete_timecard_setting';

    const PERMISSION_CONTRACT_SETTING_VIEW = 'admin.view_contract_setting';
    const PERMISSION_CONTRACT_SETTING_CREATE = 'admin.create_contract_setting';
    const PERMISSION_CONTRACT_SETTING_UPDATE = 'admin.update_contract_setting';
    const PERMISSION_CONTRACT_SETTING_DELETE = 'admin.delete_contract_setting';

    const PERMISSION_DISPATCHHR_SETTING_VIEW = 'admin.view_dispatchhr_setting';
    const PERMISSION_DISPATCHHR_SETTING_CREATE = 'admin.create_dispatchhr_setting';
    const PERMISSION_DISPATCHHR_SETTING_UPDATE = 'admin.update_dispatchhr_setting';
    const PERMISSION_DISPATCHHR_SETTING_DELETE = 'admin.delete_dispatchhr_setting';
    const GROUP_MENU = ['利用状況', '操作履歴', '全体設定','管理者設定', '利用者設定','機能設定','長期保管','グループウェア設定','スケジューラ設定','HR機能','派遣管理','ササッと明細','特設サイト','ササッとTalk設定','経費精算'];

    const PERMISSION_ADMIN_EXPENSE_SETTING_VIEW = 'admin.view_admin_expense_setting';
    const PERMISSION_ADMIN_EXPENSE_SETTING_CREATE = 'admin.create_admin_expense_setting';
    const PERMISSION_ADMIN_EXPENSE_SETTING_UPDATE = 'admin.update_admin_expense_setting';
    const PERMISSION_ADMIN_EXPENSE_SETTING_DELETE = 'admin.delete_admin_expense_setting';
    const PERMISSION_MASTER_EXPENSE_SETTING_VIEW = 'admin.view_master_expense_setting';
    const PERMISSION_MASTER_EXPENSE_SETTING_CREATE = 'admin.create_master_expense_setting';
    const PERMISSION_MASTER_EXPENSE_SETTING_UPDATE = 'admin.update_master_expense_setting';
    const PERMISSION_MASTER_EXPENSE_SETTING_DELETE = 'admin.delete_master_expense_setting';
    const PERMISSION_STYLE_EXPENSE_SETTING_VIEW = 'admin.view_style_expense_setting';
    const PERMISSION_STYLE_EXPENSE_SETTING_CREATE = 'admin.create_style_expense_setting';
    const PERMISSION_STYLE_EXPENSE_SETTING_UPDATE = 'admin.update_style_expense_setting';
    const PERMISSION_STYLE_EXPENSE_SETTING_DELETE = 'admin.delete_style_expense_setting';
    const PERMISSION_EXPENSE_APP_LIST_VIEW = 'admin.view_expense_app_list';
    const PERMISSION_EXPENSE_APP_LIST_CREATE = 'admin.create_expense_app_list';
    const PERMISSION_EXPENSE_APP_LIST_UPDATE = 'admin.update_expense_app_list';
    const PERMISSION_EXPENSE_APP_LIST_DELETE = 'admin.delete_expense_app_list';
    const PERMISSION_EXPENSE_JOURNAL_LIST_VIEW = 'admin.view_expense_journal_list';
    const PERMISSION_EXPENSE_JOURNAL_LIST_CREATE = 'admin.create_expense_journal_list';
    const PERMISSION_EXPENSE_JOURNAL_LIST_UPDATE = 'admin.update_expense_journal_list';
    const PERMISSION_EXPENSE_JOURNAL_LIST_DELETE = 'admin.delete_expense_journal_list';

    // PAC_14-45 休日設定 Start
    const PERMISSION_HOLIDAY_SETTING_VIEW = 'admin.view_holiday_setting';
    const PERMISSION_HOLIDAY_SETTING_CREATE = 'admin.create_holiday_setting';
    const PERMISSION_HOLIDAY_SETTING_UPDATE = 'admin.update_holiday_setting';
    const PERMISSION_HOLIDAY_SETTING_DELETE = 'admin.delete_holiday_setting';
    // PAC_14-45 End

    // PAC_5-2663
    const PERMISSION_TALK_USER_SETTING_VIEW = 'admin.view_talk_users_setting';
    const PERMISSION_TALK_USER_SETTING_CREATE = 'admin.create_talk_users_setting';
    const PERMISSION_TALK_USER_SETTING_UPDATE = 'admin.update_talk_users_setting';
    const PERMISSION_TALK_USER_SETTING_DELETE = 'admin.delete_talk_users_setting';
    
    const PERMISSION_ATTACHMENTS_SETTING_VIEW = 'admin.view_attachments';
    const PERMISSION_ATTACHMENTS_SETTING_DELETE = 'admin.delete_attachments';

    const PERMISSION_CIRCULARS_SAVED_VIEW = 'admin.view_circulars_saved';
    const PERMISSION_CIRCULARS_SAVED_DELETE = 'admin.delete_circulars_saved';
    
    const PERMISSION_BIZ_CARDS_VIEW = 'admin.view_biz_cards';
    const PERMISSION_BIZ_CARDS_DELETE = 'admin.delete_biz_cards';
    const PERMISSION_BIZ_CARDS_UPDATE = 'admin.update_biz_cards';

    const PERMISSION_TEMPLATE_CSV_VIEW = 'admin.view_template_csv';

    const PERMISSION_TEMPLATE_INDEX_VIEW = 'admin.view_template_index';
    const PERMISSION_TEMPLATE_INDEX_CREATE = 'admin.create_template_index';
    const PERMISSION_TEMPLATE_INDEX_DELETE = 'admin.delete_template_index';

    const DEFAULT_VALUE = [
        //利用状況
        '利用状況'=>[1,0,0,0],
        //操作履歴
        '管理者操作履歴'=>[1,0,0,0],
        '利用者操作履歴'=>[1,0,0,0],
        //'利用者操作API呼出履歴'=>[2,0,0,0],
        //全体設定
        'ブランディング設定'=>[1,0,2,0],
        '管理者権限初期値設定'=>[1,0,2,0],
        'パスワードポリシー設定'=>[1,0,2,0],
        '日付印設定'=>[1,0,2,0],
        '共通印設定'=>[1,0,2,2],
        '制限設定'=>[1,0,2,0],
        '保護設定'=>[1,0,2,0],
        // PAC_5-955 生成権限と削除権限を作成
        //'接続IP制限設定'=>[2,0,2,0],
        '接続IP制限設定'=>[1,2,2,2],
        '電子証明書設定'=>[1,2,2,2],
        '長期保管設定'=>[1,0,2,0],
        '長期保管インデックス設定'=>[1,2,2,2],
        '長期保管フォルダ管理'=>[1,2,2,2],
        //管理者設定
        // PAC_5-407 初期値を0(機能なし)から2(利用不可)に変更
        '管理者設定'=>[1,2,2,2],
        '共通印グループ管理者割当'=>[1,0,2,0],
        //利用者設定
        '利用者設定'=>[1,2,2,2],
        'グループウェア専用利用者'=>[1,2,2,2],
        '受信専用利用者'=>[1,2,2,2],
        '共通印割当'=>[1,0,2,0],
        '監査用アカウント設定'=>[1,2,2,2],
        //機能設定
        '共通アドレス帳'=>[1,2,2,2],
        '部署・役職'=>[1,2,2,2],
        '承認ルート'=>[1,2,2,0],
        '回覧一覧'=>[1,0,0,2],
        '保存文書一覧'=>[1,0,0,1],
        'タイムスタンプ設定'=>[1,0,2,0],
        '外部連携'=>[2,0,2,0],
        'ササッと明細:利用ユーザ登録'=>[1,2,1,2],
        '派遣先管理'=>[1,2,2,2],
        '契約管理'=>[1,2,2,2],
        '人材管理'=>[1,2,2,2],
        //特設サイト PAC_5-1902
        '文書登録'=>[1,2,2,2],
        '連携承認'=>[1,0,2,0],
        '連携申請'=>[1,0,2,0],
        'ササッと明細:明細項目設定'=>[1,2,2,2],
    ];
    const DEFAULT_VALUE_PORTAL = [
        'マスタ同期設定'=>[2,0,2,0],
        'アプリ利用設定'=>[1,0,2,0],
        'スケジューラ制限設定'=>[1,0,2,0],
        'アプリロール設定'=>[1,2,2,2],
        '設備'=>[1,2,2,2],
        'カテゴリ設定' => [1,2,2,2],
        '休日設定'=>[1,2,2,2], // PAC_14-45 休日設定
        'タイムカード設定'=>[1,2,2,2],
    ];
    const DEFAULT_VALUE_HR = [
        '勤務表一覧'=>[1,2,2,2],
        '勤務確認'=>[1,2,2,2],
        '利用ユーザ登録'=>[1,2,2,2],
        '管理ユーザ登録'=>[1,2,2,2],
        '就労時間管理'=>[1,2,2,2],
        '日報確認'=>[1,2,2,2],

    ];
    const DEFAULT_VALUE_TALK = [
        'ササッとTalk利用者設定'=>[1,2,2,2],
    ];

    const DEFAULT_VALUE_EXPENSE = [
        '利用ユーザ管理'=>[1,2,1,2],
        'マスタ管理'=>[1,2,1,2],
        '様式管理'=>[1,2,1,2],
        '経費申請一覧'=>[1,2,1,2],
        '経費仕訳一覧'=>[1,2,1,2],
    ];
    const DEFAULT_VALUE_ATTACHMENT = [
        '添付ファイル一覧' => [1, 0, 0, 2],
    ];
    const DEFAULT_VALUE_BIZ_CARD = [
        '名刺一覧' => [1, 0, 1, 1],
    ];
    const DEFAULT_VALUE_TEMPLATE = [
        'テンプレート' => [1, 1, 0, 1],
    ];
    const DEFAULT_VALUE_TEMPLATE_CSV = [
        '回覧完了テンプレート一覧' => [1, 0, 0, 0],
    ];
}
