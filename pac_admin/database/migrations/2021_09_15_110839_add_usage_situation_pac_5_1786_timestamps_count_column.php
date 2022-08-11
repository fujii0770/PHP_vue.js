<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsageSituationPac51786TimestampsCountColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usage_situation', function (Blueprint $table) {
            $table->integer('timestamps_count')->comment('タイムスタンプ契約');
            $table->integer('total_contract_count')->comment('契約数');
            $table->integer('total_option_contract_count')->comment('オプション契約数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usage_situation', function (Blueprint $table) {
            $table->dropColumn('timestamps_count');
            $table->dropColumn('total_contract_count');
            $table->dropColumn('total_option_contract_count');
        });
    }
}
