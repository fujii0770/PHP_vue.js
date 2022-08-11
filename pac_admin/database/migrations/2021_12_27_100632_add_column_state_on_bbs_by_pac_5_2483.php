<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnStateOnBbsByPac52483 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('bbs')) {
            Schema::table('bbs', function (Blueprint $table) {
                 if (!Schema::hasColumn('bbs','state')){
                     $table->integer('state')->default(1)->comment('有効:1|下書:0');
                 }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('bbs')) {
            Schema::table('bbs', function (Blueprint $table) {
                if (Schema::hasColumn('bbs','state')) {
                    $table->dropColumn('state');
                }
            });
        }
    }
}
