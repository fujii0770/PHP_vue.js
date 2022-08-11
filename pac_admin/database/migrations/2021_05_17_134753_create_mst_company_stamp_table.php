<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstCompanyStampTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_company_stamp', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_company_id')->unsigned()->index('idx_mst_company_stamp_on_mst_company_id');
			$table->string('stamp_name', 32);
			$table->integer('stamp_division')->comment('0：氏名印

1：日付印');
			$table->integer('font')->comment('-1：不明

0：楷書

1：古印

2：行書');
			$table->longtext('stamp_image')->nullable();
			$table->integer('width')->unsigned();
			$table->integer('height')->unsigned();
			$table->integer('date_dpi')->nullable()->comment('日付のdpi');
			$table->integer('date_x')->nullable()->comment('日付の描画位置(X座標)');
			$table->integer('date_y')->nullable()->comment('日付の描画位置(Y座標)');
			$table->integer('date_width')->nullable();
			$table->integer('date_height')->nullable();
			$table->integer('del_flg')->comment('0：未削除

1：削除済');
			$table->datetime('create_at')->useCurrent();
			$table->string('create_user', 128);
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('update_user', 128)->nullable();
			$table->string('serial', 32)->default('');
        });
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_company_stamp');
	}

}
