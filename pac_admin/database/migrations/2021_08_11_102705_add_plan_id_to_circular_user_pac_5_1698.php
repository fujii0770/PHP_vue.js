<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlanIdToCircularUserPac51698 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular_user', function (Blueprint $table) {
            if (!Schema::hasColumn("circular_user", "plan_id")) {
                $table->bigInteger("plan_id")->default(0)->comment("合議ID");
            }
        });
        $subTime=0;
        $flg=true;
        while ($flg){
            if (Schema::hasTable('circular_user'.\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'))){
                Schema::table('circular_user'.\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), function (Blueprint $table)use($subTime) {
                    if (!Schema::hasColumn("circular_user".\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), "plan_id")) {
                        $table->bigInteger("plan_id")->default(0)->comment("合議ID");
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
        Schema::table('circular_user', function (Blueprint $table) {
            $table->dropColumn("plan_id");
        });
        $subTime=0;
        $flg=true;
        while ($flg){
            if (Schema::hasTable('circular_user'.\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'))){
                Schema::table('circular_user'.\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), function (Blueprint $table)use($subTime) {
                    if (Schema::hasColumn("circular_user".\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), "plan_id")) {
                        $table->dropColumn("plan_id");
                    }
                });
            }
            $subTime++;
            if (\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym')<202007){
                $flg=false;
            }
        }
    }
}
