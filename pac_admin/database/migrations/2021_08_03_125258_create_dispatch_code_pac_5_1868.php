<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDispatchCodePac51868 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatch_code', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()
                ->comment('派遣機能コードID');
            $table->integer('kbn')->unsigned()
                ->comment('区分');                   
            $table->integer('code')->unsigned()
                ->comment('コード');                   
            $table->string('name', 512)         
                ->comment('名称');
            $table->integer('order')->unsigned()
                ->comment('並び順');                   
            $table->string('remarks', 256)
                ->nullable()            
                ->comment('備考');                 
            $table->integer('del_flg')->unsigned()
                ->default(0)
                ->comment('削除フラグ');  
            $table->timestamp('create_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->string('create_user', 128)
                ->comment('作成者');
            $table->timestamp('update_at')
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->nullable()
                ->comment('更新日');
            $table->string('update_user', 128)
                ->comment('更新者');
        });
        DB::statement("alter table dispatch_code comment '派遣機能コードテーブル';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatch_code');
    }
}
