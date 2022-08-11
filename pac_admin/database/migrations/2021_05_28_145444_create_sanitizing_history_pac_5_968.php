<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSanitizingHistoryPac5968 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sanitizing_history', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('download_request_id')->comment('ダウンロード要求ID');

            $table->string('file_token', 256)->nullable()->comment('ファイルトークン');

            $table->integer('req_type')->comment('要求タイプ 1:無害化 2:ダウンロードURL 3:ダウンロード 4:削除');
            
            $table->integer('type')->comment('タイプ 1:要求 2:callback');

            $table->integer('status')->nullable()->comment('callbackの結果');

            $table->string('details')->nullable()->comment('callbackの詳細');
            
            $table->dateTime('create_at')->comment('作成日');
        });

        // ALTER 文を実行しテーブルにコメントを設定
        DB::statement("ALTER TABLE sanitizing_history COMMENT '無害化サーバ通信履歴'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sanitizing_history');
    }
}
