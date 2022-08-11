<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddColumnChatAdminRoleIdInMstChatPac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_chat', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_chat', 'chat_admin_role_id')) {
                $table->string('chat_admin_role_id', 128)->nullable()
                    ->after('contract_type')
                    ->comment('管理者ロールIDを保持 この値を基にテナント管理者のロールを付与する');
            }
        });

        // Update comment column
        DB::statement("ALTER TABLE chat_server_users MODIFY COLUMN status TINYINT Unsigned not null
                comment '0：無効, 1：有効, 2:停止, 9：削除,
                        10:登録待ち(長期間の保存はされない),
                        11:削除待ち(長期間の保存はされない),
                        90:登録失敗(Rocket chat側登録処理で失敗),
                        91:削除失敗(Rocket chat側削除処理で失敗)'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_chat', function (Blueprint $table) {
            if (Schema::hasColumn('mst_chat', 'chat_admin_role_id')) {
                $table->dropColumn('chat_admin_role_id');

            }
        });
    }
}
