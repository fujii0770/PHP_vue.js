<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLimitSkipFlgToMstLimitTablePac52821 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_limit', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('mst_limit', 'limit_skip_flg')) {
                $table->integer('limit_skip_flg')->default(1)->comment('スキップ機能 1:有効|0:無効');
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
        Schema::table('mst_limit', function (Blueprint $table) {
            //
            if (Schema::hasColumn('mst_limit', 'limit_skip_flg')) {
                $table->dropColumn('limit_skip_flg');
            }
        });
    }
}
