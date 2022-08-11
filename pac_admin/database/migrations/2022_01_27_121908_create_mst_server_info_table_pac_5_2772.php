<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstServerInfoTablePac52772 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('mst_server_info')) {
            Schema::create('mst_server_info', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('contract_app');
                $table->integer('app_env');
                $table->integer('contract_server');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_server_info');
    }
}
