<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstCompanyPac53028AddEditionId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            $table->bigInteger('edition_id')->default(0)->comment('契約Editionキー');
            $table->bigInteger('contract_edition_sample_flg')->default(0)->comment('契約Editionサンプルフラグ');
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
            $table->dropColumn('edition_id');
            $table->dropColumn('contract_edition_sample_flg');
        });
    }
}
