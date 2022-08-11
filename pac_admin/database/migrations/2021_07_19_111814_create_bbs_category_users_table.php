<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBbsCategoryUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bbs_category_users', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()
                ->comment('カテゴリユーザID');
            $table->bigInteger('bbs_category_id')->unsigned()
                ->index('fk_bbs_category_id_of_bbs_category_users_idx')
                ->comment('カテゴリID');
            $table->bigInteger('mst_user_id')->unsigned()
                ->index('fk_user_id_of_bbs_category_users_idx')
                ->comment('権限ユーザID');
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->timestamp('updated_at')
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日');
        });
        DB::statement("alter table bbs_category_users comment '掲示板カテゴリに属するユーザ';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bbs_category_users');
    }
}
