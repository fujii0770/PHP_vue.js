<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMstOperationInfoAndMstDisplayDataPac52204Data extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // START PAC_5-2204
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 77],
            [
                'display_name' => '完了一覧',
                'role' => 0,
            ]);
        // END PAC_5-2204


        // PAC_5-2204 START
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 344],
            [
                'info' => '完了一覧画面を表示',
                'role' => 1,
            ]);
        // PAC_5-2204 END
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('mst_operation_info')->where("id",344)->delete();
        \DB::table('mst_display')->where("id",77)->delete();
    }
}
