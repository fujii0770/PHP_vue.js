<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateToDoListTableByPac52889 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
	    if (!Schema::hasTable('to_do_list')) {
            Schema::create('to_do_list', function(Blueprint $table)
            {
                $table->bigInteger('id', true)->unsigned()->comment('id');
                $table->bigInteger('mst_user_id')->unsigned()->comment('ユーザーマスタID');
                $table->bigInteger('mst_company_id')->unsigned()->comment('会社マスタID');
                $table->tinyInteger('type')->default(1)->comment('1:個人 2:共有');
                $table->string('title', 50)->comment('リスト名');
                $table->dateTime('created_at')->comment("作成日時");
                $table->dateTime('updated_at')->nullable()->comment("更新日時");
                $table->string('create_user', 128)->comment('作成者');
                $table->string('update_user', 128)->nullable()->comment('更新者');
    
                $table->foreign('mst_user_id')->references('id')->on('mst_user')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->foreign('mst_company_id')->references('id')->on('mst_company')->onUpdate('CASCADE')->onDelete('CASCADE');
            });
            DB::statement("alter table to_do_list comment 'To-Doリスト';");
        }
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::dropIfExists('to_do_list');
	}
}