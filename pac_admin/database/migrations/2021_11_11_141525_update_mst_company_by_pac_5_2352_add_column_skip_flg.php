<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMstCompanyByPac52352AddColumnSkipFlg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn("mst_company" , "skip_flg")){
            Schema::table('mst_company', function (Blueprint $table) {
                $table->boolean('skip_flg')->default(0)->comment('スキップFLG 0： 有効にしない  1： 有効にする');
            });
        }


        if(!Schema::hasColumn("circular_user" , "is_skip")){
            Schema::table('circular_user', function (Blueprint $table) {
                $table->tinyInteger("is_skip")->default(0)->nullable(false)->comment("スキップ(手動) 0 いいえ  1 はい、");
            });
            $subTime = 0;
            $flg = true;
            while ($flg) {
                if (Schema::hasTable('circular_user' . \Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'))) {
                    Schema::table('circular_user' . \Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), function (Blueprint $table) use ($subTime) {
                        if (!Schema::hasColumn("circular_user" . \Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), "is_skip")) {
                            $table->tinyInteger("is_skip")->default(0)->nullable(false)->comment("スキップ(手動) 0 いいえ  1 はい、");
                        }
                    });
                }
                $subTime++;
                if (\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym') < 202007) {
                    $flg = false;
                }
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

        if(Schema::hasColumn("mst_company" , "skip_flg")){
            Schema::table('mst_company', function (Blueprint $table) {
                $table->dropColumn('skip_flg');
            });
        }

        if(Schema::hasColumn("circular_user" , "is_skip")){
            Schema::table('circular_user', function (Blueprint $table) {
                $table->dropColumn("is_skip");
            });
            $subTime = 0;
            $flg = true;
            while ($flg) {
                if (Schema::hasTable('circular_user' . \Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'))) {
                    Schema::table('circular_user' . \Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), function (Blueprint $table) use ($subTime) {
                        if (Schema::hasColumn("circular_user" . \Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym'), "is_skip")) {
                            $table->dropColumn('is_skip');
                        }
                    });
                }
                $subTime++;
                if (\Carbon\Carbon::now()->subMonthsWithNoOverflow($subTime)->format('Ym') < 202007) {
                    $flg = false;
                }
            }
        }

    }
}
