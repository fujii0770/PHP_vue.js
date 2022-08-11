<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFrmOthersColsPac52935FrmIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('frm_others_cols', function (Blueprint $table) {
            $table->string('frm_index1_col',128)->nullable()->comment('帳票項目設定1');
            $table->string('frm_index2_col',128)->nullable()->comment('帳票項目設定2');
            $table->string('frm_index3_col',128)->nullable()->comment('帳票項目設定3');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('frm_others_cols', function (Blueprint $table) {
            $table->dropColumn('frm_index1_col');
            $table->dropColumn('frm_index2_col');
            $table->dropColumn('frm_index3_col');
        });
    }
}
