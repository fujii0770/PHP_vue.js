<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCirculaUserStampFlgToCirculaUserCopyTablePac52011 extends Migration
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
            if (Schema::hasTable('circular_user' . \Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'))) {
                Schema::table('circular_user' . \Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), function (Blueprint $table) use ($subTime) {
                    if (!Schema::hasColumn("circular_user" . \Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), "stamp_flg")) {
                        $table->tinyInteger("stamp_flg")->default(0)->nullable(false)->comment("捺印状況 0 承認(捺印なし)  1 承認(捺印あり)");
                    }
                });
            }
            $subTime++;
            if (\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym') < 202007) {
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
        Schema::table('circular_user', function (Blueprint $table) {
            //
        });
    }
}
