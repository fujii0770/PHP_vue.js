<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrmTemplateViewingUserByPac52280 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frm_template_viewing_user', function (Blueprint $table) {
            $table->bigIncrements('id', true)->comment('帳票テンプレート印鑑ID');
            $table->bigInteger('frm_template_id')->unsigned()->comment('帳票テンプレートID');
            $table->integer('parent_send_order')->comment('企業間の順番');
            $table->bigInteger('mst_company_id')->unsigned()->nullable()->comment('企業ID');
            $table->bigInteger('mst_user_id')->unsigned()->nullable()->comment('ユーザーマスタID');
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user', 128);
            $table->dateTime('update_at')->nullable();
            $table->string('update_user',128)->nullable();
            $table->bigInteger('version')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('frm_template_viewing_user');
    }
}
