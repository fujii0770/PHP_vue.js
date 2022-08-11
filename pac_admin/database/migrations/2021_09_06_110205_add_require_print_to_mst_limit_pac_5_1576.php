<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequirePrintToMstLimitPac51576 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_limit', function (Blueprint $table) {
            if (!Schema::hasColumn("mst_limit","require_print")){
                $table->integer("require_print")->default(0)->comment("捺印必須;0：無効|1：有効");
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
        Schema::table('mst_limit', function (Blueprint $table) {
            $table->dropColumn('require_print');
        });
    }
}
