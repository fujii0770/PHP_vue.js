<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;


class TemplateSizeVerification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SizeVerification:Template';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'release template size verification';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try{
            Log::channel('cron-daily')->debug("Run to template size verrification start");
            $root_path = config('app.s3_storage_root_folder');

            //templateファイルのサイズを取得 → template_fileテーブルに保存
            $template_file_id_array = DB::table('template_file')
                                        ->where('file_size_flg',0)
                                        ->select('id')
                                        ->get();
            $template_file_id_array = json_decode(json_encode($template_file_id_array),true);

            $company_ids = DB::table('mst_company')
                            ->select('id')
                            ->where('template_flg',1)
                            ->get();
            $company_ids = json_decode(json_encode($company_ids),true);

            foreach($template_file_id_array as $id){
                $size = 0;
                $location = DB::table('template_file')
                            ->where('id', $id)
                            ->select('location')
                            ->first();
                    $location = json_decode(json_encode($location),true);

                if(strpos($location['location'],$root_path) !== false){
                    $s3path = strstr($location['location'] , $root_path , false);
                    $size = Storage::disk('s3')->size($s3path);

                    DB::table('template_file')
                        ->where('id',$id )
                        ->update([
                            'file_size' => $size,
                            'file_size_flg' => 1,
                        ]);
                }
            }

            //各企業ごとのtemplateファイルの合計*1.2を集計 → usage_situationテーブルに保存
            foreach($company_ids as $company_id){
                $template_file_size_sum = 0;
                $template_sum_size = 0;
                $template_file_size_sum = DB::table('template_file')
                                            ->where('mst_company_id',$company_id)
                                            ->sum('file_size');

                $template_sum_size = intval($template_file_size_sum) * 1.2;

                DB::table('usage_situation')
                    ->where('mst_company_id',$company_id )
                    ->update([
                        'template_company_sum_size' => $template_sum_size,
                ]);
            }
            
        } catch (\Exception $e) {
            Log::channel('cron-daily')->debug('Run to template size verrification  failed');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }
    }
}