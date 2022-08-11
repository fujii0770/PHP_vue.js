<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstSanitizingLineByPac52912 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_sanitizing_line', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->string('sanitizing_line_name', 256)->comment('回線名');
            $table->integer('sanitize_request_limit')->default(0)->comment('1時間当たりの無害化要求ファイル数');
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
        Schema::dropIfExists('mst_sanitizing_line');
    }
}
