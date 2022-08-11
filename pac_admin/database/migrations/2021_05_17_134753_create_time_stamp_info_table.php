<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimeStampInfoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('time_stamp_info', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('circular_document_id')->unsigned();
			$table->bigInteger('mst_company_id')->unsigned()->nullable();
			$table->bigInteger('mst_user_id')->unsigned()->nullable();
			$table->dateTime('create_at');
			$table->integer('app_env');
			$table->integer('contract_server');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('time_stamp_info');
	}

}
