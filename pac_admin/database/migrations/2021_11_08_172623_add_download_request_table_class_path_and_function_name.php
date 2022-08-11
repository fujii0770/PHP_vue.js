<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDownloadRequestTableClassPathAndFunctionName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('download_request', function (Blueprint $table) {
            $table->string('class_path')->comment('ダウンロードファイル生成処理が宣言されているクラスパス');
            $table->string('function_name')->comment('ダウンロードファイル生成処理関数名');
            $table->longtext('arguments')->comment('ダウンロードファイル生成処理関数の引数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('download_request', function (Blueprint $table) {
            $table->dropColumn('class_path');
            $table->dropColumn('function_name');
            $table->dropColumn('arguments');
        });
    }
}
