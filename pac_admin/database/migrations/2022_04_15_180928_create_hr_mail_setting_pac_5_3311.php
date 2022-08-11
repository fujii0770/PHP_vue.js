<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHrMailSettingPac53311 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hr_mail_setting', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mst_user_id')->unsigned()->comment('ユーザーマスタID');
            $table->string('mail_address_1', 256)->nullable()->comment('メールアドレス1');
            $table->string('name_1', 128)->nullable()->comment('氏名1');
            $table->string('mail_address_2', 256)->nullable()->comment('メールアドレス2');
            $table->string('name_2', 128)->nullable()->comment('氏名2');
            $table->string('mail_address_3', 256)->nullable()->comment('メールアドレス3');
            $table->string('name_3', 128)->nullable()->comment('氏名3');
            $table->string('mail_address_4', 256)->nullable()->comment('メールアドレス4');
            $table->string('name_4', 128)->nullable()->comment('氏名4');
            $table->string('mail_address_5', 256)->nullable()->comment('メールアドレス5');
            $table->string('name_5', 128)->nullable()->comment('氏名5');
            $table->string('text_1', 600)->nullable()->comment('文面1');
            $table->string('text_2', 600)->nullable()->comment('文面2');
            $table->string('text_3', 600)->nullable()->comment('文面3');
            $table->string('signature', 600)->nullable()->comment('署名');

            $table->datetime('create_at')->useCurrent()->comment('作成日時');
            $table->string('create_user', 128)->comment('作成者');
            $table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable()->comment('更新日時');
            $table->string('update_user', 128)->nullable()->comment('更新者');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hr_mail_setting');
    }
}
