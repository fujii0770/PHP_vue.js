<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDownloadWaitDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('download_wait_data', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('download_request_id')->unsigned()->comment('ダウンロード要求ID');
			$table->longtext('data')->nullable()->comment('ダウンロードデータ');
			$table->string('url', 256)->nullable()->comment('ダウンロードURL');
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
		Schema::drop('download_wait_data');
	}

}
