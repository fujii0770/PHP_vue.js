<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToContractOptionTablePac51868 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contract_option', function (Blueprint $table) {
			$table->foreign('contract_id')->references('id')->on('contract')->onUpdate('CASCADE')->onDelete('RESTRICT');
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
        Schema::table('contract_option', function (Blueprint $table) {
			$table->dropForeign('contract_option_contract_id_foreign');
			$table->dropForeign('contract_option_dispatch_code_id_foreign');       
        });    
    }
}
