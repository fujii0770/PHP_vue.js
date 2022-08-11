<?php

use Illuminate\Database\Migrations\Migration;

class UpdateMstUserInfoDefaultPac52612Data extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::table('mst_user_info')
            ->update([
                "completion_sender_notice_flg" => DB::raw("completion_notice_flg  + 0 ")
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
