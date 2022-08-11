<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CeateDispatchhrJobcareerPac51869 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatchhr_jobcareer', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()
                ->comment('人材職歴ID');
            $table->bigInteger('dispatchhr_id')->unsigned()
                ->comment('人材ID');
            $table->string('work_startym', 6)
                ->comment('開始期間');   
            $table->string('work_toym', 6)
                ->comment('終了期間');   
            $table->string('company_department', 256)
                ->comment('会社と部署');
            $table->string('industry', 128)
                ->comment('業種');
            $table->integer('employment')->unsigned()
                ->comment('就業形態');
            $table->string('business_content', 512)
                ->comment('業務内容');
            $table->string('salary', 128)
                ->nullable()
                ->comment('給与');
            $table->string('retirement_reason', 128)
                ->nullable()
                ->comment('退職理由');

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
        DB::statement("alter table dispatchhr_jobcareer comment '人材職歴テーブル';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatchhr_jobcareer');
    }
}
