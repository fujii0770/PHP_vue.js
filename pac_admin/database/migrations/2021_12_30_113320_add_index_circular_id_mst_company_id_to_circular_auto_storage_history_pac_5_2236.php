<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexCircularIdMstCompanyIdToCircularAutoStorageHistoryPac52236 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular_auto_storage_history', function (Blueprint $table) {
            if (Schema::hasColumn('circular_auto_storage_history','mst_company_id')){
                $table->index('mst_company_id','INX_mst_company_id');
            }
            if (Schema::hasColumn('circular_auto_storage_history','circular_id')){
                $table->index('circular_id','INX_circular_id');
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
        Schema::table('circular_auto_storage_history', function (Blueprint $table) {
            //
        });
    }
}
