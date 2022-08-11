<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexCircularStatusCreateAtToCircularOperationHistoryPac53385 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular_operation_history', function (Blueprint $table) {
            //PAC_5-3385 【速度改善】捺印台帳のSQL改善  DownloadControllerUtils@getStampLedger
            $table->index(['circular_status'],'INX_circular_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('circular_operation_history', function (Blueprint $table) {
            $table->dropIndex('INX_circular_status');
        });
    }
}
