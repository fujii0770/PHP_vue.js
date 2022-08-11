<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDispatchAreaOptionTablePac51868 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatcharea_option', function (Blueprint $table) {
			$table->foreign('dispatcharea_id')->references('id')->on('dispatcharea')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('dispatch_code_id')->references('id')->on('dispatch_code')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dispatcharea_option', function (Blueprint $table) {
			$table->dropForeign('dispatcharea_option_dispatcharea_id_foreign');
			$table->dropForeign('dispatcharea_option_dispatch_code_id_foreign');       
        });    
    }

}
