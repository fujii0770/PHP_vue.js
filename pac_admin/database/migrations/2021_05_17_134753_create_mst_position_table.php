<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstPositionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_position', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_company_id')->unsigned();
			$table->string('position_name', 256)->nullable();
			$table->integer('state')->comment('0:無効

1:有効');
			$table->dateTime('create_at');
			$table->string('create_user', 128);
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('update_user', 128);
        });
        DB::statement("alter table mst_position comment '役職データを格納する。\r\n\r\n※現行のDomainMasterテーブル\r\n\r\n　役職マスタと同一テーブルだったが、\r\n\r\n　新エディ';");
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_position');
	}

}
