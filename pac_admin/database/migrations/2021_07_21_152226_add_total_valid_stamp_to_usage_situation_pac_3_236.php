<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalValidStampToUsageSituationPac3236 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usage_situation', function (Blueprint $table) {
            if (!Schema::hasColumn("usage_situation", "total_valid_stamp")) {
                $table->integer("total_valid_stamp")->nullable()->default(0)->comment("有効の印面の合計");
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
        Schema::table('usage_situation', function (Blueprint $table) {
            $table->dropColumn("total_valid_stamp");
        });
    }
}
