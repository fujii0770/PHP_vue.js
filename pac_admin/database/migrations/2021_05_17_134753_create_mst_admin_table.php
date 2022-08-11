<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstAdminTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_admin', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('id');
			$table->bigInteger('mst_company_id')->unsigned()->index('idx_mst_admin_on_mst_company_id')->comment('会社マスタID');
			$table->string('login_id', 128)->comment('ログインID');
			$table->string('given_name', 64)->comment('名');
			$table->string('family_name', 64)->nullable()->comment('姓');
			$table->string('email', 256)->comment('メールアドレス');
			$table->string('password', 512)->nullable()->comment('パスワード;ハッシュ化したパスワード');
			$table->integer('role_flg')->comment("役割フラグ;0：通常管理者\r\n1：企業管理者");
			$table->string('department_name', 256)->nullable()->comment('部署名');
			$table->string('phone_number', 15)->nullable()->comment('電話番号');
			$table->integer('state_flg')->nullable()->comment("状態フラグ;-1：削除\r\n0：登録（パスワード設定前）\r\n1：有効\r\n9：無効");
			$table->datetime('create_at')->useCurrent()->comment('作成日時');
			$table->string('create_user', 128)->comment('作成者');
			$table->dateTime('update_at')->nullable()->comment('更新日時');
			$table->string('update_user', 128)->nullable()->comment('更新者');
			$table->string('remember_token', 128)->nullable()->comment('ログイントークン');
			$table->dateTime('password_change_date')->nullable()->comment('パスワード変更日');
			$table->dateTime('last_mfa_login_at')->nullable();
			$table->string('one_time_password', 512)->nullable()->comment('ハッシュ化したワンタイムパスワード');
			$table->dateTime('one_time_password_expires_at')->nullable();
			$table->integer('email_auth_flg')->default(0)->comment('0:無効|1:有効');
			$table->integer('email_auth_dest_flg')->default(0)->comment('0:登録メールアドレス|1:その他アドレス');
			$table->string('auth_email', 256)->nullable();
			$table->integer('enable_email')->default(1)->comment('0:無効/1:有効');
			$table->integer('email_format')->default(1)->comment('0:テキスト/1:HTML');
        });
        DB::statement("alter table mst_admin comment '管理者テーブル';");
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_admin');
	}

}
