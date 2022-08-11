<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToNoticeReadManagementTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('notice_read_management', function(Blueprint $table)
		{
			$table->foreign('mst_user_id')->references('id')->on('mst_user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('notice_management_id')->references('id')->on('notice_management')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('notice_read_management', function(Blueprint $table)
		{
			$table->dropForeign('notice_read_management_mst_user_id_foreign');
			$table->dropForeign('notice_read_management_notice_management_id_foreign');
		});
	}

}
