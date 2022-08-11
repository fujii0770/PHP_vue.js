<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstProtectionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_protection', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_company_id')->unsigned()->index('mst_protection_mst_company_id_foreign');
			$table->integer('protection_setting_change_flg')->comment('0：有効にする|1：有効にしない');
			$table->integer('destination_change_flg')->comment('0：許可する|1：許可しない');
			$table->integer('enable_email_thumbnail')->comment('0：表示する|1：表示しない');
			$table->integer('access_code_protection')->comment('0：保護する|1：保護しない');
			$table->dateTime('create_at');
			$table->string('create_user', 128)->nullable();
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
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
		Schema::drop('mst_protection');
	}

}
