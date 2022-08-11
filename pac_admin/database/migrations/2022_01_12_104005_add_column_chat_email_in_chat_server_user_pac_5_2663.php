<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnChatEmailInChatServerUserPac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_server_users', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_server_users', 'chat_email')) {
                $table->string('chat_email', 256)->after('chat_user_name')
                    ->comment('ささっとTalk登録時のメールアドレス');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('chat_server_users', 'chat_email'))
        {
            Schema::table('chat_server_users', function (Blueprint $table)
            {
                $table->dropColumn('chat_email');
            });
        }
    }
}
