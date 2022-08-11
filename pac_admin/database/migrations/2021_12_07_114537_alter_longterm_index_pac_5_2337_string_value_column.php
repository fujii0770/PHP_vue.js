<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterLongtermIndexPac52337StringValueColumn extends Migration
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
            $table->longtext('string_value')->default(null)->nullable()->change();
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
            //
            $table->longtext('string_value',128)->default(null)->nullable()->change();
        });
    }
}
