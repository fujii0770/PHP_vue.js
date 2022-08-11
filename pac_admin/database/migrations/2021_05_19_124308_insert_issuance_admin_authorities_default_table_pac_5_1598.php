<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class InsertIssuanceAdminAuthoritiesDefaultTablePac51598 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
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
                '帳票発行サービス:利用ユーザ登録',
                1,
                2,
                1,
                2,
                now(),
                'admin',
                now(),
                'admin'
                from mst_company
                where mst_company.id not in (select distinct c.id from mst_company c, admin_authorities_default a
                where c.id = a.mst_company_id and a.code='帳票発行サービス:利用ユーザ登録')
            ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('admin_authorities_default')
            ->where('code', '利用ユーザ登録')
            ->delete();
    }
}
