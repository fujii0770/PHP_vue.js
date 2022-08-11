<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertAdminAuthoritiesDefaultPac51869 extends Migration
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
                '人材管理',
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
                where c.id = a.mst_company_id and a.code='人材管理')
        ");
 
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
