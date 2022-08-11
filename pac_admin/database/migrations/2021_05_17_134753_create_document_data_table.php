<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('document_data', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('circular_document_id')->unsigned()->index('circular_document_id');
			$table->longtext('file_data')->comment('捺印情報を含めてBase64変換後、AES256にて暗号化し保持(現行はDocumentsとImplimentsテーブルで持っている)');
			$table->datetime('create_at')->useCurrent();
			$table->string('create_user', 128);
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('update_user', 128)->nullable();
			$table->integer('copy_flg')->default(0);
        });
        DB::statement("alter table document_data comment '文書管理用テーブル。\r\n新エディションにて新設。\r\n新エディションでは、１度に複数文書が回覧可能となるため、\r\n文書テー';");
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('document_data');
	}

}
