<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAdminAuthoritiesDefaultPac52715AuthorityChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update("UPDATE admin_authorities_default SET read_authority = 2 WHERE code ='保護設定' AND read_authority = 0;");
        DB::update("UPDATE admin_authorities_default SET update_authority = 2 WHERE code ='保護設定' AND update_authority = 0;");
        DB::update("UPDATE admin_authorities_default SET read_authority = 2 WHERE code ='接続IP制限設定' AND read_authority = 0;");
        DB::update("UPDATE admin_authorities_default SET create_authority = 2 WHERE code ='接続IP制限設定' AND create_authority = 0;");
        DB::update("UPDATE admin_authorities_default SET update_authority = 2 WHERE code ='接続IP制限設定' AND update_authority = 0;");
        DB::update("UPDATE admin_authorities_default SET delete_authority = 2 WHERE code ='接続IP制限設定' AND delete_authority = 0;");
        DB::update("UPDATE admin_authorities_default SET read_authority = 2 WHERE code ='電子証明書設定' AND read_authority = 0;");
        DB::update("UPDATE admin_authorities_default SET create_authority = 2 WHERE code ='電子証明書設定' AND create_authority = 0;");
        DB::update("UPDATE admin_authorities_default SET update_authority = 2 WHERE code ='電子証明書設定' AND update_authority = 0;");
        DB::update("UPDATE admin_authorities_default SET delete_authority = 2 WHERE code ='電子証明書設定' AND delete_authority = 0;");
        DB::update("UPDATE admin_authorities_default SET delete_authority = 2 WHERE code ='管理者設定' AND delete_authority = 0;");
        DB::update("UPDATE admin_authorities_default SET delete_authority = 2 WHERE code ='管理ユーザ登録' AND delete_authority = 0;");
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
