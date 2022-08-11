<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAdminAuthoritiesDefaultTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_authorities_default', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('id');
			$table->bigInteger('mst_company_id')->comment('-1の場合は全企業向けの定義');
			$table->string('code', 64)->comment('機能名;例えば、企業管理者向け利用状況なら「MydomainReport」');
			$table->integer('read_authority')->comment("参照権限;0：設定なし（そもそも機能に該当操作なし）\r\n1：権限あり\r\n2：権限なし");
			$table->integer('create_authority')->comment("生成権限;0：設定なし（そもそも機能に該当操作なし）\r\n1：権限あり\r\n2：権限なし");
			$table->integer('update_authority')->comment("編集権限;0：設定なし（そもそも機能に該当操作なし）\r\n1：権限あり\r\n2：権限なし");
			$table->integer('delete_authority')->comment("削除権限;0：設定なし（そもそも機能に該当操作なし）\r\n1：権限あり\r\n2：権限なし");
			$table->dateTime('create_at')->comment('作成日時');
			$table->string('create_user', 128)->nullable()->comment('作成者');
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('update_user', 128)->nullable()->comment('更新者');
		});
        DB::statement("alter table admin_authorities_default comment '管理者権限初期値テーブル';");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admin_authorities_default');
	}

}
