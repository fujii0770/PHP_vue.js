<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDispatchhrTablePac51869 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('dispatchhr', function(Blueprint $table)
		{
			$table->foreign('mst_company_id')->references('id')->on('mst_company')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('dispatchhr', function(Blueprint $table)
		{
			$table->dropForeign('dispatchhr_mst_company_id_foreign');
		});
	}

}

