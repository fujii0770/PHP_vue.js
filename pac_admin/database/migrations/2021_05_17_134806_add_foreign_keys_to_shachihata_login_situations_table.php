<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToShachihataLoginSituationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('shachihata_login_situations', function(Blueprint $table)
		{
			$table->foreign('mst_shachihata_id')->references('id')->on('mst_shachihata')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('shachihata_login_situations', function(Blueprint $table)
		{
			$table->dropForeign('shachihata_login_situations_mst_shachihata_id_foreign');
		});
	}

}
