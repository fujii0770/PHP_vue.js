<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShachihataLoginSituationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shachihata_login_situations', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_shachihata_id')->unsigned()->index();
			$table->string('ip_address', 15);
			$table->string('user_agent', 2048);
			$table->dateTime('create_at');
			$table->string('create_user', 128);
			$table->dateTime('update_at')->index();
			$table->string('update_user', 128);
		});
        DB::statement("ALTER TABLE shachihata_login_situations MODIFY COLUMN update_at datetime(0) NOT NULL ON UPDATE CURRENT_TIMESTAMP(0);");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('shachihata_login_situations');
	}

}
