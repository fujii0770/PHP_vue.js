<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstLimitTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_limit', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('id');
			$table->bigInteger('mst_company_id')->unsigned()->index('mst_company_id')->comment('会社マスタID');
			$table->integer('storage_local')->comment("使用するストレージ（ローカル）;0：使用不可\r\n1：使用可");
			$table->integer('storage_box')->comment("使用するストレージ（BOX）;0：使用不可\r\n1：使用可");
			$table->integer('storage_google')->comment("使用するストレージ（GoogleDrive）;0：使用不可\r\n1：使用可");
			$table->integer('storage_dropbox')->comment("使用するストレージ（Dropbox）;0：使用不可\r\n1：使用可");
			$table->integer('storage_onedrive')->comment("使用するストレージ（OneDrive）;0：使用不可\r\n1：使用可");
			$table->integer('enable_any_address')->comment("送信先の制限;0：制限しない\r\n1：共通アドレス帳と管理者が登録した利用者のアドレスのみに制限する");
			$table->integer('link_auth_flg')->comment("リンク認証フラグ;0：不要\r\n1：必要");
			$table->integer('enable_email_thumbnail')->comment("通知メール内のサムネイル表示;0：表示しない\r\n1：表示する");
			$table->integer('receiver_permission')->comment("受取人の追加削除の権限;0：不可\r\n1：可");
			$table->dateTime('create_at')->comment("作成日時");
			$table->string('create_user', 128)->nullable()->comment("作成者");
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('update_user', 128)->nullable()->comment("更新者");
			$table->integer('environmental_selection_dialog')->default(0);
			$table->integer('time_stamp_permission')->default(0)->comment('0：無効|1：有効');
			$table->integer('box_enabled_automatic_storage')->unsigned()->default(0)->comment('自動保管(1:有効 0:無効)');
			$table->string('box_enabled_folder_to_store', 512)->nullable()->comment('保管先フォルダ');
			$table->string('box_auto_save_folder_id', 20)->nullable()->default('')->comment('保管先フォルダID');
			$table->string('box_enabled_output_file', 16)->nullable()->comment('出力ファイル(1:署名なし・捺印履歴なし 2:署名なし・捺印履歴あり 3:署名あり・捺印履歴なし 4:署名あり・捺印履歴あり)');
			$table->integer('box_enabled_automatic_delete')->unsigned()->default(0)->comment('保管後の自動削除(1:有効 0:無効)');
			$table->integer('box_max_auto_delete_days')->unsigned()->default(14)->comment('自動削除の保管期限(デフォルト:14)');
			$table->string('box_refresh_token', 128)->nullable()->comment('Box更新トークン');
			$table->integer('mfa_login_timing_flg')->unsigned()->default(1)->comment('多要素認証ログインタイミング 0:毎回|1:指定日数毎');
			$table->integer('mfa_interval_hours')->unsigned()->default(12)->comment('多要素認証ログイン保持指定日数');
        });
        DB::statement("alter table mst_limit comment '制限マスタ';");
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_limit');
	}

}
