<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCircularUserRoutesAddColumnPac52508 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('circular_user_routes', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('circular_user_routes','template_id')) {
                $table->bigInteger('template_id')->unsigned()->nullable()->comment('テンプレートファイルID');
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
        //
        Schema::table('circular_user_routes', function (Blueprint $table) {
            //
            if (Schema::hasColumn('circular_user_routes','template_id')) {
                $table->dropColumn('template_id');
            }
        });
    }
}
