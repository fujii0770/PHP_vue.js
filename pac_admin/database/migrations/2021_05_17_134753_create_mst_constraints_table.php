<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstConstraintsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_constraints', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_company_id')->unsigned()->index('INX_mst_company_id');
			$table->integer('max_requests')->comment('0は無制限

～999,999,999回');
			$table->integer('max_document_size')->comment('～999MB

テーブルに格納するとき、MB→Bに変換');
			$table->bigInteger('user_storage_size')->comment('Byte単位にて格納');
			$table->integer('use_storage_percent')->unsigned()->comment('1～100の間で設定');
			$table->integer('max_keep_days')->unsigned()->comment('～9,999日');
			$table->integer('delete_informed_days_ago')->comment('～9,999日');
			$table->datetime('create_at')->useCurrent();
			$table->string('create_user', 128)->nullable();
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('update_user', 128)->nullable();
			$table->integer('long_term_storage_percent')->unsigned()->default(90);
			$table->bigInteger('dl_max_keep_days')->unsigned()->default(30)->comment('ダウンロード要求からの最大保存期間');
			$table->integer('dl_after_proc')->default(0)->comment('0：削除 1：一定期間保持後削除');
			$table->bigInteger('dl_after_keep_days')->unsigned()->default(0)->comment('ダウンロード後からの最大保存期間');
			$table->integer('dl_request_limit')->default(0)->comment('0：無制限');
			$table->bigInteger('dl_file_total_size_limit')->unsigned()->default(1048576)->comment('ファイル容量(システム総量)(単位:MB)');
			$table->integer('max_ip_address_count')->unsigned()->default(40)->comment('接続IP制限設定 上限値');
			$table->integer('max_viwer_count')->unsigned()->default(10)->comment('閲覧ユーザー設定上限数');
        });
        DB::statement("alter table mst_constraints comment '企業に関する設定のうち、マスタ管理者のみに変更可能なもののデータを格納する。\r\n\r\nPDFへの電子証明書付加（esigned';");
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_constraints');
	}

}
