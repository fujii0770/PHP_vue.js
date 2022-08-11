<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstApplicationUsersPac52376 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_application_users', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()->comment('アプリユーザマスタID');
            $table->bigInteger('mst_application_id')->unsigned()
                ->index('fk_mst_application_id_of_mst_application_users_idx')
                ->comment('アプリマスタID');
            $table->bigInteger('mst_user_id')->unsigned()
                ->index('fk_mst_user_id_of_mst_application_users_idx')
                ->comment('ユーザマスタID');
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->timestamp('updated_at')->nullable()
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日');
        });
        DB::statement("alter table mst_application_companies comment 'アプリユーザマスタ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_application_users');
    }
}
