<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToGuestUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('guest_user', function(Blueprint $table)
		{
			$table->foreign('circular_id')->references('id')->on('circular')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('guest_user', function(Blueprint $table)
		{
			$table->dropForeign('guest_user_circular_id_foreign');
		});
	}

}
