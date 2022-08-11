<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFrmOthersDataPac52935FrmIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('frm_others_data', function (Blueprint $table) {
            $table->string('frm_index1',1000)->nullable()->comment('帳票項目設定内容1');;
            $table->string('frm_index2',1000)->nullable()->comment('帳票項目設定内容2');;
            $table->string('frm_index3',1000)->nullable()->comment('帳票項目設定内容3');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('frm_others_data', function (Blueprint $table) {
            $table->dropColumn('frm_index1');
            $table->dropColumn('frm_index2');
            $table->dropColumn('frm_index3');
        });
    }
}
