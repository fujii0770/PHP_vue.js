<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateToDoGroupTableByPac53240 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
	    if (!Schema::hasTable('to_do_group')) {
            Schema::create('to_do_group', function(Blueprint $table)
            {
                $table->bigInteger('id', true)->unsigned()->comment('id');
                $table->string('title', 50)->comment('グループ名');
                $table->bigInteger('mst_company_id')->unsigned()->comment('会社マスタID');
                $table->bigInteger('mst_user_id')->unsigned()->comment('ユーザーマスタID');
                $table->dateTime('created_at')->comment("作成日時");
                $table->dateTime('updated_at')->nullable()->comment("更新日時");

                $table->foreign('mst_user_id')->references('id')->on('mst_user')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->foreign('mst_company_id')->references('id')->on('mst_company')->onUpdate('CASCADE')->onDelete('CASCADE');
            });
            DB::statement("alter table to_do_group comment 'To-Doグループ';");
        }
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::dropIfExists('to_do_group');
	}
}