<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMstCompanyTableAddAttendanceSystemFlgColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function(Blueprint $table) {
            $table->unsignedTinyInteger('attendance_system_flg')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_company', function(Blueprint $table) {
            $table->dropColumn('attendance_system_flg');

        });
    }
}
