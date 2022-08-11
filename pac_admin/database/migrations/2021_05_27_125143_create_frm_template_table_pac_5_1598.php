<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrmTemplateTablePac51598 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frm_template', function (Blueprint $table) {
            if (Schema::hasTable("frm_template")) {
                Schema::dropIfExists('frm_template');
            }
            $table->bigIncrements('id');
            $table->bigInteger('mst_company_id')->unsigned();
            $table->bigInteger('mst_user_id')->unsigned();
            $table->string('file_name', 256);
            $table->tinyInteger('document_type')->comment("0：Execl|1：Word");
            $table->string('frm_template_code',15);
            $table->tinyInteger('frm_type')->comment("0：その他|1：請求書");
            $table->tinyInteger('frm_template_access_flg')->comment("0：社内|1：部署|2：登録者");
            $table->tinyInteger('frm_template_edit_flg')->comment("0：社内|1：部署|2：登録者");
            $table->dateTime('disabled_at')->comment("null:有効|その他:無効")->nullable();
            $table->string('remarks', 100)->nullable();
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user', 128);
            $table->dateTime('update_at')->nullable();
            $table->string('update_user',128)->nullable();
            $table->bigInteger('version')->unsigned()->default(0);
            $table->unique(['mst_company_id', 'frm_template_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('frm_template');
    }
}
