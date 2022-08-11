<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PAC_5-1599 追加部署と役職
 * Class AddDepartmentPosition
 */
class AddDepartmentPositionPac51599MstUserInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_user_info', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_user_info', 'mst_department_id_1')) {
                $table->bigInteger('mst_department_id_1')->unsigned()->nullable();
            }
            if (!Schema::hasColumn('mst_user_info', 'mst_department_id_2')) {
                $table->bigInteger('mst_department_id_2')->unsigned()->nullable();
            }
            if (!Schema::hasColumn('mst_user_info', 'mst_position_id_1')) {
                $table->bigInteger('mst_position_id_1')->unsigned()->nullable();
            }
            if (!Schema::hasColumn('mst_user_info', 'mst_position_id_2')) {
                $table->bigInteger('mst_position_id_2')->unsigned()->nullable();
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
            if (Schema::hasColumn('mst_user_info', 'mst_department_id_1')) {
                $table->dropColumn('mst_department_id_1');
            }
            if (Schema::hasColumn('mst_user_info', 'mst_department_id_2')) {
                $table->dropColumn('mst_department_id_2');
            }
            if (Schema::hasColumn('mst_user_info', 'mst_position_id_1')) {
                $table->dropColumn('mst_position_id_1');
            }
            if (Schema::hasColumn('mst_user_info', 'mst_position_id_2')) {
                $table->dropColumn('mst_position_id_2');
            }
        });
    }
}
