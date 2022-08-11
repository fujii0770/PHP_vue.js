<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAdminAuthoritiesDefaultPac2298UpdatePermissionData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('admin_authorities_default')->where('code','カテゴリ設定')
            ->update([
                'create_authority' => 2,
                'update_authority' => 2,
                'delete_authority' => 2
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
