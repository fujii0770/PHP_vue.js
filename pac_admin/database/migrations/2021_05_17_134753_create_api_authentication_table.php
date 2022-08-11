<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiAuthenticationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('api_authentication', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('api_name', 64)->comment('Api名');
			$table->string('access_id', 64)->comment('認証アカウント');
			$table->string('access_code', 64)->comment('認証パスワード');
			$table->datetime('create_at')->useCurrent();
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('api_authentication');
	}

}
