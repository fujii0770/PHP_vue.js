<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAdminAuthoritiesDefaultPac53054Code extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update("UPDATE admin_authorities_default SET code = 'ササッと明細:明細項目設定' WHERE code ='ササッと明細:帳票項目設定';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::update("UPDATE admin_authorities_default SET code = 'ササッと明細:帳票項目設定' WHERE code ='ササッと明細:明細項目設定';");
    }
}
