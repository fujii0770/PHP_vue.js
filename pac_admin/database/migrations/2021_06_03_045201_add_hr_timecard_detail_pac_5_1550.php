<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddHrTimecardDetailPac51550 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("alter table hr_timecard_detail " .
            "add working_time int null comment '稼働時間' after work_end_time "
        );

        DB::statement("alter table hr_timecard_detail " .
            "add overtime int null comment '残業時間' after working_time "
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hr_timecard_detail', function (Blueprint $table) {
            $table->dropColumn('working_time');
        });
        Schema::table('hr_timecard_detail', function (Blueprint $table) {
            $table->dropColumn('overtime');
        });
    }
}
