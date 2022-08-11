<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLongTermDepartmentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('long_term_department', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('long_term_document_id')->unsigned();
			$table->string('department_id');
			$table->string('create_user', 128)->nullable();
			$table->string('update_user', 128)->nullable();
			$table->dateTime('create_at');
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
		Schema::drop('long_term_department');
	}

}
