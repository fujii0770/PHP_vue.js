<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertMstLongtermIndexPac52134Customer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            insert into mst_longterm_index (
                mst_company_id,
                mst_user_id,
                index_name,
                data_type,
                permission,
                create_at,
                create_user,
                update_at,
                update_user,
                template_flg,
                circular_id,
                template_valid_flg,
                auth_flg
            ) value(
                0,
                0,
                '取引先',
                1,
                0,
                now(),
                'admin',
                NULL,
                NULL,
                0,
                NULL,
                0,
                1)
            ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mst_longterm_index')
            ->where('index_name', '取引先')
            ->where('permission', 0)
            ->delete();
    }
}
