<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMstCompanyPac51786OldContractData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mst_company')->update(['old_contract_flg' => 1]);
        DB::table('mst_company')->where('contract_edition',1)->update([
            'default_stamp_flg' => 1,
            'confidential_flg' => 1
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
