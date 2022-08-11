<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTextAppendFileToMstProtectionPac51774 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_protection', function (Blueprint $table) {
            if (!Schema::hasColumn("mst_protection","text_append_flg")){
                $table->integer("text_append_flg")->nullable()->default(1)->comment("0：許可する|1：許可しない");
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
        Schema::table('mst_protection', function (Blueprint $table) {
            $table->dropColumn("text_append_flg");
        });
    }
}
