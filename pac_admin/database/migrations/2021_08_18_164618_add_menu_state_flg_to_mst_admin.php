<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMenuStateFlgToMstAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_admin', function (Blueprint $table) {
            if (!Schema::hasColumn("mst_admin", "menu_state_flg")) {
                $table->integer("menu_state_flg")->default(0)->comment("0:未設定 1:簡易メニューバ 2:通常メニューバ");
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
        Schema::table('mst_admin', function (Blueprint $table) {
            $table->dropColumn("menu_state_flg");
        });
    }
}
