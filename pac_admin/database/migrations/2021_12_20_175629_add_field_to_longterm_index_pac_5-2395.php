<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToLongtermIndexPac52395 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('longterm_index', function (Blueprint $table) {
            //PAC_5-2337
            $table->integer('long_term_document_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('longterm_index', function (Blueprint $table) {
            //PAC_5-2337
            $table->dropColumn('long_term_document_id');
        });
    }
}
