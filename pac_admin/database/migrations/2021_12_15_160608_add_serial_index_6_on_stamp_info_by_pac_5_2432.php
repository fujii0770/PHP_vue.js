<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSerialIndex6OnStampInfoByPac52432 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('stamp_info')) {
            Schema::table('stamp_info', function (Blueprint $table) {
                if (Schema::hasColumn('stamp_info', 'serial')) {
                    $index = \Illuminate\Support\Facades\DB::select('SHOW INDEX FROM stamp_info WHERE Key_name=\'idx_stamp_info_on_serial_of_left_6\'');
                    if (count($index) === 0) {
                        \Illuminate\Support\Facades\DB::statement('ALTER TABLE stamp_info ADD INDEX idx_stamp_info_on_serial_of_left_6(serial(6))');
                    };
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
        if (Schema::hasTable('stamp_info')) {
            Schema::table('stamp_info', function (Blueprint $table) {
                if (Schema::hasColumn('stamp_info', 'serial')) {
                    $index = \Illuminate\Support\Facades\DB::select('SHOW INDEX FROM stamp_info WHERE Key_name=\'idx_stamp_info_on_serial_of_left_6\'');
                    if (count($index) > 0) {
                        $table->dropIndex('idx_stamp_info_on_serial_of_left_6');
                    }
                }
            });
        }
    }
}
