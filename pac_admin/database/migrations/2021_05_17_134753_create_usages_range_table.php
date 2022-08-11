<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsagesRangeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('usages_range', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('PK連番自動設定');
			$table->bigInteger('mst_company_id')->unsigned()->comment('企業ID');
			$table->string('company_name', 256)->comment('企業名前');
			$table->string('company_name_kana', 256)->comment('企業名前カナ');
			$table->string('email', 256)->comment('電子メール');
			$table->smallInteger('range')->comment('範囲(1:１ヶ月|3:３ヶ月|6:６ヶ月)');
			$table->smallInteger('disk_usage_rank')->comment('ファイル容量ランク');
			$table->bigInteger('disk_usage')->unsigned()->comment('ファイル容量');
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
		Schema::drop('usages_range');
	}

}
