<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCircularAttachmentPac51398Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('circular_attachment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('circular_id');
            $table->integer('confidential_flg')->comment('社外秘に設定 0：無効 | 1：有効');
            $table->string('file_name',256)->comment('ファイルの名前');
            $table->string('file_size',20)->comment('ファイルのサイズ')->nullable();
            $table->string('server_url',516)->comment('ファイルパス')->nullable();
            $table->integer('status')->comment('0：ウイルスチェック待ち | 1：ウイルスチェック成功 | 2：ウイルスチェック失敗 | 9：削除');
            $table->bigInteger('create_user_id')->comment('作成者ID');
            $table->bigInteger('create_company_id')->comment('作成会社ID');
            $table->integer('edition_flg')->comment('作成者環境フラグ(0：スタンダード(現行) | 1：プロフェッショナル(新))');
            $table->integer('env_flg')->comment('作成者エディションフラグ(0：AWS | 1：K5)');
            $table->integer('server_flg')->comment('作成者サーバフラグ');
            $table->integer('apply_user_id')->comment('申請者ID');
            $table->string('name',256)->comment('作成者の名前');
            $table->string('title', 256)->nullable()->comment('件名');
            $table->dateTime('create_at')->useCurrent()->comment('作成日時');
            $table->string('create_user',128)->comment('作成者のメールアドレス');
            $table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable()->comment('更新日時');
            $table->string('update_user',128)->nullable()->comment('更新者');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('circular_attachment');
    }
}
