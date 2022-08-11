<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToContractTablePac51868 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('contract', function(Blueprint $table)
		{
			$table->foreign('mst_admin_id')->references('id')->on('mst_admin')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('contract', function(Blueprint $table)
		{
			$table->dropForeign('contract_mst_admin_id_foreign');
		});
	}

}

