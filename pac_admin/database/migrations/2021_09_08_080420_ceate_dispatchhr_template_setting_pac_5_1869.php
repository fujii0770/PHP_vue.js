<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CeateDispatchhrTemplateSettingPac51869 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatchhr_template_setting', function (Blueprint $table) {
            $table->bigInteger('mst_company_id')->unsigned()
                ->comment('作成会社ID');                 
            $table->bigInteger('dispatchhr_template_id')->unsigned()
                ->comment('人材テンプレートID');                    
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
            $table->primary(['mst_company_id','dispatchhr_template_id'])
            ->name('dispatchhr_template_setting_mst_company_id_template_id_primary');
            
        });
        DB::statement("alter table dispatchhr_template_setting comment '人材テンプレート設定テーブル';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatchhr_template_setting');
    }
}
