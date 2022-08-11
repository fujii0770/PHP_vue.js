<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstApplicationPac52376 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_application', function (Blueprint $table) {
            $table->bigInteger('id', true)->comment("Id");
            $table->integer("app_code", false, true)->comment('アプリケーションコード');
            $table->string('app_name', 50)->comment('アプリケーション名');
            $table->timestamp('created_at')->useCurrent()->comment('作成日');
            $table->timestamp('updated_at')->useCurrent()->comment('更新日');
        });
        DB::statement('ALTER TABLE mst_application CHANGE app_code app_code INT(3) UNSIGNED ZEROFILL NOT NULL COMMENT "アプリケーションコード"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_application');
    }
}
