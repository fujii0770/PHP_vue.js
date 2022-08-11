<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DeleteRedundantDataInAdminAuthoritiesDefaultTablePac53170 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * Delete redundant data permission in admin_authorities_default
         * when create new company (permission use service Talk)
         *
         */

        $ids = DB::table('mst_company')
            ->join('admin_authorities_default as aau', 'aau.mst_company_id', 'mst_company.id')
            ->where([
                'aau.code' => 'ささっとTalk利用者設定',
                'aau.read_authority' => 1,
                'aau.create_authority' => 0,
                'aau.update_authority' => 0,
                'aau.delete_authority' => 0,
                'mst_company.chat_flg' => 0,
            ])->pluck('aau.id');
        DB::table('admin_authorities_default')
            ->whereIn('id', $ids)
            ->delete();
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
