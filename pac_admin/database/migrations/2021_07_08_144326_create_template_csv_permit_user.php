<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateCsvPermitUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_csv_permit_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('template_id');
            $table->unsignedBigInteger('circular_id');
            $table->unsignedBigInteger('mst_user_id');
            $table->unsignedBigInteger('mst_company_id');
            $table->string('csv_permit_user', 256);
            $table->dateTime('template_create_at');
            $table->string('template_create_user', 128);
            $table->dateTime('template_update_at')->nullable();
            $table->string('template_update_user', 128)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('template_csv_permit_user');
    }
}
