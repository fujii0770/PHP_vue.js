<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCircularUserTemplateRoutesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('circular_user_template_routes', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('PK連番自動設定');
			$table->bigInteger('template')->unsigned()->comment('circular_user_templatesのID');
			$table->bigInteger('mst_position_id')->unsigned()->comment('役職ID');
			$table->bigInteger('mst_department_id')->unsigned()->comment('部署ID');
			$table->integer('child_send_order')->comment('企業内の回覧順序　申請者:０|承認者:１~');
			$table->integer('mode')->comment('承認方法 全員承認:1|過半数承認:2|n人以上承認:3');
			$table->integer('option')->comment('承認人数　全員承認:０|n人以上承認:n');
			$table->integer('wait')->comment('全承認者の処理を待つか 待つ:1|待たない:0');
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
		Schema::drop('circular_user_template_routes');
	}

}
