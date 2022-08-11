<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFolderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('folder', function(Blueprint $table)
		{
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_user_id')->unsigned()->index('mst_user_id');
            $table->datetime('create_at')->useCurrent()->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('folder');
	}

}
