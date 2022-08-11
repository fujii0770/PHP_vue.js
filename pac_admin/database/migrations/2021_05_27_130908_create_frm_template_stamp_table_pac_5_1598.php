<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrmTemplateStampTablePac51598 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frm_template_stamp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mst_company_id')->unsigned();
            $table->bigInteger('frm_template_id')->unsigned();
            $table->bigInteger('mst_company_stamp_id')->unsigned();
            $table->double('stamp_top')->default(0);
            $table->double('stamp_left')->default(0);
            $table->integer('stamp_deg')->default(0);
            $table->integer('stamp_page')->default(0);
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
        Schema::dropIfExists('frm_template_stamp');
    }
}
