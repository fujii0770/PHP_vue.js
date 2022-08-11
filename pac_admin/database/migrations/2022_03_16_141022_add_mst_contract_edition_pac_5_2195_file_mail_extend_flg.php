<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstContractEditionPac52195FileMailExtendFlg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_contract_edition', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_contract_edition', 'file_mail_extend_flg')) {
                $table->integer('file_mail_extend_flg')->default(0)->comment('送信履歴保持延長 1:有効|0:無効');
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
        Schema::table('mst_contract_edition', function (Blueprint $table) {
            if (Schema::hasColumn('mst_contract_edition', 'file_mail_extend_flg')) {
                $table->dropColumn('file_mail_extend_flg');
            }
        });
    }
}
