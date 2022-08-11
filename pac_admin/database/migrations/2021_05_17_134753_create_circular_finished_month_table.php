<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCircularFinishedMonthTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('circular_finished_month', function(Blueprint $table)
		{
			$table->bigInteger('circular_id')->primary()->comment('回覧ID');
			$table->string('month', 6)->nullable()->comment('完了日時');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('circular_finished_month');
	}

}
