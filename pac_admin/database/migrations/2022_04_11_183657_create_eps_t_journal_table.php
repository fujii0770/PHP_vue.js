<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpsTJournalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('eps_t_journal');
        Schema::create('eps_t_journal', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mst_company_id')->unsigned();
            $table->bigInteger('eps_t_app_id')->nullable();
            $table->unsignedInteger('eps_t_app_item_id')->nullable();
            $table->unsignedInteger('eps_app_item_bno')->default(1);
            $table->date('rec_date',20)->nullable();
            $table->string('rec_dept',256)->nullable();
            $table->string('debit_rec_dept',20)->nullable();
            $table->string('debit_account',20)->nullable();
            $table->string('debit_subaccount',1000)->nullable();
            $table->decimal('debit_amount',12)->nullable();
            $table->unsignedInteger('debit_tax_div')->nullable();
            $table->decimal('debit_tax_rate',5,2)->nullable();
            $table->decimal('debit_tax',12)->nullable();
            $table->string('credit_rec_dept',20)->nullable();
            $table->string('credit_account',20)->nullable();
            $table->string('credit_subaccount',1000)->nullable();
            $table->decimal('credit_amount',12)->nullable();
            $table->unsignedInteger('credit_tax_div')->nullable();
            $table->decimal('credit_tax_rate',5)->nullable();
            $table->decimal('credit_tax',12)->nullable();
            $table->string('remarks',1000)->nullable();
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
        Schema::dropIfExists('eps_t_journal');
    }
}
