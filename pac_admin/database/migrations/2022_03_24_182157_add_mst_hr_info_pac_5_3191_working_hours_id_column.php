<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstHrInfoPac53191WorkingHoursIdColumn extends Migration
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
            $table->integer('working_hours_id')->nullable()->comment('0:通常1:シフト2:フレックス');
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
            $table->dropColumn('working_hours_id');
        });
    }
}
