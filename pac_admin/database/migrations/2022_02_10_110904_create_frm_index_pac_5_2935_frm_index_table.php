<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrmIndexPac52935FrmIndexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frm_index', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->bigInteger('mst_company_id')->unsigned()->comment('会社ID');
            $table->string('index_name', 256)->comment('帳票項目名');
            $table->integer('data_type')->comment('データ型|0:数字|1:文字|2:日付');
            $table->integer('frm_index_number')->comment('追加検索項目NO|1:frm_index1_col|2:frm_index2_col|3:frm_index3_col');
            $table->timestamp('create_at')->useCurrent();
            $table->string('create_user', 128);
            $table->timestamp('update_at')->useCurrent()->nullable();
            $table->string('update_user', 128)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('frm_index');
    }
}
