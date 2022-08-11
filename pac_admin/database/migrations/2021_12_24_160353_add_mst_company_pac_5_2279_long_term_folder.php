<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstCompanyPac52279LongTermFolder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_company', 'long_term_folder_flg')) {
                $table->integer('long_term_folder_flg')->default(0)->comment('長期保管フォルダ管理');
            }
            if (!Schema::hasColumn('mst_company', 'long_term_default_folder_id')) {
                $table->bigInteger('long_term_default_folder_id')->unsigned()->default(0)->comment('文書自動保管フォルダID');
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
            $table->dropColumn('long_term_folder_flg');
            $table->dropColumn('long_term_default_folder_id');
        });
    }
}
