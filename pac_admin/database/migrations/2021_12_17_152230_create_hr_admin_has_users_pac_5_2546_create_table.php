<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHrAdminHasUsersPac52546CreateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hr_admin_has_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mst_company_id');
            $table->unsignedBigInteger('admin_mst_user_id')->comment('管理者のID');
            $table->unsignedBigInteger('user_mst_user_id')->comment('利用者のID');
            $table->integer('del_flg')->comment('0：未削除 1：削除済');
            $table->timestamp('create_at')->useCurrent();
            $table->string('create_user', 128);
            $table->timestamp('update_at')->useCurrent()->nullable();
            $table->string('update_user', 128)->nullable();

            $table->foreign('mst_company_id')->references('id')->on('mst_company');
            $table->foreign('admin_mst_user_id')->references('id')->on('mst_user');
            $table->foreign('user_mst_user_id')->references('id')->on('mst_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hr_admin_has_users');
    }
}
