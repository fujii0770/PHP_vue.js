<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstUserInfoPac51264RotateAngleFlg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_user_info', function (Blueprint $table) {
            $table->integer('rotate_angle_flg')->default(0)->comment('おじぎ印 0：無効|1：有効');  
        });

        // 企業おじぎ印がＯＮの配下ユーザー取得
        $userInfoIds = DB::table('mst_user_info')
            ->join('mst_user', 'mst_user_info.mst_user_id', '=', 'mst_user.id')
            ->join('mst_company', 'mst_user.mst_company_id', '=', 'mst_company.id')
            ->where('mst_company.rotate_angle_flg','=', 1)
            ->select('mst_user_info.mst_user_id')
            ->pluck('mst_user_info.mst_user_id')
            ->toArray();

        // 存在すれば、該当ユーザーのおじぎ印がＯＮに設定する
        if (count($userInfoIds)){
            DB::table('mst_user_info')
                ->whereIn('mst_user_id', $userInfoIds)
                ->update(['rotate_angle_flg' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_user_info', function (Blueprint $table) {
            $table->dropColumn('rotate_angle_flg');
        });
    }
}
