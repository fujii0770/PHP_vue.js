<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMstLimitTableAddLimitReceivePlanFlgColumnPac53415 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_limit', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_limit','limit_receive_plan_flg')){
                $table->tinyInteger('limit_receive_plan_flg')->default(0);
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
        Schema::table('mst_limit', function (Blueprint $table) {
            if (Schema::hasColumn('mst_limit','limit_receive_plan_flg')){
                $table->dropColumn('limit_receive_plan_flg');
            }
        });
    }
}
