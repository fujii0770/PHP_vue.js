<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCircularUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('circular_user', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('id');
			$table->bigInteger('circular_id')->unsigned()->index('circular_id')->comment('回覧ID');
			$table->integer('parent_send_order')->comment("親送信順;企業間の順番を格納\r\n同企業内のユーザーには同じ番号を振る");
			$table->integer('env_flg')->nullable()->comment("環境フラグ;0：AWS\r\n1：K5");
			$table->integer('edition_flg')->comment("エディションフラグ;0：スタンダード(現行)\r\n1：プロフェッショナル(新)");
			$table->integer('server_flg')->default(0);
			$table->bigInteger('mst_company_id')->unsigned()->nullable()->comment('会社マスタID');
			$table->string('email', 256)->index('INX_EMAIL')->comment('メールアドレス');
			$table->string('name', 128)->nullable()->comment('名前');
			$table->binary('title')->comment('件名');
			$table->integer('circular_status')->comment("回覧ステータス;0：未通知\r\n1：通知済/未読\r\n2：既読\r\n3：承認(捺印あり)\r\n4：承認(捺印なし)\r\n5：差戻し\r\n6：差戻し(未読)\r\n7：差戻依頼\r\n8：引戻し\r\n9：文書破棄");
			$table->dateTime('create_at')->comment("作成日時");
			$table->string('create_user', 128)->nullable()->comment("作成者");
			$table->dateTime('update_at')->nullable()->comment("更新日時");
			$table->string('update_user', 128)->nullable()->comment("更新者");
			$table->integer('child_send_order')->comment("子送信順;企業内の送信順を格納\r\n企業間ごとに1から番号を振る");
			$table->integer('del_flg')->comment("削除フラグ;0：未削除\r\n1：削除済");
			$table->bigInteger('mst_user_id')->unsigned()->nullable()->comment("ユーザーマスタID");
			$table->string('origin_circular_url', 516)->nullable()->comment("オリジナル回覧URL;別環境または別エディションが振出元であった場合に回覧元のURLを格納");
			$table->integer('return_flg')->default(0)->comment("窓口返却フラグ;1会社内の先頭のユーザー(窓口ユーザー)へ送信するかのフラグ\r\nデフォルトは0を設定\r\n0：窓口へ戻す\r\n1：窓口へ戻さない");
			$table->string('mst_company_name', 256)->nullable();
			$table->dateTime('received_date')->nullable();
			$table->dateTime('sent_date')->nullable();
			$table->text('sender_name')->nullable();
			$table->text('sender_email')->nullable();
			$table->text('receiver_name')->nullable();
			$table->text('receiver_email')->nullable();
			$table->text('receiver_name_email')->nullable();
			$table->text('receiver_title')->nullable();
			$table->integer('copy_flg')->default(0);
			$table->index(['circular_id','email'], 'INX_circular_id_email');
        });
        DB::statement("alter table circular_user comment '回覧ユーザーテーブル';");
        DB::statement("ALTER TABLE circular_user MODIFY COLUMN title varbinary(256) NOT NULL comment '件名';");
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('circular_user');
	}

}
