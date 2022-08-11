<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTextAppendFileToCircularCopyPac5Xx extends Migration
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
        $subTime=0;
        $flg=true;
        while ($flg){
            if (Schema::hasTable('circular'.\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'))){
                Schema::table('circular'.\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), function (Blueprint $table)use($subTime) {
                    if (!Schema::hasColumn("circular".\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), "text_append_flg")) {
                        $table->integer("text_append_flg")->nullable()->default(1)->comment("0：許可する|1：許可しない");
                    }
                });
            }
            $subTime++;
            if (\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym')<202007){
                $flg=false;
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
