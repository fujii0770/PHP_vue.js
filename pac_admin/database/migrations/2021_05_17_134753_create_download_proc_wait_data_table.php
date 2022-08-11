<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDownloadProcWaitDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('download_proc_wait_data', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('state')->unsigned()->comment('状態');
			$table->bigInteger('download_request_id')->unsigned()->comment('ダウンロード要求ID');
			$table->bigInteger('num')->unsigned()->comment('ファイルNo');
			$table->bigInteger('circular_id')->unsigned()->comment('回覧ID');
			$table->bigInteger('circular_document_id')->unsigned()->comment('回覧ドキュメントID');
			$table->bigInteger('document_data_id')->unsigned()->comment('ドキュメントデータID');
			$table->longtext('document_data')->nullable()->comment('ドキュメントデータ');
			$table->string('title', 256)->nullable()->comment('件名');
			$table->string('file_name', 256)->nullable()->comment('ファイル名');
			$table->dateTime('create_at')->comment('作成日');
			$table->bigInteger('create_user')->unsigned()->comment('作成ユーザ');
			$table->dateTime('circular_update_at')->comment('回覧更新時間');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('download_proc_wait_data');
	}

}
