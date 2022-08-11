<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_user', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_company_id')->unsigned()->index('idx_mst_user_on_mst_company_id');
			$table->string('login_id', 128)->unique('login_id');
			$table->integer('system_id')->unsigned();
			$table->string('family_name', 64);
			$table->string('given_name', 64);
			$table->string('email', 256);
			$table->string('password', 512)->comment('ハッシュ化したパスワード');
			$table->integer('state_flg')->comment('-1：削除
0：登録（パスワード設定前）
1：有効
9：無効');
			$table->integer('amount')->unsigned()->comment('ファイルサイズの合計をMB換算にて格納');
			$table->datetime('create_at')->useCurrent();
			$table->string('create_user', 128);
			$table->dateTime('update_at')->nullable()->useCurrent();
			$table->string('update_user', 128)->nullable();
			$table->string('remember_token', 128)->nullable();
			$table->dateTime('password_change_date')->nullable();
			$table->dateTime('last_mfa_login_at')->nullable();
			$table->string('one_time_password', 512)->nullable()->comment('ハッシュ化したワンタイムパスワード');
			$table->dateTime('one_time_password_expires_at')->nullable();
			$table->integer('one_time_password_confirmed')->nullable()->comment('null:未発行|0:未確認|1:確認済(QRコード認証で使用)');
			$table->dateTime('delete_at')->nullable();
			$table->dateTime('invalid_at')->nullable();
        });
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_user');
	}

}
