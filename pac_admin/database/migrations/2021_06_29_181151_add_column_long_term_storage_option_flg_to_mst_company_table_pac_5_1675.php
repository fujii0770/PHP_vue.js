<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnLongTermStorageOptionFlgToMstCompanyTablePac51675 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_company', 'long_term_storage_option_flg')) {
                $table->integer('long_term_storage_option_flg')->default(0)->comment('長期保存オプションフラグ');
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
            if (Schema::hasColumn('mst_company', 'long_term_storage_option_flg')) {
                $table->dropColumn('long_term_storage_option_flg');
            }
        });
    }
}
