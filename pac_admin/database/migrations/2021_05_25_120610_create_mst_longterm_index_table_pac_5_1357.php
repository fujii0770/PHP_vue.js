<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstLongtermIndexTablePac51357 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_longterm_index', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mst_company_id');
            $table->unsignedBigInteger('mst_user_id');
            $table->string('index_name', 32);
            $table->integer('data_type')->comment('0:数字, 1:文字, 2:日付');
            $table->integer('permission')->comment('0:全体許可, 1:会社のみ');
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
        Schema::dropIfExists('mst_longterm_index');
    }
}
