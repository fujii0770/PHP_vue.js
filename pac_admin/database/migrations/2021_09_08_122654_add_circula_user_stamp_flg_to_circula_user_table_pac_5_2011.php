<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCirculaUserStampFlgToCirculaUserTablePac52011 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular_user', function (Blueprint $table) {
            $table->tinyInteger("stamp_flg")->default(0)->nullable(false)->comment("捺印状況 0 承認(捺印なし)  1 承認(捺印あり)");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('circular_user', function (Blueprint $table) {
            //
        });
    }
}
