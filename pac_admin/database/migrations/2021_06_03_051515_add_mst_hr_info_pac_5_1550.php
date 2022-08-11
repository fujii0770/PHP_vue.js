<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstHrInfoPac51550 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hr_timecard_detail', function (Blueprint $table) {
            DB::statement('ALTER TABLE `hr_timecard_detail`
                        ADD COLUMN  `break_time` INT NOT NULL DEFAULT 0
                        COMMENT "0～999" AFTER `work_end_time`');
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
            $table->dropColumn('break_time');
        });
    }
}
