<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToNoticeManagementTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('notice_management', function(Blueprint $table)
		{
			$table->foreign('mst_company_id')->references('id')->on('mst_company')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('mst_department_id')->references('id')->on('mst_department')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('mst_position_id')->references('id')->on('mst_position')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('notice_management', function(Blueprint $table)
		{
			$table->dropForeign('notice_management_mst_company_id_foreign');
			$table->dropForeign('notice_management_mst_department_id_foreign');
			$table->dropForeign('notice_management_mst_position_id_foreign');
		});
	}

}
