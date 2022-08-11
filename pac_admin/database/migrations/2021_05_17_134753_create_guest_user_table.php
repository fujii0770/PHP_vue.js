<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuestUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('guest_user', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('circular_id')->unsigned()->index('guest_user_circular_id_foreign');
			$table->bigInteger('create_user_id');
			$table->string('email', 256);
			$table->bigInteger('create_company_id')->unsigned();
			$table->string('name', 128)->nullable();
			$table->datetime('create_at')->useCurrent();
			$table->string('create_user', 128);
            $table->dateTime('update_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
			$table->string('update_user', 128)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('guest_user');
	}

}
