<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstLimitPac52705RequireApproveFlagColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_limit', function (Blueprint $table) {
            $table->integer('require_approve_flag')->default(0)->comment('最終承認者から直接社外に送る  0:無効｜1:有効');
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
            $table->dropColumn('require_approve_flag');
        });
    }
}
