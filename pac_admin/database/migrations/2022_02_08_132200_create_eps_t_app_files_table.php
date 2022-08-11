<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpsTAppFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eps_t_app_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mst_company_id');
            $table->unsignedBigInteger('eps_app_no');
            $table->unsignedInteger('eps_app_item_no')->nullable();
            $table->unsignedInteger('attached_file_no')->nullable();
            $table->string('s3_path',256)->nullable();
            $table->string('s3_file_name',256)->nullable();
            $table->tinyInteger('document_type')->nullable();
            $table->string('original_file_name',256)->nullable();
            $table->string('saved_file_name',256)->nullable();
            $table->Integer('file_size')->nullable();  
            $table->string('file_sha256',256)->nullable();          
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user',128);
            $table->dateTime('update_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->string('update_user',128);
            $table->bigInteger('version')->unsigned()->default(0);
            //FK
            $table->foreign('mst_company_id')->references('id')->on('mst_company');

        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eps_t_app_files');
    }
}
