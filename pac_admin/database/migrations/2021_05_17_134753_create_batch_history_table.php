<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchHistoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('batch_history', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('execution_date');
			$table->string('batch_name');
			$table->integer('status');
			$table->timestamps(3);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('batch_history');
	}

}
