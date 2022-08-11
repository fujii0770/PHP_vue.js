<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldOnTemplateInputDataPac51527ConfirmFlg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('template_input_data', function (Blueprint $table) {
            //PAC_5-1527 確定フラグ追加
            $table->integer('confirm_flg')->default(0)->comment('テンプレート編集機能確定フラグ ０:無効｜１:有効');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('template_input_data', function (Blueprint $table) {
            $table->dropColumn('confirm_flg');
        });
    }
}
