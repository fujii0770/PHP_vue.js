<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFrmSrvFlgToMstCompanyPac51598 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_company', 'frm_srv_flg')) {
                $table->integer('frm_srv_flg')->default(0);
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
            if (Schema::hasColumn('mst_company', 'frm_srv_flg')) {
                $table->dropColumn('frm_srv_flg');
            }
        });
    }
}
