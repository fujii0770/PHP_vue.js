<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpenseInputData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_input_data', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('t_app_id')->unsigned();
			$table->bigInteger('circular_id')->unsigned();
			$table->bigInteger('user_id')->unsigned();
			$table->longText('expense_placeholder_name');
			$table->tinyInteger('data_type');
			$table->dateTime('date_data')->nullable();
			$table->decimal('num_data', 15, 5)->nullable();
			$table->longText('text_data')->nullable();
			$table->dateTime('create_at');
			$table->string('create_user', 128);
		});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expense_input_data');
    }
}
