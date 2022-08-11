<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBbsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bbs', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()
                ->comment('掲示板ID');
            $table->bigInteger('bbs_category_id')->unsigned()
                ->index('fk_bbs_category_id_of_bbs_idx')
                ->comment('カテゴリID');
            $table->bigInteger('mst_user_id')->unsigned()
                ->index('fk_mst_user_id_of_bbs_idx')
                ->comment('作成ユーザID');
            $table->string('title', 45)
                ->comment('タイトル');
            $table->string('s3path', 100)
                ->nullable()
                ->comment('S3ファイルパス');
            $table->datetime('start_date')
                ->useCurrent()
                ->comment('掲載開始日');
            $table->datetime('end_date')
                ->nullable()
                ->comment('掲載終了日');
            $table->integer('total_file_size')->unsigned()
                ->default(0)
                ->comment('合計ファイルサイズ(S3)');
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->timestamp('updated_at')
                ->default(DB::raw('null on update CURRENT_TIMESTAMP'))
                ->nullable()
                ->comment('更新日');
        });
        DB::statement("alter table bbs comment '掲示板';");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bbs');
    }
}
