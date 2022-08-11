<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToHrTimecardDetailPac53300 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hr_timecard_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_timecard_detail', 'break1_start_time')) {
                $table->dateTime('break1_start_time')->nullable();
            }
            if (!Schema::hasColumn('hr_timecard_detail', 'break1_end_time')) {
                $table->dateTime('break1_end_time')->nullable();
            }
            if (!Schema::hasColumn('hr_timecard_detail', 'break2_start_time')) {
                $table->dateTime('break2_start_time')->nullable();
            }
            if (!Schema::hasColumn('hr_timecard_detail', 'break2_end_time')) {
                $table->dateTime('break2_end_time')->nullable();
            }
            if (!Schema::hasColumn('hr_timecard_detail', 'break3_start_time')) {
                $table->dateTime('break3_start_time')->nullable();
            }
            if (!Schema::hasColumn('hr_timecard_detail', 'break3_end_time')) {
                $table->dateTime('break3_end_time')->nullable();
            }
            if (!Schema::hasColumn('hr_timecard_detail', 'break4_start_time')) {
                $table->dateTime('break4_start_time')->nullable();
            }
            if (!Schema::hasColumn('hr_timecard_detail', 'break4_end_time')) {
                $table->dateTime('break4_end_time')->nullable();
            }
            if (!Schema::hasColumn('hr_timecard_detail', 'break5_start_time')) {
                $table->dateTime('break5_start_time')->nullable();
            }
            if (!Schema::hasColumn('hr_timecard_detail', 'break5_end_time')) {
                $table->dateTime('break5_end_time')->nullable();
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
            $table->dropColumn('break1_start_time');
            $table->dropColumn('break1_end_time');
            $table->dropColumn('break2_start_time');
            $table->dropColumn('break2_end_time');
            $table->dropColumn('break3_start_time');
            $table->dropColumn('break3_end_time');
            $table->dropColumn('break4_start_time');
            $table->dropColumn('break4_end_time');
            $table->dropColumn('break5_start_time');
            $table->dropColumn('break5_end_time');
        });
    }
}
