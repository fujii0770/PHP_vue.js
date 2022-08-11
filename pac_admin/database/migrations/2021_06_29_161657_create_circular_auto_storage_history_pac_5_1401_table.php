<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCircularAutoStorageHistoryPac51401Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('circular_auto_storage_history', function (Blueprint $table) {
            //
            $table->bigIncrements('id')->comment('番号');
            $table->integer('circular_id')->comment('回覧ID');
            $table->integer('mst_company_id')->comment('企業ID');
            $table->string('applied_email', 256)->comment('申請者メールアドレス');
            $table->string('applied_name', 128)->comment('申請者名');
            $table->string('title', 256)->comment('件名');
            $table->string('file_name', 1024)->comment('文書名');
            $table->string('route', 512)->nullable()->comment('自動保管のパス');
            $table->integer('result')->comment('自動保管結果(1:成功 2:失敗)');
            $table->dateTime('create_at')->comment('作成日');
            $table->dateTime('update_at')->nullable()->comment('更新日');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('circular_auto_storage_history');
    }
}
