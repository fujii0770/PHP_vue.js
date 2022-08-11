<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsInChatServerUsersTablePac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_server_users', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_server_users', 'chat_personal_name'))
            {
                $table->string('chat_personal_name', 256)->after('chat_user_id')
                    ->comment('ささっとTalkの氏名 mst_user.family_nameと mst_user.given_nameの組み合わせ');
            }
            if (!Schema::hasColumn('chat_server_users', 'system_remark'))
            {
                $table->string('system_remark', 512)->nullable()
                    ->after('chat_user_id')
                    ->comment('RoketChat処理時にエラーが発生した際に情報を登録');
            }

            $table->string('chat_user_name', 80)->comment('ささっとTalkのユーザー名 64文字制限 一括登録時に、ユーザーが未入力だった場合は、mst_user.emailの@より前の値を登録')->change();

            if (!Schema::hasColumn('chat_server_users', 'status'))
            {
                $table->unsignedTinyInteger('status')
                    ->after('chat_role_flg')
                    ->comment('0：無効, 1：有効, 2:停止
                        9：削除, 10:登録待ち(長期間の保存はされない)
                        11:削除待ち(長期間の保存はされない),
                        99:処理失敗(Rocket chat側処理で失敗した際に使用)');
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
