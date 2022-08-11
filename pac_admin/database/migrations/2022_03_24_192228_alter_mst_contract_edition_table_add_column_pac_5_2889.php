<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMstContractEditionTableAddColumnPac52889 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_contract_edition', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_contract_edition', 'to_do_list_flg')) {
                $table->integer('to_do_list_flg')->default(0)->comment('サポート掲示板無制限');
            }
            if (!Schema::hasColumn('mst_contract_edition', 'to_do_list_limit_flg')) {
                $table->integer('to_do_list_limit_flg')->default(0)->comment('サポート掲示板無制限');
            }
            if (!Schema::hasColumn('mst_contract_edition', 'to_do_list_buy_count')) {
                $table->integer('to_do_list_buy_count')->default(0)->comment('サポート掲示板購入数');
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
            $table->dropColumn('to_do_list_flg');
            $table->dropColumn('to_do_list_limit_flg');
            $table->dropColumn('to_do_list_buy_count');
        });
    }
}
