<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTextInfoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('text_info', function(Blueprint $table)
		{
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
			$table->bigInteger('id', true);
			$table->bigInteger('circular_document_id')->unsigned()->index('idx_text_info_on_circular_document_id');
			$table->integer('circular_operation_id')->nullable()->comment('承認履歴情報ID');
			$table->text('text')->charset('utf8')->collation('utf8_general_ci');
			$table->string('name', 128)->nullable();
			$table->string('email', 256)->charset('utf8')->collation('utf8_general_ci');
			$table->dateTime('create_at');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('text_info');
	}

}
