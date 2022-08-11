<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDispatchhrInfoTablePac51869 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('dispatchhr_info', function(Blueprint $table)
		{
			$table->foreign('dispatchhr_id')->references('id')->on('dispatchhr')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('dispatchhr_screenitems_id')->references('id')->on('dispatchhr_screenitems')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('dispatchhr_info', function(Blueprint $table)
		{
			$table->dropForeign('dispatchhr_info_dispatchhr_id_foreign');
			$table->dropForeign('dispatchhr_info_dispatchhr_screenitems_id_foreign');
		});
	}

}

