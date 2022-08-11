<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToMstAssignStampTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('mst_assign_stamp', function(Blueprint $table)
		{
			$table->foreign('mst_user_id', 'mst_assign_stamp_ibfk_1')->references('id')->on('mst_user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('mst_assign_stamp', function(Blueprint $table)
		{
			$table->dropForeign('mst_assign_stamp_ibfk_1');
		});
	}

}
