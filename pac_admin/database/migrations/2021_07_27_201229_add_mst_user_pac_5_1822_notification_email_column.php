<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstUserPac51822NotificationEmailColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_user', function (Blueprint $table) {
            //
            $table->string('notification_email', 256)->nullable()->comment('通知先メールアドレス');
            $table->integer('option_flg')->default(0)->comment('オプション利用者区別フラグ');
            $table->string('reference', 256)->nullable()->comment('備考');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_user', function (Blueprint $table) {
            //
            $table->dropColumn(['notification_email', 'option_flg', 'reference']);
        });
    }
}
