<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstMypageLayoutTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_mypage_layout', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('layout_name', 50);
			$table->text('layout_src');
			$table->text('layout')->comment('Json保存');
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
		Schema::drop('mst_mypage_layout');
	}

}
