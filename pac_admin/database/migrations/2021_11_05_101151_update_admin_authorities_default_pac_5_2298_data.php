<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateAdminAuthoritiesDefaultPac52298Data extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('admin_authorities_default')->where('code','スケジュール種別設定')
            ->update([
                'code' => 'カテゴリ設定'
            ]);
        DB::table('admin_authorities_default')->where('code','アプリ制限設定')
            ->update([
                'code' => '制限設定'
            ]);
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
