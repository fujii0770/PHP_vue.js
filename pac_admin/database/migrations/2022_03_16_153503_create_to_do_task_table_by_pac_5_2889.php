<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateToDoTaskTableByPac52889 extends Migration {
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('to_do_task')) {
            Schema::create('to_do_task', function(Blueprint $table)
            {
                $table->bigInteger('id', true)->unsigned()->comment('id');
                $table->bigInteger('to_do_list_id')->unsigned()->comment('To-DoリストのメインID');
                $table->bigInteger('mst_user_id')->unsigned()->comment('ユーザーマスタID');
                $table->bigInteger('mst_company_id')->unsigned()->comment('会社マスタID')->index('mst_company_id');
                $table->bigInteger('parent_id')->unsigned()->default(0)->comment('親タスクID');
                $table->string('title', 50)->comment('タスク名');
                $table->text('content')->comment('タスクの詳細');
                $table->tinyInteger('important')->unsigned()->default(0)->comment('重要度 1:低 2：中 3：高');
                $table->dateTime('deadline')->nullable()->comment('期限日');
                $table->bigInteger('scheduler_id')->unsigned()->default(0)->comment('スケジューラID');
                $table->bigInteger('scheduler_task_id')->unsigned()->default(0)->comment('スケジューラのタスクID');
                $table->tinyInteger('state')->default(0)->comment('-1:削除済み|0:保留中の通知|1:通知済み|2:完了');
                $table->dateTime('created_at')->comment("作成日時");
                $table->dateTime('updated_at')->nullable()->comment("更新日時");
                $table->string('create_user', 128)->comment('作成者');
                $table->string('update_user', 128)->nullable()->comment('更新者');

                $table->foreign('mst_user_id')->references('id')->on('mst_user')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->foreign('mst_company_id')->references('id')->on('mst_company')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->foreign('to_do_list_id')->references('id')->on('to_do_list')->onUpdate('CASCADE')->onDelete('CASCADE');
            });
            DB::statement("alter table to_do_task comment 'To-Doリストタスク';");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('to_do_task');
    }
}