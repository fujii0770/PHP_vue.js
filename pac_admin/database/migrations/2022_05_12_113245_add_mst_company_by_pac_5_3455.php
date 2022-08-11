<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstCompanyByPac53455 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //PAC_5-3455  add field long_term_storage_move_flg 移動の削除
        Schema::table('mst_company', function (Blueprint $table) {
        $table->integer('long_term_storage_move_flg')->default(0)->comment('長期保管利用者側の移動ボタン ０:無効｜１:有効');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('mst_company', function (Blueprint $table) {
        $table->dropColumn('long_term_storage_move_flg');
      });
    }
}
