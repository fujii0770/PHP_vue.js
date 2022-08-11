<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatServerOnetimeTokenPac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('chat_server_onetime_token')) {
            Schema::create('chat_server_onetime_token', function (Blueprint $table) {
                $table->unsignedBigInteger('sub_domain_id')->primary();
                $table->string('onetime_token', 128)->unique();
                $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->string('create_user', 128)->nullable();
                $table->dateTime('update_at')->nullable();
                $table->string('update_user',128)->nullable();

            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_server_onetime_token');
    }
}
