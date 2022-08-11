<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstConstraintsPac5968 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_constraints', function (Blueprint $table) {
            $table->integer('sanitize_request_limit')->comment('1時間当たりの無害化要求ファイル数')->default(10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_constraints', function (Blueprint $table) {
            $table->dropColumn('sanitize_request_limit');
        });
    }
}
