<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequirePrintToCircularPac51576 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular', function (Blueprint $table) {
            if (!Schema::hasColumn("circular","require_print")){
                $table->integer("require_print")->default(0)->comment("捺印必須;0：必須にしない|1：必須にする");
            }
            $subTime=0;
            $flg=true;
            while ($flg){
                if (Schema::hasTable('circular'.\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'))){
                    Schema::table('circular'.\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), function (Blueprint $table)use($subTime) {
                        if (!Schema::hasColumn("circular".\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), "require_print")) {
                            $table->integer("require_print")->nullable()->default(0)->comment("0：必須にしない|1：必須にする");
                        }
                    });
                }
                $subTime++;
                if (\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym')<202007){
                    $flg=false;
                }
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

    }
}
