<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UpdateSpecialSiteReceiveSendAvailableStatePac52494Data extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

       $companies = DB::table('mst_company as ms')
           ->select('ms.id')
           ->leftJoin('special_site_receive_send_available_state as ssrs', 'ssrs.company_id','ms.id')
           ->where('ssrs.company_id',null)
           ->get();

       Log::debug((array)$companies);

       DB::beginTransaction();
        try {
            foreach ($companies as $company){
                DB::table('special_site_receive_send_available_state')->insert([
                    'company_id' => $company->id,
                    'is_special_site_receive_available' => 0,
                    'is_special_site_send_available' => 0,
                    'created_at' => Carbon::now(),
                    'create_user' => "Shachihata"
                ]);
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
