<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstConstraintsByPac52792 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_constraints', function (Blueprint $table) {
            $table->integer('max_frm_document')->comment('帳票発行機能で発行できる文書数を帳票発行文書数上限')->default(100);
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
            $table->dropColumn('max_frm_document');
        });
    }
}
