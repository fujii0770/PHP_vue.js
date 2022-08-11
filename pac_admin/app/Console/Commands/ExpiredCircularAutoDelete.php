<?php

namespace App\Console\Commands;

use App\Http\Utils\BoxUtils;
use App\Http\Utils\CircularAttachmentUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Circular;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\CircularUtils;
use GuzzleHttp\RequestOptions;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Carbon;

class ExpiredCircularAutoDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expired_circular:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '保存期限終了の回覧を削除します';

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
        Log::channel('cron-daily')->debug("expired circular automatic delete start");

        try{
            // 現在の今日の時間を取得
            $today = strtotime(date("Y/m/d 00:00:00"));

            $finishedDates = [];
            // デフォルトは今月処理のみ
            $size = 1;
            // TODO 14日は自動保存完了後、デフォルトの最長保管日、変更がある場合は、同じ期間に対応する必要があります
            // 14日前は今月しない、先月のデータを処理も
            if (date('m') != date('m', strtotime("- 14 day"))) {
                $size = 2;
            }

            for ($i = 0; $i < $size; $i++) {
                // 完了日時
//                $finishedDate = date('Ym', strtotime(date('Ym') . " -$i month"));
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$i)->format('Ym');
                // 今月の場合
                if ($i === 0) {
                    $finishedDate = '';
                }
                // 保存期間終了もしくは終了間近の回覧を取得する
                $auto_delete_lists = DB::table("circular$finishedDate as C")
                    ->join("circular_document$finishedDate as CD", 'C.id', 'CD.circular_id')
                    ->join('mst_user', 'C.mst_user_id', 'mst_user.id')
                    ->join('mst_company', 'mst_user.mst_company_id', 'mst_company.id')
                    ->join('mst_limit', 'mst_company.id', 'mst_limit.mst_company_id')
                    ->join('circular_auto_storage_history', 'C.id', 'circular_auto_storage_history.circular_id')
                    ->where('mst_company.box_enabled', BoxUtils::BOX_ENABLED)
                    ->where('mst_limit.box_enabled_automatic_storage', BoxUtils::BOX_ENABLED_AUTO_STORAGE)
                    ->where('circular_auto_storage_history.result', BoxUtils::BOX_AUTOMATIC_STORAGE_RESULT_SUCCESS)
                    ->where('mst_limit.box_enabled_automatic_delete', BoxUtils::BOX_ENABLED_AUTO_DELETE)
                    ->where('C.env_flg', config('app.pac_app_env'))
                    ->where('C.edition_flg', config('app.pac_contract_app'))
                    ->where('C.server_flg', config('app.pac_contract_server'))
                    ->where('C.circular_status', '!=', CircularUtils::DELETE_STATUS)
                    ->select('C.id', 'C.circular_status', 'mst_limit.box_max_auto_delete_days', 'C.applied_date')
                    ->get();

                // データがない
                if (count($auto_delete_lists) == 0){
                    continue;
                }
                // 今月の場合、今月のテーブルを処理も
                if ($i === 0) {
                    $finishedDates[date('Ym')] = $auto_delete_lists;
                }
                $finishedDates[$finishedDate] = $auto_delete_lists;
            }
            foreach ($finishedDates as $finishedDate => $auto_delete_lists) {
                foreach ($auto_delete_lists as $auto_delete_list) {
                    // 自動保存日時
                    $box_max_auto_delete_days = $auto_delete_list->box_max_auto_delete_days;
                    // 作成日時
                    $applied_date = $auto_delete_list->applied_date ? $auto_delete_list->applied_date : $auto_delete_list->create_at;
                    // 今日より有効期限が小さい
                    if(strtotime("+$box_max_auto_delete_days day", strtotime($applied_date)) <= $today){
                        Log::channel('cron-daily')->debug('expired circular automatic delete circular id:' . $auto_delete_list->id);
                        // 保存期限終了の回覧を削除します
                        DB::table("circular$finishedDate")
                            ->where('id', $auto_delete_list->id)
                            ->update(['circular_status' => CircularUtils::DELETE_STATUS]);
                        CircularAttachmentUtils::deleteAttachments(array($auto_delete_list->id));//PAC_5-1398 回覧中のすべての添付ファイルを削除します。
                        $circular_users = DB::table("circular_user$finishedDate")->where('circular_id', $auto_delete_list->id)->select('edition_flg', 'env_flg', 'server_flg')->distinct()->get();
                        foreach ($circular_users as $circular_user) {
                            // 他環境の場合
                            if ($circular_user->edition_flg == config('app.pac_contract_app')
                                && ($circular_user->env_flg != config('app.pac_app_env')
                                    || $circular_user->server_flg != config('app.pac_contract_server'))) {
                                $envClient = EnvApiUtils::getUnauthorizeClient($circular_user->env_flg, $circular_user->server_flg);
                                if (!$envClient) {
                                    //TODO message
                                    Log::channel('cron-daily')->error('Cannot connect to Env Api');
                                } else {
                                    $response = $envClient->post('public/circulars/' . $auto_delete_list->id . '/autoStorageUpdateStatus', [
                                        RequestOptions::JSON => ['edition_flg' => config('app.pac_contract_app'), 'env_flg' => config('app.pac_app_env'), 'server_flg' => config('app.pac_contract_server'),
                                            'circular_status' => CircularUtils::DELETE_STATUS, 'finishedDate' => $finishedDate
                                        ]
                                    ]);
                                    if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                                        Log::channel('cron-daily')->error('autoStorageUpdateStatus failed.(edition_flg:' . config('app.pac_contract_app') . ';env_flg:' . config('app.pac_app_env') . ';server_flg:'
                                            . config('app.pac_contract_server') . 'origin_circular_id' . $auto_delete_list->id);
                                        Log::channel('cron-daily')->error($response->getBody());
                                    }
                                }
                            }
                        }
                    }
                }
            }
            Log::channel('cron-daily')->debug('expired circular automatic delete finished');
        }catch(\Exception $e){
            Log::channel('cron-daily')->error('expired circular automatic delete failed');
            Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
    }
}
