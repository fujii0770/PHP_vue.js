<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnEnableAnyAddressFlgToMstCompanyPac52616 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_company','enable_any_address_flg')){
                $table->tinyInteger('enable_any_address_flg')->default(0)->comment('承認ルートのみに制限');
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
            $table->dropColumn('enable_any_address_flg');
        });
    }
}
