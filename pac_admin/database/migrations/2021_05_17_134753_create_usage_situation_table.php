<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsageSituationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('usage_situation', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_company_id')->unsigned()->index('usage_situation_mst_company_id_foreign');
			$table->string('company_name', 256);
			$table->string('company_name_kana', 256);
			$table->integer('target_month');
			$table->integer('user_total_count');
			$table->integer('total_name_stamp');
			$table->integer('total_date_stamp');
			$table->integer('total_common_stamp');
			$table->string('max_date', 10)->nullable();
			$table->integer('total_time_stamp');
			$table->string('create_user', 128);
			$table->string('update_user', 128)->nullable();
			$table->dateTime('create_at');
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->integer('storage_use_capacity')->nullable();
			$table->integer('guest_company_id')->unsigned()->nullable();
			$table->string('guest_company_name', 256)->nullable();
			$table->string('guest_company_name_kana', 256)->nullable();
			$table->integer('guest_company_app_env');
			$table->integer('guest_company_contract_server');
			$table->integer('guest_user_total_count')->default(0);
			$table->integer('same_domain_number')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('usage_situation');
	}

}
