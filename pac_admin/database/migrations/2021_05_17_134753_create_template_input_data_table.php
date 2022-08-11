<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateInputDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('template_input_data', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('template_id')->unsigned();
			$table->bigInteger('circular_id')->unsigned();
			$table->bigInteger('user_id')->unsigned();
			$table->longText('template_placeholder_name');
			$table->longText('template_placeholder_data');
			$table->tinyInteger('data_type');
			$table->dateTime('date_data')->nullable();
			$table->decimal('num_data', 15, 5)->nullable();
			$table->longText('text_data')->nullable();
			$table->dateTime('create_at');
			$table->string('create_user', 128);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('template_input_data');
	}

}
