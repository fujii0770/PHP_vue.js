<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEpsMJournalConfigTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eps_m_journal_config', function (Blueprint $table) {
            $table->bigInteger('id', true)->comment('ID');
            $table->bigInteger('mst_company_id', false)->unsigned()->comment('会社ID');
            $table->string('purpose_name', 20)->comment('目的名');
            $table->string('wtsm_name', 20)->comment('用途名');
            $table->string('account_name', 20)->comment('勘定科目名');
            $table->string('sub_account_name', 200)->nullable()->comment('勘定補助科目');
            $table->json('criteria')->nullable()->comment('条件');
            $table->string('remarks', 2000)->nullable()->comment('摘要');
            $table->integer('display_order')->default(0)->comment('並び順');
            $table->string('memo', 2000)->nullable()->comment('memo');
            $table->datetime('create_at')->useCurrent()->comment('作成日時');
            $table->string('create_user', 128)->comment('作成者');
            $table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable()->comment('更新日時');
            $table->string('update_user', 128)->nullable()->comment('更新者');
            $table->bigInteger('version')->default(0);
            $table->unique(['mst_company_id', 'purpose_name', 'wtsm_name', 'account_name'],'UNIQUE1');
        });
        DB::statement("alter table eps_m_journal_config comment '経費精算_仕訳設定マスタ';");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('eps_m_journal_config');
    }

}
