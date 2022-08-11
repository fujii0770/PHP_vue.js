<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBrandingTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('branding', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('id');
			$table->bigInteger('mst_company_id')->unsigned()->comment('会社マスタID');
			$table->text('logo_file_data')->nullable()->comment('ロゴ画像データ');
			$table->string('background_color', 6)->nullable()->comment('背景色');
			$table->string('color', 6)->nullable()->comment('文字色');
			$table->dateTime('create_at')->comment('作成日時');
			$table->string('create_user', 128)->nullable()->comment('作成者');
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable()->comment('更新日時');
			$table->string('update_user', 128)->nullable()->comment('更新者');
		});
        DB::statement("alter table branding comment 'ブランディング設定テーブル';");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('branding');
	}

}
