<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembersTempDistinctTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('members_temp_distinct', function(Blueprint $table)
		{
			$table->integer('rowid')->comment('行id');
			$table->string('email', 256)->nullable()->comment('メールアドレス');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('members_temp_distinct');
	}

}
