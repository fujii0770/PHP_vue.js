<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpsMExpFormTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eps_m_exp_form', function (Blueprint $table) {
            $table->unsignedBigInteger('mst_company_id');
            $table->string('exp_form_code',20);
            $table->tinyInteger('release_flag');
            $table->string('exp_form_name',50);
            $table->unsignedTinyInteger('exp_file_type')->default(0);
            $table->string('exp_form_description',100)->nullable();
            $table->string('original_file_name',120)->nullable();
            $table->string('saved_file_name',120)->nullable();
            $table->bigInteger('original_version')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user',128);
            $table->dateTime('update_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->string('update_user',128);
            $table->bigInteger('version')->unsigned()->default(0);
            //PK
            $table->primary(['mst_company_id','exp_form_code','release_flag'],'primary_column');
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
        Schema::dropIfExists('eps_m_exp_form');
    }
}
