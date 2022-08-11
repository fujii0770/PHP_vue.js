<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstHrInfoPac53030Shift1StartTimeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_hr_info', function (Blueprint $table) {
            //
            $table->time('shift1_start_time')->nullable();
            $table->time('shift1_end_time')->nullable();
            $table->time('shift2_start_time')->nullable();
            $table->time('shift2_end_time')->nullable();
            $table->time('shift3_start_time')->nullable();
            $table->time('shift3_end_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_hr_info', function (Blueprint $table) {
            //
            $table->dropColumn('shift1_start_time');
            $table->dropColumn('shift1_end_time');
            $table->dropColumn('shift2_start_time');
            $table->dropColumn('shift2_end_time');
            $table->dropColumn('shift3_start_time');
            $table->dropColumn('shift3_end_time');
        });
    }
}
