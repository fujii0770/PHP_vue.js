<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateFileTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('template_file', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_company_id')->unsigned();
			$table->bigInteger('mst_user_id')->unsigned();
			$table->string('file_name', 256);
			$table->string('storage_file_name', 256);
			$table->string('location', 1024);
			$table->tinyInteger('document_type');
			$table->tinyInteger('document_access_flg');
			$table->dateTime('template_create_at');
			$table->string('template_create_user', 128);
			$table->dateTime('template_update_at')->nullable();
			$table->string('template_update_user', 128)->nullable();
            $table->tinyInteger('is_generation_flg')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('template_file');
	}

}
