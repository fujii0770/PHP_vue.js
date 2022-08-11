<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstCompanyPac53033WithoutEmailFlg extends Migration
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
            $table->integer('without_email_flg')->default(0)->comment('メールアドレス無し');
        });
    }

    /**
     * Reverse the  migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            //
            $table->dropColumn('without_email_flg');
        });
    }
}
