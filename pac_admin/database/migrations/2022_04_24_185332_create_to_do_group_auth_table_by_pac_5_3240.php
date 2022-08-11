<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateToDoGroupAuthTableByPac53240 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
	    if (!Schema::hasTable('to_do_group_auth')) {
            Schema::create('to_do_group_auth', function(Blueprint $table)
            {
                $table->bigInteger('id', true)->unsigned()->comment('id');
                $table->bigInteger('group_id')->unsigned()->comment('グループID');
                $table->tinyInteger('auth_type')->unsigned()->comment('認証タイプ  1：部署ID  2：ユーザーマスタID');
                $table->bigInteger('auth_department_id')->default(0)->comment('部署ID');
                $table->bigInteger('auth_user_id')->default(0)->comment('ユーザーID');
                $table->bigInteger('mst_user_id')->unsigned()->comment('ユーザーマスタID');
                $table->dateTime('created_at')->comment("作成日時");
                $table->dateTime('updated_at')->nullable()->comment("更新日時");

                $table->foreign('group_id')->references('id')->on('to_do_group')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->foreign('mst_user_id')->references('id')->on('mst_user')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->index(['group_id', 'auth_department_id', 'auth_user_id'], 'Index_to_do_group_auth');
            });
            DB::statement("alter table to_do_group_auth comment 'To-Doグループ権限';");
        }
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::dropIfExists('to_do_group_auth');
	}
}