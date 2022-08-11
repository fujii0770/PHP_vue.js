<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CeateDispatchhrTemplatePac51869 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatchhr_template', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()
                ->comment('人材テンプレートID');
            $table->integer('tabno')->unsigned()
                ->comment('タブ番号');
            $table->integer('order')->unsigned()
                ->comment('並び順');
            $table->string('remarks', 256)
                ->comment('説明等');
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
        DB::statement("alter table dispatchhr_template comment '人材テンプレートテーブル';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatchhr_template');
    }
}
