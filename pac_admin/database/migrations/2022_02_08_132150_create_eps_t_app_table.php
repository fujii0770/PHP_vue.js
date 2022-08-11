<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpsTAppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eps_t_app', function (Blueprint $table) {
            $table->unsignedBigInteger('mst_company_id');
            $table->unsignedBigInteger('eps_app_no');
            $table->string('form_code',20);
            $table->unsignedBigInteger('mst_user_id');
            $table->string('purpose_name',20)->nullable();
            $table->date('target_period_from');
            $table->date('target_period_to')->nullable();
            $table->string('form_dtl',1000)->nullable();
            $table->decimal('expected_amt',12)->nullable();
            $table->decimal('desired_suspay_amt',12)->nullable();
            $table->decimal('suspay_amt',12)->nullable();
            $table->date('suspay_date')->nullable();
            $table->tinyInteger('suspay_method')->nullable();
            $table->decimal('eps_amt',12)->nullable();
            $table->decimal('eps_diff',12)->nullable();
            $table->date('diff_date')->nullable();
            $table->tinyInteger('diff_method')->nullable();
            $table->bigInteger('circular_id')->nullable();
            $table->date('completed_date')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user',128);
            $table->dateTime('update_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->string('update_user',128);
            $table->bigInteger('version')->unsigned()->default(0);
            //PK
            $table->primary(['mst_company_id','eps_app_no'],'primary_column');
            //FK
            $table->foreign('mst_company_id')->references('id')->on('mst_company');
            $table->foreign('mst_user_id')->references('id')->on('mst_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eps_t_app');
    }
}
