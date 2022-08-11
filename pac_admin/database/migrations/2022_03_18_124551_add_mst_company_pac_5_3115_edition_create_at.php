<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstCompanyPac53115EditionCreateAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            $table->dateTime('regular_at')->nullable()->comment('トライアル企業：本契約切替日　本契約：登録日');
        });
        DB::update("UPDATE mst_company SET regular_at = create_at  WHERE contract_edition_sample_flg = 0 AND contract_edition != 3;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            $table->dropColumn('regular_at');
        });
    }
}
