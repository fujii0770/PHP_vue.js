<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTemplateInputDataPac52388TemplatePlaceholderData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('template_input_data', function (Blueprint $table) {
            //PAC_5-2388 template_input_data カラム制約変更
            $table->longtext('template_placeholder_data')->nullable()->change();
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
            $table->text('template_placeholder_data')->nullable(false)->change();
        });
    }
}
