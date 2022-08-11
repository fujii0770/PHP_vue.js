<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSynchronizationApiRequestPac51880 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('synchronization_api_request', function (Blueprint $table) {
            $table->bigInteger('id',true)->unsigned()->comment('ID');
            $table->integer('mst_company_id')->unsigned()->comment('パソコンクラウド決済のID');
            $table->dateTime('request_datetime')->comment('リクエスト受付時間');
            $table->string('command',50)->comment('コマンド名');
            $table->integer('execution_flg')->nullable()->default(null)->comment('実行フラグ:リクエスト受付時（デフォルト）：null || 実行処理中：0 || 実行処理終了：1');
            $table->dateTime('execution_start_datetime')->nullable()->default(null)->comment('実行開始時間:リクエスト受付時（デフォルト）：null');
            $table->dateTime('execution_end_datetime')->nullable()->default(null)->comment('実行終了時間:リクエスト受付時（デフォルト）：null');
            $table->integer('result')->nullable()->default(null)->comment('実行結果:リクエスト受付時（デフォルト）：null || 成功：1 || 失敗：0');
            $table->string('message',200)->nullable()->comment(null)->comment('メッセージ: 失敗時にエラーメッセージを登録する; デフォルトはnull');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('synchronization_api_request');
    }
}
