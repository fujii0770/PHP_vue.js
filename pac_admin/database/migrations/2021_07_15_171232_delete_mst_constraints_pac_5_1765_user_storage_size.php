<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteMstConstraintsPac51765UserStorageSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // カラム削除
        Schema::table('mst_constraints', function (Blueprint $table) {
            $table->dropColumn('user_storage_size');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // カラム削除回復
        Schema::table('mst_constraints', function (Blueprint $table) {
              $table->bigInteger('user_storage_size')->comment('Byte単位にて格納');

        });
    }
}
