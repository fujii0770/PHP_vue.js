<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetsPolicyPac53114Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('password_resets_policy', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email', 256)->comment('メール');
            $table->integer('type_flg')->default(0)->unsigned()->comment('1：user,2: admin');
            $table->integer('password_change_flg')->default(1)->unsigned()->comment('パスワード変更回数制限');
            $table->timestamp('last_update_at')->nullable()->comment('code更新日時');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('password_resets_policy');
    }
}
