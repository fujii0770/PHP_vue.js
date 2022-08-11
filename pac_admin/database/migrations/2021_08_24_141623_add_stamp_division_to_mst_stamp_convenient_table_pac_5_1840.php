<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStampDivisionToMstStampConvenientTablePac51840 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_stamp_convenient', function (Blueprint $table) {
            $table->integer('stamp_date_flg')->comment('日付設定　0：無効｜１：有効');
            $table->integer('date_dpi')->nullable()->comment('日付のdpi');
            $table->integer('date_x')->nullable()->comment('日付の描画位置(X座標)');
            $table->integer('date_y')->nullable()->comment('日付の描画位置(Y座標)');
            $table->integer('date_width')->nullable()->comment('日付の幅');
            $table->integer('date_height')->nullable()->comment('日付の高さ');
            $table->string('date_color', 8)->nullable()->default('FF0000')->comment('日付色の指定(16進数のカラーコードで指定)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_stamp_convenient', function (Blueprint $table) {
            $table->dropColumn('stamp_date_flg');
            $table->dropColumn('date_dpi');
            $table->dropColumn('date_x');
            $table->dropColumn('date_y');
            $table->dropColumn('date_width');
            $table->dropColumn('date_height');
            $table->dropColumn('date_color');
        });
    }
}
