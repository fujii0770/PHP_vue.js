<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBizcardManagePac51889ManageVersionAndDisplay extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bizcard_manage', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mst_company_id');
            $table->unsignedBigInteger('version');
            $table->integer('display_type')->comment('会社:0, 部署:1, 個人:2, グループ:3');
            $table->string('display_target', 12288)->nullable();
            $table->integer('del_flg')->default(0)->comment('未削除:0, 削除済:1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bizcard_manage');
    }
}
