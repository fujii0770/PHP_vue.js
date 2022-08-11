<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTemplateFileAddColumnPac52508 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('template_file', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('template_file','template_route_id')) {
                $table->bigInteger('template_route_id')->unsigned()->nullable()->comment('承認ルートID');
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
        //
        Schema::table('template_file', function (Blueprint $table) {
            //
            if (Schema::hasColumn('template_file','template_route_id')) {
                $table->dropColumn('template_route_id');
            }
        });
    }
}
