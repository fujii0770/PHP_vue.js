<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFailedJobsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('failed_jobs', function(Blueprint $table)
		{
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
			$table->bigInteger('id', true)->unsigned();
			$table->text('connection')->charset('utf8')->collation('utf8_unicode_ci');
			$table->text('queue')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
			$table->longtext('payload')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
			$table->longtext('exception')->charset('utf8')->collation('utf8_unicode_ci');
			$table->timestamp('failed_at')->useCurrent();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('failed_jobs');
	}

}
