<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHrTimecardDetailPac52545AddAbsentFlg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hr_timecard_detail', function (Blueprint $table) {
            $table->integer('absent_flg')->default(0)->after('overtime')->comment('欠勤フラグ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hr_timecard_detail', function (Blueprint $table) {
            $table->dropColumn('absent_flg');
        });
    }
}
