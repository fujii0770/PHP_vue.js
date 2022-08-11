<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsageSituationDetailPac52251Column extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usage_situation_detail', function (Blueprint $table) {
            //
            $table->integer('storage_convenient_file')->default(0)->comment('ファイルメール便');
            $table->integer('storage_convenient_file_re')->default(0)->comment('ファイルメール便再計算');
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
            //
            $table->dropColumn(['storage_convenient_file', 'storage_convenient_file_re']);
        });
    }
}
