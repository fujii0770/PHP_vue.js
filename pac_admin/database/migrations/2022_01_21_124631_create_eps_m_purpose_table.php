<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEpsMPurposeTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eps_m_purpose', function (Blueprint $table) {
            $table->bigInteger('mst_company_id', false)->unsigned()->comment('会社ID');
            $table->string('purpose_name', 20)->comment('目的名');
            $table->string('describe', 200)->nullable()->comment('説明');
            $table->string('remarks', 2000)->nullable()->comment('備考');
            $table->integer('display_order')->default(0)->comment('並び順');
            $table->datetime('create_at')->useCurrent()->comment('作成日時');
            $table->string('create_user', 128)->comment('作成者');
            $table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable()->comment('更新日時');
            $table->string('update_user', 128)->nullable()->comment('更新者');
            $table->bigInteger('version')->default(0);
            $table->primary(['mst_company_id','purpose_name']);
        });
        DB::statement("alter table eps_m_purpose comment '経費精算_目的マスタ';");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('eps_m_purpose');
    }

}
