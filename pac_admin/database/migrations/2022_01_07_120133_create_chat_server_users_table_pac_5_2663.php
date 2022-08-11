<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatServerUsersTablePac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('chat_server_users')) {
            Schema::create('chat_server_users', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('mst_company_id');
                $table->unsignedBigInteger('mst_user_id');
                $table->unsignedBigInteger('chat_server_sub_domain_id');
                $table->string('chat_user_id', 128)->nullable()->comment('文字列データ');
                $table->string('chat_user_name', 256)->comment('ささっとTalkのユーザー名');
                $table->unsignedTinyInteger('chat_role_flg')->comment('0：テナント管理者, 1：利用者');
                $table->unsignedTinyInteger('status')->comment('0：無効, 1：有効, 9：削除');
                $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'))->nullable();
                $table->string('create_user', 128);
                $table->dateTime('update_at')->nullable();
                $table->string('update_user',128)->nullable();
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
        Schema::dropIfExists('chat_server_users');
    }
}
