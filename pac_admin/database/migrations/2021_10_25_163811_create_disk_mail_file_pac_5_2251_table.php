<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiskMailFilePac52251Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disk_mail_file', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('disk_mail_id');
            $table->string('file_name', '256')->comment('ファイル名');
            $table->integer('file_size')->comment('ファイルのサイズ');
            $table->string('file_url',516)->comment('ファイルパス')->nullable();
            $table->integer('status')->comment('状態');
            $table->dateTime('create_at')->comment("作成日時");
            $table->string('create_user', 128)->nullable()->comment("作成者");
            $table->dateTime('update_at')->nullable()->comment("更新日時");
            $table->string('update_user', 128)->nullable()->comment("更新者");
            $table->index(['disk_mail_id'],'INX_DISK_MAIL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disk_mail_file');
    }
}
