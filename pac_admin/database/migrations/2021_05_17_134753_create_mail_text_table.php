<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailTextTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mail_text', function(Blueprint $table)
		{
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('circular_user_id')->unsigned()->index('INX_circular_user_id');
			$table->text('text')->charset('utf8mb4')->collation('utf8mb4_general_ci');;
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
		Schema::drop('mail_text');
	}

}
