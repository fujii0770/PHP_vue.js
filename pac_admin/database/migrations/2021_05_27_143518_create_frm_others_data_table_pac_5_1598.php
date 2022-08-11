<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrmOthersDataTablePac51598 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frm_others_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mst_company_id')->unsigned();
            $table->bigInteger('frm_template_id')->unsigned();
            $table->string('frm_template_code',15);
            $table->bigInteger('frm_seq')->unsigned();
            $table->string('frm_name',128);
            $table->bigInteger('circular_id')->unsigned()->nullable();
            $table->string('to_name',256)->nullable();
            $table->string('to_email',256)->nullable();
            $table->date('reference_date')->nullable();
            $table->string('customer_name',1000)->nullable();
            $table->string('customer_code',1000)->nullable();
            $table->string('company_frm_id',24);
            $table->json('frm_data')->nullable();
            $table->bigInteger('frm_imp_mgr_id')->unsigned()->default(0);
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user', 128);
            $table->dateTime('update_at')->nullable();
            $table->string('update_user',128)->nullable();
            $table->bigInteger('version')->default(0)->nullable()->unsigned();
            $table->unique(['frm_template_id', 'frm_seq']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('frm_others_data');
    }
}
