<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialSiteDocumentDataRequestPac52437Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_site_document_data_request', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token', 1024)->comment("トークン");
            $table->integer('company_id');
            $table->integer('circular_id');
            $table->integer('circular_document_id');
            $table->integer('template_file_id');
            $table->integer('status')->comment("状態(0:作成待ち 0:作成中 2:作成済み 3:作成失敗)");
            $table->dateTime('create_at')->comment("作成日時");
            $table->dateTime('update_at')->nullable()->comment("更新日時");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_site_document_data_request');
    }
}
