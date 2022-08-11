<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstStampConvenientDivisionPac51545StampConvenientDivision extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_stamp_convenient_division', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->string('division_name', 32)->comment('区分名');
            $table->integer('del_flg')->comment('0：未削除 1：削除済');
            $table->datetime('create_at')->useCurrent();
            $table->string('create_user', 128);
            $table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
            $table->string('update_user', 128)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_stamp_convenient_division');
    }
}
