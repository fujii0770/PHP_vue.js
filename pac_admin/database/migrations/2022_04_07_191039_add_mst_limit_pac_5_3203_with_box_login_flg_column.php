<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstLimitPac53203WithBoxLoginFlgColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_limit', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_limit', 'shachihata_login_flg')) {
                $table->integer('shachihata_login_flg')->default(0)->comment('shachihata cloud利用者ログインの制限  0:制限しない｜1:制限する');
            }
            if (!Schema::hasColumn('mst_limit','with_box_login_flg')){
                $table->integer('with_box_login_flg')->default(0)->comment('box捺印利用者ログインの制御  0:制限しない｜1:制限する');
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
            if (Schema::hasColumn('mst_limit', 'shachihata_login_flg')) {
                $table->dropColumn('shachihata_login_flg');
            }
            if (Schema::hasColumn('mst_limit','with_box_login_flg')){
                $table->dropColumn('with_box_login_flg');
            }
        });
    }
}
