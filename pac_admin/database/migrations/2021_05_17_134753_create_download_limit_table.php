<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDownloadLimitTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('download_limit', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_company_id')->unsigned()->comment('会社マスタID');
			$table->bigInteger('max_keep_days')->unsigned()->comment('保存期間');
			$table->integer('after_proc')->comment('ダウンロード後処理');
			$table->integer('after_keep_days')->comment('ダウンロード後保存期間');
			$table->integer('request_limit')->comment('ダウンロード要求上限');
			$table->dateTime('create_at')->comment('作成日');
			$table->dateTime('update_at')->nullable()->comment('更新日');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('download_limit');
	}

}
