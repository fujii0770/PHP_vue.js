<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstStampSynonymsMapTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_stamp_synonyms_map', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('origin', 20)->comment('印面検索文字');
			$table->string('synonym', 20)->comment('印面検索結果文字');
			$table->datetime('create_at')->useCurrent();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_stamp_synonyms_map');
	}

}
