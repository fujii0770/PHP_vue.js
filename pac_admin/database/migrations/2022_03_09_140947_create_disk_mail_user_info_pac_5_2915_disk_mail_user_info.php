<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiskMailUserInfoPac52915DiskMailUserInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disk_mail_user_info', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()->comment('ID');
            $table->bigInteger('mst_user_id')->unsigned()->comment('権限ユーザID');
            $table->dateTime('create_at');
            $table->string('create_user', 128)->nullable();
            $table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
            $table->string('update_user', 128)->nullable();
            $table->string('comment1')->default('確認をお願いします。');
            $table->string('comment2')->default('ご確認をお願い致します。');
            $table->string('comment3')->default('至急確認をお願いします。');
            $table->string('comment4')->default('至急ご確認をお願い致します。');
            $table->string('comment5')->default('ご確認の程よろしくお願い申し上げます。');
            $table->string('comment6')->default('');
            $table->string('comment7')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disk_mail_user_info');
    }
}
