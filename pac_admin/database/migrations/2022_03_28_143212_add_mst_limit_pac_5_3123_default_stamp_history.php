<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstLimitPac53123DefaultStampHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_limit', function (Blueprint $table) {
            $table->integer('default_stamp_history_flg')->default(0)->comment('回覧履歴を付ける  0:無効｜1:有効');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_limit', function (Blueprint $table) {
            $table->dropColumn('default_stamp_history_flg');
        });
    }
}
