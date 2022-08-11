<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentCsvTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('department_csv', function(Blueprint $table)
		{
		    $table->charset = 'utf8';
		    $table->collation = 'utf8_general_ci';
			$table->bigInteger('id', true);
			$table->bigInteger('mst_company_id')->unsigned();
			$table->bigInteger('mst_user_id')->unsigned();
			$table->string('file_name', 25)->nullable()->charset('utf8')->collation('utf8_general_ci');
			$table->longtext('contents')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
			$table->timestamp('request_date')->useCurrent();
			$table->integer('state');
			$table->dateTime('contents_create_at')->nullable();
        });
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('department_csv');
	}

}
