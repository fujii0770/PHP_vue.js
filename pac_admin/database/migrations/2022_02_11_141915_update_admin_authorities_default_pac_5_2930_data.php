<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAdminAuthoritiesDefaultPac52930Data extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update("UPDATE admin_authorities_default SET code = 'ササッと明細:利用ユーザ登録' WHERE code ='帳票発行サービス:利用ユーザ登録';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::update("UPDATE admin_authorities_default SET code = '帳票発行サービス:利用ユーザ登録' WHERE code ='ササッと明細:利用ユーザ登録';");
    }
}
