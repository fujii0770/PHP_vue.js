<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEpsMJournalConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('eps_m_journal_config');

        Schema::create('eps_m_journal_config', function (Blueprint $table) {
            $table->unsignedBigInteger('mst_company_id');
            $table->bigInteger('id', true)->unsigned();
            $table->string('purpose_name',20);
            $table->string('wtsm_name',20);
            $table->string('account_name',20);
            $table->string('sub_account_name',200)->nullable();
            $table->json('criteria')->nullable();
            $table->string('remarks',2000)->nullable();
            $table->integer('display_order')->default(0);
            $table->string('memo',2000)->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user',128);
            $table->dateTime('update_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->string('update_user',128);
            $table->integer('version')->default(0);
            //FK
            $table->foreign('mst_company_id')->references('id')->on('mst_company');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eps_m_journal_config');
        
    }
}
