<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstLongtermIndexPac52485AddSort extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_longterm_index', function (Blueprint $table) {
            // ソート
            $table->integer('sort_id')->default(10)->comment('ソート順');
        });

        DB::table('mst_longterm_index')
            ->Where('mst_company_id', 0)
            ->Where('index_name', '取引年月日')
            ->update([
                'sort_id' => 1
            ]);
        DB::table('mst_longterm_index')
            ->Where('mst_company_id', 0)
            ->Where('index_name', '金額')
            ->update([
                'sort_id' => 2
            ]);
        DB::table('mst_longterm_index')
            ->Where('mst_company_id', 0)
            ->Where('index_name', '取引先')
            ->update([
                'sort_id' => 3
            ]);
        DB::table('mst_longterm_index')
            ->Where('mst_company_id', 0)
            ->Where('index_name', 'テンプレート項目')
            ->update([
                'sort_id' => 4
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_longterm_index', function (Blueprint $table) {
            //
            $table->dropColumn('sort_id');
        });
    }
}
