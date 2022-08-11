<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCircularAutoStorageHistoryPac53485FailedCountColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular_auto_storage_history', function (Blueprint $table) {
            //
            $table->integer('failed_count')->default(0)->comment('失敗回数');
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
