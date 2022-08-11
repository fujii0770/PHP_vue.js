<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PAC_5-1599 追加部署と役職
 * Class AddDepartmentPosition
 */
class addMstCompanyTablePac51599MultipleDepartmentPositionFlgColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_company', 'multiple_department_position_flg')) {
                $table->integer('multiple_department_position_flg')->default(0)->comment('部署・役職複数登録 0:無効/1:有効');
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
        Schema::table('mst_user_info', function (Blueprint $table) {
            if (Schema::hasColumn('mst_company', 'multiple_department_position_flg')) {
                $table->dropColumn('multiple_department_position_flg');
            }
        });
    }
}
