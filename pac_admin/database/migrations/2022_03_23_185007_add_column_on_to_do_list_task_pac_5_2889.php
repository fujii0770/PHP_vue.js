<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use  \Illuminate\Support\Facades\DB;

class AddColumnOnToDoListTaskPac52889 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('to_do_task', function (Blueprint $table) {
            if (!Schema::hasColumn('to_do_task','renotify_flg')){
                $table->integer('renotify_flg')->default(0)->comment('当日Flgに通知');
            }
        });
        Schema::table('to_do_circular_task', function (Blueprint $table) {
            if (!Schema::hasColumn('to_do_circular_task','renotify_flg')){
                $table->integer('renotify_flg')->default(0)->comment('当日Flgに通知');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('to_do_task',function (Blueprint $table){
            if (!Schema::hasColumn('to_do_task','renotify_flg')) {
                $table->dropColumn('renotify_flg');
            }
        });
        Schema::table('to_do_circular_task',function (Blueprint $table){
            if (!Schema::hasColumn('to_do_circular_task','renotify_flg')) {
                $table->dropColumn('renotify_flg');
            }
        });
    }
}
