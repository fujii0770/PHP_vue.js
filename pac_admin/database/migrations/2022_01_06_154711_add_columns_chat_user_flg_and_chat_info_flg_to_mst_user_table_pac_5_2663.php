<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsChatUserFlgAndChatInfoFlgToMstUserTablePac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_user', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_user', 'chat_user_flg')) {
                $table->tinyInteger('chat_user_flg')->unsigned()->default(0)->after('frm_srv_user_flg')
                    ->comment('0:無効, 1:利用中, 2:停止中');
            }
            if (!Schema::hasColumn('mst_user', 'chat_info_flg')) {
                $table->tinyInteger('chat_info_flg')->unsigned()->default(0)->after('chat_user_flg')
                    ->comment('0:未登録, 1:登録済');
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

        Schema::table('mst_user', function (Blueprint $table)
        {
            if (Schema::hasColumn('mst_user', 'chat_user_flg')) {
                $table->dropColumn('chat_user_flg');
            }
            if (Schema::hasColumn('mst_user', 'chat_info_flg')) {
                $table->dropColumn('chat_info_flg');
            }
        });
    }
}
