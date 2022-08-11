<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMstUserInfoAddColumnPac52612 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('mst_user_info', function (Blueprint $table) {
            //
            $table->integer('approval_request_flg')->default(1)->comment('承認(回覧)依頼メール:0：無効 1：有効')->change();
            $table->integer('completion_notice_flg')->comment('回覧完了メール(承認者時):0：無効 1：有効')->change();
            if (!Schema::hasColumn('mst_user_info','completion_sender_notice_flg')) {
                $table->tinyInteger('completion_sender_notice_flg')->default(1)->comment('回覧完了メール(申請者時):0：無効｜1：有効');
            }
            if (!Schema::hasColumn('mst_user_info','pullback_notice_flg')) {
                $table->tinyInteger('pullback_notice_flg')->default(1)->comment('引戻し通知:0：無効｜1：有効');
            }
            if (!Schema::hasColumn('mst_user_info','sendback_notice_flg')) {
                $table->tinyInteger('sendback_notice_flg')->default(1)->comment('差戻し通知:0：無効｜1：有効');
            }
            if (!Schema::hasColumn('mst_user_info','download_notice_flg')) {
                $table->tinyInteger('download_notice_flg')->default(1)->comment('ダウンロード処理完了通知:0：無効｜1：有効');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('mst_user_info', function (Blueprint $table) {
            //
            if (Schema::hasColumn('mst_user_info','completion_sender_notice_flg')) {
                $table->dropColumn('completion_sender_notice_flg');
            }
            if (Schema::hasColumn('mst_user_info','pullback_notice_flg')) {
                $table->dropColumn('pullback_notice_flg');
            }
            if (Schema::hasColumn('mst_user_info','sendback_notice_flg')) {
                $table->dropColumn('sendback_notice_flg');
            }
            if (Schema::hasColumn('mst_user_info','download_notice_flg')) {
                $table->dropColumn('download_notice_flg');
            }
        });
    }
}
