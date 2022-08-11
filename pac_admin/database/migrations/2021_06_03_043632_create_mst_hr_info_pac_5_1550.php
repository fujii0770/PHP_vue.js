<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstHrInfoPac51550 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_hr_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mst_user_id');
            $table->string('assigned_company', 128);
            $table->dateTime('Regulations_work_start_time')->nullable();
            $table->dateTime('Regulations_work_end_time')->nullable();
            $table->integer('overtime_unit')->nullable();
            $table->integer('break_time')->nullable();
            $table->timestamp('create_at')->useCurrent();
            $table->string('create_user', 128);
            $table->timestamp('update_at')->useCurrent()->nullable();
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
        Schema::dropIfExists('mst_hr_info');
    }
}
