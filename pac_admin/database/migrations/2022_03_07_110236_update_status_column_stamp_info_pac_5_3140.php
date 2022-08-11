<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateStatusColumnStampInfoPac53140 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('stamp_info')
            ->where('size','>',0)
            ->update([
                'status' => \App\Http\Utils\AppUtils::STAMP_COLLECTED
            ]);

        DB::table('assign_stamp_info')
            ->update([
                'status' => \App\Http\Utils\AppUtils::STAMP_COLLECTED
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
