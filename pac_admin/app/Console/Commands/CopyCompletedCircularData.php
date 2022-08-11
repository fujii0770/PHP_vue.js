<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CopyCompletedCircularData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:completedCircular {--month=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'copy completed circular data';

    /**
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
            Log::channel('cron-daily')->debug("回覧完了データコピーバッチ実行開始");

            $month = $this->option('month');
            if (!$month) {
                // 当月
                $month = date('Ym');
            }
            DB::beginTransaction();
            // 当月内回覧完了データを取得
            DB::table('circular')->select(['id'])
                ->whereIn('circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS])
                ->where('completed_copy_flg', CircularUtils::CIRCULAR_COMPLETED_COPY_FLG_FALSE)
                ->whereRaw("DATE_FORMAT( completed_date, '%Y%m' ) = $month")
                ->orderBy('id')
                ->chunkById(100, function ($items) use ($month) {
                    $circular_ids = $items->pluck('id')->toArray();

                    // テーブル作成
                    if (!Schema::hasTable("circular$month")) {
                        $this->createCopyTable($month);
                    }
                    // データ移行
                    $this->dataCopy($circular_ids, $month);
                });

            // 毎一日の時、上月コピーなしのデータコピー実施
            if ((integer)date('d') == 1) {
                // テーブル作成
                if (!Schema::hasTable("circular$month")) {
                    Log::channel('cron-daily')->debug("本月回覧バックアップテーブル作成");
                    $this->createCopyTable($month);
                }
                Log::channel('cron-daily')->debug("上月回覧完了データコピーバッチ実行");
                $last_month = Carbon::now()->addMonthsNoOverflow(-1)->format('Ym');
                // 回覧完了データを取得
                DB::table('circular')->select(['id'])
                    ->whereIn('circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS])
                    ->where('completed_copy_flg', CircularUtils::CIRCULAR_COMPLETED_COPY_FLG_FALSE)
                    ->whereRaw("DATE_FORMAT( completed_date, '%Y%m' ) = $last_month")
                    ->orderBy('id')
                    ->chunkById(100, function ($items) use ($last_month) {
                        $circular_ids = $items->pluck('id')->toArray();
                        // データ移行
                        $this->dataCopy($circular_ids, $last_month);
                    });
            }
            DB::commit();
            Log::channel('cron-daily')->debug("回覧完了データコピーバッチ実行終了");
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::channel('cron-daily')->error('回覧完了データコピーバッチ実行エラーが発生');
            Log::channel('cron-daily')->error($ex->getMessage() . $ex->getTraceAsString());
            throw $ex;
        }
    }

    /**
     * 完了データ移行
     *
     * @param $circular_ids array 回覧ID
     * @param $month string 完了日時
     * @throws \Exception
     */
    private function dataCopy($circular_ids, $month)
    {
        try {
            $circular_idsStr = '(' . implode(', ', $circular_ids) . ')';

            $circular_column = DB::table('information_schema.COLUMNS')->select('column_name')
                ->where('table_name','circular')
                ->distinct()->pluck('COLUMN_NAME')->toArray();
            $circular_user_column = DB::table('information_schema.COLUMNS')->select('column_name')
                ->where('table_name','circular_user')
                ->distinct()->pluck('COLUMN_NAME')->toArray();
            $circular_document_column = DB::table('information_schema.COLUMNS')->select('column_name')
                ->where('table_name','circular_document')
                ->distinct()->pluck('COLUMN_NAME')->toArray();
            $document_data_column = DB::table('information_schema.COLUMNS')->select(DB::raw("column_name as COLUMN_NAME"))
                ->where('table_name','document_data')->distinct()
                ->pluck('COLUMN_NAME')->toArray();
            $insert_document_data_columns = [];
            $document_data_column = array_filter($document_data_column,function ($column) use (&$insert_document_data_columns){
                if ($column != 'copy_flg'){
                    $insert_document_data_columns[] = 'DD.'.$column;
                }
                return $column != 'copy_flg';
            });

            $circular_columns = implode(',',$circular_column);
            $circular_user_columns = implode(',',$circular_user_column);
            $circular_document_columns = implode(',',$circular_document_column);
            $document_data_columns = implode(',',$document_data_column);
            $insert_document_data_columns = implode(',',$insert_document_data_columns);

            // 完了データを移行
            DB::statement("insert into circular$month ($circular_columns) select $circular_columns from circular where id in $circular_idsStr");
            DB::statement("insert into circular_user$month ($circular_user_columns) select $circular_user_columns from circular_user where circular_id in $circular_idsStr");
            DB::statement("insert into circular_document$month ($circular_document_columns) select $circular_document_columns from circular_document where circular_id in $circular_idsStr");
            DB::statement("insert into document_data$month ($document_data_columns) (select $insert_document_data_columns from document_data as DD inner join circular_document as CD on CD.id = DD.circular_document_id where CD.circular_id in $circular_idsStr)");

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
        } catch (\Exception $ex) {
            Log::channel('cron-daily')->error("回覧完了データ移行エラーが発生。");
            Log::channel('cron-daily')->error($ex->getMessage() . $ex->getTraceAsString());
            throw new \Exception('回覧完了データ移行エラーが発生。');
        }
    }

    private function createCopyTable($month)
    {
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
    }
}
