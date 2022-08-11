<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBizcardPac51889BizcardIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bizcard', function (Blueprint $table) {
            if (!Schema::hasColumn("bizcard", "bizcard_id")) {
                $table->unsignedBigInteger('bizcard_id')->after('id');
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
        Schema::table('bizcard', function (Blueprint $table) {
            $table->dropColumn("bizcard_id");
        });
    }
}
