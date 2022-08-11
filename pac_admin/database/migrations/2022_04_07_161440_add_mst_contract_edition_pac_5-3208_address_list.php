<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstContractEditionPac53208AddressList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_contract_edition', function (Blueprint $table) {
            $table->integer('address_list_flg')->default(0)->comment('利用者名簿');
            $table->integer('address_list_limit_flg')->default(0)->comment('利用者名簿無制限');
            $table->integer('address_list_buy_count')->default(0)->comment('利用者名簿購入数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_contract_edition', function (Blueprint $table) {
            $table->dropColumn('address_list_flg');
            $table->dropColumn('address_list_limit_flg');
            $table->dropColumn('address_list_buy_count');
        });
    }
}
