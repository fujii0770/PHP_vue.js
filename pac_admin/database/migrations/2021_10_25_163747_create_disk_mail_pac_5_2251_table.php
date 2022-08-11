<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiskMailPac52251Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disk_mail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('mst_user_id')->nullable()->comment('ユーザーマスタID');
            $table->string('access_code', '64')->nullable()->comment('セキュリティコード');
            $table->text('receiver_email')->nullable()->comment('宛先');
            $table->string('title', '64')->nullable()->comment('件名');
            $table->text('message')->nullable()->comment('メッセージ');
            $table->dateTime('applied_date')->nullable()->comment('申請時間');
            $table->dateTime('expiration_date')->nullable()->comment('ダウンロード有効期限');
            $table->integer('download_limit')->nullable()->comment('ダウンロード最大回数');
            $table->integer('download_count')->default(0)->comment('ダウンロード回数');
            $table->string('download_link', 256)->nullable()->comment('ダウンロードURL');
            $table->integer('file_mail_resume_id')->nullable()->comment('ファイルメール送信履歴ID');
            $table->integer('access_mail_resume_id')->nullable()->comment('セキュリティコード送信履歴ID');
            $table->integer('status')->comment('状態');
            $table->dateTime('create_at')->comment("作成日時");
            $table->string('create_user', 128)->comment("作成者");
            $table->dateTime('update_at')->nullable()->comment("更新日時");
            $table->string('update_user', 128)->nullable()->comment("更新者");
            $table->index(['mst_user_id'], 'INX_USER');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disk_mail');
    }
}
