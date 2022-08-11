<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class MoveSkipDataToLongTermByPac53171 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $arrLtcuID = DB::table("long_term_circular_user as ltcu")->join("circular_user as cu",'ltcu.id','=','cu.id')
            ->select("ltcu.id")
            ->where("cu.is_skip",1)
            ->get()->pluck("id");
        if(!empty($arrLtcuID)){
            DB::table("long_term_circular_user")->whereIn("id",$arrLtcuID)->update(['is_skip'=>1]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
