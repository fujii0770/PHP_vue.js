<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHrWorkingHoursPac53190Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hr_working_hours', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()->comment('id');
            $table->bigInteger('mst_company_id')->unsigned()->comment('会社マスタID');
            $table->string('definition_name','100')->nullable()->comment('定義名称');//varchar
            $table->integer('work_form_kbn')->default(0)->comment('0:通常1:シフト2:フレックス');
            $table->time('regulations_work_start_time')->nullable()->comment('規定業務開始時刻');
            $table->time('regulations_work_end_time')->nullable()->comment('規定業務終了時刻');
            $table->time('shift1_start_time')->nullable()->comment('シフト1開始時刻');
            $table->time('shift1_end_time')->nullable()->comment('シフト1終了時刻');
            $table->time('shift2_start_time')->nullable()->comment('シフト2開始時刻');
            $table->time('shift2_end_time')->nullable()->comment('シフト2終了時刻');
            $table->time('shift3_start_time')->nullable()->comment('シフト3開始時刻');
            $table->time('shift3_end_time')->nullable()->comment('シフト3終了時刻');
            $table->integer('regulations_working_hours')->nullable()->comment('規定就労時間');
            $table->integer('overtime_unit')->nullable()->comment('残業発生時間単位');
            $table->integer('break_time')->nullable()->comment('休憩時間');
            $table->dateTime('create_at')->nullable()->comment('作成日時');
            $table->string('create_user','128')->nullable()->comment('作成者');//varchar
            $table->dateTime('update_at')->nullable()->comment('更新日時');
            $table->string('update_user','128')->nullable()->comment('更新者');//varchar
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hr_working_hours');
    }
}
