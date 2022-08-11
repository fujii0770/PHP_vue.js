<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDispatchAreaTablePac51868 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatcharea', function (Blueprint $table) {
			$table->foreign('dispatcharea_agency_id')->references('id')->on('dispatcharea_agency')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dispatcharea', function (Blueprint $table) {
			$table->dropForeign('dispatcharea_dispatcharea_agency_id_foreign');
        });    
    }
}
