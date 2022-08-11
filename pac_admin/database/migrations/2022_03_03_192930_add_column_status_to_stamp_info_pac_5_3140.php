<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnStatusToStampInfoPac53140 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stamp_info', function (Blueprint $table) {
            if (!Schema::hasColumn('stamp_info','status')){
                $table->tinyInteger('status')->default(0)->index('INX_status')->comment('0:未集計 | 1:集計中(未使用) | 2:集計完了');
            }
        });
        Schema::table('assign_stamp_info', function (Blueprint $table) {
            if (!Schema::hasColumn('assign_stamp_info','status')){
                $table->tinyInteger('status')->default(0)->index('INX_status')->comment('0:未集計 | 1:集計中(未使用) | 2:集計完了');
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
        Schema::table('stamp_info', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('assign_stamp_info', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
