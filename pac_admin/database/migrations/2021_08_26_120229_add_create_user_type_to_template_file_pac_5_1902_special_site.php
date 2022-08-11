<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreateUserTypeToTemplateFilePac51902SpecialSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('template_file', function (Blueprint $table) {
            if (!Schema::hasColumn("template_file", "create_user_type")) {
                $table->integer("create_user_type")->nullable()->comment("利用者：1、管理者：2");
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
        Schema::table('template_file', function (Blueprint $table) {
            $table->dropColumn("create_user_type");
        });
    }
}
