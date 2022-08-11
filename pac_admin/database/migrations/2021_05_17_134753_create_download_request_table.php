<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDownloadRequestTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('download_request', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_company_id')->unsigned()->comment('会社マスタID');
			$table->bigInteger('mst_user_id')->unsigned()->comment('ユーザーマスタID');
			$table->integer('user_auth')->comment('ユーザー権限');
			$table->string('file_name', 256)->nullable();
			$table->timestamp('request_date')->useCurrent()->comment('リクエスト日時');
			$table->integer('state')->comment('状態');
			$table->dateTime('contents_create_at')->nullable()->comment('コンテンツ作成日時');
			$table->dateTime('download_period')->nullable()->comment('ダウンロード期限');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('download_request');
	}

}
