<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCodeServiceTalkInAdminAuthoritiesDefaultTablePac53170 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('admin_authorities_default')
            ->where([
                // Old code name
                'code' => 'ささっとTalk利用者設定'
            ])->update([
                // new code name
                'code' => 'ササッとTalk利用者設定'
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('admin_authorities_default')
            ->where([
                'code' => 'ササッとTalk利用者設定'
            ])->update([
                'code' => 'ささっとTalk利用者設定'
            ]);
    }
}
