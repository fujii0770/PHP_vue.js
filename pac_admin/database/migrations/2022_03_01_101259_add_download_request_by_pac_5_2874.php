<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDownloadRequestByPac52874 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('download_request', function (Blueprint $table) {
            $table->integer('sanitizing_state')->comment('0：無害化不要;1：無害化要;2：無害化待ち')->default(0);
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
            $table->dropColumn('sanitizing_state');
        });
    }
}
