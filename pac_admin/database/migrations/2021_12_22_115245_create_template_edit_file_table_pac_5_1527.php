<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateEditFileTablePac51527 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_edit_file', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_company_id')->unsigned();
			$table->bigInteger('mst_user_id')->unsigned();
			$table->string('file_name', 256);
			$table->string('storage_file_name', 256);
			$table->string('location', 1024);
            $table->bigInteger('template_file_id')->unsigned();
            $table->bigInteger('circular_id')->unsigned();
            $table->bigInteger('edit_number')->unsigned()->comment("テンプレート途中編集回数");
            $table->bigInteger('status')->unsigned()->comment("編集前：0、編集中：1、編集完了：2");
            $table->integer('delete_flg')->default(0)->unsigned()->comment("削除前：0、削除済み：1");
            $table->dateTime('template_edit_at')->comment("テンプレート途中編集時間");
			$table->string('template_edit_user', 128)->comment("テンプレート途中編集ユーザ");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('template_edit_file');
    }
}
