<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHrTimecardDetailPac51550 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hr_timecard_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mst_company_id');
            $table->unsignedBigInteger('mst_user_id');
            $table->string('work_date', 8);
            $table->dateTime('work_start_time')->nullable();
            $table->dateTime('work_end_time')->nullable();
            $table->integer('earlyleave_flg')->default(0)->comment("0：通常|1：早退");
            $table->integer('paid_vacation_flg')->default(0)->comment("0：通常|1：有給");
            $table->integer('sp_vacation_flg')->default(0)->comment("0：通常|1：特休");
            $table->integer('day_off_flg')->default(0)->comment("0：通常|1：代休");
            $table->integer('approval_state')->default(0)->comment("0：未承認|1：承認済|2：修正依頼");
            $table->string('approval_user')->nullable();
            $table->dateTime('approval_date')->nullable();
            $table->integer('state')->default(0)->comment("0：未確定|1：確定|9：削除");
            $table->text('memo')->nullable();
            $table->text('admin_memo')->nullable();
            $table->timestamp('create_at')->useCurrent();
            $table->string('create_user', 128);
            $table->timestamp('update_at')->useCurrent()->nullable();
            $table->string('update_user', 128)->nullable();

            $table->foreign('mst_company_id')->references('id')->on('mst_company');
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
        Schema::drop('hr_timecard_detail');
    }
}
