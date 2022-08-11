<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateMstStampPac52303Data extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mst_stamp')
            ->where('stamp_division',0)
            ->where('font',0)
            ->where('date_x','!=',0)
            ->where('stamp_name','not like','%（土）%')
            ->update(['stamp_division' => 1]);
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
