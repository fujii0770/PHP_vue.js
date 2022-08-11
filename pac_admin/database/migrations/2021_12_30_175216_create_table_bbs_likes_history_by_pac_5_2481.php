<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableBbsLikesHistoryByPac52481 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('bbs_likes_history')) {
            Schema::create('bbs_likes_history', function (Blueprint $table) {
                $table->bigInteger('id', true)->comment('ID');
                $table->bigInteger('mst_user_id')->unsigned()->comment('ユーザーマスタID');
                $table->bigInteger('mst_company_id')->unsigned()->comment('会社マスタID');
                $table->bigInteger('bbs_id')->unsigned()->index('fk_bbs_id_of_bbs_idx')->comment('掲示板ID');
                $table->timestamp('created_at')->useCurrent()->comment('作成日');
            });
        }
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bbs_likes_history');
    }
}