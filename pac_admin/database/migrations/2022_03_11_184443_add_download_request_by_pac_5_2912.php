<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDownloadRequestByPac52912 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('download_request', function (Blueprint $table) {
            $table->bigInteger('mst_sanitizing_line_id')->unsigned()->nullable()->comment('回線マスタID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('download_request', function (Blueprint $table) {
            $table->dropColumn('mst_sanitizing_line_id');
        });
    }
}
