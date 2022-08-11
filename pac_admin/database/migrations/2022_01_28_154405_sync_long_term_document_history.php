<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class SyncLongTermDocumentHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            ini_set('memory_limit','2048M');
            $longTermCircular= DB::table('long_term_circular')->pluck('id')->toArray();
            DB::table('long_term_document')->select('circular_id','id')
                ->selectRaw("DATE_FORMAT( completed_at, '%Y%m')  as completed_at")
                ->orderBy('id')
                ->chunkById(200,function ($items) use ($longTermCircular){
                    foreach ($items as $longTerm) {
                        $finishedDate=($longTerm->completed_at==Carbon::now()->format('Ym'))?'':$longTerm->completed_at;
                        if($finishedDate!=''){
                            if ($this->dateToTime($finishedDate)<=$this->dateToTime("202007")){
                                continue;
                            }
                        }
                        if(empty($longTermCircular) || !in_array($longTerm->circular_id,$longTermCircular)){
                            Artisan::call('copy:doCircularToLongTerm', [
                                'circular_id' => $longTerm->circular_id,'finishedDate'=>$finishedDate,'id'=>$longTerm->id
                            ]);
                        }
                    }

                });
        }catch (Exception $e){
            Log::error('err',[
                'track'=>$e->getTraceAsString(),
                'sql'=>$e->getPrevious()
            ]);
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
    private function dateToTime($d)
    {
        $year=((int)substr($d,0,4));
        $month=((int)substr($d,4,2));
        return mktime(1,0,0,$month,1,$year);
    }
}
