<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstDisplayTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_display', function(Blueprint $table)
		{
		    $table->charset = 'utf8';
		    $table->collation = 'utf8_general_ci';
			$table->bigInteger('id', true);
			$table->string('display_name', 50)->charset('utf8')->collation('utf8_general_ci');
			$table->integer('role')->unsigned()->index('idx_mst_display_on_role')->comment('0：管理者 1：利用者 2：API');
        });
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_display');
	}

}
