<?php

use Illuminate\Database\Seeder;

class permissions_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        \DB::table('permissions')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'admin.view_usage_situation',
                'menu' => '利用状況',
                'group_menu' => '利用状況',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 2],
            [
                'name' => 'admin.view_administrator_operation_history',
                'menu' => '管理者操作履歴',
                'group_menu' => '操作履歴',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 3],
            [
                'name' => 'admin.view_user_operation_history',
                'menu' => '利用者操作履歴',
                'group_menu' => '操作履歴',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 5],
            [
                'name' => 'admin.view_administrator_settings',
                'menu' => '管理者設定',
                'group_menu' => '管理者設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 6],
            [
                'name' => 'admin.create_administrator_settings',
                'menu' => '管理者設定',
                'group_menu' => '管理者設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 7],
            [
                'name' => 'admin.update_administrator_settings',
                'menu' => '管理者設定',
                'group_menu' => '管理者設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 8],
            [
                'name' => 'admin.view_branding_settings',
                'menu' => 'ブランディング設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 9],
            [
                'name' => 'admin.update_branding_settings',
                'menu' => 'ブランディング設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 10],
            [
                'name' => 'admin.view_administrator_authority_default_value_setting',
                'menu' => '管理者権限初期値設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 11],
            [
                'name' => 'admin.update_administrator_authority_default_value_setting',
                'menu' => '管理者権限初期値設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 12],
            [
                'name' => 'admin.view_password_policy_settings',
                'menu' => 'パスワードポリシー設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 13],
            [
                'name' => 'admin.update_password_policy_settings',
                'menu' => 'パスワードポリシー設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 14],
            [
                'name' => 'admin.view_date_stamp_setting',
                'menu' => '日付印設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 15],
            [
                'name' => 'admin.update_date_stamp_setting',
                'menu' => '日付印設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 16],
            [
                'name' => 'admin.view_common_mark_setting',
                'menu' => '共通印設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 18],
            [
                'name' => 'admin.update_common_mark_setting',
                'menu' => '共通印設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 19],
            [
                'name' => 'admin.delete_common_mark_setting',
                'menu' => '共通印設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 20],
            [
                'name' => 'admin.view_limit_setting',
                'menu' => '制限設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 21],
            [
                'name' => 'admin.update_limit_setting',
                'menu' => '制限設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 22],
            [
                'name' => 'admin.view_user_settings',
                'menu' => '利用者設定',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 23],
            [
                'name' => 'admin.create_user_settings',
                'menu' => '利用者設定',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 24],
            [
                'name' => 'admin.update_user_settings',
                'menu' => '利用者設定',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 25],
            [
                'name' => 'admin.delete_user_settings',
                'menu' => '利用者設定',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 26],
            [
                'name' => 'admin.view_common_seal_assignment',
                'menu' => '共通印割当',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 27],
            [
                'name' => 'admin.update_common_seal_assignment',
                'menu' => '共通印割当',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 28],
            [
                'name' => 'admin.view_common_address_book',
                'menu' => '共通アドレス帳',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 29],
            [
                'name' => 'admin.create_common_address_book',
                'menu' => '共通アドレス帳',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 30],
            [
                'name' => 'admin.update_common_address_book',
                'menu' => '共通アドレス帳',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 31],
            [
                'name' => 'admin.delete_common_address_book',
                'menu' => '共通アドレス帳',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 35],
            [
                'name' => 'admin.view_department_title',
                'menu' => '部署・役職',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 36],
            [
                'name' => 'admin.create_department_title',
                'menu' => '部署・役職',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 37],
            [
                'name' => 'admin.update_department_title',
                'menu' => '部署・役職',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 38],
            [
                'name' => 'admin.delete_department_title',
                'menu' => '部署・役職',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 39],
            [
                'name' => 'admin.view_circulation_list',
                'menu' => '回覧一覧',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 40],
            [
                'name' => 'admin.delete_circulation_list',
                'menu' => '回覧一覧',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 41],
            [
                'name' => 'admin.company_setting',
                'menu' => '企業設定',
                'group_menu' => '基本設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 42],
            [
                'name' => 'admin.general_user_setting',
                'menu' => '一般利用者設定',
                'group_menu' => '基本設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 43],
            [
                'name' => 'admin.constraint_setting',
                'menu' => '制約条件設定',
                'group_menu' => '基本設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 44],
            [
                'name' => 'admin.mail_history',
                'menu' => 'メール送信履歴',
                'group_menu' => 'メール送信履歴',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 45],
            [
                'name' => 'admin.view_ip_restriction_setting',
                'menu' => '接続IP制限設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 46],
            [
                'name' => 'admin.update_ip_restriction_setting',
                'menu' => '接続IP制限設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 47],
            [
                'name' => 'admin.view_certificate_setting_setting',
                'menu' => '電子証明書設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 48],
            [
                'name' => 'admin.update_certificate_setting_setting',
                'menu' => '電子証明書設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 49],
            [
                'name' => 'admin.view_audit_account_settings',
                'menu' => '監査用アカウント設定',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 50],
            [
                'name' => 'admin.update_audit_account_settings',
                'menu' => '監査用アカウント設定',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 51],
            [
                'name' => 'admin.create_audit_account_settings',
                'menu' => '監査用アカウント設定',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 52],
            [
                'name' => 'admin.delete_audit_account_settings',
                'menu' => '監査用アカウント設定',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 55],
            [
                'name' => 'admin.view_protection_setting',
                'menu' => '保護設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 56],
            [
                'name' => 'admin.update_protection_setting',
                'menu' => '保護設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 57],
            [
                'name' => 'admin.view_stamp_group_setting',
                'menu' => '共通印グループ管理者割当',
                'group_menu' => '管理者設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 58],
            [
                'name' => 'admin.update_stamp_group_setting',
                'menu' => '共通印グループ管理者割当',
                'group_menu' => '管理者設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 59],
            [
                'name' => 'admin.delete_certificate_setting_setting',
                'menu' => '電子証明書設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 60],
            [
                'name' => 'admin.create_certificate_setting_setting',
                'menu' => '電子証明書設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 61],
            [
                'name' => 'admin.view_long_term_storage_settings',
                'menu' => '長期保管設定',
                'group_menu' => '長期保管',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 62],
            [
                'name' => 'admin.update_long_term_storage_settings',
                'menu' => '長期保管設定',
                'group_menu' => '長期保管',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 67],
            [
                'name' => 'admin.create_ip_restriction_setting',
                'menu' => '接続IP制限設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 68],
            [
                'name' => 'admin.delete_ip_restriction_setting',
                'menu' => '接続IP制限設定',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 69],
            [
                'name' => 'admin.view_box_enabled_auto_storage_settings',
                'menu' => '外部連携',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 70],
            [
                'name' => 'admin.update_box_enabled_auto_storage_settings',
                'menu' => '外部連携',
                'group_menu' => '全体設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 71],
            [
                'name' => 'admin.view_master_sync_setting',
                'menu' => 'マスタ同期設定',
                'group_menu' => 'グループウェア設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 72],
            [
                'name' => 'admin.update_master_sync_setting',
                'menu' => 'マスタ同期設定',
                'group_menu' => 'グループウェア設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 73],
            [
                'name' => 'admin.view_app_use_setting',
                'menu' => 'スケジューラ制限設定',
                'group_menu' => 'スケジューラ設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 75],
            [
                'name' => 'admin.update_app_use_setting',
                'menu' => 'スケジューラ制限設定',
                'group_menu' => 'スケジューラ設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 74],
            [
                'name' => 'admin.view_app_use_setting',
                'menu' => 'アプリ利用設定',
                'group_menu' => 'グループウェア設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 76],
            [
                'name' => 'admin.update_app_use_setting',
                'menu' => 'アプリ利用設定',
                'group_menu' => 'グループウェア設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 77],
            [
                'name' => 'admin.view_app_role_setting',
                'menu' => 'アプリロール設定',
                'group_menu' => 'グループウェア設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 78],
            [
                'name' => 'admin.create_app_role_setting',
                'menu' => 'アプリロール設定',
                'group_menu' => 'グループウェア設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 79],
            [
                'name' => 'admin.update_app_role_setting',
                'menu' => 'アプリロール設定',
                'group_menu' => 'グループウェア設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 80],
            [
                'name' => 'admin.delete_app_role_setting',
                'menu' => 'アプリロール設定',
                'group_menu' => 'グループウェア設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 81],
            [
                'name' => 'admin.view_facility_setting',
                'menu' => '設備',
                'group_menu' => 'スケジューラ設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 82],
            [
                'name' => 'admin.create_facility_setting',
                'menu' => '設備',
                'group_menu' => 'スケジューラ設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 83],
            [
                'name' => 'admin.update_facility_setting',
                'menu' => '設備',
                'group_menu' => 'スケジューラ設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 84],
            [
                'name' => 'admin.delete_facility_setting',
                'menu' => '設備',
                'group_menu' => 'スケジューラ設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 85],
            [
                'name' => 'admin.delete_administrator_settings',
                'menu' => '管理者設定',
                'group_menu' => '管理者設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 86],
            [
                'name' => 'admin.view_template_route',
                'menu' => '承認ルート',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 87],
            [
                'name' => 'admin.create_template_route',
                'menu' => '承認ルート',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 88],
            [
                'name' => 'admin.update_template_route',
                'menu' => '承認ルート',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 89],
            [
                'name' => 'admin.view_hr_user_setting',
                'menu' => '利用ユーザ登録',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'view',
            ]);

             \DB::table('permissions')->updateOrInsert(
            ['id' => 90],
            [
                'name' => 'admin.create_hr_user_setting',
                'menu' => '利用ユーザ登録',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'create',
            ]);

             \DB::table('permissions')->updateOrInsert(
            ['id' => 91],
            [
                'name' => 'admin.update_hr_user_setting',
                'menu' => '利用ユーザ登録',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'update',
            ]);

             \DB::table('permissions')->updateOrInsert(
            ['id' => 92],
            [
                'name' => 'admin.delete_hr_user_setting',
                'menu' => '利用ユーザ登録',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);

             \DB::table('permissions')->updateOrInsert(
            ['id' => 93],
            [
                'name' => 'admin.view_daily_report_setting',
                'menu' => '日報確認',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'view',
            ]);

             \DB::table('permissions')->updateOrInsert(
            ['id' => 94],
            [
                'name' => 'admin.create_hr_user_setting',
                'menu' => '日報確認',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'create',
            ]);

             \DB::table('permissions')->updateOrInsert(
            ['id' => 95],
            [
                'name' => 'admin.update_daily_report_setting',
                'menu' => '日報確認',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'update',
            ]);

             \DB::table('permissions')->updateOrInsert(
            ['id' => 96],
            [
                'name' => 'admin.delete_hr_user_setting',
                'menu' => '日報確認',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);

             \DB::table('permissions')->updateOrInsert(
            ['id' => 97],
            [
                'name' => 'admin.view_schedule_list_setting',
                'menu' => '勤務表一覧',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'view',
            ]);

             \DB::table('permissions')->updateOrInsert(
            ['id' => 98],
            [
                'name' => 'admin.create_schedule_list_setting',
                'menu' => '勤務表一覧',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'create',
            ]);

             \DB::table('permissions')->updateOrInsert(
            ['id' => 99],
            [
                'name' => 'admin.update_schedule_list_setting',
                'menu' => '勤務表一覧',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'update',
            ]);

             \DB::table('permissions')->updateOrInsert(
            ['id' => 100],
            [
                'name' => 'admin.delete_schedule_list_setting',
                'menu' => '勤務表一覧',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);

             \DB::table('permissions')->updateOrInsert(
            ['id' => 101],
            [
                'name' => 'admin.view_work_confirm_setting',
                'menu' => '勤務確認',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'view',
            ]);

             \DB::table('permissions')->updateOrInsert(
            ['id' => 102],
            [
                'name' => 'admin.create_work_confirm_setting',
                'menu' => '勤務確認',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'create',
            ]);


             \DB::table('permissions')->updateOrInsert(
            ['id' => 103],
            [
                'name' => 'admin.update_work_confirm_setting',
                'menu' => '勤務確認',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'update',
            ]);


             \DB::table('permissions')->updateOrInsert(
            ['id' => 104],
            [
                'name' => 'admin.delete_work_confirm_setting',
                'menu' => '勤務確認',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 107],
            [
                'name' => 'admin.view_option_users',
                'menu' => 'グループウェア専用利用者',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 108],
            [
                'name' => 'admin.update_option_users',
                'menu' => 'グループウェア専用利用者',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 109],
            [
                'name' => 'admin.create_option_users',
                'menu' => 'グループウェア専用利用者',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 110],
            [
                'name' => 'admin.delete_option_users',
                'menu' => 'グループウェア専用利用者',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
        ['id' => 111],
        [
            'name' => 'admin.view_admin_issuance_setting',
            'menu' => '利用ユーザ登録',
            'group_menu' => 'ササッと明細',
            'guard_name' => 'web',
            'action' => 'view',
        ]);

        \DB::table('permissions')->updateOrInsert(
        ['id' => 112],
        [
            'name' => 'admin.create_admin_issuance_setting',
            'menu' => '利用ユーザ登録',
            'group_menu' => 'ササッと明細',
            'guard_name' => 'web',
            'action' => 'create',
        ]);

        \DB::table('permissions')->updateOrInsert(
        ['id' => 113],
        [
            'name' => 'admin.update_admin_issuance_setting',
            'menu' => '利用ユーザ登録',
            'group_menu' => 'ササッと明細',
            'guard_name' => 'web',
            'action' => 'update',
        ]);

        \DB::table('permissions')->updateOrInsert(
        ['id' => 114],
        [
            'name' => 'admin.delete_admin_issuance_setting',
            'menu' => '利用ユーザ登録',
            'group_menu' => 'ササッと明細',
            'guard_name' => 'web',
            'action' => 'delete',
        ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 115],
            [
                'name' => 'admin.view_long_term_storage_index_setting',
                'menu' => '長期保管インデックス設定',
                'group_menu' => '長期保管',
                'guard_name' => 'web',
                'action' => 'view',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 116],
            [
                'name' => 'admin.create_long_term_storage_index_setting',
                'menu' => '長期保管インデックス設定',
                'group_menu' => '長期保管',
                'guard_name' => 'web',
                'action' => 'create',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 117],
            [
                'name' => 'admin.update_long_term_storage_index_setting',
                'menu' => '長期保管インデックス設定',
                'group_menu' => '長期保管',
                'guard_name' => 'web',
                'action' => 'update',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 118],
            [
                'name' => 'admin.delete_long_term_storage_index_setting',
                'menu' => '長期保管インデックス設定',
                'group_menu' => '長期保管',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 119],
            [
                'name' => 'admin.view_colorcategory_setting',
                'menu' => 'カテゴリ設定',
                'group_menu' => 'スケジューラ設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 120],
            [
                'name' => 'admin.create_colorcategory_setting',
                'menu' => 'カテゴリ設定',
                'group_menu' => 'スケジューラ設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 121],
            [
                'name' => 'admin.update_colorcategory_setting',
                'menu' => 'カテゴリ設定',
                'group_menu' => 'スケジューラ設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 122],
            [
                'name' => 'admin.delete_colorcategory_setting',
                'menu' => 'カテゴリ設定',
                'group_menu' => 'スケジューラ設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        // PAC_14-45 休日設定 Start
        \DB::table('permissions')->updateOrInsert(
            ['id' => 123],
            [
                'name' => 'admin.view_holiday_setting',
                'menu' => '休日設定',
                'group_menu' => 'スケジューラ設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 124],
            [
                'name' => 'admin.create_holiday_setting',
                'menu' => '休日設定',
                'group_menu' => 'スケジューラ設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 125],
            [
                'name' => 'admin.update_holiday_setting',
                'menu' => '休日設定',
                'group_menu' => 'スケジューラ設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 126],
            [
                'name' => 'admin.delete_holiday_setting',
                'menu' => '休日設定',
                'group_menu' => 'スケジューラ設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        // PAC_14-45 End
        \DB::table('permissions')->updateOrInsert(
            ['id' => 127],
            [
                'name' => 'admin.view_special_site_upload',
                'menu' => '文書登録',
                'group_menu' => '特設サイト',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 128],
            [
                'name' => 'admin.create_special_site_upload',
                'menu' => '文書登録',
                'group_menu' => '特設サイト',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 129],
            [
                'name' => 'admin.update_special_site_upload',
                'menu' => '文書登録',
                'group_menu' => '特設サイト',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 130],
            [
                'name' => 'admin.delete_special_site_upload',
                'menu' => '文書登録',
                'group_menu' => '特設サイト',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 131],
            [
                'name' => 'admin.view_special_site_receive',
                'menu' => '連携承認',
                'group_menu' => '特設サイト',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 132],
            [
                'name' => 'admin.update_special_site_receive',
                'menu' => '連携承認',
                'group_menu' => '特設サイト',
                'guard_name' => 'web',
                'action' => 'update',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 133],
            [
                'name' => 'admin.view_special_site_send',
                'menu' => '連携申請',
                'group_menu' => '特設サイト',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 135],
            [
                'name' => 'admin.update_special_site_send',
                'menu' => '連携申請',
                'group_menu' => '特設サイト',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 136],
            [
                'name' => 'admin.view_timecard_setting',
                'menu' => 'タイムカード設定',
                'group_menu' => 'グループウェア設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 137],
            [
                'name' => 'admin.create_timecard_setting',
                'menu' => 'タイムカード設定',
                'group_menu' => 'グループウェア設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 138],
            [
                'name' => 'admin.update_timecard_setting',
                'menu' => 'タイムカード設定',
                'group_menu' => 'グループウェア設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 139],
            [
                'name' => 'admin.delete_timecard_setting',
                'menu' => 'タイムカード設定',
                'group_menu' => 'グループウェア設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 140],
            [
                'name' => 'admin.view_dispatcharea_setting',
                'menu' => '派遣先管理',
                'group_menu' => '派遣管理',
                'guard_name' => 'web',
                'action' => 'view',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 141],
            [
                'name' => 'admin.create_dispatcharea_setting',
                'menu' => '派遣先管理',
                'group_menu' => '派遣管理',
                'guard_name' => 'web',
                'action' => 'create',
            ]);


        \DB::table('permissions')->updateOrInsert(
            ['id' => 142],
            [
                'name' => 'admin.update_dispatcharea_setting',
                'menu' => '派遣先管理',
                'group_menu' => '派遣管理',
                'guard_name' => 'web',
                'action' => 'update',
            ]);


        \DB::table('permissions')->updateOrInsert(
            ['id' => 143],
            [
                'name' => 'admin.delete_dispatcharea_setting',
                'menu' => '派遣先管理',
                'group_menu' => '派遣管理',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 144],
            [
                'name' => 'admin.view_contract_setting',
                'menu' => '契約管理',
                'group_menu' => '派遣管理',
                'guard_name' => 'web',
                'action' => 'view',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 145],
            [
                'name' => 'admin.create_contract_setting',
                'menu' => '契約管理',
                'group_menu' => '派遣管理',
                'guard_name' => 'web',
                'action' => 'create',
            ]);


        \DB::table('permissions')->updateOrInsert(
            ['id' => 146],
            [
                'name' => 'admin.update_contract_setting',
                'menu' => '契約管理',
                'group_menu' => '派遣管理',
                'guard_name' => 'web',
                'action' => 'update',
            ]);


        \DB::table('permissions')->updateOrInsert(
            ['id' => 147],
            [
                'name' => 'admin.delete_contract_setting',
                'menu' => '契約管理',
                'group_menu' => '派遣管理',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 148],
            [
                'name' => 'admin.view_dispatchhr_setting',
                'menu' => '人材管理',
                'group_menu' => '派遣管理',
                'guard_name' => 'web',
                'action' => 'view',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 149],
            [
                'name' => 'admin.create_dispatchhr_setting',
                'menu' => '人材管理',
                'group_menu' => '派遣管理',
                'guard_name' => 'web',
                'action' => 'create',
            ]);


        \DB::table('permissions')->updateOrInsert(
            ['id' => 150],
            [
                'name' => 'admin.update_dispatchhr_setting',
                'menu' => '人材管理',
                'group_menu' => '派遣管理',
                'guard_name' => 'web',
                'action' => 'update',
            ]);


        \DB::table('permissions')->updateOrInsert(
            ['id' => 151],
            [
                'name' => 'admin.delete_dispatchhr_setting',
                'menu' => '人材管理',
                'group_menu' => '派遣管理',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);



        \DB::table('permissions')->updateOrInsert(
            ['id' => 152],
            [
                'name' => 'admin.view_receive_users',
                'menu' => '受信専用利用者',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 153],
            [
                'name' => 'admin.update_receive_users',
                'menu' => '受信専用利用者',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 154],
            [
                'name' => 'admin.create_receive_users',
                'menu' => '受信専用利用者',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 155],
            [
                'name' => 'admin.delete_receive_users',
                'menu' => '受信専用利用者',
                'group_menu' => '利用者設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 156],
            [
                'name' => 'admin.view_hr_admin_setting',
                'menu' => '管理ユーザ登録',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'view',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 157],
            [
                'name' => 'admin.create_hr_admin_setting',
                'menu' => '管理ユーザ登録',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'create',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 158],
            [
                'name' => 'admin.update_hr_admin_setting',
                'menu' => '管理ユーザ登録',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 159],
            [
                'name' => 'admin.view_long_term_folder',
                'menu' => '長期保管フォルダ管理',
                'group_menu' => '長期保管',
                'guard_name' => 'web',
                'action' => 'view',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 160],
            [
                'name' => 'admin.create_long_term_folder',
                'menu' => '長期保管フォルダ管理',
                'group_menu' => '長期保管',
                'guard_name' => 'web',
                'action' => 'create',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 161],
            [
                'name' => 'admin.update_long_term_folder',
                'menu' => '長期保管フォルダ管理',
                'group_menu' => '長期保管',
                'guard_name' => 'web',
                'action' => 'update',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 162],
            [
                'name' => 'admin.delete_long_term_folder',
                'menu' => '長期保管フォルダ管理',
                'group_menu' => '長期保管',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 163],
            [
                'name' => 'admin.view_admin_expense_setting',
                'menu' => '利用ユーザ管理',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 164],
            [
                'name' => 'admin.create_admin_expense_setting',
                'menu' => '利用ユーザ管理',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 165],
            [
                'name' => 'admin.update_admin_expense_setting',
                'menu' => '利用ユーザ管理',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 166],
            [
                'name' => 'admin.delete_admin_expense_setting',
                'menu' => '利用ユーザ管理',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 167],
            [
                'name' => 'admin.view_master_expense_setting',
                'menu' => 'マスタ管理',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 168],
            [
                'name' => 'admin.create_master_expense_setting',
                'menu' => 'マスタ管理',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 169],
            [
                'name' => 'admin.update_master_expense_setting',
                'menu' => 'マスタ管理',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 170],
            [
                'name' => 'admin.delete_master_expense_setting',
                'menu' => 'マスタ管理',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 171],
            [
                'name' => 'admin.view_style_expense_setting',
                'menu' => '様式管理',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 172],
            [
                'name' => 'admin.create_style_expense_setting',
                'menu' => '様式管理',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 173],
            [
                'name' => 'admin.update_style_expense_setting',
                'menu' => '様式管理',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 174],
            [
                'name' => 'admin.delete_style_expense_setting',
                'menu' => '様式管理',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 175],
            [
                'name' => 'admin.view_expense_app_list',
                'menu' => '経費申請一覧',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 176],
            [
                'name' => 'admin.create_expense_app_list',
                'menu' => '経費申請一覧',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 177],
            [
                'name' => 'admin.update_expense_app_list',
                'menu' => '経費申請一覧',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 178],
            [
                'name' => 'admin.delete_expense_app_list',
                'menu' => '経費申請一覧',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 179],
            [
                'name' => 'admin.view_expense_journal_list',
                'menu' => '経費仕訳一覧',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 180],
            [
                'name' => 'admin.create_expense_journal_list',
                'menu' => '経費仕訳一覧',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 181],
            [
                'name' => 'admin.update_expense_journal_list',
                'menu' => '経費仕訳一覧',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 182],
            [
                'name' => 'admin.delete_expense_journal_list',
                'menu' => '経費仕訳一覧',
                'group_menu' => '経費精算',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 183],
            [
                'name' => 'admin.view_frm_index_setting',
                'menu' => '明細項目設定',
                'group_menu' => 'ササッと明細',
                'guard_name' => 'web',
                'action' => 'view',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 184],
            [
                'name' => 'admin.create_frm_index_setting',
                'menu' => '明細項目設定',
                'group_menu' => 'ササッと明細',
                'guard_name' => 'web',
                'action' => 'create',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 185],
            [
                'name' => 'admin.update_frm_index_setting',
                'menu' => '明細項目設定',
                'group_menu' => 'ササッと明細',
                'guard_name' => 'web',
                'action' => 'update',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 186],
            [
                'name' => 'admin.delete_frm_index_setting',
                'menu' => '明細項目設定',
                'group_menu' => 'ササッと明細',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);

        // PAC_5-2663 : permissions id use [187; 190]

        \DB::table('permissions')->updateOrInsert(
            ['id' => 187],
            [
                'name' => 'admin.view_talk_users_setting',
                'menu' => 'ササッとTalk利用者設定',
                'group_menu' => 'ササッとTalk設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 188],
            [
                'name' => 'admin.update_talk_users_setting',
                'menu' => 'ササッとTalk利用者設定',
                'group_menu' => 'ササッとTalk設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 189],
            [
                'name' => 'admin.create_talk_users_setting',
                'menu' => 'ササッとTalk利用者設定',
                'group_menu' => 'ササッとTalk設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);

        \DB::table('permissions')->updateOrInsert(
            ['id' => 190],
            [
                'name' => 'admin.delete_talk_users_setting',
                'menu' => 'ササッとTalk利用者設定',
                'group_menu' => 'ササッとTalk設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);

        /*
         * 3190-3191
         */
         \DB::table('permissions')->updateOrInsert(
            ['id' => 191],
            [
                'name' => 'admin.view_hr_working_hour',
                'menu' => '就労時間管理',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 192],
            [
                'name' => 'admin.create_hr_working_hour',
                'menu' => '就労時間管理',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 193],
            [
                'name' => 'admin.update_hr_working_hour',
                'menu' => '就労時間管理',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 194],
            [
                'name' => 'admin.delete_hr_working_hour',
                'menu' => '就労時間管理',
                'group_menu' => 'HR機能',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 199],
            [
                'name' => 'admin.view_attachments',
                'menu' => '添付ファイル一覧',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 200],
            [
                'name' => 'admin.delete_attachments',
                'menu' => '添付ファイル一覧',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 201],
            [
                'name' => 'admin.view_circulars_saved',
                'menu' => '保存文書一覧',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 202],
            [
                'name' => 'admin.delete_circulars_saved',
                'menu' => '保存文書一覧',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 203],
            [
                'name' => 'admin.view_biz_cards',
                'menu' => '名刺一覧',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 204],
            [
                'name' => 'admin.update_biz_cards',
                'menu' => '名刺一覧',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'update',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 205],
            [
                'name' => 'admin.delete_biz_cards',
                'menu' => '名刺一覧',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 206],
            [
                'name' => 'admin.view_template_csv',
                'menu' => '回覧完了テンプレート一覧',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 207],
            [
                'name' => 'admin.view_template_index',
                'menu' => 'テンプレート',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'view',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 208],
            [
                'name' => 'admin.create_template_index',
                'menu' => 'テンプレート',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'create',
            ]);
        \DB::table('permissions')->updateOrInsert(
            ['id' => 209],
            [
                'name' => 'admin.delete_template_index',
                'menu' => 'テンプレート',
                'group_menu' => '機能設定',
                'guard_name' => 'web',
                'action' => 'delete',
            ]);





    }
}
