<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstUserPac52546AddHrAdminFlg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_user', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_user', 'hr_admin_flg')) {
                $table->integer('hr_admin_flg')->default(0)->comment('0:非管理者|1:管理者');
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
        Schema::table('mst_user', function (Blueprint $table) {
            if (Schema::hasColumn('mst_user', 'hr_admin_flg')) {
                $table->dropColumn('hr_admin_flg');
            }
        });
    }
}
