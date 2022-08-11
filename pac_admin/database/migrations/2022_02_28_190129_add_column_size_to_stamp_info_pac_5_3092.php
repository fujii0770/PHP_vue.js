<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnSizeToStampInfoPac53092 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stamp_info', function (Blueprint $table) {
            if (!Schema::hasColumn('stamp_info','size')){
                $table->integer('size')->default(0)->comment('サイズ');
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
        Schema::table('stamp_info', function (Blueprint $table) {
           $table->dropColumn('size');
        });
    }
}
