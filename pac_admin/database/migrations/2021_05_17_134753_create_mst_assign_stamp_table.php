<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstAssignStampTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_assign_stamp', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_user_id')->unsigned()->index('mst_user_id');
			$table->integer('mst_admin_id')->nullable();
			$table->integer('stamp_id')->index('INX_stamp_id')->comment('印面マスタまたは企業印面マスタのハンコID');
			$table->integer('display_no')->comment('表示順序を格納 デフォルトはインクリメントされた番号');
			$table->integer('stamp_flg')->comment('0：通常印（印面マスタを参照）
1：共通印（企業印面マスタを参照）');
			$table->datetime('create_at')->useCurrent();
			$table->string('create_user', 128);
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('update_user', 128)->nullable();
			$table->integer('state_flg')->default(1);
			$table->dateTime('delete_at')->nullable();
			$table->integer('time_stamp_permission')->default(0);
        });
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_assign_stamp');
	}

}
