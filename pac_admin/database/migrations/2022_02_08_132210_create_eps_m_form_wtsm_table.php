<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpsMFormwtsmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eps_m_form_wtsm', function (Blueprint $table) {
            $table->unsignedBigInteger('mst_company_id');
            $table->string('form_code',20);
            $table->string('wtsm_name',20);
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user',128);
            $table->dateTime('update_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->string('update_user',128);
            $table->bigInteger('version')->unsigned()->default(0);
            //PK
            $table->primary(['mst_company_id','form_code','wtsm_name'],'primary_column');
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
        Schema::dropIfExists('eps_m_form_wtsm');
    }
}
