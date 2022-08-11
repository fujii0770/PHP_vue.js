<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateLongTermDocumentPac52262AddTimestampAutomaticDateData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function up()
    {

        $upd_circular_ids = [];
        for ($i = 0; $i < 12; $i++) {
            // 完了日時
            $finishedDate = Carbon::now()->addMonthsNoOverflow(-$i)->format('Ym');
            // 今月の場合
            if ($i === 0) {
                $finishedDate = '';
            }

            $is_time_stamp_circular_ids = DB::table('time_stamp_info as tsi')
                ->select('cd.circular_id')
                ->join("circular_document$finishedDate as cd" ,'cd.id','tsi.circular_document_id')
                ->join('long_term_document as ltd','ltd.circular_id','cd.circular_id')
                ->get()->values();
            $upd_circular_ids = array_merge($upd_circular_ids,data_get($is_time_stamp_circular_ids,'*.circular_id'));
        }

        $upd_ids_arrays = array_chunk(array_unique($upd_circular_ids),100);
        DB::beginTransaction();

        DB::table('long_term_document')->whereNotNull('add_timestamp_automatic_date')
            ->update([
                'add_timestamp_automatic_date' => DB::raw('completed_at')
            ]);

        try {
            foreach ($upd_ids_arrays as $upd_ids_array){
                DB::table('long_term_document')->whereIn('circular_id',$upd_ids_array)
                    ->update([
                        'add_timestamp_automatic_date' => DB::raw('completed_at')
                    ]);
            }
        }catch (Exception $e){
            DB::rollBack();
            throw $e;
        }
        DB::commit();

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
