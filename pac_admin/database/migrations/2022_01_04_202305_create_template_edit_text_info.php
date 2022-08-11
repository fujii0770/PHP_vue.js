<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateEditTextInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_edit_text_info', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
			$table->bigInteger('circular_id')->unsigned();
            $table->string('fontColor',128);
            $table->string('fontFamily',128);
            $table->decimal('fontSize',19,15)->unsigned();
			$table->bigInteger('page')->unsigned();
            $table->longText('text');
            $table->decimal('x_axis', 19, 15)->unsigned();
            $table->decimal('y_axis', 19, 15)->unsigned();
            $table->timestamp('created_at')->useCurrent()->comment('作成日');
            $table->string('create_user',128)->comment('作成者');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('template_edit_text_info');
    }
}
