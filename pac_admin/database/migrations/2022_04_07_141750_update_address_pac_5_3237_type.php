<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateAddressPac53237Type extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $bbs_address_all = DB::table('address')
            ->where('type',2)
            ->select('mst_user_id','id','email')
            ->get();
        DB::beginTransaction();
        try {
            foreach ($bbs_address_all as $bbs_address) {
                $address = DB::table('address')
                    ->where('type',0)
                    ->where('mst_user_id',$bbs_address->mst_user_id)
                    ->where('email',$bbs_address->email)
                    ->first();
                if($address){
                    DB::table('address')
                        ->where('type',2)
                        ->where('mst_user_id',$bbs_address->mst_user_id)
                        ->where('email',$bbs_address->email)
                        ->delete();
                }else{
                    DB::table('address')
                        ->where('type',2)
                        ->where('mst_user_id',$bbs_address->mst_user_id)
                        ->where('email',$bbs_address->email)
                        ->update([
                            'type' => 0
                        ]);
                }
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getTraceAsString());
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
