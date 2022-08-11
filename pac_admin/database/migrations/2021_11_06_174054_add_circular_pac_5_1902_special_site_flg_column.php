<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCircularPac51902SpecialSiteFlgColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('circular', function (Blueprint $table) {
            if (!Schema::hasColumn("circular", "special_site_flg")) {
                $table->integer("special_site_flg")->default(0)->comment("特設サイトのフラグ;0：未使用|1：使用する");
            }
            $subTime = 0;
            $flg = true;
            while ($flg) {
                if (Schema::hasTable('circular' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'))) {
                    Schema::table('circular' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), function (Blueprint $table) use ($subTime) {
                        if (!Schema::hasColumn("circular" . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), "special_site_flg")) {
                            $table->integer("special_site_flg")->default(0)->comment("特設サイトのフラグ;0：未使用|1：使用する");
                        }
                    });
                }
                $subTime++;
                if (Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym') < 202009) {
                    $flg = false;
                }
            }
        });

        Schema::table('circular_user', function (Blueprint $table) {
            if (!Schema::hasColumn("circular_user", "special_site_receive_flg")) {
                $table->integer("special_site_receive_flg")->nullable()->comment("特設サイトのフラグ;0：提出側|1：受取側");
            }
            $subTime = 0;
            $flg = true;
            while ($flg) {
                if (Schema::hasTable('circular_user' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'))) {
                    Schema::table('circular_user' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), function (Blueprint $table) use ($subTime) {
                        if (!Schema::hasColumn("circular_user" . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), "special_site_receive_flg")) {
                            $table->integer("special_site_receive_flg")->nullable()->comment("特設サイトのフラグ;0：提出側|1：受取側");
                        }
                    });
                }
                $subTime++;
                if (Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym') < 202009) {
                    $flg = false;
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
