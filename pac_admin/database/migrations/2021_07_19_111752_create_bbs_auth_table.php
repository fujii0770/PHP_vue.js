<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBbsAuthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bbs_auth', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()
                ->comment('掲示板権限ID');
            $table->integer('auth_code')->unsigned()
                ->comment('権限コード');
            $table->string('auth_content', 45)
                ->comment('権限内容');
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->timestamp('updated_at')
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日');
        });
        DB::statement("alter table bbs_auth comment '掲示板の権限';");
        DB::statement("alter table bbs_auth change auth_code auth_code INT(3) UNSIGNED ZEROFILL NOT NULL comment '権限コード'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bbs_auth');
    }
}
