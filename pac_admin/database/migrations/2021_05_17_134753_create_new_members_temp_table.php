<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewMembersTempTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('new_members_temp', function(Blueprint $table)
		{
			$table->integer('rowid')->comment('行id');
			$table->string('email', 256)->nullable()->comment('メールアドレス');
			$table->string('print_setting', 1)->nullable()->comment('印面文字');
			$table->string('print_font', 32)->nullable()->comment('印面文字タイプ');
			$table->string('firstname', 64)->nullable()->comment('姓');
			$table->string('lastname', 64)->nullable()->comment('名');
			$table->string('api_use', 1)->nullable()->comment('APIの使用');
			$table->string('datestamp_changedate', 1)->nullable()->comment('日付印の日付変更');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('new_members_temp');
	}

}
