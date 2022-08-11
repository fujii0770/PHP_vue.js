<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMstUserInfoFieldPageDisplayFirstDefaultValuePac52119 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_user_info', function (Blueprint $table) {
            if (Schema::hasColumn("mst_user_info","page_display_first")){
                $table->string('page_display_first')->default('ポータル')->change();
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
        Schema::table('mst_user_info', function (Blueprint $table) {
            if (Schema::hasColumn("mst_user_info","page_display_first")){
                $table->string('page_display_first')->default('ホーム')->change();
            }
        });
    }
}
