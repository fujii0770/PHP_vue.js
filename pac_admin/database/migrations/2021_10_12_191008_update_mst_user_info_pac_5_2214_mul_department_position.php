<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateMstUserInfoPac52214MulDepartmentPosition extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $update_user_ids = DB::table('mst_company')
            ->join('mst_user', 'mst_company.id', 'mst_user.mst_company_id')
            ->where('multiple_department_position_flg', 0)
            ->select('mst_user.id')
            ->pluck('mst_user.id')
            ->toArray();

        $update_user_id_lst = array_chunk($update_user_ids,100);

        foreach ($update_user_id_lst as $update_user_ids){
            DB::table('mst_user_info')
                ->whereIn('mst_user_id', $update_user_ids)
                ->update([
                    'mst_department_id_1' => null
                    ,'mst_department_id_2' => null
                    ,'mst_position_id_1' => null
                    ,'mst_position_id_2' => null
                ]);
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
            //
        });
    }
}
