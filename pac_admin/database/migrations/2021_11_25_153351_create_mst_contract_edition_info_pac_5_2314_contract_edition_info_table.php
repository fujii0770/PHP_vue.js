<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstContractEditionInfoPac52314ContractEditionInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_contract_edition_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mst_contract_edition_id')->comment('契約エディションマスタID');
            $table->integer('department_stamp_flg')->default(0)->comment('部署名入り日付印');
            $table->integer('rotate_angle_flg')->default(0)->comment('おじぎ印');
            $table->integer('attachment_flg')->default(0)->comment('添付ファイル機能');
            $table->integer('convenient_flg')->default(0)->comment('便利印');
            $table->integer('sticky_notes')->default(0)->comment('付箋機能(今後予定)');
            $table->integer('default_stamp_flg')->default(0)->comment('デフォルト印');
            $table->integer('esigned_flg')->default(0)->comment('PDFへの電子署名付加');
            $table->integer('signature_flg')->default(0)->comment('電子証明書設定');
            $table->integer('stamp_flg')->default(0)->comment('タイムスタンプ付署名');
            $table->integer('timestamps_count')->default(0)->comment('タイムスタンプ契約（回）');
            $table->integer('time_stamp_issuing_count')->default(0)->comment('タイムスタンプ発行を自社でカウント');
            $table->integer('long_term_storage_flg')->default(0)->comment('長期保管');
            $table->integer('long_term_storage_option_flg')->default(0)->comment('長期保管オプション');
            $table->integer('max_usable_capacity')->default(0)->comment('長期保管使用容量(GB)');
            $table->integer('hr_flg')->default(0)->comment('HR機能の使用許可');
            $table->integer('option_user_flg')->default(0)->comment('オプション限定利用者');
            $table->integer('bizcard_flg')->default(0)->comment('名刺機能');
            $table->integer('local_stamp_flg')->default(0)->comment('ローカル捺印');
            $table->integer('dispatch_flg')->default(0)->comment('派遣機能');
            $table->integer('template_route_flg')->default(0)->comment('承認ルート');
            $table->integer('phone_app_flg')->default(0)->comment('携帯アプリ');
            $table->integer('portal_flg')->default(0)->comment('ポータル機能');
            $table->integer('usage_flg')->default(0)->comment('利用状況');
            $table->integer('confidential_flg')->default(0)->comment('社外秘');
            $table->integer('ip_restriction_flg')->default(0)->comment('接続IP制限');
            $table->integer('permit_unregistered_ip_flg')->default(0)->comment('登録外IPのログイン許可');
            $table->integer('repage_preview_flg')->default(0)->comment('改ページ調整プレビュー');
            $table->integer('box_enabled')->default(0)->comment('外部連携');
            $table->integer('mfa_flg')->default(0)->comment('多要素認証');
            $table->integer('template_flg')->default(0)->comment('テンプレート機能');
            $table->integer('template_search_flg')->default(0)->comment('テンプレート検索機能');
            $table->integer('template_csv_flg')->default(0)->comment('テンプレートcsv出力機能');
            $table->integer('multiple_department_position_flg')->default(0)->comment('部署・役職複数登録');
            $table->integer('discussion_flg')->default(0)->comment('合議機能(今後予定)');
            $table->integer('frm_srv_flg')->default(0)->comment('帳票発行サービスの使用許可');
            $table->integer('with_box_flg')->default(0)->comment('with box');
            $table->integer('enable_email')->default(0)->comment('メール(企業)');
            $table->integer('sanitizing_flg')->default(0)->comment('ダウンロードファイル無害化');
            $table->integer('received_only_flg')->default(0)->comment('受信のみ');
            $table->integer('email_format')->default(0)->comment('メールフォーマット');
            $table->integer('addressbook_only_flag')->default(0)->comment('送信先の制限');
            $table->integer('view_notification_email_flg')->default(0)->comment('閲覧通知メール設定');
            $table->integer('updated_notification_email_flg')->default(0)->comment('更新通知メール設定');
            $table->integer('enable_email_thumbnail')->default(0)->comment('メール内の文書のサムネイル表示');
            $table->integer('receive_user_flg')->default(0)->comment('受信専用利用者');
            $table->integer('board_flg')->default(0)->comment('グループウェア機能・掲示板');
            $table->integer('pdf_annotation_flg')->default(0)->comment('捺印情報表示(PDF)');
            $table->integer('scheduler_flg')->default(0)->comment('スケジューラー');
            $table->integer('scheduler_limit_flg')->default(0)->comment('スケジューラー無制限');
            $table->integer('scheduler_buy_count')->default(0)->comment('スケジューラー購入数');
            $table->integer('caldav_flg')->default(0)->comment('CalDAV(カレンダー連携)');
            $table->integer('caldav_limit_flg')->default(0)->comment('CalDAV無制限');
            $table->integer('caldav_buy_count')->default(0)->comment('CalDAV購入数');
            $table->integer('google_flg')->default(0)->comment('Google連携');
            $table->integer('outlook_flg')->default(0)->comment('Outlook連携');
            $table->integer('apple_flg')->default(0)->comment('Apple連携');
            $table->integer('file_mail_flg')->default(0)->comment('ファイルメール便');
            $table->integer('file_mail_limit_flg')->default(0)->comment('ファイルメール便無制限');
            $table->integer('file_mail_buy_count')->default(0)->comment('ファイルメール便購入数');
            $table->integer('attendance_flg')->default(0)->comment('タイムカード');
            $table->integer('attendance_limit_flg')->default(0)->comment('タイムカード無制限');
            $table->integer('attendance_buy_count')->default(0)->comment('タイムカード購入数');
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('create_user',128)->comment('作成者');
            $table->dateTime('update_at')->nullable()->comment('更新日時');
            $table->string('update_user',128)->nullable()->comment('更新者');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_contract_edition_info');
    }
}
