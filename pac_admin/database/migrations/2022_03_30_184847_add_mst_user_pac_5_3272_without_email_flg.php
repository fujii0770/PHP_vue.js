<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstUserPac53272WithoutEmailFlg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('mst_user', function (Blueprint $table) {
            $table->integer('without_email_flg')->comment('メールアドレス無し')->default(0);
        });
            DB::table('mst_user')->where('option_flg',3)->update([
                'without_email_flg' => 1,
                'option_flg' => 0
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_user', function (Blueprint $table) {
            $table->dropColumn('without_email_flg');
        });
    }
}
