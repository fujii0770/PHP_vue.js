<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDownloadWaitDataPac5968 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('download_wait_data', function (Blueprint $table) {
            $table->string('file_token', 256)->nullable()->comment('ファイルトークン');
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
            $table->dropColumn('file_token');
        });
    }
}
