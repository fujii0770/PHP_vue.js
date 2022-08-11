<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsAdminIdAdminTokenTenantKeyInMstChatTablePac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_chat', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_chat', 'admin_id'))
            {
                $table->string('admin_id', 128)
                    ->after('contract_type')->nullable();
            }
            if (!Schema::hasColumn('mst_chat', 'admin_token'))
            {
                $table->string('admin_token', 512)
                    ->after('admin_id')->nullable();
            }
            if (!Schema::hasColumn('mst_chat', 'tenant_key'))
            {
                $table->string('tenant_key', 256)
                    ->after('admin_token')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_chat', function (Blueprint $table)
        {
            if (Schema::hasColumn('mst_chat', 'admin_id'))
            {
                $table->dropColumn('admin_id');
            }
            if (Schema::hasColumn('mst_chat', 'admin_token'))
            {
                $table->dropColumn('admin_token');
            }
            if (Schema::hasColumn('mst_chat', 'tenant_key'))
            {
                $table->dropColumn('tenant_key');
            }

        });
    }
}
