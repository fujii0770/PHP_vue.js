<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstCompanyPac52318 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // PAC_5-2318  add field long_term_storage_delete_flg 文書の削除
        Schema::table('mst_company', function (Blueprint $table) {
            $table->integer('long_term_storage_delete_flg')->default(0)->comment('長期保管利用者側の削除ボタン ０:無効｜１:有効');
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
            $table->dropColumn('long_term_storage_delete_flg');
        });
    }
}
