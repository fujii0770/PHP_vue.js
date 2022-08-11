<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnByPac52603OnMovieManagement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable("movie_management")) {
            Schema::table('movie_management', function (Blueprint $table) {
                if (!Schema::hasColumn('movie_management', 'location_type')) {
                    $table->tinyInteger('location_type')->after('mst_position_id')->default(3)->comment('表示場所: 1 ビデオビット1; 2 ビデオビット2; 3 動画一覧');
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
        if (Schema::hasTable("movie_management")) {
            Schema::table('movie_management', function (Blueprint $table) {
                if (Schema::hasColumn('movie_management', 'location_type')) {
                    $table->dropColumn('location_type');
                }
            });
        }
    }
}
