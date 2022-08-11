<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPasswordResetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_password_resets', function(Blueprint $table)
		{
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
			$table->string('email', 191)->index('password_resets_email_index')->charset('utf8')->collation('utf8_unicode_ci');
			$table->string('token', 191)->charset('utf8')->collation('utf8_unicode_ci');
			$table->timestamp('created_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_password_resets');
	}

}
