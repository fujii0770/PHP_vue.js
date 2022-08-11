<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstUserInfoPac51618DefaultOpacity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_user_info', function (Blueprint $table) {
            $table->integer('default_opacity')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_user_info', function (Blueprint $table) {
            $table->dropColumn('default_opacity');

        });
    }
}
