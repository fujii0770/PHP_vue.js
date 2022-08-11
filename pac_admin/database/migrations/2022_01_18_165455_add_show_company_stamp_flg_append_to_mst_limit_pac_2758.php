<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowCompanyStampFlgAppendToMstLimitPac2758 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            if (!Schema::hasColumn("mst_company","is_show_current_company_stamp")){
                $table->tinyInteger("is_show_current_company_stamp")->nullable(false)->default(0)->comment("自社のみの回覧履歴を付ける 0：表示する|1：表示しない");
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
            if (Schema::hasColumn("mst_company","is_show_current_company_stamp")){
                $table->dropColumn("is_show_current_company_stamp");
            }
        });
    }
}
