<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToApiKeyAdminEventsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('api_key_admin_events', function(Blueprint $table)
		{
			$table->foreign('api_key_id')->references('id')->on('api_keys')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('api_key_admin_events', function(Blueprint $table)
		{
			$table->dropForeign('api_key_admin_events_api_key_id_foreign');
		});
	}

}
