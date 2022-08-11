<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertAdminAuthoritiesDefaultPac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * 1: Delete all old data
         * 2: Insert if mst_company.chat_flg = 1;
         *
         */
        DB::table('admin_authorities_default')
            ->where('code', 'ささっとTalk利用者設定')
            ->delete();

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
                    'ささっとTalk利用者設定',
                    1,
                    2,
                    2,
                    2,
                    now(),
                    'Shachihata',
                    now(),
                    'Shachihata'
                    from mst_company
                    where mst_company.chat_flg = 1
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
