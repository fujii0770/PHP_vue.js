<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMstHrInfoPac51550 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_hr_info', function (Blueprint $table) {
            $table->time('Regulations_work_start_time')
                ->nullable()
                ->after('assigned_company')
                ->comment('規定業務開始時刻')
                ->change();
        });

        Schema::table('mst_hr_info', function (Blueprint $table) {
            $table->time('Regulations_work_end_time')
                ->nullable()
                ->after('Regulations_work_start_time')
                ->comment('規定業務終了時刻')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //カラムの順番の並えおよびコメント追加のマイグレーションなのでロールバックなし
    }
}
