<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnMstChatIdIntoChatServerUsersTablePac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_server_users', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_server_users', 'mst_chat_id')) {
                $table->unsignedBigInteger('mst_chat_id')->after('mst_user_id');
            }
            if (Schema::hasColumn('chat_server_users', 'chat_server_sub_domain_id')) {
                $table->dropColumn('chat_server_sub_domain_id');
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
        if (Schema::hasColumn('chat_server_users', 'mst_chat_id'))
        {
            Schema::table('chat_server_users', function (Blueprint $table) {
                $table->dropColumn('mst_chat_id');
            });
        }
    }
}
