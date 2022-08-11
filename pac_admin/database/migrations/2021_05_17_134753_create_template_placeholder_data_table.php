<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatePlaceholderDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('template_placeholder_data', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('template_file_id')->unsigned();
			$table->longText('template_placeholder_name');
			$table->string('cell_address', 8)->nullable();
			$table->dateTime('template_create_at');
			$table->string('template_create_user', 128);
			$table->dateTime('template_update_at')->nullable();
			$table->string('template_update_user', 128)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('template_placeholder_data');
	}

}
