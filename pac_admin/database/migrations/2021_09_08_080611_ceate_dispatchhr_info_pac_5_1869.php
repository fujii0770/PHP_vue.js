<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CeateDispatchhrInfoPac51869 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatchhr_info', function (Blueprint $table) {
            $table->bigInteger('dispatchhr_id')->unsigned()
                ->comment('人材ID');
            $table->bigInteger('dispatchhr_screenitems_id')->unsigned()
                ->comment('人材画面項目ID');
            $table->string('value', 512)
                ->nullable()
                ->comment('値');
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
            $table->primary(['dispatchhr_id','dispatchhr_screenitems_id']);

        });
        DB::statement("alter table dispatchhr_info comment '人材情報テーブル';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatchhr_info');
    }
}
