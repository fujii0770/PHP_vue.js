<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upload_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('upload_data')->comment('upload_document');
            $table->longText('first_img_review')->comment('upload_document_pdf_first_page_img_review');
            $table->tinyInteger('status')->default(1);
            $table->integer('file_size');
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
        Schema::dropIfExists('upload_data');
    }
}
