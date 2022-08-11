<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAdminLoginSituationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('admin_login_situations', function(Blueprint $table)
		{
			$table->foreign('mst_admin_id')->references('id')->on('mst_admin')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('admin_login_situations', function(Blueprint $table)
		{
			$table->dropForeign('admin_login_situations_mst_admin_id_foreign');
		});
	}

}
