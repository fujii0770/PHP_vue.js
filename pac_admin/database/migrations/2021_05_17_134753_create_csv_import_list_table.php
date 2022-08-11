<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsvImportListTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('csv_import_list', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('PK連番自動設定');
			$table->bigInteger('company_id')->unsigned()->comment('企業ID');
			$table->bigInteger('user_id')->unsigned()->comment('管理者ID');
			$table->string('name', 256)->comment('CSVファイル名');
			$table->integer('success_num')->comment('成功件数');
			$table->integer('failed_num')->comment('失敗件数');
			$table->integer('total_num')->comment('総件数');
			$table->integer('result')->comment('取込状態(0：失敗|1：成功)');
			$table->dateTime('create_at');
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('file_path', 512)->nullable()->comment('ファイルパス');
			$table->longtext('file_data')->nullable()->comment('ファイルbase64');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('csv_import_list');
	}

}
