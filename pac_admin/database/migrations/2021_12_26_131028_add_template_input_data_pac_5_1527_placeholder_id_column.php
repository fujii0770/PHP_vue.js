<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTemplateInputDataPac51527PlaceholderIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('template_input_data', function (Blueprint $table) {
            //PAC_5-1527 placeholderカラム追加
            $table->bigInteger('placeholder_id')->default(0)->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('template_input_data', function (Blueprint $table) {
            //
            $table->dropColumn('placeholder_id');
        });
    }
}
