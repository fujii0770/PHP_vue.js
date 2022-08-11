<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsagesDailyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('usages_daily', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('PK連番自動設定');
			$table->bigInteger('mst_company_id')->unsigned()->comment('企業ID');
			$table->string('company_name', 256)->comment('企業名前');
			$table->string('company_name_kana', 256)->comment('企業名前カナ');
			$table->date('date')->comment('日付');
			$table->integer('new_requests')->comment('申請件数');
			$table->integer('guest_company_id')->unsigned()->nullable()->comment('ゲスト企業ID');
			$table->string('guest_company_name', 256)->nullable()->comment('ゲスト企業名前');
			$table->string('guest_company_name_kana', 256)->nullable()->comment('ゲスト企業名前カナ');
			$table->integer('guest_company_app_env');
			$table->integer('guest_company_contract_server');
			$table->datetime('create_at')->useCurrent();
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('usages_daily');
	}

}
