<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateContractedSitesCallbackUrlPac52400 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('contracted_sites_callback_url')) {
            Schema::create('contracted_sites_callback_url', function (Blueprint $table) {
                $table->unsignedBigInteger('mst_chat_id')->primary();
                $table->string('call_back_url', 512);
                $table->unsignedTinyInteger('status')->default(0);
                $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->string('create_user', 128)->nullable();
                $table->dateTime('update_at')->nullable();
                $table->string('update_user',128)->nullable();
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contracted_sites_callback_url');
    }
}
