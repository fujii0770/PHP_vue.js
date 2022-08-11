<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySynchronizationApiRequestPac52327MessageLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('synchronization_api_request', function (Blueprint $table) {
            $table->longText('message')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('synchronization_api_request', function (Blueprint $table) {
            $table->string('message',500)->change();
        });
    }
}
