<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembersTempTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('members_temp', function(Blueprint $table)
		{
			$table->integer('rowid')->comment('行id');
			$table->string('email', 256)->nullable()->comment('メールアドレス');
			$table->string('firstname', 64)->nullable()->comment('姓');
			$table->string('lastname', 64)->nullable()->comment('名');
			$table->string('department', 256)->nullable()->charset('utf8mb4')->collation('utf8mb4_general_ci');
			$table->string('position', 256)->nullable()->comment('役職名');
			$table->string('postal_code', 8)->nullable()->comment('郵便番号');
			$table->string('address', 256)->nullable()->comment('住所');
			$table->string('phone_no', 15)->nullable()->comment('電話番号');
			$table->string('fax_no', 15)->nullable()->comment('FAX番号');
			$table->string('homepage_url', 500)->nullable()->comment('ホームページ');
			$table->string('api_use', 1)->nullable()->comment('APIの使用');
			$table->string('print_setting', 1)->nullable()->comment('印面文字');
			$table->string('print_font', 32)->nullable()->comment('印面文字タイプ');
			$table->string('members_state', 1)->nullable()->comment('有効化');
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
		Schema::drop('members_temp');
	}

}
