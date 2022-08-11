<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstHrDailyReportPac51550 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_hr_daily_report', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mst_user_id');
            $table->dateTime('report_date');
            $table->string('daily_report', 512)->nullable();
            $table->dateTime('create_at')->nullable();
            $table->string('create_user', 128);
            $table->dateTime('update_at')->nullable();
            $table->string('update_user', 128)->nullable();

            $table->foreign('mst_user_id')->references('id')->on('mst_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_hr_daily_report');
    }
}
