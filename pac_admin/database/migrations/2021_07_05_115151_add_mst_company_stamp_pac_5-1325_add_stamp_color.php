<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstCompanyStampPac51325AddStampColor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company_stamp', function (Blueprint $table) {
            //
            $table->string('date_color', 8)->nullable()->default('FF0000')->comment('日付色の指定(16進数のカラーコードで指定)');
        });

        DB::table('mst_company_stamp')->update(['date_color' => 'FF0000']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_company_stamp', function (Blueprint $table) {
            //
            $table->dropColumn('date_color');
        });
    }
}
