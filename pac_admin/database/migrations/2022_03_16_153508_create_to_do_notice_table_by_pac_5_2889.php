<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateToDoNoticeTableByPac52889 extends Migration {
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('to_do_notice')) {
            Schema::create('to_do_notice', function(Blueprint $table)
            {
                $table->bigInteger('id', true)->unsigned()->comment('id');
                $table->bigInteger('from_id')->unsigned()->comment('タスクID');
                $table->bigInteger('mst_user_id')->unsigned()->comment('ユーザーマスタID');
                $table->string('title', 100)->comment('通知タイトル');
                $table->tinyInteger('from_type')->unsigned()->comment('1:個人のなタスク 2:共有のなタスク 3:文書のタスク');
                $table->tinyInteger('is_read')->default(0)->comment('0:未読|1:既読');
                $table->dateTime('created_at')->comment("作成日時");
                $table->dateTime('updated_at')->nullable()->comment("更新日時");

                $table->foreign('mst_user_id')->references('id')->on('mst_user')->onUpdate('CASCADE')->onDelete('CASCADE');
            });
            DB::statement("alter table to_do_notice comment 'ToDoリスト通知';");
        }
    }
  
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('to_do_notice');
    }
}