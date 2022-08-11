<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToHrTimecardDetailPac53304 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hr_timecard_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_timecard_detail','start_stamping')){
                $table->dateTime('start_stamping')->nullable();
            }
            if (!Schema::hasColumn('hr_timecard_detail','end_stamping')){
                $table->dateTime('end_stamping')->nullable();
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
            $table->dropColumn('start_stamping');
            $table->dropColumn('end_stamping');
        });
    }
}
