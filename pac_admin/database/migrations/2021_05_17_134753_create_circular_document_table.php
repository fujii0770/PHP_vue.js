<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCircularDocumentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('circular_document', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('circular_id')->unsigned()->index('INX_circular_id');
			$table->bigInteger('origin_document_id');
			$table->integer('confidential_flg')->comment("0：無効\r\n1：有効");
			$table->string('file_name', 256);
			$table->datetime('create_at')->useCurrent();
			$table->string('create_user', 128);
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('update_user', 128)->nullable();
			$table->integer('origin_env_flg');
			$table->bigInteger('file_size');
			$table->integer('origin_edition_flg');
			$table->integer('origin_server_flg')->default(0);
			$table->bigInteger('create_company_id')->unsigned();
			$table->integer('create_user_id');
			$table->integer('parent_send_order');
			$table->integer('document_no');
			$table->integer('copy_flg')->default(0);
			$table->index(['circular_id','create_company_id'], 'INX_circular_id_create_company_id');
		});

        DB::statement("alter table circular_document comment '回覧の設定と文書のデータを紐づけるためのテーブル';");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('circular_document');
	}

}
