<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUseMobileAppFlgToMstLimitTablePac51483 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_limit', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_limit', 'use_mobile_app_flg')) {
                $table->integer('use_mobile_app_flg')->default(1)->comment('0:不可|1:可')->nullable();
            }else{
                $table->integer('use_mobile_app_flg')->default(1)->comment('0:不可|1:可')->nullable()->change();
                DB::table('mst_limit')->update(['use_mobile_app_flg' => 1]);
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
        Schema::table('mst_limit', function (Blueprint $table) {
            if (Schema::hasColumn('mst_limit', 'use_mobile_app_flg')) {
                $table->dropColumn('use_mobile_app_flg');
            }
        });
    }
}
