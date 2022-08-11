<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreateDocumentDataRequestPac52437Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('create_document_data_request', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token', 1024)->comment("トークン");
            $table->integer('status')->comment("状態(0:作成待ち 1:作成済み 1:作成失敗)");
            $table->dateTime('create_at')->comment("作成日時");
            $table->string('create_user', 128)->nullable()->comment("作成者");
            $table->dateTime('update_at')->nullable()->comment("更新日時");
            $table->string('update_user', 128)->nullable()->comment("更新者");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('create_document_data_request');
    }
}
