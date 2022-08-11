<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeCardPermissionIntoPac52246PermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
           insert into admin_authorities_default (
                mst_company_id,
                `code`,
                read_authority,
                create_authority,
                update_authority,
                delete_authority,
                create_at,
                create_user,
                update_at,
                update_user
             ) select
                mst_company.id,
                'タイムカード設定',
                1,
                2,
                2,
                2,
                now(),
                'admin',
                now(),
                'admin'
                from mst_company
                where mst_company.id not in (select distinct c.id from mst_company c, admin_authorities_default a
                where c.id = a.mst_company_id and a.code='タイムカード設定')
                and portal_flg = 1
             ");

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
