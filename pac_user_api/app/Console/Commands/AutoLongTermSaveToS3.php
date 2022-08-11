<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUtils;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoLongTermSaveToS3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autoSave:circularCompleted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '完了一覧内の文書は自動保管';

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
        Log::channel('cron-daily')->debug("完了一覧内の文書は自動保管バッチ開始");
        try{
            ini_set('memory_limit','1024M');
            $autoSaveCompanyIds = DB::table('mst_company')
                ->where('auto_save',1)
                ->whereNotNull('auto_save_num')
                ->select('id','max_usable_capacity','auto_save_num','long_term_folder_flg','long_term_default_folder_id')
                ->get()
                ->keyBy('id');

            $finishedDates = [];
            for ($i = 0; $i < 12; $i++) {
                // 完了日時
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$i)->format('Ym');
                // 今月の場合
                if ($i === 0) {
                    $finishedDate = '';
                }
                foreach ($autoSaveCompanyIds as $companyId => $company){

                    // check max_usable_capacity
                    $storage_size = DB::table('long_term_document')
                        ->where('mst_company_id', $companyId)
                        ->select(DB::raw('sum(file_size) as storage_size'))
                        ->value('storage_size');
                    if($storage_size >= $company->max_usable_capacity * 1024 * 1024 * 1024){
                        Log::channel('cron-daily')->debug("company $companyId データ容量($company->max_usable_capacity GB)を超えています。");
                    }else{
                        //長期保存されているデータ
                        $saveCircularIds = DB::table('long_term_document')
                            ->where('mst_company_id',$companyId)
                            ->select('circular_id')
                            ->pluck('circular_id')
                            ->toArray();

                        $circularIds = DB::table("circular_user$finishedDate as U")
                            ->select('U.circular_id','C.completed_date')
                            ->join("circular$finishedDate as C",function ($query) use($finishedDate){
                                $query->on('C.id','U.circular_id');
                                if (!$finishedDate){
                                    $query->where(function ($query1){
                                        $query1->where('C.completed_copy_flg',CircularUtils::CIRCULAR_COMPLETED_COPY_FLG_FALSE);
                                        $query1->orWhere('C.completed_date','>=',Carbon::now()->startOfMonth())
                                            ->where('C.completed_date','<=',Carbon::now()->endOfMonth());
                                    });
                                }
                                $query->whereIn('C.circular_status',[CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS]);
                            })
                            ->where('U.edition_flg',config('app.edition_flg'))
                            ->where('U.env_flg', config('app.server_env'))
                            ->where('U.server_flg', config('app.server_flg'))
                            ->where('U.mst_company_id',$companyId)
                             ->orderBy('C.completed_date')
                            ->distinct()
                            ->pluck('U.circular_id')->toArray();
                        // 未保存のすべての回覧IDを取得します。
                        $circularIds = array_diff($circularIds,$saveCircularIds);
                        $circularIds=array_slice($circularIds,0,$company->auto_save_num);
                        $finishedDates[$i][$companyId] = ['circularIds' => $circularIds ,
                            'folder_id' => $company->long_term_folder_flg ? $company->long_term_default_folder_id : 0];//フォルダを選択

                        unset($circularIds);
                    }
                }
            }
            foreach ($finishedDates as $finishedDate => $companys){
                foreach ($companys as $companyId => $data){
                    foreach ($data['circularIds'] as $circularId) {
                        \Artisan::call("circular:storeS3",
                            ['circular_id' => $circularId, 'company_id' => $companyId, '--keyword' => '', 'finishedDate' => $finishedDate, '--keyword_flg' => 0, '--folder_id' => $data['folder_id']]);
                    }
                }
            }
        }catch (\Exception $e){
            Log::channel('cron-daily')->error('完了一覧内の文書は自動保管失敗');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
        }
        Log::channel('cron-daily')->debug("完了一覧内の文書は自動保管バッチ完了");

    }
}
