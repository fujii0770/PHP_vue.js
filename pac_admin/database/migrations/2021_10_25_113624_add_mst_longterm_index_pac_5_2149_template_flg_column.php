<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstLongtermIndexPac52149TemplateFlgColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //PAC_5-2149
        Schema::table('mst_longterm_index', function (Blueprint $table) {
            $table->integer('template_flg')->default(0)->comment('長期保管インデックステンプレート項目 0:無効/1:有効');
            $table->bigInteger('circular_id')->unsigned()->nullable()->comment('circularテーブルid');
            $table->integer('template_valid_flg')->default(0)->comment('テンプレートインデックス有効化フラグ 0:無効/1:有効');
            $table->integer('auth_flg')->default(1)->comment('管理者登録フラグ 0:無効/1:有効');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_longterm_index', function (Blueprint $table) {
            $table->dropColumn('template_flg');
            $table->dropColumn('template_input_id');
            $table->dropColumn('template_valid_flg');
            $table->dropColumn('auth_flg');
        });
    }
}
