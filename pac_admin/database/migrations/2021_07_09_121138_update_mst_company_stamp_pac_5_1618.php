<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;

class UpdateMstCompanyStampPac51618 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $stamps = DB::table('mst_company_stamp')->get();
        try{
            DB::beginTransaction();

            foreach ($stamps as $stamp){
                $image = Image::make($stamp->stamp_image);
                $image->encode('png');
                $imageBase64 = (string) $image->encode('data-url');
                $imageBase64 = explode(',', $imageBase64);

                DB::table('mst_company_stamp')->where('id', $stamp->id)
                    ->update([
                        'stamp_image' =>  $imageBase64[1]
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
