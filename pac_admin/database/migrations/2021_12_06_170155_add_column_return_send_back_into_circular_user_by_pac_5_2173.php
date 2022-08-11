<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AddColumnReturnSendBackIntoCircularUserByPac52173 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular_user', function (Blueprint $table) {
            if (!Schema::hasColumn('circular_user', 'return_send_back')) {
                $table->tinyInteger('return_send_back')->unsigned()->default(0)->nullable()->comment('0:最終承認者差戻しない | 1:最終承認者差戻し');
            }
            $subTime = 0;
            $flg = true;
            while ($flg) {
                if (Schema::hasTable('circular_user' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'))) {
                    Schema::table('circular_user' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), function (Blueprint $table) use ($subTime) {
                        if (!Schema::hasColumn("circular_user" . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), "return_send_back")) {
                            $table->tinyInteger('return_send_back')->unsigned()->default(0)->nullable()->comment('0:最終承認者差戻しない | 1:最終承認者差戻し');
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
            if (Schema::hasColumn('circular_user', 'return_send_back')) {
                $table->dropColumn('return_send_back');
            }
            $subTime = 0;
            $flg = true;
            while ($flg) {
                if (Schema::hasTable('circular_user' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'))) {
                    Schema::table('circular_user' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), function (Blueprint $table) use ($subTime) {
                        if (Schema::hasColumn("circular_user" . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), "return_send_back")) {
                            $table->dropColumn('return_send_back');
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
