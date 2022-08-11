<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMstUserInfoPac51558PostalCodeLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_user_info', function (Blueprint $table) {
            //
            //$table->string('postal_code', 10)->change();

            DB::statement("ALTER TABLE `mst_user_info`
            CHANGE COLUMN `postal_code` `postal_code` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `fax_number`");
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
            //
        });
    }
}
