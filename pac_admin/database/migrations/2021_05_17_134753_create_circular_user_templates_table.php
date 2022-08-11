<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCircularUserTemplatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('circular_user_templates', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('PK連番自動設定');
			$table->bigInteger('mst_company_id')->unsigned()->comment('企業ID');
			$table->string('name', 128)->comment('名称');
			$table->integer('state')->comment('有効:1|無効:0');
			$table->datetime('create_at')->useCurrent()->comment('作成日時');
			$table->string('create_user', 128)->comment('作成者');
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable()->comment('更新日時');
			$table->string('update_user', 128)->nullable()->comment('更新者');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('circular_user_templates');
	}

}
