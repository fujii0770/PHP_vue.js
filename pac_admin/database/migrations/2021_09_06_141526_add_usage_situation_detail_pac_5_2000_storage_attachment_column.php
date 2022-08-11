<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsageSituationDetailPac52000StorageAttachmentColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usage_situation_detail', function (Blueprint $table) {
            $table->bigInteger('storage_attachment')->after('storage_mail')->unsigned()->comment('ストレージ＿添付ファイル');
            $table->bigInteger('storage_attachment_re')->after('storage_mail_re')->unsigned()->comment('ストレージ＿添付ファイル_再計算');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usage_situation_detail', function (Blueprint $table) {
            $table->dropColumn('storage_attachment');
            $table->dropColumn('storage_attachment_re');
        });
    }
}
