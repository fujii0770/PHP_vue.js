<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSynchronizationApiDomainPac51880 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('synchronization_api_domain', function (Blueprint $table) {
            $table->string('login_id',50);
            $table->string('login_password',50);
            $table->string('sftp_username',100)->comment('SFTP用ユーザ');
            $table->integer('mst_company_id')->unsigned()->comment('パソコンクラウドCloudのID');
            $table->string('email_addresses',256)->nullable()->comment('結果通知先メールアドレス  メールアドレスを入力するときは[;]で区切ります：email1;email2;');
            $table->integer('status')->comment('値が1の場合：　APIを使用できる || 値が0の場合：　APIの使用はできない（認証時にエラーとする）');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('synchronization_api_domain');
    }
}
