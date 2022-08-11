<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstStampSpecialTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_stamp_special', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('face', 20)->comment('印面検索文字');
			$table->integer('stamp_division')->comment('0：氏名印 1：日付印');
			$table->integer('font')->comment('-1：不明 0：楷書 1：古印 2：行書');
			$table->longtext('contents');
			$table->decimal('realWidth', 11)->nullable();
			$table->decimal('realHeight', 11)->nullable();
			$table->integer('datex');
			$table->integer('datey');
			$table->integer('datew');
			$table->integer('dateh');
			$table->datetime('create_at')->useCurrent();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_stamp_special');
	}

}
