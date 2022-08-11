<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Database\Query\Builder;

class UpdateAppRoleUsersForDelRepeatUserPac53408 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('app_role_users')->
        groupBy('mst_user_id')
            ->select(DB::raw("count(*) as count ,mst_user_id"))
            ->having('count', '>', '1')
            ->get()
            ->each(function ($item) {
                $maxId = DB::table('app_role_users')
                    ->where('mst_user_id', $item->mst_user_id)
                    ->max('id');
                DB::table('app_role_users')
                    ->where('id', '!=', $maxId)
                    ->where('mst_user_id', $item->mst_user_id)
                    ->delete();
            });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
