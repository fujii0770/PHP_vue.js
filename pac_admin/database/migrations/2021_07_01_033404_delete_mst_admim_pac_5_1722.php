<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteMstAdmimPac51722 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('mst_domain');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('mst_domain', function(Blueprint $table){
			$table->bigInteger('id', true)->unsigned();
			$table->string('domain');
			$table->dateTime('create_at');
		});
    }
}
