<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstFrmSrvUserInfoTablePac51598 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_frm_srv_user_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('mst_user_id');
            $table->dateTime('create_at')->nullable();
            $table->string('create_user', 128);
            $table->string('update_user', 128)->nullable();
            $table->dateTime('update_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_frm_srv_user_info');
    }
}
