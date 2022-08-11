<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMstContractEditionTableAddSharedSchedulerFlgColumnPac4101 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_contract_edition', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_contract_edition','shared_scheduler_flg')){
                $table->integer('shared_scheduler_flg')->default(0)->comment('共有スケジューラ');
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
        Schema::table('mst_contract_edition',function (Blueprint $table){
            $table->dropColumn('shared_scheduler_flg');
        });
    }
}
