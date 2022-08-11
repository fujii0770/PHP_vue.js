<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CeateDispatchhrScreenitemsPac51869 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatchhr_screenitems', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()
                ->comment('人材テンプレート画面項目ID');
            $table->bigInteger('dispatchhr_template_id')->unsigned()
                ->comment('人材テンプレートID');
            $table->integer('row')->unsigned()
                ->comment('行');
            $table->integer('col')->unsigned()
                ->comment('列');
            $table->string('remarks', 256)
                ->comment('説明等');
            $table->string('type', 20)
                ->comment('コントロールタイプ');
            $table->integer('code_flg')->unsigned()
                ->comment('dispatch_codeテーブルの利用有無　1[利用]');
            $table->integer('dispatch_code_kbn')->unsigned()
                ->nullable()
                ->comment('dispatch_codeテーブルの区分');
            $table->integer('regist_table_kbn')->unsigned()
                ->comment('登録先　0[dispatchhr_info] 1[dispatchhr_option]');
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
        DB::statement("alter table dispatchhr_screenitems comment '人材テンプレート画面項目テーブル';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatchhr_screenitems');
    }
}
