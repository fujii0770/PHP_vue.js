<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailSendResumeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mail_send_resume', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('PK連番自動設定');
			$table->integer('mst_company_id')->comment('会社ID');
			$table->text('to_email')->comment('受信者メールアドレス');
			$table->string('template', 512)->nullable()->comment('テンプレート名値がある場合、テンプレート送信。値がない場合、テキストフォーマット送信');
			$table->text('param')->nullable()->comment('送信パラメーター');
			$table->integer('type')->nullable()->comment('受信対象0：利用者1：管理者9：その他');
			$table->string('subject', 512)->nullable()->comment('件名');
			$table->text('body')->nullable()->comment('メール本文');
			$table->integer('state')->nullable()->comment('送信状態0：送信待ち1：送信中2：送信成功3：送信失敗');
			$table->integer('send_times')->comment('送信回数ディフォルト：0');
			$table->dateTime('create_at')->comment('作成日時');
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable()->comment('更新日時');
			$table->integer('email_format')->default(1)->comment('0:テキスト/1:HTML');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mail_send_resume');
	}

}
