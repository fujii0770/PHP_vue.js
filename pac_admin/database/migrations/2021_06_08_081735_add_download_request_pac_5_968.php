<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDownloadRequestPac5968 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('download_request', function (Blueprint $table) {
            $table->integer('retry_cnt')->length(1)->nullable()->comment('リトライカウント')->default(0);
            $table->dateTime('sanitizing_request_at')->nullable()->comment('無害化要求時刻');
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
            $table->dropColumn('retry_cnt');
            $table->dropColumn('sanitizing_request_at');
        });
    }
}
