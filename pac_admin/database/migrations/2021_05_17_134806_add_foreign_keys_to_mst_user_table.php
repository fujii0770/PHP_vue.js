<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToMstUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('mst_user', function(Blueprint $table)
		{
			$table->foreign('mst_company_id', 'mst_user_ibfk_1')->references('id')->on('mst_company')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('mst_user', function(Blueprint $table)
		{
			$table->dropForeign('mst_user_ibfk_1');
		});
	}

}
