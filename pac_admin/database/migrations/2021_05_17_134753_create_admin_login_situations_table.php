<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAdminLoginSituationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_login_situations', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('id');
			$table->bigInteger('mst_admin_id')->unsigned()->index('admin_login_situations_mst_admin_id_index_1')->comment('管理者マスタID');
			$table->string('ip_address', 15)->comment('IPアドレス');
			$table->string('user_agent', 2048)->comment('ユーザーエージェント');
			$table->dateTime('create_at')->comment('作成日時');
			$table->string('create_user', 128)->comment('作成者');
			$table->dateTime('update_at')->index('admin_login_situations_update_at_index_1');
			$table->string('update_user', 128)->comment('更新者');
		});
        DB::statement("ALTER TABLE admin_login_situations MODIFY COLUMN update_at dateTime NOT NULL ON UPDATE CURRENT_TIMESTAMP(0);");
        DB::statement("alter table admin_login_situations comment '管理者ログイン状況テーブル';");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admin_login_situations');
	}

}
