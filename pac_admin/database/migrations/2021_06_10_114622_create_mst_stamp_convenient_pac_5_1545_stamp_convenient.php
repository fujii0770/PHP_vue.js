<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstStampConvenientPac51545StampConvenient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_stamp_convenient', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->string('stamp_name', 32)->comment('便利印名称');
            $table->integer('stamp_division')->comment('便利印区分');
            $table->longtext('stamp_image')->comment('印面データ');
            $table->integer('width')->unsigned();
            $table->integer('height')->unsigned();
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
        Schema::dropIfExists('mst_stamp_convenient');
    }
}
