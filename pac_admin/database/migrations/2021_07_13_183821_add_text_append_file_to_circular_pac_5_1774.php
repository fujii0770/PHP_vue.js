<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTextAppendFileToCircularPac51774 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular', function (Blueprint $table) {
            if (!Schema::hasColumn("circular","text_append_flg")){
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
        Schema::table('circular', function (Blueprint $table) {
            $table->dropColumn("text_append_flg");
        });
    }
}
