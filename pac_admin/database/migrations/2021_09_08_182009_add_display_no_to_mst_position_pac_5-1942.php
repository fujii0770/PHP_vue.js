<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisplayNoToMstPositionPac51942 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_position', function (Blueprint $table) {
            if (!Schema::hasColumn("mst_position","display_no")){
                $table->integer("display_no")->default(0);
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
        Schema::table('mst_position', function (Blueprint $table) {
            $table->dropColumn('display_no');
        });
    }
}
