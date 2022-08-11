<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstAuditTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_audit', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->bigInteger('mst_company_id')->unsigned()->index('mst_audit_mst_company_id_foreign');
			$table->string('login_id', 128);
			$table->string('account_name', 64);
			$table->string('email', 256);
			$table->string('password', 512)->nullable();
			$table->date('expiration_date')->nullable();
			$table->string('remember_token', 128)->nullable();
			$table->dateTime('password_change_date')->nullable();
			$table->tinyInteger('state_flg');
			$table->dateTime('create_at')->useCurrent();
			$table->string('create_user', 128);
			$table->dateTime('update_at')->nullable();
			$table->string('update_user', 128)->nullable();
        });
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_audit');
	}

}
