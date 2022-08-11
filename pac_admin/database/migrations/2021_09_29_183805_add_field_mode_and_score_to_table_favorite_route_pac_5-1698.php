<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldModeAndScoreToTableFavoriteRoutePac51698 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('favorite_route', function (Blueprint $table) {
            if (!Schema::hasColumn("favorite_route", "mode")) {
                $table->integer("mode")->nullable(true)->default(null)->comment("承認方法 全員承認:1|n人以上承認:3");
            }
            if (!Schema::hasColumn("favorite_route", "score")) {
                $table->integer("score")->nullable(true)->default(null)->comment("必要な承認の人数");
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
            $table->dropColumn("score");
            $table->dropColumn("mode");
        });
    }
}
