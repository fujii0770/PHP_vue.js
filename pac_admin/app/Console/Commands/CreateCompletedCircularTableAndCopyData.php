<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class CreateCompletedCircularTableAndCopyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'circular:createTableAndCopyData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '完了回覧分割テーブルの作成';

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
        Log::channel('cron-daily')->debug("完了回覧分割テーブルの作成バッチ開始");
        DB::beginTransaction();
        try {
            $month = date('Ym');
            for ($i = 0; $i < 12; $i++) {
//                $month = date('Ym', strtotime("$month -$i month"));
                $month = Carbon::now()->addMonthsNoOverflow(-$i)->format('Ym');
                // テーブル作成
                DB::statement("CREATE TABLE IF NOT EXISTS circular$month LIKE circular");
                DB::statement("CREATE TABLE IF NOT EXISTS circular_user$month LIKE circular_user");
                DB::statement("CREATE TABLE IF NOT EXISTS circular_document$month LIKE circular_document");
                DB::statement("CREATE TABLE IF NOT EXISTS document_data$month LIKE document_data");

                // 自増削除
                DB::statement("ALTER TABLE circular$month CHANGE id id BIGINT(0) UNSIGNED NOT NULL;");
                DB::statement("ALTER TABLE circular_user$month CHANGE id id BIGINT(0) UNSIGNED NOT NULL;");
                DB::statement("ALTER TABLE circular_document$month CHANGE id id BIGINT(0) UNSIGNED NOT NULL;");
                DB::statement("ALTER TABLE document_data$month CHANGE id id BIGINT(0) UNSIGNED NOT NULL;");

                // 完了回覧コピー
                DB::table('circular')->select(['id'])
                    ->whereRaw("DATE_FORMAT( completed_date, '%Y%m' ) = $month")
                    ->where('completed_copy_flg', CircularUtils::CIRCULAR_COMPLETED_COPY_FLG_FALSE)
                    ->whereIn('circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS])
                    ->orderBy('id')
                    ->chunkById(100, function ($items) use ($month) {
                        $circular_ids = $items->pluck('id')->toArray();
                        $circular_idsStr = '(' . implode(', ', $circular_ids) . ')';
                        DB::statement("insert into circular$month select * from circular where id in $circular_idsStr");
                        DB::statement("insert into circular_user$month select * from circular_user where circular_id in $circular_idsStr");
                        DB::statement("insert into circular_document$month select * from circular_document where circular_id in $circular_idsStr");
                        DB::statement("insert into document_data$month (select DD.* from document_data as DD inner join circular_document as CD on CD.id = DD.circular_document_id where CD.circular_id in $circular_idsStr)");

                        // コピーフラッグは済みを変更
                        DB::table('circular')
                            ->whereIn('id', $circular_ids)
                            ->update(['completed_copy_flg' => CircularUtils::CIRCULAR_COMPLETED_COPY_FLG_TRUE]);

                        // 回覧完了日時テーブルに追加
                        $data = [];
                        foreach ($circular_ids as $circular_id) {
                            $data[] = ['circular_id' => $circular_id, 'month' => $month];
                        }
                        DB::table('circular_finished_month')->insert($data);
                    });
            }
            DB::commit();
            Log::channel('cron-daily')->debug("完了回覧分割テーブルの作成変更バッチ完了");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('cron-daily')->error('完了回覧分割テーブルの作成変更失敗');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
        }
    }
}
