<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstLimitPac51670BoxRefreshTokenUpdatedDateColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('mst_limit', function (Blueprint $table) {
            $table->dateTime('box_refresh_token_updated_date')->nullable()->comment('BOX更新トークン更新時間');
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
        Schema::table('mst_limit', function (Blueprint $table) {
            $table->dropColumn('box_refresh_token_updated_date');
        });
    }
}
