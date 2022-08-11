<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewTimeCardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('time_card');
        Schema::create('time_card', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mst_user_id')->index();
            $table->json('punch_data')->comment('json 文字列で保存する');
            $table->tinyInteger('num_flg')->comment('打刻順位1-10');
            $table->dateTime('punched_at')->comment('実際打刻日時');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('time_card');
    }
}
