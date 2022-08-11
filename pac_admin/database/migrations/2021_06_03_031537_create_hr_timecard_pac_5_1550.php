<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHrTimecardPac51550 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hr_timecard', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mst_user_id');
            $table->string('working_month', 6);
            $table->integer('submission_state')->default(0)->comment("0：未提出|1：提出済");
            $table->dateTime('submission_date')->nullable();
            $table->integer('approval_state')->default(0)->comment("0：未承認|1：承認済|2：修正依頼");
            $table->string('approval_user', 128)->nullable();
            $table->dateTime('approval_date')->nullable();
            $table->integer('state')->default(0)->comment("0：未確定|1：確定|9：削除");
            $table->text('memo')->nullable();
            $table->timestamp('create_at')->useCurrent();
            $table->string('create_user', 128);
            $table->timestamp('update_at')->useCurrent()->nullable();
            $table->string('update_user', 128)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hr_timecard');
    }
}
