<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AddColumnTextCircularMonthPac52639 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $subTime = 0;
        $flg = true;
        while ($flg) {
            if (Schema::hasTable('circular_user' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'))) {
                Schema::table('circular_user' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), function (Blueprint $table) use ($subTime) {
                    if (!Schema::hasColumn('circular_user' . Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), "text")) {
                        $table->text('text')->nullable()->comment('メッセージ');
                    }
                });
            }
            $subTime++;
            if (Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym') < 202009) {
                $flg = false;
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
