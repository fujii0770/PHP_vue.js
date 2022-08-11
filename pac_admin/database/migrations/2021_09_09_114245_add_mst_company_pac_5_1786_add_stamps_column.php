<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstCompanyPac51786AddStampsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            //
            $table->integer("timestamps_count")->default(0)->comment("タイムスタンプ契約（回）");
            $table->integer("option_contract_flg")->default(0)->comment("オプション契約");
            $table->integer("option_contract_count")->default(0)->comment("オプション契約数");
            $table->integer("old_contract_flg")->default(0)->comment("旧契約形態|0:新契約;1:旧契約");
            $table->integer('default_stamp_flg')->default(0)->comment('デフォルト印');
            $table->integer('confidential_flg')->default(0)->comment('社外秘');
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
            $table->dropColumn(['timestamps_count','option_contract_flg','option_contract_count','old_contract_flg','default_stamp_flg','confidential_flg']);
        });
    }
}
