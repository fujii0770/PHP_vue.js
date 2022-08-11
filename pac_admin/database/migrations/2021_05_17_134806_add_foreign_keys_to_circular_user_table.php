<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCircularUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('circular_user', function(Blueprint $table)
		{
			$table->foreign('circular_id', 'circular_user_ibfk_1')->references('id')->on('circular')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('circular_user', function(Blueprint $table)
		{
			$table->dropForeign('circular_user_ibfk_1');
		});
	}

}
