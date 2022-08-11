<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAdminAuthoritiesDefaultPac52776Data extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            DB::beginTransaction();
            DB::table("admin_authorities_default")->where('code', '文書登録')->delete();
            DB::table("admin_authorities_default")->where('code', '連携承認')->delete();
            DB::table("admin_authorities_default")->where('code', '連携申請')->delete();
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
                    '文書登録',
                    1,
                    2,
                    2,
                    2,
                    now(),
                    'Shachihata',
                    now(),
                    'Shachihata'
                    from mst_company
                    where mst_company.id not in (select distinct c.id from mst_company c, admin_authorities_default a
                    where c.id = a.mst_company_id and a.code = '文書登録')
            ");
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
                    '連携承認',
                    1,
                    0,
                    2,
                    0,
                    now(),
                    'Shachihata',
                    now(),
                    'Shachihata'
                    from mst_company
                    where mst_company.id not in (select distinct c.id from mst_company c, admin_authorities_default a
                    where c.id = a.mst_company_id and a.code = '連携承認')
            ");
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
                    '連携申請',
                    1,
                    0,
                    2,
                    0,
                    now(),
                    'Shachihata',
                    now(),
                    'Shachihata'
                    from mst_company
                    where mst_company.id not in (select distinct c.id from mst_company c, admin_authorities_default a
                    where c.id = a.mst_company_id and a.code = '連携申請')
            ");
            DB::table("permissions")->where('name', 'admin.create_special_site_send')->delete();
            DB::commit();;
        } catch (\Exception $e) {
            DB::rollBack();
        }

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
