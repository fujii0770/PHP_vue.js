<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTemplateFileSizeToUsageSituation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usage_situation', function (Blueprint $table) {
            //企業ごとのtemplateファイルの合計サイズ
            $table->bigInteger('template_company_sum_size')->default(0)->comment('テンプレートファイルサイズ企業集計');
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
        Schema::table('usage_situation', function (Blueprint $table) {
            //
            $table->dropColumn('template_company_sum_size');
        });
    }
}