<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrmInvoiceColsTablePac51598 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frm_invoice_cols', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mst_company_id')->unsigned();
            $table->bigInteger('frm_template_id')->unsigned()->unique();
            $table->string('frm_default_name', 128);
            $table->string('to_email_name_imp', 128)->nullable();
            $table->string('to_email_addr_imp', 128)->nullable();
            $table->string('trading_date_col', 128)->nullable();
            $table->string('invoice_no_col', 128)->nullable();
            $table->string('invoice_date_col', 128)->nullable();
            $table->string('customer_name_col', 128)->nullable();
            $table->string('customer_code_col', 128)->nullable();
            $table->string('invoice_amt_col', 128)->nullable();
            $table->string('payment_date_col', 128)->nullable();
            $table->json('frm_imp_cols')->nullable();
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user', 128);
            $table->dateTime('update_at')->nullable();
            $table->string('update_user',128)->nullable();
            $table->bigInteger('version')->unsigned()->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('frm_invoice_cols');
    }
}
