<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstUserInfoPac51888LastTextFont extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_user_info', function (Blueprint $table) {
            //
            $table->string('last_text_font', 10)->nullable()->comment('テキストフォント');
            $table->string('last_text_size', 10)->nullable()->comment('テキストフォントサイズ');
            $table->string('last_text_color', 10)->nullable()->comment('テキストフォント色');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_user_info', function (Blueprint $table) {
            //
            $table->dropColumn(['last_text_font', 'last_text_size', 'last_text_color']);
        });
    }
}
