<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMstAppFunctionManagementPac52376 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_app_function_management', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()->comment('アプリマスタとアプリ機能マスタの中間テーブルID');
            $table->bigInteger('mst_application_id')->unsigned()
                ->index('fk_mst_application_id_of_mst_app_function_management_idx')
                ->comment('アプリマスタID');
            $table->bigInteger('mst_app_function_id')->unsigned()
                ->index('fk_mst_app_function_id_of_mst_app_function_management_idx')
                ->comment('アプリ機能マスタId');
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->timestamp('updated_at')->nullable()
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日');
        });
        DB::statement("alter table mst_app_function_management comment 'アプリマスタとアプリ機能マスタの中間テーブル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_app_function_management');
    }
}
