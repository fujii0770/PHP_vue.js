<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBizcardTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bizcard', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('mst_user_id');
			$table->string('name', 128)->nullable();
			$table->string('company_name', 256)->nullable();
			$table->string('phone_number', 128)->nullable();
			$table->string('address', 256)->nullable();
			$table->string('email', 256)->nullable();
			$table->string('department', 128)->nullable();
			$table->string('position', 128)->nullable();
			$table->string('path')->unique();
			$table->string('link_page_url', 256)->nullable();
			$table->timestamp('created_at')->useCurrent();
			$table->string('create_user', 128);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->nullable();
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
		Schema::drop('bizcard');
	}

}
