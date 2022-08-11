<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstLimitPac52251FileMailSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_constraints', function (Blueprint $table) {
            //
            $table->integer('file_mail_size_single')->default(500)->comment('最大ファイルサイズ(MB)');
            $table->integer('file_mail_size_total')->default(5)->comment('合計の最大ファイルサイズ(G)');
            $table->integer('file_mail_count')->default(10)->comment('最大アップロードファイル数(個)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_constraints', function (Blueprint $table) {
            //
            $table->dropColumn(['file_mail_size_single', 'file_mail_size_total', 'file_mail_count']);
        });
    }
}
