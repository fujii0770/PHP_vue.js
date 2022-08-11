<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialSiteCircularPac51902Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_site_circular', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('circular_id');
            $table->integer('special_template_id');
            $table->integer('receive_mst_company_id');
            $table->integer('receive_edition_flg');
            $table->integer('receive_env_flg');
            $table->integer('receive_server_flg');
            $table->string('circular_token', 1024);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_site_circular');
    }
}
