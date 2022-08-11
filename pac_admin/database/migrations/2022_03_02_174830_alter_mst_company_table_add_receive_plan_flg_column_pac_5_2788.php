<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMstCompanyTableAddReceivePlanFlgColumnPac52788 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company',function (Blueprint $table){
            if(!Schema::hasColumn('mst_company','receive_plan_flg')){
                $table->tinyInteger('receive_plan_flg')->default(0)->comment('受信専用プラン 0:無効\n1:有効');
            }
            if(!Schema::hasColumn('mst_company','receive_plan_url')){
                $table->string('receive_plan_url',500)->nullable()->comment('受信専用プラン URL');
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
        Schema::table('mst_company', function (Blueprint $table) {
            $table->dropColumn('receive_plan_flg');
            $table->dropColumn('receive_plan_url');
        });
    }
}
