<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCircularUserPac5582Text extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular_user', function (Blueprint $table) {
            $table->text('text')->after('title')->nullable()->comment('メッセージ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('circular_user', function (Blueprint $table) {
            $table->dropColumn('text');
        });
    }
}
