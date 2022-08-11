<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UpdateCircular202102Pac52638CopyFinalUpdatedDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $env_flg = config('app.pac_app_env');
        $server_flg = config('app.pac_contract_server');

        if (($env_flg == 0 && ($server_flg == 1 || $server_flg == 2)) || ($env_flg == 1 && $server_flg == 0)){
            try {
                DB::beginTransaction();

                DB::table('circular')
                    ->select('id','final_updated_date')
                    ->whereRaw("DATE_FORMAT( completed_date, '%Y%m' ) = 202102")
                    ->chunk(50,function ($circulars){
                        foreach ($circulars as $circular){
                            DB::table('circular202102')->where('id',$circular->id)->update(['final_updated_date'=>$circular->final_updated_date]);
                        }
                    });

                DB::commit();
            }catch (Exception $ex){
                DB::rollBack();
                Log::error($ex->getMessage() . $ex->getTraceAsString());
            }
        }
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
