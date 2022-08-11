<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstCompanyConvenientFlgPac51545 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            DB::statement('ALTER TABLE `mst_company`
                        ADD COLUMN  `convenient_flg` integer NOT NULL DEFAULT 0
                        COMMENT " 0：無効|1：有効" AFTER `long_term_storage_flg`');
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
            $table->dropColumn('convenient_flg');
        });
    }
}
