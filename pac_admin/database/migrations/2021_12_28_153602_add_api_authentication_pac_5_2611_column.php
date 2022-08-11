<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApiAuthenticationPac52611Column extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_authentication', function (Blueprint $table) {
            //
            $table->integer( 'mst_company_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_authentication', function (Blueprint $table) {
            //
            $table->dropColumn('mst_company_id');
        });
    }
}
