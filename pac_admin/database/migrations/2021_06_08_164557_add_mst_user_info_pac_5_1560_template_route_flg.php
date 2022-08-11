<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstUserInfoPac51560TemplateRouteFlg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_user_info', function (Blueprint $table) {
            DB::statement('ALTER TABLE `mst_user_info`
                        ADD COLUMN `template_route_flg` INT(1) NOT NULL DEFAULT 0
                        COMMENT "自分関係承認ルート表示のみフラッグ。0：無効；1：有効"');
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
            $table->dropColumn('template_route_flg');
        });
    }
}
