<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCloumnFileCountToBbsPac51807 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bbs', function (Blueprint $table) {
             if (!Schema::hasColumn('bbs','file_count')){
                 $table->integer('file_count')->default(0);
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
        Schema::table('bbs_pac_5_1807', function (Blueprint $table) {
            $table->dropColumn('file_count');
        });
    }
}
