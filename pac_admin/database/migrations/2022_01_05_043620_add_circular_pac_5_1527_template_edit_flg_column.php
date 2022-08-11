<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCircularPac51527TemplateEditFlgColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular', function (Blueprint $table) {
            //
            $table->integer('template_edit_flg')->default(0)->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('circular', function (Blueprint $table) {
            //
            $table->dropColumn('template_edit_flg');
        });
    }
}
