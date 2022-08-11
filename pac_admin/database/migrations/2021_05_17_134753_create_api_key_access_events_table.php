<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiKeyAccessEventsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('api_key_access_events', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('api_key_id')->unsigned()->index('api_key_access_events_api_key_id_foreign');
			$table->string('ip_address', 45)->index();
			$table->text('url');
            $table->timestamp('created_at')->nullable();
			$table->timestamp('updated_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('api_key_access_events');
	}

}
