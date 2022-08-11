<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSynonumsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('synonums', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('origin', 8);
			$table->string('synonym', 8);
			$table->datetime('create_at')->useCurrent();
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
		Schema::drop('synonums');
	}

}
