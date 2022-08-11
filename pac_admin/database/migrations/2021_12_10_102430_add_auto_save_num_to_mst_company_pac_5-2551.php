<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoSaveNumToMstCompanyPac52551 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_company', 'auto_save_num')) {
                $table->integer('auto_save_num')->default(500);
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
            if (Schema::hasColumn('mst_company', 'auto_save_num')) {
                $table->dropColumn('auto_save_num');
            }
        });
    }
}
