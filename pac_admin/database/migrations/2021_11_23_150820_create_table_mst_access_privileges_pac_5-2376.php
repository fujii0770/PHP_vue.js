<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMstAccessPrivilegesPac52376 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_access_privileges', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()->comment('アクセス権限マスタID');
            $table->bigInteger('mst_app_function_id')->unsigned()
                ->index('fk_mst_app_function_id_of_mst_access_privileges_idx')
                ->comment('アプリケーション機能マスタの外部キー');
            $table->integer('privilege_code')->unsigned()->comment('権限コード');
            $table->string('privilege_content', 45)->comment('権限');
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->timestamp('updated_at')->nullable()
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日');
        });
        DB::statement("alter table mst_access_privileges comment 'アクセス権限マスタ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_access_privileges');
    }
}
