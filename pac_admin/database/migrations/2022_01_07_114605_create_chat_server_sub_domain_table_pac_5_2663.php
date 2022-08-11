<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatServerSubDomainTablePac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('chat_server_sub_domain')) {
            Schema::create('chat_server_sub_domain', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('mst_company_id');
                $table->string('group_domain', 20);
                $table->string('sub_domain', 20);
                $table->string('admin_id', 128);
                $table->string('admin_token', 512);
                $table->unsignedTinyInteger('status')->default(0)->comment('0：無効, 1：有効, 9：削除');
                $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'))->nullable();
                $table->string('create_user', 128);
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
        Schema::dropIfExists('chat_server_sub_domain');
    }
}
