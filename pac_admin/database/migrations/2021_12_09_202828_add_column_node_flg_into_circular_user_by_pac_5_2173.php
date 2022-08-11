<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AddColumnNodeFlgIntoCircularUserByPac52173 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular_user', function (Blueprint $table) {
            if (!Schema::hasColumn('circular_user', 'node_flg')) {
                $table->tinyInteger('node_flg')->unsigned()->default(0)->nullable()->comment('0:その他 | 1:承認 | 2:ノード完了');
            }
            $subTime = 0;
            $flg = true;
            while ($flg) {
                if (Schema::hasTable('circular_user' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'))) {
                    Schema::table('circular_user' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), function (Blueprint $table) use ($subTime) {
                        if (!Schema::hasColumn("circular_user" . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), "node_flg")) {
                            $table->tinyInteger('node_flg')->unsigned()->default(0)->nullable()->comment('0:その他 | 1:承認 | 2:ノード完了');
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
        Schema::table('circular_user', function (Blueprint $table) {
            if (Schema::hasColumn('circular_user', 'node_flg')) {
                $table->dropColumn('node_flg');
            }
            $subTime = 0;
            $flg = true;
            while ($flg) {
                if (Schema::hasTable('circular_user' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'))) {
                    Schema::table('circular_user' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), function (Blueprint $table) use ($subTime) {
                        if (Schema::hasColumn("circular_user" . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), "node_flg")) {
                            $table->dropColumn('node_flg');
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
}
