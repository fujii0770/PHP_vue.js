<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsageSituationDetailPac51721StorageSchedule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usage_situation_detail', function (Blueprint $table) {
            $table->integer('storage_schedule')->unsigned()->default(0)->after('storage_rate')->comment('ストレージ_スケジューラ');
            $table->integer('storage_schedule_re')->unsigned()->default(0)->after('storage_rate_re')->comment('ストレージ_スケジューラ_再計算');
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
            $table->dropColumn('storage_schedule');
            $table->dropColumn('storage_schedule_re');
        });
    }
}
