<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCircularListCsvOnCompanyByPac52024 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_company', 'circular_list_csv')) {
                $table->tinyInteger('circular_list_csv')->unsigned()->default(0)->comment('回覧一覧CSV出力');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            if (Schema::hasColumn('mst_company', 'circular_list_csv')) {
                $table->dropColumn('circular_list_csv');
            }
        });
    }
}
