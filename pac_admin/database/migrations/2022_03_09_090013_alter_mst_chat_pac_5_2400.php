<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMstChatPac52400 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_chat', function (Blueprint $table) {
            $table->string('mongo_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_chat', function (Blueprint $table) {
            $table->string('mongo_url', 256)->after('tenant_key')->nullable(false)->change();
        });
    }
}
