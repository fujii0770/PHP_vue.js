<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToMstLongTermSaveTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('mst_long_term_save', function(Blueprint $table)
		{
			$table->foreign('mst_company_id')->references('id')->on('mst_company')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('mst_long_term_save', function(Blueprint $table)
		{
			$table->dropForeign('mst_long_term_save_mst_company_id_foreign');
		});
	}

}
