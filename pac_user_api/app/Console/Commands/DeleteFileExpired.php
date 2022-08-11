<?php

namespace App\Console\Commands;

use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\CircularUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Carbon;

class DeleteFileExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:fileExpired';

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
        Log::channel('cron-daily')->debug('Run to DeleteFileExpired');

        try{
            $circularIds = DB::table('circular_document as D')
                ->join('circular as CI', 'D.circular_id', 'CI.id')
                ->join('mst_constraints as C', 'D.create_company_id', 'C.mst_company_id')
                ->where('D.origin_env_flg', config('app.server_env'))
                ->where('D.origin_edition_flg', config('app.edition_flg'))
                ->where('D.origin_server_flg', config('app.server_flg'))
                ->whereRaw('DATE_SUB(now(),INTERVAL C.max_keep_days DAY ) > D.create_at')
                ->whereIn('CI.circular_status', [CircularUtils::CIRCULATING_STATUS,
                                                CircularUtils::SEND_BACK_STATUS])
                ->pluck('D.circular_id');
            if (count($circularIds)){
                $toRemoveCircularIds = [];
                foreach ($circularIds->chunk(100) as $chunkCircularIds){
                    // get all company from circular
                    $circularUsers = DB::table("circular_user")->join("mst_company", "circular_user.mst_company_id","=","mst_company.id")
                        ->select(["mst_company.id as mst_company_id", "circular_user.circular_id", DB::raw("CONCAT_WS('-', mst_company.id, circular_user.circular_id) as circular_company")])
                        ->whereIn("circular_id", $chunkCircularIds)
                        ->where('env_flg', config('app.server_env'))
                        ->where('edition_flg', config('app.edition_flg'))
                        ->where('server_flg', config('app.server_flg'))
                        ->distinct()
                        ->get()->keyBy('circular_company')
                        ->toArray();

                    foreach ($circularUsers as $circularUser){
                        $toRemoveCircularIds[] = $circularUser->circular_id;
                    }
                }

                if (count($toRemoveCircularIds)){
                    DB::beginTransaction();
                    foreach (collect($toRemoveCircularIds)->chunk(100)->toArray() as $chunkToRemoveCircularId){
                        //PAC_5-1398 回覧中のすべての添付ファイルを削除します。
                        CircularAttachmentUtils::deleteAbsoluteAttachments($chunkToRemoveCircularId);

                        $circularDocumentIds = DB::table('circular_document')
                            ->whereIn('circular_id', $chunkToRemoveCircularId)
                            ->pluck('id');

                        foreach ($circularDocumentIds->chunk(100) as $chunkCircularDocumentIds){

                            DB::table('document_data')
                                ->whereIn('circular_document_id', $chunkCircularDocumentIds)
                                ->delete();

                            DB::table('text_info')
                                ->whereIn('circular_document_id', $chunkCircularDocumentIds)
                                ->delete();

                            DB::table('document_comment_info')
                                ->whereIn('circular_document_id', $chunkCircularDocumentIds)
                                ->delete();

                            //PAC_5-3140 容量集計の性能アップ対応
//                            DB::table('stamp_info')
//                                ->whereIn('circular_document_id', $chunkCircularDocumentIds)
//                                ->delete();

                            DB::table('circular_document')
                                ->whereIn('id', $chunkCircularDocumentIds)
                                ->delete();
                        }

                        DB::table('circular_user')
                            ->whereIn('circular_id', $chunkToRemoveCircularId)
                            ->delete();

                        DB::table('viewing_user')
                            ->whereIn('circular_id', $chunkToRemoveCircularId)
                            ->delete();

                        DB::table('guest_user')
                            ->whereIn('circular_id', $chunkToRemoveCircularId)
                            ->delete();

                        DB::table('circular_operation_history')
                            ->whereIn('circular_id', $chunkToRemoveCircularId)
                            ->delete();

                        DB::table('assign_stamp_info')
                            ->whereIn('circular_id', $chunkToRemoveCircularId)
                            ->delete();

                        DB::table('circular')
                            ->whereIn('id', $chunkToRemoveCircularId)
                            ->delete();
                    }
                    DB::commit();
                }
            }
        }catch(\Exception $e){
            DB::rollBack();
            Log::channel('cron-daily')->error('Run to DeleteFileExpired failed');
            Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }

        Log::channel('cron-daily')->debug('Run to DeleteFileExpired finished');
    }
}
