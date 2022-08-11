<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimeCardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('time_card', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mst_user_id')->index();
            $table->tinyInteger('num')->comment('上限は10回、何回目を示す');
            $table->dateTime('punched_at')->nullable()->comment('打刻時間');
            $table->tinyInteger('type')->comment('1: 出勤; 2:退勤');
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
        Schema::dropIfExists('time_card');
    }
}
