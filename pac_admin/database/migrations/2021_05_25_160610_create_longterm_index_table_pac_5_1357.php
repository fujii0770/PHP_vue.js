<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLongtermIndexTablePac51357 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('longterm_index', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mst_company_id');
            $table->unsignedBigInteger('mst_user_id');
            $table->unsignedBigInteger('circular_id');
            $table->unsignedBigInteger('longterm_index_id');
            $table->string('string_value', 128)->nullable();
            $table->decimal('num_value', 15, 5)->nullable();
            $table->dateTime('date_value')->nullable();
            $table->dateTime('create_at');
            $table->string('create_user', 128);
            $table->dateTime('update_at')->nullable();
            $table->string('update_user', 128)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('longterm_index');
    }
}
