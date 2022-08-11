<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppRoleUsersPac52376 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_role_users', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()->comment('アプリケーションロールに属するユーザID');
            $table->bigInteger('app_role_id')->unsigned()
                ->index('fk_app_role_id_of_app_role_users_idx')
                ->comment('アプリケーションロールの外部キー');
            $table->bigInteger('mst_user_id')->unsigned()
                ->index('fk_mst_user_id_of_app_role_users_idx')
                ->comment('ユーザマスタの外部キー');
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->timestamp('updated_at')->nullable()
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日');
        });
        DB::statement("alter table app_role_users comment 'アプリケーションロールに属するユーザ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_role_users');
    }
}
