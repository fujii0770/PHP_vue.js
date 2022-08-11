<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CeateDispatchhrOptionPac51869 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatchhr_option', function (Blueprint $table) {
            $table->bigInteger('dispatchhr_id')->unsigned()
                ->comment('人材ID');
            $table->bigInteger('dispatchhr_screenitems_id')->unsigned()
                ->comment('人材画面項目ID');
            $table->bigInteger('dispatch_code_id')->unsigned()
                ->comment('コードID');   
            $table->integer('status')->unsigned()
                ->comment('状態');  
                                  
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
            $table->primary(['dispatchhr_id','dispatchhr_screenitems_id','dispatch_code_id'])
                ->name('dispatchhr_option_dispatchhr_id_screenitems_id_code_id_primary');
        });
        DB::statement("alter table dispatchhr_option comment '人材情報補足テーブル';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatchhr_option');
    }
}
