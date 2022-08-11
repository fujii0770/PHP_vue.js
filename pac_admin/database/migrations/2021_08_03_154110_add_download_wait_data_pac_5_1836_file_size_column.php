<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDownloadWaitDataPac51836FileSizeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('download_wait_data', function (Blueprint $table) {
            $table->bigInteger('file_size')->nullable()->comment('ファイルのサイズ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('download_wait_data', function (Blueprint $table) {
            $table->dropColumn('file_size');
        });
    }
}
