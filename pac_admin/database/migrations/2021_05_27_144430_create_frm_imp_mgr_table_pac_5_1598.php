<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrmImpMgrTablePac51598 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frm_imp_mgr', function (Blueprint $table) {
            if (Schema::hasTable("frm_imp_mgr")) {
                Schema::dropIfExists('frm_imp_mgr');
            }
            $table->bigIncrements('id');
            $table->bigInteger('mst_company_id')->unsigned();
            $table->bigInteger('frm_template_id')->unsigned();
            $table->unsignedBigInteger('frm_template_ver')->comment('登録時の帳票テンプレートのVersion。実行時に一致しない場合は実行しない。');
            $table->bigInteger('mst_user_id')->unsigned();
            $table->dateTime('request_datetime');
            $table->tinyInteger('request_method')->comment("0:Web画面, 1:WebAPI");
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();
            $table->string('imp_filename',256)->nullable();
            $table->tinyInteger('imp_status')->default(0)->comment("0:待機中|1:実行中(Step1) |2:実行中(Step2) |5:成功(正常終了) |-1:取消(実行の取消) |-11:中断(Step1) |-12:中断(Step2)|-21:エラー(Step1でのデータエラー)|-22:エラー(Step2でエラー)|-99:異常終了");
            $table->integer('imp_rows')->unsigned()->default(0);
            $table->integer('registered_rows')->unsigned()->default(0)->comment('登録データ件数');
            $table->dateTime('cancel_req_datetime')->nullable();
            $table->string('remarks', 2000)->nullable();
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user', 128);
            $table->dateTime('update_at')->nullable();
            $table->string('update_user',128)->nullable();
            $table->bigInteger('version')->default(0)->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('frm_imp_mgr');
    }
}
