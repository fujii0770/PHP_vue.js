<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCloumnBbsMaxAttachmentSizeBbsMaxTotalAttachmentSizeBbsMaxAttachmentCountToMstConstraintsPac51807 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            if (Schema::hasColumn('mst_company','bbs_max_attachment_size')){
                $table->dropColumn('bbs_max_attachment_size');
            }
            if (Schema::hasColumn('mst_company','bbs_max_total_attachment_size')){
                $table->dropColumn('bbs_max_total_attachment_size');
            }
            if (Schema::hasColumn('mst_company','bbs_max_attachment_count')){
                $table->dropColumn('bbs_max_attachment_count');
            }
        });
        Schema::table('mst_constraints', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_constraints','bbs_max_attachment_size')){
                $table->integer('bbs_max_attachment_size')->unsigned()->default('500')->comment('添付ファイル容量(MB):添付ファイル機能の1ファイルあたりの最大ファイルサイズ');
            }
            if (!Schema::hasColumn('mst_constraints','bbs_max_total_attachment_size')){
                $table->integer('bbs_max_total_attachment_size')->unsigned()->default('5')->comment('添付ファイル合計容量(GB):添付ファイル機能の合計の最大ファイルサイズ');
            }
            if (!Schema::hasColumn('mst_constraints','bbs_max_attachment_count')){
                $table->integer('bbs_max_attachment_count')->unsigned()->default('10')->comment('添付ファイル数:添付ファイル機能の最大アップロードファイル数');
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
        Schema::table('mst_constraints', function (Blueprint $table) {
            $table->dropColumn('bbs_max_attachment_size');
            $table->dropColumn('bbs_max_total_attachment_size');
            $table->dropColumn('bbs_max_attachment_count');
        });
    }
}
