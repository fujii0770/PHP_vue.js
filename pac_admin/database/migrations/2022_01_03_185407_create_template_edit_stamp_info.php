<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateEditStampInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_edit_stamp_info', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
			$table->bigInteger('circular_id')->unsigned();
            $table->decimal('height', 15, 11)->unsigned();
            $table->bigInteger('stamp_id')->unsigned();
            $table->decimal('opacity', 10, 5)->unsigned();
			$table->bigInteger('pageno')->unsigned();
            $table->integer('repeated')->unsigned();
            $table->bigInteger('rotateAngle')->unsigned();
            $table->string('serial', 32);
            $table->bigInteger('sid')->nullable();
            $table->longText('stamp_data');
            $table->bigInteger('stamp_flg')->unsigned();
            $table->longText('stamp_url')->nullable();
            $table->bigInteger('time_stamp_permission');
            $table->decimal('width', 15, 11)->unsigned();
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
        Schema::dropIfExists('template_edit_stamp_info');
    }
}
