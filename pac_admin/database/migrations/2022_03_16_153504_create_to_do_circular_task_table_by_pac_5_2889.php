<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateToDoCircularTaskTableByPac52889 extends Migration {
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('to_do_circular_task')) {
            Schema::create('to_do_circular_task', function(Blueprint $table)
            {
                $table->bigInteger('id', true)->unsigned()->comment('id');
                $table->bigInteger('circular_user_id')->unique()->unsigned()->comment('回覧ユーザーテーブルのメインID');
                $table->bigInteger('mst_user_id')->unsigned()->comment('ユーザーマスタID');
                $table->string('title', 50)->comment('タスク名');
                $table->text('content')->comment('タスクの詳細');
                $table->tinyInteger('important')->unsigned()->default(0)->comment('重要度 1:低 2：中 3：高');
                $table->dateTime('deadline')->nullable()->comment('期限日');
                $table->bigInteger('scheduler_id')->unsigned()->default(0)->comment('スケジューラID');
                $table->bigInteger('scheduler_task_id')->unsigned()->default(0)->comment('スケジューラのタスクID');
                $table->tinyInteger('state')->default(0)->comment('-1:削除済み|0:保留中の通知|1:通知済み|2:完了');
                $table->dateTime('created_at')->comment("作成日時");
                $table->dateTime('updated_at')->nullable()->comment("更新日時");
    
                $table->foreign('circular_user_id')->references('id')->on('circular_user')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->foreign('mst_user_id')->references('id')->on('mst_user')->onUpdate('CASCADE')->onDelete('CASCADE');
            });
            DB::statement("alter table to_do_circular_task comment '受信一覧タスク';");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('to_do_circular_task');
    }
}