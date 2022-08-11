<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFavoriteFlgToFavoriteRouteTablePac51982 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('favorite_route', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('favorite_route','favorite_flg')) {
                $table->tinyInteger('favorite_flg')->after('favorite_name')->default(0)->comment('お気に入り登録:0:宛先、回覧順｜1:閲覧ユーザー設定');
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
        Schema::table('favorite_route', function (Blueprint $table) {
            //
            if (Schema::hasColumn('favorite_route','favorite_flg')) {
                $table->dropColumn('favorite_flg');
            }
        });
    }
}
