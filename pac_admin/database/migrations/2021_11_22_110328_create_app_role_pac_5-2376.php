<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppRolePac52376 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_role', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()->comment('アプリケーションロールID');
            $table->string('name', 50)->comment('ロール名');
            $table->bigInteger('mst_company_id')->unsigned()->nullable()
                ->index('fk_mst_company_id_of_app_role_idx')
                ->comment('会社ID');
            $table->bigInteger('mst_application_id')->unsigned()
                ->index('fk_mst_application_id_of_app_role_idx')
                ->comment('アプリマスタID');
            $table->string('memo', 255)->nullable()->comment('ロール名');
            $table->boolean('is_default')->default(false)->comment('標準フラグ（標準ロールかどうか）');
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->timestamp('updated_at')->nullable()
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日');
        });
        DB::statement("alter table app_role comment 'アプリケーションロール'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_role');
    }
}
