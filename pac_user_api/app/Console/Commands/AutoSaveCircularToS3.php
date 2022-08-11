<?php

namespace App\Console\Commands;

use App\Http\Utils\CircularUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Carbon;

class AutoSaveCircularToS3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'save:circularCompleted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        Log::channel('cron-daily')->debug('Run to AutoSaveCircularToS3');

        try{
            $finishedDates = [];
            for ($i = 0; $i < 12; $i++) {
                // 完了日時
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$i)->format('Ym');;
                // 今月の場合
                if ($i === 0) {
                    $finishedDate = '';
                }
                $circularIds = DB::table("circular_document$finishedDate as D")
                    ->join("circular$finishedDate as CI", 'D.circular_id', 'CI.id')
                    ->join('mst_long_term_save as C', 'D.create_company_id', 'C.mst_company_id')
                    ->where('D.origin_env_flg', config('app.server_env'))
                    ->where('D.origin_edition_flg', config('app.edition_flg'))
                    ->where('D.origin_server_flg', config('app.server_flg'))
                    ->whereRaw('DATE_SUB(now(),INTERVAL C.auto_save_days DAY ) > CI.completed_date')
                    ->pluck('D.circular_id')
                    ->toArray();
                // データがない
                if (count($circularIds) == 0){
                    continue;
                }
                $finishedDates[$finishedDate] = $circularIds;
            }

            // 全て回覧処理
            foreach ($finishedDates as $finishedDate => $circularIds) {
                if (count($circularIds)){
                    // get all company from circular
//                    $circularUsers = DB::table("circular_user$finishedDate as C")
//                        ->join("mst_company", "C.mst_company_id","=","mst_company.id")
//                        ->join('mst_long_term_save', 'mst_company.id', 'mst_long_term_save.mst_company_id')
//                        ->select(["mst_company.id as mst_company_id", "C.circular_id", DB::raw("CONCAT_WS('-', mst_company.id, C.circular_id) as circular_company")])
//                        ->whereIn("circular_id", $circularIds)
//                        ->where('env_flg', config('app.server_env'))
//                        ->where('edition_flg', config('app.edition_flg'))
//                        ->where('server_flg', config('app.server_flg'))
//                        ->where('long_term_storage_flg', \App\Http\Utils\AppUtils::STATE_VALID)
//                        ->where('mst_long_term_save.auto_save', \App\Http\Utils\AppUtils::STATE_VALID)
//                        ->distinct()
//                        ->get()->keyBy('circular_company')
//                        ->toArray();

                    $circularIdsArr = array_chunk($circularIds,30);
                    $circularUsers = [];
                    foreach ($circularIdsArr as $circularIdPer){
                        $circularUserPer = DB::table("circular_user$finishedDate as C")
                            ->join("mst_company", "C.mst_company_id","=","mst_company.id")
                            ->join('mst_long_term_save', 'mst_company.id', 'mst_long_term_save.mst_company_id')
                            ->select(["mst_company.id as mst_company_id", "C.circular_id", DB::raw("CONCAT_WS('-', mst_company.id, C.circular_id) as circular_company")])
                            ->whereIn("circular_id", $circularIdPer)
                            ->where('env_flg', config('app.server_env'))
                            ->where('edition_flg', config('app.edition_flg'))
                            ->where('server_flg', config('app.server_flg'))
                            ->where('long_term_storage_flg', \App\Http\Utils\AppUtils::STATE_VALID)
                            ->where('mst_long_term_save.auto_save', \App\Http\Utils\AppUtils::STATE_VALID)
                            ->distinct()
                            ->get()->keyBy('circular_company')
                            ->toArray();

                        $circularUsers = array_merge($circularUsers,$circularUserPer);
                    }

                    $companyIds = [];
                    foreach ($circularUsers as $circularUser){
                        if(in_array($circularUser->mst_company_id, $companyIds)){
                            continue;
                        }
                        $companyIds[] = $circularUser->mst_company_id;
                    }
                    if (count($companyIds)){
//                        $storageSizes = DB::table('long_term_document')->rightJoin("mst_company", "long_term_document.mst_company_id","=","mst_company.id")
//                            ->whereIn('long_term_document.mst_company_id', $companyIds)
//                            ->select(['long_term_document.mst_company_id', DB::raw('sum(long_term_document.file_size) as storage_size'), 'mst_company.max_usable_capacity'])
//                            ->groupBy('long_term_document.mst_company_id', 'mst_company.max_usable_capacity')
//                            ->get()->keyBy('mst_company_id');

                        $companyIdsArr = array_chunk($companyIds,30);
                        $storageSizes = [];
                        foreach ($companyIdsArr as $i => $companyIdsPer){
                            $storageSizePer = DB::table('long_term_document')->rightJoin("mst_company", "long_term_document.mst_company_id","=","mst_company.id")
                                ->whereIn('long_term_document.mst_company_id', $companyIdsPer)
                                ->select(['long_term_document.mst_company_id', DB::raw('sum(long_term_document.file_size) as storage_size'), 'mst_company.max_usable_capacity'])
                                ->groupBy('long_term_document.mst_company_id', 'mst_company.max_usable_capacity')
                                ->get()->keyBy('mst_company_id');
                            if($i == 0){
                                $storageSizes = $storageSizePer;
                            }else{
                                $storageSizes = $storageSizes->merge($storageSizePer);
                            }
                        }

                    }else{
                        $storageSizes = [];
                    }

//                    $longTermDocuments = DB::table('long_term_document')
//                        ->select(['circular_id', DB::raw("CONCAT_WS('-', mst_company_id, circular_id) as circular_company")])
//                        ->whereIn(DB::raw("CONCAT_WS('-', mst_company_id, circular_id)"), array_keys($circularUsers))
//                        ->get()->keyBy('circular_company')
//                        ->toArray();

                    $circularUserKeyArr = array_chunk(array_keys($circularUsers),30);
                    $longTermDocuments = [];
                    foreach ($circularUserKeyArr as $circularUserKeyPer){
                        $longTermDocumentPer = DB::table('long_term_document')
                            ->select(['circular_id', DB::raw("CONCAT_WS('-', mst_company_id, circular_id) as circular_company")])
                            ->whereIn(DB::raw("CONCAT_WS('-', mst_company_id, circular_id)"), $circularUserKeyPer)
                            ->get()->keyBy('circular_company')
                            ->toArray();
                        $longTermDocuments = array_merge($longTermDocuments,$longTermDocumentPer);
                    }

                    $circularUsers = array_diff_key($circularUsers, $longTermDocuments);

                    $finishedDateKey = '';
                    foreach ($circularUsers as $circularUser){
                        if ($storageSizes && $storageSizes->has($circularUser->mst_company_id)){
                            $storageSize = $storageSizes->get($circularUser->mst_company_id);
                            $storage_size = $storageSize->storage_size;
                            if($storage_size >= ($storageSize->max_usable_capacity)*1024*1024*1024){
                                Log::channel('cron-daily')->warning("Cannot store document for company $circularUser->mst_company_id by exceed max_usable_capacity");
                                continue;
                            }
                        }
                        for ($i = 0; $i < 12; $i++) {
                            if ($finishedDate === (Carbon::now()->addMonthsNoOverflow(-$i)->format('Ym'))) {
                                $finishedDateKey = $i;
                                break;
                            }
                        }
                        \Artisan::call("circular:storeS3", ['circular_id' => $circularUser->circular_id, 'company_id' => $circularUser->mst_company_id, '--keyword' => '', 'finishedDate' => $finishedDateKey, '--keyword_flg' => 0]);
                        $returnMsg =  str_replace(array("\r", "\n"), '', \Artisan::output());
                    }
                }
            }
        }catch(\Exception $e){
            DB::rollBack();
            Log::channel('cron-daily')->error('Run to AutoSaveCircularToS3 failed');
            Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }

        Log::channel('cron-daily')->debug('Run to AutoSaveCircularToS3 finished');
    }
}
