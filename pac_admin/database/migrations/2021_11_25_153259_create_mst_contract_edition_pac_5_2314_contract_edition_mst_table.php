<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstContractEditionPac52314ContractEditionMstTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_contract_edition', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('select_id')->comment('選択ID');
            $table->string('contract_edition_name',256)->comment('契約エディション名');
            $table->string('memo',256)->nullable()->comment('メモ');
            $table->integer('state_flg')->default(0)->comment('ステータス 1:有効、0:無効、9:削除');
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('create_user',128)->comment('作成者');
            $table->dateTime('update_at')->nullable()->comment('更新日時');
            $table->string('update_user',128)->nullable()->comment('更新者');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_contract_edition');
    }
}
