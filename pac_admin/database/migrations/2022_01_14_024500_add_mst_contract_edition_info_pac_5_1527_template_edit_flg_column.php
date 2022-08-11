<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstContractEditionInfoPac51527TemplateEditFlgColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_contract_edition_info', function (Blueprint $table) {
            //PAC_5-1527 
            $table->integer('template_edit_flg')->default(0)->comment('テンプレート編集フラグ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_contract_edition_info', function (Blueprint $table) {
            //
            $table->dropColumn('template_edit_flg');
        });
    }
}
