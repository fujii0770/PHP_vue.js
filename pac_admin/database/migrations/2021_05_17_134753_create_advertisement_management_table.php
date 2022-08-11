<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisementManagementTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('advertisement_management', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_advertisement_id')->unsigned()->comment('統合ID管理テーブルの広告マスタ.id');
			$table->bigInteger('mst_company_id')->unsigned()->index('advertisement_management_mst_company_id_foreign');
			$table->bigInteger('mst_department_id')->unsigned()->nullable()->index('advertisement_management_mst_department_id_foreign');
			$table->bigInteger('mst_position_id')->unsigned()->nullable()->index('advertisement_management_mst_position_id_foreign');
			$table->dateTime('create_at');
			$table->string('create_user', 128);
			$table->dateTime('update_at')->nullable();
			$table->string('update_user', 128)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('advertisement_management');
	}

}
