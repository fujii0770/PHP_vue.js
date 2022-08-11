<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMstContractEditionTableAddColumnPac53099 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_contract_edition', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_contract_edition','faq_board_limit_flg')){
                $table->integer('faq_board_limit_flg')->default(0)->comment('サポート掲示板無制限');
            }
            if (!Schema::hasColumn('mst_contract_edition','faq_board_buy_count')){
                $table->integer('faq_board_buy_count')->default(0)->comment('サポート掲示板購入数');
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
            $table->dropColumn('faq_board_limit_flg');
            $table->dropColumn('faq_board_buy_count');
        });
    }
}
