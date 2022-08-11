<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstCompanyStampOrderHistoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_company_stamp_order_history', function(Blueprint $table)
		{
			$table->string('pdf_number', 25)->comment('共通印申込書番号');
			$table->integer('mst_admin_id')->comment('申込者番号');
			$table->datetime('create_at')->useCurrent();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_company_stamp_order_history');
	}

}
