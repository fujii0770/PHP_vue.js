<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnDefaultOnMypageByPac52605 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('mypage')) {
            Schema::table('mypage', function (Blueprint $table) {
                if (!Schema::hasColumn('mypage', 'default')) {
                    $table->tinyInteger('default')->default(0)->comment('デフォルトのテンプレート');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('mypage')) {
            Schema::table('mypage', function (Blueprint $table) {
                if (Schema::hasColumn('mypage', 'default')) {
                    $table->dropColumn('default');
                }
            });
        }
    }
}
