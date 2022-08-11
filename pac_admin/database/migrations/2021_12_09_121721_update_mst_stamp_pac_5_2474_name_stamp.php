<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMstStampPac52474NameStamp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mst_stamp')
            ->where('stamp_name', 'like','（土）%')
            ->where('stamp_division', 0)
            ->where('width', 17780)
            ->where('height', 17780)
            ->where('date_x', 82)
            ->where('date_y', 158)
            ->where('date_width', 248)
            ->where('date_height', 100)
            ->update([
            'width' => 13500,
            'height' => 13500,
            'date_x' => 0,
            'date_y' => 0,
            'date_width' => 0,
            'date_height' => 0,
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
