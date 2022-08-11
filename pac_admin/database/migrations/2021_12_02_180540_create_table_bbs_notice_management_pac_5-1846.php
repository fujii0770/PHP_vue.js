<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableBbsNoticeManagementPac51846 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('bbs_notice_management')) {
            Schema::create('bbs_notice_management', function (Blueprint $table) {
                $table->bigInteger('id', true)->comment('Id');
                $table->bigInteger('mst_user_id')->index('fk_mst_user_id_of_notice_management_idx')->comment('ユーザマスタの外部キー');
                $table->bigInteger('notice_id')->index('fk_notice_id_of_notice_management_idx')->comment('お知らせテーブルの外部キー');
                $table->tinyInteger('is_read')->comment('既読フラグ');
                $table->timestamp('created_at')->useCurrent()->comment('作成日');
                $table->timestamp('updated_at')->useCurrent()->comment('更新日');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bbs_notice_management');
    }
}
