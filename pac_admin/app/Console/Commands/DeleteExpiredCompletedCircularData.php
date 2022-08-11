<?php

namespace App\Console\Commands;

use App\Http\Utils\CircularUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteExpiredCompletedCircularData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:expiredCompletedCircular';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'コピーが完了した文書レコード削除バッチ';

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
        try {
            Log::channel('cron-daily')->debug("コピーが完了した文書レコード削除バッチ実行開始");

            //2か月前のすべてのデータ
            $circular_count = DB::table('circular')
                ->where('completed_copy_flg',CircularUtils::CIRCULAR_COMPLETED_COPY_FLG_TRUE)
                ->where('completed_date','<', Carbon::now()->subMonthsNoOverflow(2))
                ->count();

            $limit = env('DETECT_LIMIT_COUNT',1000);

            for ($i=0;$i<ceil($circular_count/$limit);$i++){

                if (Carbon::now() < Carbon::createFromTime(5,30)){
                    DB::beginTransaction();
                    $circular_query = DB::table('circular')
                        ->select('id')
                        ->where('completed_copy_flg',CircularUtils::CIRCULAR_COMPLETED_COPY_FLG_TRUE)
                        ->where('completed_date','<', Carbon::now()->subMonthsNoOverflow(2))
                        ->orderBy('completed_date','asc')
                        ->limit($limit);

                    $user_query = DB::table('circular_user')
                        ->whereExists(function ($query) use ($circular_query){
                            $query->select('id')
                                ->fromSub($circular_query,'sub')
                                ->whereRaw('sub.id=circular_user.circular_id');
                        });

                    $document_query = DB::table('circular_document')
                        ->select('id')
                        ->whereExists(function ($query) use ($circular_query){
                            $query->select('id')
                                ->fromSub($circular_query,'sub')
                                ->whereRaw('sub.id=circular_document.circular_id');
                        });

                    $document_data_query = DB::table('document_data')
                        ->whereExists(function ($query) use ($document_query){
                            $query->select('id')
                                ->fromSub($document_query,'doc')
                                ->whereRaw('doc.id=document_data.circular_document_id');
                        });

                    //完了回覧テーブル削除
                    DB::statement('SET FOREIGN_KEY_CHECKS = 0');
                    $circular_user_ids = $user_query->get()->pluck('id');
                    DB::table('circular_user')->whereIn('id',$circular_user_ids)->delete();
                    unset($circular_user_ids);
                    if ($i==0) Log::channel('cron-daily')->debug("circular_user 削除終了");

                    $circular_document_ids = $document_query->get()->pluck('id');
                    DB::table('circular_document')->whereIn('id',$circular_document_ids)->delete();
                    unset($circular_document_ids);
                    if ($i==0) Log::channel('cron-daily')->debug("document_data 削除終了");

                    $circular_document_data_ids = $document_data_query->get()->pluck('id');
                    DB::table('document_data')->whereIn('id',$circular_document_data_ids)->delete();
                    unset($circular_document_data_ids);
                    if ($i==0) Log::channel('cron-daily')->debug("circular_document 削除終了");

                    $circular_ids = $circular_query->get()->pluck('id');
                    DB::table('circular')->whereIn('id',$circular_ids)->delete();
                    unset($circular_ids);
                    if ($i==0) Log::channel('cron-daily')->debug("circular 削除終了");

                    DB::statement('SET FOREIGN_KEY_CHECKS = 1');
                    DB::commit();
                }
            }
            Log::channel('cron-daily')->debug("コピーが完了した文書レコード削除バッチ実行終了");
        }catch (\Exception $e){
            DB::rollBack();
            Log::channel('cron-daily')->error('コピーが完了した文書レコード削除バッチ実行エラーが発生');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }
    }
}
