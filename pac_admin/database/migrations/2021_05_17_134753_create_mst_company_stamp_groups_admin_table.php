<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstCompanyStampGroupsAdminTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_company_stamp_groups_admin', function(Blueprint $table)
		{
			$table->integer('group_id')->comment('共通印グループID');
			$table->integer('mst_admin_id')->comment('企業ID');
			$table->integer('state')->comment('0:無効|1:有効');
			$table->datetime('create_at')->useCurrent();
			$table->string('create_user', 128);
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('update_user', 128)->nullable();
			$table->primary(['group_id','mst_admin_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_company_stamp_groups_admin');
	}

}
