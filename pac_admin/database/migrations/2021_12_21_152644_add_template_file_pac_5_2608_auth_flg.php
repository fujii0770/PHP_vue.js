<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTemplateFilePac52608AuthFlg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('template_file', function (Blueprint $table) {
            //PAC_5-2608 管理者登録テンプレートファイルフラグ
            $table->integer('auth_flg')->default(0)->comment('管理者登録フラグ');
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
            $table->dropColumn('auth_flg');
        });
    }
}
