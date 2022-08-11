<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsvImportDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('csv_import_detail', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('PK連番自動設定');
			$table->integer('list_id')->comment('CSVリストのID');
			$table->integer('row_id')->comment('CSV行目');
			$table->string('email', 256)->comment('メールアドレス');
			$table->string('comment', 512)->comment('コメント');
			$table->dateTime('create_at');
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
		Schema::drop('csv_import_detail');
	}

}
