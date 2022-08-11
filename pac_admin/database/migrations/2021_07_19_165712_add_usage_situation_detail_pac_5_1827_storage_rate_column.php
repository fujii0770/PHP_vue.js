<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsageSituationDetailPac51827StorageRateColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usage_situation_detail', function (Blueprint $table) {
            $table->decimal('storage_rate',11,2)->unsigned()->default(0)->after('storage_sum')->comment('ストレージ_比例');
            $table->decimal('storage_rate_re',11,2)->unsigned()->default(0)->after('storage_sum_re')->comment('ストレージ_比例_再計算');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usage_situation_detail', function (Blueprint $table) {
            $table->dropColumn('storage_rate');
            $table->dropColumn('storage_rate_re');
        });
    }
}
