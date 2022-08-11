<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDispatchhrJobcareerTablePac51869 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('dispatchhr_jobcareer', function(Blueprint $table)
		{
			$table->foreign('dispatchhr_id')->references('id')->on('dispatchhr')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('dispatchhr_jobcareer', function(Blueprint $table)
		{
			$table->dropForeign('dispatchhr_jobcareer_dispatchhr_id_foreign');
		});
	}

}

