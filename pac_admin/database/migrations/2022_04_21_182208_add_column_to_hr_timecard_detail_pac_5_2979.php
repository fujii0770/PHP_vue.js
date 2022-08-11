<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToHrTimecardDetailPac52979 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hr_timecard_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_timecard_detail','midnight_time')){
                $table->integer('midnight_time')->nullable();
            }
            if (!Schema::hasColumn('hr_timecard_detail','midnight_break_time')){
                $table->integer('midnight_break_time')->default(0)->comment('0～999');
            }
            if (!Schema::hasColumn('hr_timecard_detail','holiday_working_time')){
                $table->integer('holiday_working_time')->nullable();
            }
            if (!Schema::hasColumn('hr_timecard_detail','holiday_work_flg')){
                $table->integer('holiday_work_flg')->default(0)->comment('0：休日出勤なし | 1：休日出勤あり');
            }
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
            $table->dropColumn('midnight_time');
            $table->dropColumn('midnight_break_time');
            $table->dropColumn('holiday_working_time');
            $table->dropColumn('holiday_work_flg');

        });
    }
}
