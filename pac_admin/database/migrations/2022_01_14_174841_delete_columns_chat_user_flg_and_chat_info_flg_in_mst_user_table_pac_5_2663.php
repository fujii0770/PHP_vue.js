<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteColumnsChatUserFlgAndChatInfoFlgInMstUserTablePac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_user', function (Blueprint $table) {
            if (Schema::hasColumn('mst_user', 'chat_user_flg'))
            {
                $table->dropColumn('chat_user_flg');
            }
            if (Schema::hasColumn('mst_user', 'chat_info_flg'))
            {
                $table->dropColumn('chat_info_flg');
            }
        });
        Schema::table('chat_server_users', function (Blueprint $table) {
            if (Schema::hasColumn('chat_server_users', 'status'))
            {
                $table->dropColumn('status');
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

    }
}
