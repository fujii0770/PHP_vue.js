<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstCompanyPac5759 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            //
            $table->integer('repage_preview_flg')->default(0)->comment('改ページプレビュー(0：無効|1：有効)');
        });

        DB::table('mst_company')->update(['repage_preview_flg' => 0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            //
            $table->dropColumn('repage_preview_flg');
        });
    }
}
