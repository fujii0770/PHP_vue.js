<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHrTimecardDetailLateFlgPac51550 extends Migration
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
                        ADD COLUMN  `late_flg` TINYINT(1) NOT NULL DEFAULT 0
                        COMMENT "0:通常 1:遅刻" AFTER `overtime`');
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
            $table->dropColumn('late_flg');
        });
    }
}
