<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToFavoriteServiceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('favorite_service', function(Blueprint $table)
		{
			$table->foreign('mst_user_id')->references('id')->on('mst_user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('favorite_service', function(Blueprint $table)
		{
			$table->dropForeign('favorite_service_mst_user_id_foreign');
		});
	}

}
