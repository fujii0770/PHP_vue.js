<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstTemplatePlaceholderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_template_placeholder', function(Blueprint $table)
		{
			$table->bigInteger('id')->unsigned();
			$table->longtext('special_template_placeholder');
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
		Schema::drop('mst_template_placeholder');
	}

}
