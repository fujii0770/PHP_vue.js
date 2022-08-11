<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMstHrInfoPac51550 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_hr_info', function (Blueprint $table) {
            $table->time('Regulations_work_start_time')->change();
            $table->time('Regulations_work_end_time')->change();
            $table->integer('overtime_unit')->default(0)->change();
            $table->integer('break_time')->default(0)->change();
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
         
            $table->dateTime('Regulations_work_start_time')->nullable()->change();
            $table->dateTime('Regulations_work_end_time')->nullable()->change();
            $table->integer('overtime_unit')->nullable()->change();
            $table->integer('break_time')->nullable()->change();
        });
    }
}
