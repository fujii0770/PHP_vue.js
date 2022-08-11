<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstShachihataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_shachihata', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('name', 128);
			$table->string('email', 256)->unique('email');
			$table->string('password', 512)->comment('ハッシュ化したパスワード');
			$table->datetime('create_at')->useCurrent();
			$table->string('create_user', 128)->nullable();
            $table->dateTime('update_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('update_user', 128)->nullable();
			$table->string('remember_token', 128)->nullable();
			$table->dateTime('last_mfa_login_at')->nullable();
			$table->string('one_time_password', 512)->nullable()->comment('ハッシュ化したワンタイムパスワード');
			$table->dateTime('one_time_password_expires_at')->nullable();
			$table->integer('email_auth_flg')->default(0)->comment('0:無効|1:有効');
			$table->integer('email_auth_dest_flg')->default(0)->comment('0:登録メールアドレス|1:その他アドレス');
			$table->string('auth_email', 256)->nullable();
        });
        DB::statement("alter table mst_shachihata comment 'システム管理者ユーザの\r\nmaster@shachihata.co.jp専用テーブル\r\n（1レコードのみ登録）';");
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_shachihata');
	}

}
