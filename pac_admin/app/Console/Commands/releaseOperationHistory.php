<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class releaseOperationHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'release:operationHistory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'release operation history data';

    // 毎回処理件数
    private const HANDLE_COUNT = 50000;

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
     * @throws \Exception
     */
    public function handle()
    {
        Log::channel('cron-daily')->debug("Copy operation history start");
        ini_set('memory_limit','1024M');
        try {
            // 今回処理について、最後のidを取得
            $maxId = DB::table("operation_history")
                ->max('id');
            // 前回処理について、最後のidを取得
            $minId = DB::table("operation_history")
                ->where("copy_flg","1")
                ->max('id');
            //　今回のコピー対象は、バックアップテーブルを削除する
            for ($mst_company_id_suffix = 0; $mst_company_id_suffix < 10; $mst_company_id_suffix++) {
                DB::table('operation_history'.$mst_company_id_suffix)
                    ->whereBetween('id', [$minId + 1, $maxId])
                    ->delete();
            }
            // 今回処理回数ループ
            for($now_min_id = $minId; $now_min_id <= $maxId; $now_min_id = $now_min_id + self::HANDLE_COUNT){
                // 該当処理最小id = 前回最後のid + 1
                $min_id = $now_min_id + 1;
                // 最後のループすれば、今回処理最大id = 今回最後のid
                // 以外の場合、今回処理最大id = 該当処理最小 + 毎回処理件数
                $max_id = $now_min_id + self::HANDLE_COUNT > $maxId ? $maxId : $now_min_id + self::HANDLE_COUNT;
                Log::channel('cron-daily')->debug("Repeat times--Copy operation history id between " .$min_id ." and " .$max_id ." start");
                $this->line("Repeat times--Copy operation history id between " .$min_id ." and " .$max_id ." start");
                #10テーブル分割処理
                 for ($mst_company_id_suffix = 0; $mst_company_id_suffix < 10; $mst_company_id_suffix++) {
                            DB::statement("INSERT INTO operation_history$mst_company_id_suffix
                                    (id,
                                    auth_flg,
                                    user_id,
                                    mst_display_id,
                                    mst_operation_id,
                                    result,
                                    detail_info,
                                    ip_address,
                                    create_at,
                                    create_user,
                                    update_at,
                                    update_user)
                                select
                                    a.id as id,
                                    a.auth_flg as auth_flg,
                                    a.user_id as user_id,
                                    a.mst_display_id as mst_display_id,
                                    a.mst_operation_id as mst_operation_id,
                                    a.result as result,
                                    a.detail_info as detail_info,
                                    a.ip_address as ip_address,
                                    a.create_at as create_at,
                                    a.create_user as create_user,
                                    a.update_at as update_at,
                                    a.update_user as update_user
                                  from operation_history as a  
                                  left join mst_user as b on a.user_id = b.id 
                                  left join mst_admin as c on a.user_id = c.id 
                                  where a.copy_flg = 0
                                    and (a.id BETWEEN $min_id AND $max_id)
                                    and ((substr(b.mst_company_id,-1,1) = $mst_company_id_suffix and a.auth_flg=1)
                                        or (substr(c.mst_company_id,-1,1) = $mst_company_id_suffix and a.auth_flg=0))
                                  ");
                 }
                DB::table('operation_history')
                    ->where('copy_flg', 0)
                    ->where('user_id', '!=', 0)
                    ->whereBetween('id', [$min_id, $max_id])
                    ->update(['copy_flg'=>'1']);
                Log::channel('cron-daily')->debug("Repeat times--Copy operation history id between " .$min_id ." and " .$max_id ." end");
                $this->line("Repeat times--Copy operation history id between " .$min_id ." and " .$max_id ." end");
            }
            $this->line("Copy operation history end");
            Log::channel('cron-daily')->debug("Copy operation history end");
        } catch (\Exception $e) {
            Log::channel('cron-daily')->error('Run to Convert email to lowercase failed');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }
    }
}