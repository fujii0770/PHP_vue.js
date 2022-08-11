<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticeReadManagementTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notice_read_management', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('notice_management_id')->unsigned()->index('notice_read_management_notice_management_id_foreign');
			$table->bigInteger('mst_user_id')->unsigned()->index('notice_read_management_mst_user_id_foreign');
			$table->boolean('is_read')->nullable()->comment('1:既読 1以外:未読');
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
		Schema::drop('notice_read_management');
	}

}
