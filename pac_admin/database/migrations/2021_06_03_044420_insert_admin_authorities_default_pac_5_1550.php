<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertAdminAuthoritiesDefaultPac51550 extends Migration
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
                '勤務表一覧',
                2,
                0,
                2,
                0,
                now(),
                'admin',
                now(),
                'admin'
                from mst_company
                where mst_company.id not in (select distinct c.id from mst_company c, admin_authorities_default a
                where c.id = a.mst_company_id and a.code='勤務表一覧')
        ");
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
                '勤務確認',
                2,
                0,
                2,
                0,
                now(),
                'admin',
                now(),
                'admin'
                from mst_company
                where mst_company.id not in (select distinct c.id from mst_company c, admin_authorities_default a
                where c.id = a.mst_company_id and a.code='勤務確認')
        ");
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
                '掲示板情報',
                2,
                0,
                2,
                0,
                now(),
                'admin',
                now(),
                'admin'
                from mst_company
                where mst_company.id not in (select distinct c.id from mst_company c, admin_authorities_default a
                where c.id = a.mst_company_id and a.code='掲示板情報')
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      //  DB::beginTransaction();
      //  DB::delete("DELETE FROM admin_authorities_default  WHERE code = '勤務表一覧'");
      //  DB::delete("DELETE FROM admin_authorities_default  WHERE code = '勤務確認'");
       // DB::delete("DELETE FROM admin_authorities_default  WHERE  code = '掲示板情報'");
       // DB::commit();
    }
}
