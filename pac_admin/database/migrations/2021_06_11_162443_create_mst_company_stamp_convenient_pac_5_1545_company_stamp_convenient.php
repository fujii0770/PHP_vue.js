<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstCompanyStampConvenientPac51545CompanyStampConvenient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_company_stamp_convenient', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->bigInteger('mst_stamp_convenient_id')->unsigned()->comment('便利印マスタID');
            $table->bigInteger('mst_company_id')->unsigned()->comment('便利印マスタID');
            $table->integer('del_flg')->comment('0：未削除 1：削除済');
            $table->datetime('create_at')->useCurrent()->comment('作成日時');
            $table->string('create_user', 128)->comment('作成者');
            $table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable()->comment('更新日時');
            $table->string('update_user', 128)->nullable()->comment('更新者');
            $table->string('serial', 32)->default('')->comment('シリアル');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_company_stamp_convenient');
    }
}
