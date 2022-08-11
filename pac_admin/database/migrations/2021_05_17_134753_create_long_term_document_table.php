<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLongTermDocumentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('long_term_document', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('circular_id')->unsigned();
			$table->bigInteger('mst_company_id')->unsigned();
			$table->string('sender_name');
			$table->string('sender_email');
			$table->text('destination_name');
			$table->text('destination_email');
			$table->text('file_name');
			$table->integer('file_size');
			$table->string('keyword')->nullable();
			$table->dateTime('request_at');
			$table->dateTime('completed_at');
			$table->string('title', 256);
			$table->string('create_user', 128)->nullable();
			$table->string('update_user', 128)->nullable();
			$table->dateTime('create_at');
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->dateTime('add_timestamp_automatic_date')->nullable();
			$table->tinyInteger('timestamp_automatic_flg')->default(0);
        });
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('long_term_document');
	}

}
