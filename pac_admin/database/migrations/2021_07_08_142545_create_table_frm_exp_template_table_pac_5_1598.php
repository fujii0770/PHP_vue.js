<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableFrmExpTemplateTablePac51598 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frm_exp_template', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mst_company_id')->unsigned();
            $table->bigInteger('mst_user_id')->unsigned();
            $table->string('file_name',256);
            $table->string('remarks',100)->nullable();
            $table->integer('display_order')->default(0);
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user', 128);
            $table->dateTime('update_at')->nullable();
            $table->string('update_user',128)->nullable();        
            $table->bigInteger('version')->unsigned()->default(0);      
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('frm_exp_template');
    }
}
