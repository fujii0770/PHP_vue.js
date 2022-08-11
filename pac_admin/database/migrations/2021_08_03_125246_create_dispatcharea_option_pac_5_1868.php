<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDispatchAreaOptionPac51868 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('dispatcharea_option', function (Blueprint $table) {
            $table->bigInteger('dispatcharea_id')->unsigned()
                ->comment('派遣先ID');
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
            $table->primary(['dispatcharea_id','dispatch_code_id']);
        });
        DB::statement("alter table dispatcharea_option comment '派遣先補足テーブル';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatcharea_option');
    }
}
