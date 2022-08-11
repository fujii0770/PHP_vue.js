<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentStampTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('department_stamp', function(Blueprint $table)
		{
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
			$table->bigInteger('id', true);
			$table->string('pribt_type', 32)->charset('utf8')->collation('utf8_general_ci');
			$table->string('layout', 32)->charset('utf8')->collation('utf8_general_ci');
			$table->string('face_up1', 32)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
			$table->string('face_up2', 32)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
			$table->string('face_down1', 32)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
			$table->string('face_down2', 32)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
			$table->string('font', 32)->charset('utf8')->collation('utf8_general_ci');
			$table->string('color', 32)->charset('utf8')->collation('utf8_general_ci');
			$table->longtext('stamp_image')->charset('utf8')->collation('utf8_general_ci');
			$table->integer('width')->unsigned()->nullable();
			$table->integer('height')->unsigned()->nullable();
			$table->integer('real_width')->unsigned()->nullable();
			$table->integer('real_height')->unsigned()->nullable();
			$table->integer('date_x')->nullable();
			$table->integer('date_y')->nullable();
			$table->integer('date_width')->nullable();
			$table->integer('date_height')->nullable();
			$table->integer('state')->comment('"0:無効 1:有効"');
			$table->dateTime('create_at');
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('serial', 32)->default('')->charset('utf8')->collation('utf8_general_ci');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('department_stamp');
	}

}
