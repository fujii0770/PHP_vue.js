<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBbsCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bbs_category', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()
                ->comment('カテゴリID');
            $table->string('name', 45)
                ->comment('カテゴリ名');
            $table->text('memo')
                ->nullable()
                ->comment('メモ');
            $table->bigInteger('mst_user_id')->unsigned()
                ->index('fk_mst_user_id_of_bbs_category')
                ->comment('作成ユーザID');
            $table->bigInteger('bbs_auth_id')->unsigned()
                ->index('fk_bbs_auth_id_of_bbs_category')
                ->comment('掲示板権限ID');
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->timestamp('updated_at')
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日');
        });
        DB::statement("alter table bbs_category comment '掲示板カテゴリ';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bbs_category');
    }
}
