<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewingUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('viewing_user', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('circular_id')->unsigned()->index();
			$table->integer('parent_send_order');
			$table->bigInteger('mst_company_id')->unsigned();
			$table->bigInteger('mst_user_id')->unsigned()->index('INX_mst_user_id');
			$table->text('memo');
			$table->integer('del_flg');
			$table->string('create_user', 128);
			$table->string('update_user', 128)->nullable();
			$table->dateTime('create_at')->nullable();
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('origin_circular_url', 516)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('viewing_user');
	}

}
