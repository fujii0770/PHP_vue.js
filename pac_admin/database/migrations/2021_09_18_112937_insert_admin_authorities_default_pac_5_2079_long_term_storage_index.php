<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertAdminAuthoritiesDefaultPac52079LongTermStorageIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_authorities_default', function (Blueprint $table) {
            DB::Statement("
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
                )SELECT 
                    mst_company.id,
                    '長期保管インデックス設定',
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
                    where c.id = a.mst_company_id and a.code = '長期保管インデックス設定')
            ");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_authorities_default', function (Blueprint $table) {
            //
        });
    }
}
