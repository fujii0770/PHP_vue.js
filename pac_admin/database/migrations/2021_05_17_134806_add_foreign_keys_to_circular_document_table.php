<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCircularDocumentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('circular_document', function(Blueprint $table)
		{
			$table->foreign('circular_id', 'circular_document_ibfk_1')->references('id')->on('circular')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('circular_document', function(Blueprint $table)
		{
			$table->dropForeign('circular_document_ibfk_1');
		});
	}

}
