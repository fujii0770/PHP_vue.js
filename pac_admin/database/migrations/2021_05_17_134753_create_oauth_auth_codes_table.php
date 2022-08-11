<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOauthAuthCodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('oauth_auth_codes', function(Blueprint $table)
		{
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
			$table->string('id', 100)->primary()->charset('utf8')->collation('utf8_unicode_ci');
			$table->bigInteger('user_id');
			$table->integer('client_id')->unsigned();
			$table->text('scopes')->nullable()->charset('utf8')->collation('utf8_unicode_ci');
			$table->boolean('revoked');
			$table->dateTime('expires_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('oauth_auth_codes');
	}

}
