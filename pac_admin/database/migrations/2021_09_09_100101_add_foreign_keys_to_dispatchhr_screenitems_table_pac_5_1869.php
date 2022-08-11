<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDispatchhrScreenitemsTablePac51869 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('dispatchhr_screenitems', function(Blueprint $table)
		{
			$table->foreign('dispatchhr_template_id')->references('id')->on('dispatchhr_template')->onUpdate('CASCADE')->onDelete('CASCADE');
			});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('dispatchhr_screenitems', function(Blueprint $table)
		{
			$table->dropForeign('dispatchhr_screenitems_dispatchhr_template_id_foreign');
		});
	}

}

