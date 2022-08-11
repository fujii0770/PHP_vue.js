<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDocumentDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('document_data', function(Blueprint $table)
		{
			$table->foreign('circular_document_id', 'document_data_ibfk_1')->references('id')->on('circular_document')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('document_data', function(Blueprint $table)
		{
			$table->dropForeign('document_data_ibfk_1');
		});
	}

}
