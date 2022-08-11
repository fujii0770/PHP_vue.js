<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateToDoNoticeConfigTableByPac52889 extends Migration {
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('to_do_notice_config')) {
            Schema::create('to_do_notice_config', function(Blueprint $table)
            {
                $table->bigInteger('id', true)->unsigned()->comment('id');
                $table->bigInteger('mst_user_id')->unsigned()->comment('ユーザーマスタID');
                $table->tinyInteger('email_flg')->default(1)->comment('電子メール通知');
                $table->tinyInteger('notice_flg')->default(0)->comment('プッシュ通知');
                $table->tinyInteger('state')->default(0)->comment('0:無効|1:有効');;
                $table->integer('advance_time')->default(86400)->comment('事前通知時間(秒)');
                $table->dateTime('created_at')->comment("作成日時");
                $table->dateTime('updated_at')->nullable()->comment("更新日時");
    
                $table->foreign('mst_user_id')->references('id')->on('mst_user')->onUpdate('CASCADE')->onDelete('CASCADE');
            });
            DB::statement("alter table to_do_notice_config comment 'ToDoリスト通知設定';");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('to_do_notice_config');
    }
}