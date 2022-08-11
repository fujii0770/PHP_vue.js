<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFrmImpMgrByPac52257 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('frm_imp_mgr', function (Blueprint $table) {
            $table->bigInteger('download_request_id')->unsigned()->nullable()->comment('ダウンロード要求ID');
            $table->string('download_request_code', 256)->nullable()->comment('ダウンロードコード');
            $table->string('download_request_message', 256)->nullable()->comment('ダウンロードメッセージ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('frm_template', function (Blueprint $table) {
            $table->dropColumn("download_request_id");
            $table->dropColumn("download_request_code");
            $table->dropColumn("download_request_message");
        });
    }
}
