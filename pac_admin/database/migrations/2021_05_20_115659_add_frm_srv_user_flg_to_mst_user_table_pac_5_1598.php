<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFrmSrvUserFlgToMstUserTablePac51598 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_user', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_user', 'frm_srv_user_flg')) {
                $table->integer('frm_srv_user_flg')->default(0)->comment("0：無効|1：利用中");
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
        Schema::table('mst_user', function (Blueprint $table) {
            if (Schema::hasColumn('mst_user', 'frm_srv_user_flg')) {
                $table->dropColumn('frm_srv_user_flg');
            }
        });
    }
}
