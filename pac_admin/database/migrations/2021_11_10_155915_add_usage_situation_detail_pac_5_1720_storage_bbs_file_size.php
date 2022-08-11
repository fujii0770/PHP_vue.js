<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsageSituationDetailPac51720StorageBbsFileSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usage_situation_detail', function (Blueprint $table) {
            $table->integer('storage_bbs_file_size')->unsigned()->default(0)->after('storage_rate')->comment('ストレージ_掲示板');
            $table->integer('storage_bbs_file_size_re')->unsigned()->default(0)->after('storage_rate_re')->comment('ストレージ_掲示板_再計算');
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
            $table->dropColumn('storage_bbs_file_size');
            $table->dropColumn('storage_bbs_file_size_re');
        });
    }
}
