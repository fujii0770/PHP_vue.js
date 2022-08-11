<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileSizeToTemplateFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('template_file', function (Blueprint $table) {
            //template_fileテーブルへfile_sizeとfile_size_flgカラムを追加
            $table->bigInteger('file_size')->default(0)->comment('テンプレートファイルサイズ');
            $table->integer('file_size_flg')->default(0)->comment('テンプレートファイルサイズ取得済みフラグ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('template_file', function (Blueprint $table) {
            //
            $table->dropColumn('file_size');
            $table->dropColumn('file_size_flg');
        });
    }
}