`<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstDepartmentPac52446TreeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_department', function (Blueprint $table) {
            $table->string('tree',512)->default('')->comment('トップ階層から、自分まで、「,」で分割、最後に「,」を付く');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_department', function (Blueprint $table) {
            $table->dropColumn('tree');
        });
    }
}
