<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpsTAppItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eps_t_app_items', function (Blueprint $table) {
            $table->unsignedBigInteger('mst_company_id');
            $table->unsignedBigInteger('eps_app_no');
            $table->unsignedInteger('eps_app_item_no')->nullable();
            $table->string('wtsm_name',20);
            $table->date('expected_pay_date');
            $table->decimal('unit_price',12)->default(0);
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('expected_pay_amt',12)->default(0);
            $table->unsignedInteger('numof_ppl')->default(0);
            $table->unsignedInteger('tax')->default(0);
            $table->string('traffic facility_name',50)->nullable();
            $table->string('from_station',50)->nullable();
            $table->string('to_station',50)->nullable();
            $table->tinyInteger('roundtrip_flag')->default(0);
            $table->string('extra_options',50)->nullable();
            $table->string('remarks',1000)->nullable();
            $table->unsignedTinyInteger('submit_method');
            $table->string('submit_other_memo',1000)->nullable();
            $table->unsignedTinyInteger('nonsubmit_type');
            $table->string('nonsubmit_reason',1000)->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user',128);
            $table->dateTime('update_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->string('update_user',128);
            $table->bigInteger('version')->unsigned()->default(0);
            //PK
            $table->primary(['mst_company_id','eps_app_no','eps_app_item_no'],'primary_column');
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
        Schema::dropIfExists('eps_t_app_items');
    }
}
