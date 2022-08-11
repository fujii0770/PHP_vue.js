<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnMongoUrlInMstChatTableAndUpdateCommentStatusInChatServerUsersPac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_chat', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_chat', 'mongo_url')) {
                $table->string('mongo_url', 256)->after('tenant_key')
                    ->comment('mongodb+srv://ユーザ名:パスワード@cluster0.zz0hb.mongodb.net/');

            }
        });
        // Update comment chat_server_users.status
        DB::statement("ALTER TABLE chat_server_users MODIFY COLUMN status TINYINT Unsigned not null
                comment '0：無効
1：有効
2:停止
9：削除
10:登録待ち
11:削除待ち
12:停止待ち
13: 停止解除待ち
90:登録失敗(Rocket chat側登録処理で失敗)
91:削除失敗(Rocket chat側削除処理で失敗)
92:停止失敗(Rocket chat側停止処理で失敗)
93:停止解除失敗(Rocket chat側停止解除処理で失敗)'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_chat', function (Blueprint $table) {
            if (Schema::hasColumn('mst_chat', 'mongo_url')) {
                $table->dropColumn('mongo_url');
            }
        });
    }
}
