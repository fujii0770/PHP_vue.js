<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnGroupIdOnToDoListTableByPac53240 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('to_do_list', function (Blueprint $table) {
            $table->bigInteger('group_id')->unsigned()->default(0)->comment('グループID')->after('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('to_do_list', function (Blueprint $table) {
            $table->dropColumn('group_id');
        });
    }
}
