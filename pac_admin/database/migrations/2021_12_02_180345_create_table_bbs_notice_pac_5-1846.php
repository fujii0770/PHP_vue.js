<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableBbsNoticePac51846 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('bbs_notice')) {
            Schema::create('bbs_notice', function (Blueprint $table) {
                $table->bigInteger('id', true)->comment('Id');
                $table->tinyInteger('type')->comment('お知らせタイプ（0：システム通知、1：ユーザ通知');
                $table->string('subject', '0')->comment('件名');
                $table->text('contents')->nullable()->comment('お知らせ内容');
                $table->bigInteger('from_user_id')->index('fk_from_user_id_of_notice_idx')->comment('ユーザマスタId（お知らせ送り主）');
                $table->string('link')->nullable()->comment('リンク');
                $table->timestamp('created_at')->useCurrent()->comment('作成日');
                $table->timestamp('updated_at')->useCurrent()->comment('更新日');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bbs_notice');
    }
}
