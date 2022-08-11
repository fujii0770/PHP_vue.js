<?php

namespace App\Console\Commands;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\GwAppApiUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\InsertUsageSituationUtils;
use App\Models\Circular;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\EnvApiUtils;
use GuzzleHttp\RequestOptions;


class InsertUsageSituationDetail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usage_situation_detail:insert {--targetDay=yyyy-mm-dd}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert Usage situation detail';
    
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
        Log::channel('cron-daily')->debug("Insert InsertUsageSituationDetail start");

        try{
            $now = Carbon::now();
            $limit = 100; // 毎回テーブル登録レコード数

            // php artisan usage_situation_detail:insert
            // php artisan usage_situation_detail:insert --targetDay=2020-01-01
            $date = $this->option('targetDay');

            // 計算基準日：
            //　パラメータ　未指定：当日　指定：指定日
            if($date == 'yyyy-mm-dd'){
                $targetDay = $now->format('Y-m-d');
            }else{
                $targetDay = $date;
            }

            $companies = DB::table('mst_company')
                ->select([
                      'id'
                    , 'guest_company_flg'
                    , 'company_name'
                    , 'company_name_kana'
                    , 'mst_company_id as host_company_id'
                    , 'host_app_env'
                    , 'host_contract_server'
                    , 'upper_limit'
                    , 'contract_edition'
                    , 'add_file_limit'
                ])
                ->get()
                ->keyBy('id');

            $usageSituationDetails = [];
            $hostUsageSituationDetailsInSameEnv = [];
            $hostUsageSituationDetailsInDiffEnv = [];
            $companyContractEdition = [];
            
            // init
            foreach ($companies as $company){
                
                $usageSituationDetails[$company->id] = [
                    'target_date' => $targetDay,
                    'mst_company_id' => $company->id,
                    'company_name' => $company->company_name,
                    'company_name_kana' => $company->company_name_kana,
                    'guest_company_id' => null,
                    'guest_company_name' => null,
                    'guest_company_name_kana' => null,
                    'guest_company_app_env' => 0,
                    'guest_company_contract_server' => 0,
                    'user_count_valid'=>0,
                    'user_count_activity'=>0,
                    'storage_stamp'=>0,
                    'storage_document'=>0,
                    'storage_operation_history'=>0,
                    'storage_mail'=>0,
                    'storage_attachment'=>0,
                    'storage_convenient_file'=>0,
                    'storage_sum'=>0,
                    'storage_rate'=>0,
                    'storage_bbs_file_size' => 0,
                    'storage_schedule'=>0,
                    'storage_stamp_re'=>0,
                    'storage_document_re'=>0,
                    'storage_operation_history_re'=>0,
                    'storage_mail_re'=>0,
                    'storage_attachment_re'=>0,
                    'storage_convenient_file_re' =>0,
                    'storage_sum_re'=>0,
                   'storage_rate_re'=>0,
                    'storage_bbs_file_size_re' => 0,
                    'storage_schedule_re'=>0,
                    'stamp_contract'=>$company->upper_limit,
                    'stamp_count'=>0,
                    'stamp_over_count'=>0,
                    'timestamp_count'=>0,
                    'timestamp_leftover_count'=>0,
                    'circular_applied_count'=>0,
                    'circular_completed_count'=>0,
                    'circular_completed_total_time'=>0,
                    'multi_comp_out'=>0,
                    'multi_comp_in'=>0,
                    'upload_count_pdf'=>0,
                    'upload_count_excel'=>0,
                    'upload_count_word'=>0,
                    'download_count_pdf'=>0,
                    'create_at' => $now,
                ];

                $companyContractEdition[$company->id] = $company->contract_edition;

                if ($company->guest_company_flg){
                    // ゲスト企業
                    if ($company->host_app_env == config('app.pac_app_env')
                        && $company->host_contract_server == config('app.pac_contract_server')){
                        //　ゲスト／ホスト企業が同環境
                        $hostUsageSituationDetailsInSameEnv[$company->id] = [
                            'target_date' => $targetDay,
                            'mst_company_id' => $company->host_company_id,
                            'company_name' => $companies[$company->host_company_id]->company_name,
                            'company_name_kana' => $companies[$company->host_company_id]->company_name_kana,
                            'guest_company_id' => $company->id,
                            'guest_company_name' => $company->company_name,
                            'guest_company_name_kana' => $company->company_name_kana,
                            'guest_company_app_env' => config('app.pac_app_env'),
                            'guest_company_contract_server' => config('app.pac_contract_server'),
                            'user_count_valid'=>0,
                            'user_count_activity'=>0,
                            'storage_stamp'=>0,
                            'storage_document'=>0,
                            'storage_operation_history'=>0,
                            'storage_mail'=>0,
                            'storage_attachment'=>0,
                            'storage_convenient_file'=>0,
                            'storage_sum'=>0,
                            'storage_rate'=>0,
                            'storage_bbs_file_size' => 0,
                            'storage_schedule'=>0,
                            'storage_stamp_re'=>0,
                            'storage_document_re'=>0,
                            'storage_operation_history_re'=>0,
                            'storage_mail_re'=>0,
                            'storage_attachment_re'=>0,
                            'storage_convenient_file_re' =>0,
                            'storage_sum_re'=>0,
                            'storage_rate_re'=>0,
                            'storage_bbs_file_size_re' => 0,
                            'storage_schedule_re'=>0,
                            'stamp_contract'=>$company->upper_limit,
                            'stamp_count'=>0,
                            'stamp_over_count'=>0,
                            'timestamp_count'=>0,
                            'timestamp_leftover_count'=>0,
                            'circular_applied_count'=>0,
                            'circular_completed_count'=>0,
                            'circular_completed_total_time'=>0,
                            'multi_comp_out'=>0,
                            'multi_comp_in'=>0,
                            'upload_count_pdf'=>0,
                            'upload_count_excel'=>0,
                            'upload_count_word'=>0,
                            'download_count_pdf'=>0,
                            'create_at' => $now,
                        ];
                    }else{
                        //　ゲスト／ホスト企業が別環境
                        $hostUsageSituationDetailsInDiffEnv[$company->host_app_env.$company->host_contract_server][$company->id] = [
                            'target_date' => $targetDay,
                            'mst_company_id' => $company->host_company_id,
                            'guest_company_id' => $company->id,
                            'guest_company_name' => $company->company_name,
                            'guest_company_name_kana' => $company->company_name_kana,
                            'guest_company_app_env' => config('app.pac_app_env'),
                            'guest_company_contract_server' => config('app.pac_contract_server'),
                            'user_count_valid'=>0,
                            'user_count_activity'=>0,
                            'storage_stamp'=>0,
                            'storage_document'=>0,
                            'storage_operation_history'=>0,
                            'storage_mail'=>0,
                            'storage_attachment'=>0,
                            'storage_convenient_file'=>0,
                            'storage_sum'=>0,
                            'storage_rate'=>0,
                            'storage_bbs_file_size' => 0,
                            'storage_schedule'=>0,
                            'storage_stamp_re'=>0,
                            'storage_document_re'=>0,
                            'storage_operation_history_re'=>0,
                            'storage_mail_re'=>0,
                            'storage_attachment_re'=>0,
                            'storage_convenient_file_re' =>0,
                            'storage_sum_re'=>0,
                            'storage_rate_re'=>0,
                            'storage_bbs_file_size_re' => 0,
                            'storage_schedule_re'=>0,
                            'stamp_contract'=>$company->upper_limit,
                            'stamp_count'=>0,
                            'stamp_over_count'=>0,
                            'timestamp_count'=>0,
                            'timestamp_leftover_count'=>0,
                            'circular_applied_count'=>0,
                            'circular_completed_count'=>0,
                            'circular_completed_total_time'=>0,
                            'multi_comp_out'=>0,
                            'multi_comp_in'=>0,
                            'upload_count_pdf'=>0,
                            'upload_count_excel'=>0,
                            'upload_count_word'=>0,
                            'download_count_pdf'=>0,
                            'create_at' => $now,
                        ];
                    }
                }
            }

            // main
            // 1) ユーザー数（有効＋残りライセンス）
            $totalUsers = DB::table('mst_user')
                ->select(['mst_company_id', DB::raw('COUNT(mst_user.id) as count_user')])
                ->join('mst_user_info', 'mst_user.id', '=', 'mst_user_info.mst_user_id')
                ->where(function ($query){
                    // 富士通(K5)以外場合、有効なユーザー
                    $query->whereIn('state_flg',[AppUtils::STATE_VALID]);

                    if (config('app.fujitsu_company_id')){
                        // 富士通会社指定ある場合、富士通以外会社統計（富士通会社別途統計）
                        // 富士通会社指定なし場合、会社条件不要
                        $query->where('mst_company_id', '<>' , config('app.fujitsu_company_id'));
                    }
                    $query->where(function ($query) {
                        $query->where('mst_user.option_flg', AppUtils::USER_NORMAL)
                            ->orWhere(function ($query){
                                $query->where('mst_user.option_flg', AppUtils::USER_OPTION)
                                    ->where('mst_user_info.gw_flg', 1);
                            });
                    });
                })
                ->orWhere(function($query1){
                    // 無効だが、今日無効にしたユーザー（今日統計されたことがある）
                    $query1->whereIn('state_flg', [AppUtils::STATE_INVALID,AppUtils::STATE_INVALID_NOPASSWORD]);
                    $query1->where(DB::raw("DATE_FORMAT(invalid_at, '%Y%m%d')"), Carbon::now()->format('Ymd'));
                    $query1->where(function ($query1) {
                        $query1->where('mst_user.option_flg', AppUtils::USER_NORMAL)
                            ->orWhere(function ($query1){
                                $query1->where('mst_user.option_flg', AppUtils::USER_OPTION)
                                    ->where('mst_user_info.gw_flg', 1);
                            });
                    });

                    if (config('app.fujitsu_company_id')){
                        // 富士通会社指定ある場合、富士通以外会社統計（富士通会社別途統計）
                        // 富士通会社指定なし場合、会社条件不要
                        $query1->where('mst_company_id', '<>' , config('app.fujitsu_company_id'));
                    }
                })
                ->groupBy('mst_company_id');

            if (config('app.fujitsu_company_id')){
                // 富士通会社指定ある場合、富士通会社追加統計
                $totalUsers = DB::table('mst_user')
                    ->select(['mst_company_id', DB::raw('COUNT(mst_user.id) as count_user')])
                    ->join('mst_user_info', 'mst_user.id', '=', 'mst_user_info.mst_user_id')
                    ->where('mst_company_id' , config('app.fujitsu_company_id'))
                    ->where(function ($query){
                        // 富士通(K5)場合、
                        // 有効でパスワードが設定してあるユーザー
                        $query->whereNotNull('password_change_date');
                        $query->whereIn('state_flg',[AppUtils::STATE_VALID]);
                        $query->where(function ($query) {
                            $query->where('mst_user.option_flg', AppUtils::USER_NORMAL)
                                ->orWhere(function ($query){
                                    $query->where('mst_user.option_flg', AppUtils::USER_OPTION)
                                        ->where('mst_user_info.gw_flg', 1);
                                });
                        });
                    })
                    ->orWhere(function($query1){
                        // 無効だが、今日無効したユーザー（今日統計されたことがある）
                        $query1->whereNotNull('password_change_date');
                        $query1->whereIn('state_flg', [AppUtils::STATE_INVALID,AppUtils::STATE_INVALID_NOPASSWORD]);
                        $query1->where(DB::raw("DATE_FORMAT(invalid_at, '%Y%m%d')"), Carbon::now()->format('Ymd'));
                        $query1->where(function ($query1) {
                            $query1->where('mst_user.option_flg', AppUtils::USER_NORMAL)
                                ->orWhere(function ($query1){
                                    $query1->where('mst_user.option_flg', AppUtils::USER_OPTION)
                                        ->where('mst_user_info.gw_flg', 1);
                                });
                        });
                    })
                    ->groupBy('mst_company_id')
                    ->union($totalUsers)
                    ->get();
            }else{
                // 富士通会社指定なし場合、上記統計結果そのまま利用
                $totalUsers = $totalUsers
                    ->get();
            }

            foreach ($totalUsers as $totalUser){
                // ホスト企業
                if (key_exists($totalUser->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$totalUser->mst_company_id]['user_count_valid'] = $totalUser->count_user + $companies[$totalUser->mst_company_id]->add_file_limit;
                    //$usageSituationDetails[$totalUser->mst_company_id]['user_count_leftover'] = $companies[$totalUser->mst_company_id]->upper_limit - $totalUser->count_user;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($totalUser->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$totalUser->mst_company_id]['user_count_valid'] = $totalUser->count_user + $companies[$totalUser->mst_company_id]->add_file_limit;
                   //$hostUsageSituationDetailsInSameEnv[$totalUser->mst_company_id]['user_count_leftover'] = $companies[$totalUser->mst_company_id]->upper_limit - $totalUser->count_user;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($totalUser->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$totalUser->mst_company_id]['user_count_valid'] = $totalUser->count_user + $companies[$totalUser->mst_company_id]->add_file_limit;
                        //$hostUsageSituationDetailsInDiffEnv[$key][$totalUser->mst_company_id]['user_count_leftover'] = $companies[$totalUser->mst_company_id]->upper_limit - $totalUser->count_user;
                    }
                }
            }
            // 2) ユーザー数（アクティビティ＋アクティビティ率）
            $activity_user_cnts = InsertUsageSituationUtils::getCircularUsageDetail(AppUtils::CIRCULAR_USER_COUNT, $now->toString(), $targetDay);
            foreach($activity_user_cnts as $activity_user_cnt){
                // ホスト企業
                if (key_exists($activity_user_cnt->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$activity_user_cnt->mst_company_id]['user_count_activity'] = $activity_user_cnt->activity_user_cnt;

                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($activity_user_cnt->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$activity_user_cnt->mst_company_id]['user_count_activity'] = $activity_user_cnt->activity_user_cnt;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($activity_user_cnt->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$activity_user_cnt->mst_company_id]['user_count_activity'] = $activity_user_cnt->activity_user_cnt;
                    }
                }
            }

            // 3) ストレージ
            $per_operation_history_size = config('app.per_operation_history_size'); // 履歴ごとの容量(B)
            $per_mail_size = config('app.per_mail_size'); // メールごとの容量(B)
            $per_schedule_size = config('app.per_schedule_size'); // スケジュールレコードごとの容量(B)
//            $notice_over_storage_percent = config('app.notice_over_storage_percent'); // 90％になったタイミングでメール通知
            $use_storage_base = config('app.use_storage_base'); // 使用容量ベース
            Log::channel('cron-daily')->debug('calculate インプリンツ容量 start');

            //捺印印面サイズの計算
            DB::table('stamp_info')
                ->where('status',AppUtils::STAMP_NOT_COLLECT)
                ->update([
                    'size' => DB::raw('length(stamp_info.stamp_image)')
                ]);

            // インプリンツ容量(本環境)
            $stamp_storage_sizes_current = DB::table('stamp_info')
                ->join('mst_user', 'stamp_info.email', 'mst_user.email')
                ->where('stamp_info.status',AppUtils::STAMP_NOT_COLLECT)
                ->where('stamp_info.mst_assign_stamp_id', '!=', 0)
                ->select(DB::raw('SUM(stamp_info.size)as length_sum'), 'mst_user.mst_company_id')
                ->groupBy('mst_user.mst_company_id')
                ->get()->keyBy('mst_user.mst_company_id');

            //前回集計日
            $old_target = DB::table('usage_situation_detail')->selectRaw('max(target_date) as target_day')->first();
            //前回集計インプリンツ容量
            if ($old_target){
                $old_stamp_storage_sizes = DB::table('usage_situation_detail')
                    ->selectRaw('mst_company_id,storage_stamp')
                    ->where('target_date',$old_target->target_day)
                    ->whereNull('guest_company_id')
                    ->groupBy('mst_company_id','storage_stamp')
                    ->get()->keyBy('mst_company_id');
            }else{
                $old_stamp_storage_sizes = collect();
            }

            Log::channel('cron-daily')->debug(' calculate インプリンツ容量 finished');
            // ホスト企業
            foreach ($usageSituationDetails as $company_id => $usageSituationDetail){
                $usageSituationDetails[$company_id]['storage_stamp'] +=
                    ($stamp_storage_sizes_current->get($company_id) ? $stamp_storage_sizes_current->get($company_id)->length_sum : 0) +
                    ($old_stamp_storage_sizes->get($company_id) ? ($old_stamp_storage_sizes->get($company_id)->storage_stamp * (1024 * 1024) / $use_storage_base) : 0);
            }
            // ゲスト企業（ホスト企業と同環境）
            foreach ($hostUsageSituationDetailsInSameEnv as $company_id => $hostUsageSituationDetail){
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_stamp'] +=
                    ($stamp_storage_sizes_current->get($company_id) ? $stamp_storage_sizes_current->get($company_id)->length_sum : 0)+
                    ($old_stamp_storage_sizes->get($company_id) ? ($old_stamp_storage_sizes->get($company_id)->storage_stamp * (1024 * 1024) / $use_storage_base) : 0);
            }
            // ゲスト企業（ホスト企業と別環境）
            foreach ($hostUsageSituationDetailsInDiffEnv as $key => $values){
                foreach ($values as $company_id => $hostUsageSituationDetailInDiffEnv){
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_stamp'] +=
                        ($stamp_storage_sizes_current->get($company_id) ? $stamp_storage_sizes_current->get($company_id)->length_sum : 0)+
                        ($old_stamp_storage_sizes->get($company_id) ? ($old_stamp_storage_sizes->get($company_id)->storage_stamp * (1024 * 1024) / $use_storage_base) : 0);
                }
            }

            // インプリンツ容量(その他の環境)
            $stamp_storage_sizes_other = DB::table('assign_stamp_info')
                ->join('mst_assign_stamp', 'assign_stamp_info.assign_stamp_id', 'mst_assign_stamp.id')
                ->join('mst_stamp', 'mst_assign_stamp.stamp_id', 'mst_stamp.id')
                ->join('mst_user', 'mst_assign_stamp.mst_user_id', 'mst_user.id')
                ->select(DB::raw('SUM(length(mst_stamp.stamp_image))as length_sum'), 'mst_user.mst_company_id')
                ->where('assign_stamp_info.status',AppUtils::STAMP_NOT_COLLECT)
                ->groupBy('mst_user.mst_company_id')
                ->get();

            foreach($stamp_storage_sizes_other as $stamp_storage_sizes_other_item){
                // ホスト企業
                if (key_exists($stamp_storage_sizes_other_item->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$stamp_storage_sizes_other_item->mst_company_id]['storage_stamp'] += $stamp_storage_sizes_other_item->length_sum;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($stamp_storage_sizes_other_item->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$stamp_storage_sizes_other_item->mst_company_id]['storage_stamp'] += $stamp_storage_sizes_other_item->length_sum;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($stamp_storage_sizes_other_item->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$stamp_storage_sizes_other_item->mst_company_id]['storage_stamp'] += $stamp_storage_sizes_other_item->length_sum;
                    }
                }
            }

            // ドキュメントデータ容量
            $document_storage_sizes = InsertUsageSituationUtils::getCircularUsageDetail(AppUtils::CIRCULAR_DOCUMENT_DATA_SIZE, $now->toString());

            foreach($document_storage_sizes as $document_storage_size){
                // ホスト企業
                if (key_exists($document_storage_size->create_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$document_storage_size->create_company_id]['storage_document'] += $document_storage_size->storage_size;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($document_storage_size->create_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$document_storage_size->create_company_id]['storage_document'] += $document_storage_size->storage_size;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($document_storage_size->create_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$document_storage_size->create_company_id]['storage_document'] += $document_storage_size->storage_size;
                    }
                }
            }

            //添付ファイルデータ容量
            $attachment_storage_sizes = InsertUsageSituationUtils::getCircularUsageDetail(AppUtils::CIRCULAR_ATTACHMENT_DATA_SIZE, $now->toString());

            foreach ($attachment_storage_sizes as $attachment_storage_size){
                // ホスト企業
                if (key_exists($attachment_storage_size->create_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$attachment_storage_size->create_company_id]['storage_attachment'] += $attachment_storage_size->storage_size;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($attachment_storage_size->create_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$attachment_storage_size->create_company_id]['storage_attachment'] += $attachment_storage_size->storage_size;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($attachment_storage_size->create_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$attachment_storage_size->create_company_id]['storage_attachment'] += $attachment_storage_size->storage_size;
                    }
                }
            }

            //ファイルメール便容量
            $disk_mail_sizes = DB::table('disk_mail as dm')
                ->join('disk_mail_file as dmf', 'dm.id', 'dmf.disk_mail_id')
                ->join('mst_user as mu', 'dm.mst_user_id', 'mu.id')
                ->select(DB::raw('mu.mst_company_id as create_company_id,sum(dmf.file_size) as storage_size'))
                ->groupBy(['create_company_id'])
                ->get();

            foreach ($disk_mail_sizes as $disk_mail_size){
                // ホスト企業
                if (key_exists($disk_mail_size->create_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$disk_mail_size->create_company_id]['storage_convenient_file'] += $disk_mail_size->storage_size;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($disk_mail_size->create_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$disk_mail_size->create_company_id]['storage_convenient_file'] += $disk_mail_size->storage_size;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($disk_mail_size->create_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$disk_mail_size->create_company_id]['storage_convenient_file'] += $disk_mail_size->storage_size;
                    }
                }
            }

            // 操作ログ容量 0：管理者
            $admin_operation_history_storage_cnts = DB::table('operation_history')
                ->join('mst_admin', function($query){
                    $query->on('mst_admin.id', '=', 'operation_history.user_id');
                })
                ->select(DB::raw('count(1) as history_cnt, mst_admin.mst_company_id'))
                ->where('operation_history.auth_flg', AppUtils::OPERATION_HISTORY_AUTH_FLG_ADMIN)
                ->groupBy('mst_admin.mst_company_id')
                ->get();
            Log::channel('cron-daily')->debug('calculate 操作ログ容量:利用者 start');
            // 操作ログ容量 1：利用者
            $user_operation_history_storage_cnts =  DB::table('operation_history')
                ->join('mst_user', function($query){
                    $query->on('mst_user.id', '=', 'operation_history.user_id');
                })
                ->select(DB::raw('count(1) as history_cnt, mst_user.mst_company_id'))
                ->where('operation_history.auth_flg', AppUtils::OPERATION_HISTORY_AUTH_FLG_USER)
                ->groupBy('mst_user.mst_company_id')
                ->get();
            Log::channel('cron-daily')->debug('calculate 操作ログ容量:利用者 finished');
            foreach($admin_operation_history_storage_cnts as $admin_operation_history_storage_cnt){
                // ホスト企業
                if (key_exists($admin_operation_history_storage_cnt->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$admin_operation_history_storage_cnt->mst_company_id]['storage_operation_history'] += $admin_operation_history_storage_cnt->history_cnt;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($admin_operation_history_storage_cnt->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$admin_operation_history_storage_cnt->mst_company_id]['storage_operation_history'] += $admin_operation_history_storage_cnt->history_cnt;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($admin_operation_history_storage_cnt->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$admin_operation_history_storage_cnt->mst_company_id]['storage_operation_history'] += $admin_operation_history_storage_cnt->history_cnt;
                    }
                }
            }

            foreach($user_operation_history_storage_cnts as $user_operation_history_storage_cnt){
                // ホスト企業
                if (key_exists($user_operation_history_storage_cnt->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$user_operation_history_storage_cnt->mst_company_id]['storage_operation_history'] += $user_operation_history_storage_cnt->history_cnt;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($user_operation_history_storage_cnt->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$user_operation_history_storage_cnt->mst_company_id]['storage_operation_history'] += $user_operation_history_storage_cnt->history_cnt;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($user_operation_history_storage_cnt->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$user_operation_history_storage_cnt->mst_company_id]['storage_operation_history'] += $user_operation_history_storage_cnt->history_cnt;
                    }
                }
            }
            Log::channel('cron-daily')->debug('calculate メール容量 start');
            // メール容量
            $mail_storage_cnts = DB::table('mail_send_resume')
                ->where('mail_send_resume.mst_company_id', '!=', '0')
                ->groupBy('mst_company_id')
                ->select(DB::raw('count(1) as mail_cnt, mail_send_resume.mst_company_id'))
                ->get();
            Log::channel('cron-daily')->debug('calculate メール容量 finished');
            foreach($mail_storage_cnts as $mail_storage_cnt){
                // ホスト企業
                if (key_exists($mail_storage_cnt->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$mail_storage_cnt->mst_company_id]['storage_mail'] = $mail_storage_cnt->mail_cnt;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($mail_storage_cnt->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$mail_storage_cnt->mst_company_id]['storage_mail'] = $mail_storage_cnt->mail_cnt;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($mail_storage_cnt->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$mail_storage_cnt->mst_company_id]['storage_mail'] = $mail_storage_cnt->mail_cnt;
                    }
                }
            }

            //PAC_5-1720 企業の掲示板容量集計
            $company_total_bbs_file_size = DB::table("bbs")
                ->Join('mst_user','bbs.mst_user_id','mst_user.id')
                ->select('mst_user.mst_company_id as company_id', DB::raw('SUM(bbs.total_file_size) as total_bbs_file_size'))
                ->groupBy('company_id')
                ->get();

            foreach($company_total_bbs_file_size as $total_bbs_file_size){
                // ホスト企業
                if (key_exists($total_bbs_file_size->company_id, $usageSituationDetails)){
                    $usageSituationDetails[$total_bbs_file_size->company_id]['storage_bbs_file_size'] = $total_bbs_file_size->total_bbs_file_size;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($total_bbs_file_size->company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$total_bbs_file_size->company_id]['storage_bbs_file_size'] = $total_bbs_file_size->total_bbs_file_size;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($total_bbs_file_size->company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$total_bbs_file_size->company_id]['storage_bbs_file_size'] = $total_bbs_file_size->total_bbs_file_size;
                    }
                }
            }
            Log::channel('cron-daily')->debug('calculate GWスケジューラ容量 start');
            // GWスケジューラ容量
            $gw_use=config('app.gw_use');
            $gw_domin=config('app.gw_domain');
            if ($gw_use == 1 && $gw_domin) {
                $count_schedule_cnts = GwAppApiUtils::getCountSchedule();
                if($count_schedule_cnts){
                    foreach($count_schedule_cnts as $count_schedule_cnt){
                        // ホスト企業
                        if (key_exists($count_schedule_cnt['company_id'], $usageSituationDetails)){
                            $usageSituationDetails[$count_schedule_cnt['company_id']]['storage_schedule'] = $count_schedule_cnt['count_schedule'];
                        }
                        // ゲスト企業（ホスト企業と同環境）
                        if (key_exists($count_schedule_cnt['company_id'], $hostUsageSituationDetailsInSameEnv)){
                            $hostUsageSituationDetailsInSameEnv[$count_schedule_cnt['company_id']]['storage_schedule'] = $count_schedule_cnt['count_schedule'];
                        }
                        // ゲスト企業（ホスト企業と別環境）
                        foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value){
                            if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($count_schedule_cnt['company_id'], $hostUsageSituationDetailsInDiffEnv[$key])){
                                $hostUsageSituationDetailsInDiffEnv[$key][$count_schedule_cnt['company_id']]['storage_schedule'] = $count_schedule_cnt['count_schedule'];
                            }
                        }
                    }
                }else{
                    Log::channel('cron-daily')->warning('Cannot connect to GW Api');
                }
            }
            Log::channel('cron-daily')->debug('calculate GWスケジューラ容量 finished');
            // 容量系再編集
            // ホスト企業
            foreach ($usageSituationDetails as $company_id => $usageSituationDetail){
                // stamp
                // 容量　×　使用容量ベース（1.3）（MB）
                $usageSituationDetails[$company_id]['storage_stamp'] =
                    $usageSituationDetails[$company_id]['storage_stamp'] * $use_storage_base / (1024 * 1024);
                $usageSituationDetails[$company_id]['storage_stamp_re'] = $usageSituationDetails[$company_id]['storage_stamp'];
                // document
                // 容量　×　使用容量ベース（1.3）（MB）
                $usageSituationDetails[$company_id]['storage_document'] =
                    $usageSituationDetails[$company_id]['storage_document'] * $use_storage_base / (1024 * 1024);
                $usageSituationDetails[$company_id]['storage_document_re'] = $usageSituationDetails[$company_id]['storage_document'];
                // operation_history
                // 履歴件数　×　平均容量　×　使用容量ベース（1.3）（MB）
                $usageSituationDetails[$company_id]['storage_operation_history'] =
                    $usageSituationDetails[$company_id]['storage_operation_history'] * $per_operation_history_size * $use_storage_base / (1024 * 1024);
                $usageSituationDetails[$company_id]['storage_operation_history_re'] = $usageSituationDetails[$company_id]['storage_operation_history'];
                // mail_size
                // メール件数　×　平均容量　×　使用容量ベース（1.3）（MB）
                $usageSituationDetails[$company_id]['storage_mail'] =
                    $usageSituationDetails[$company_id]['storage_mail'] * $per_mail_size * $use_storage_base / (1024 * 1024);
                $usageSituationDetails[$company_id]['storage_mail_re'] = $usageSituationDetails[$company_id]['storage_mail'];
                // attachment
                // 容量　×　使用容量ベース（1.3）（MB）
                $usageSituationDetails[$company_id]['storage_attachment'] =
                    $usageSituationDetails[$company_id]['storage_attachment'] * $use_storage_base / (1024 * 1024);
                $usageSituationDetails[$company_id]['storage_attachment_re'] = $usageSituationDetails[$company_id]['storage_attachment'];
                // bbs_file_size
                // 容量　×　使用容量ベース（1.3）（MB）
                $usageSituationDetails[$company_id]['storage_bbs_file_size'] =
                    $usageSituationDetails[$company_id]['storage_bbs_file_size'] * $use_storage_base / (1024 * 1024);
                $usageSituationDetails[$company_id]['storage_bbs_file_size_re'] = $usageSituationDetails[$company_id]['storage_bbs_file_size'];
                // disk_mail
                // 容量　×　使用容量ベース（1.3）（MB）
                $usageSituationDetails[$company_id]['storage_convenient_file'] =
                    $usageSituationDetails[$company_id]['storage_convenient_file'] * $use_storage_base / (1024 * 1024);
                $usageSituationDetails[$company_id]['storage_convenient_file_re'] = $usageSituationDetails[$company_id]['storage_convenient_file'];
                // schedule
                // スケジュールレコード数　×　平均容量　×　使用容量ベース（1.3）（MB）
                $usageSituationDetails[$company_id]['storage_schedule'] =
                    $usageSituationDetails[$company_id]['storage_schedule'] * $per_schedule_size * $use_storage_base / (1024 * 1024);
                $usageSituationDetails[$company_id]['storage_schedule_re'] = $usageSituationDetails[$company_id]['storage_schedule'];

                // storage_sum
                // 合計値
                $usageSituationDetails[$company_id]['storage_sum'] =
                    $usageSituationDetails[$company_id]['storage_stamp']
                     + $usageSituationDetails[$company_id]['storage_document']
                     + $usageSituationDetails[$company_id]['storage_operation_history']
                     + $usageSituationDetails[$company_id]['storage_mail']
                     + $usageSituationDetails[$company_id]['storage_attachment']
                     + $usageSituationDetails[$company_id]['storage_bbs_file_size']
                     + $usageSituationDetails[$company_id]['storage_convenient_file']
                     + $usageSituationDetails[$company_id]['storage_schedule'];
                $usageSituationDetails[$company_id]['storage_sum_re'] = $usageSituationDetails[$company_id]['storage_sum'];
                // storage_rate
                // 合計値　÷　契約数　※１契約１GB
                if ($usageSituationDetails[$company_id]['user_count_valid'] == 0){
                    $usageSituationDetails[$company_id]['storage_rate'] = 0;
                }else{
                    $usageSituationDetails[$company_id]['storage_rate'] =
                        $usageSituationDetails[$company_id]['storage_sum'] / $usageSituationDetails[$company_id]['user_count_valid'] / 1024 * 100;
                }
                $usageSituationDetails[$company_id]['storage_rate_re'] = $usageSituationDetails[$company_id]['storage_rate'];
            }
            // ゲスト企業（ホスト企業と同環境）
            foreach ($hostUsageSituationDetailsInSameEnv as $company_id => $hostUsageSituationDetailInSameEnv){
                // stamp
                // 容量　×　使用容量ベース（1.3）（MB）
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_stamp'] =
                    $hostUsageSituationDetailsInSameEnv[$company_id]['storage_stamp'] * $use_storage_base / (1024 * 1024);
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_stamp_re'] = $hostUsageSituationDetailsInSameEnv[$company_id]['storage_stamp'];
                // document
                // 容量　×　使用容量ベース（1.3）（MB）
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_document'] =
                    $hostUsageSituationDetailsInSameEnv[$company_id]['storage_document'] * $use_storage_base / (1024 * 1024);
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_document_re'] = $hostUsageSituationDetailsInSameEnv[$company_id]['storage_document'];
                // operation_history
                // 履歴件数　×　平均容量　×　使用容量ベース（1.3）（MB）
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_operation_history'] =
                    $hostUsageSituationDetailsInSameEnv[$company_id]['storage_operation_history'] * $per_operation_history_size * $use_storage_base / (1024 * 1024);
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_operation_history_re'] = $hostUsageSituationDetailsInSameEnv[$company_id]['storage_operation_history'];
                // mail_size
                // メール件数　×　平均容量　×　使用容量ベース（1.3）（MB）
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_mail'] =
                    $hostUsageSituationDetailsInSameEnv[$company_id]['storage_mail'] * $per_mail_size * $use_storage_base / (1024 * 1024);
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_mail_re'] = $hostUsageSituationDetailsInSameEnv[$company_id]['storage_mail'];
                // attachment
                // 容量　×　使用容量ベース（1.3）（MB）
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_attachment'] =
                    $hostUsageSituationDetailsInSameEnv[$company_id]['storage_attachment'] * $use_storage_base / (1024 * 1024);
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_attachment_re'] = $hostUsageSituationDetailsInSameEnv[$company_id]['storage_attachment'];
                // bbs_file_size
                // 容量　×　使用容量ベース（1.3）（MB）
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_bbs_file_size'] =
                    $hostUsageSituationDetailsInSameEnv[$company_id]['storage_bbs_file_size'] * $use_storage_base / (1024 * 1024);
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_bbs_file_size_re'] = $hostUsageSituationDetailsInSameEnv[$company_id]['storage_bbs_file_size'];
                // disk_mail
                // 容量　×　使用容量ベース（1.3）（MB）
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_convenient_file'] =
                    $hostUsageSituationDetailsInSameEnv[$company_id]['storage_convenient_file'] * $use_storage_base / (1024 * 1024);
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_convenient_file_re'] = $hostUsageSituationDetailsInSameEnv[$company_id]['storage_convenient_file'];
                // schedule
                // スケジュールレコード数　×　平均容量　×　使用容量ベース（1.3）（MB）
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_schedule'] =
                    $hostUsageSituationDetailsInSameEnv[$company_id]['storage_schedule'] * $per_schedule_size * $use_storage_base / (1024 * 1024);
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_schedule_re'] = $hostUsageSituationDetailsInSameEnv[$company_id]['storage_schedule'];
                // storage_sum
                // 合計値（MB）
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_sum'] =
                    $hostUsageSituationDetailsInSameEnv[$company_id]['storage_stamp']
                    + $hostUsageSituationDetailsInSameEnv[$company_id]['storage_document']
                    + $hostUsageSituationDetailsInSameEnv[$company_id]['storage_operation_history']
                    + $hostUsageSituationDetailsInSameEnv[$company_id]['storage_mail']
                    + $hostUsageSituationDetailsInSameEnv[$company_id]['storage_attachment']
                    + $hostUsageSituationDetailsInSameEnv[$company_id]['storage_bbs_file_size']
                    + $hostUsageSituationDetailsInSameEnv[$company_id]['storage_convenient_file']
                    + $hostUsageSituationDetailsInSameEnv[$company_id]['storage_schedule'];
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_sum_re'] = $hostUsageSituationDetailsInSameEnv[$company_id]['storage_sum'];
                // storage_rate
                // 合計値　÷　契約数　※１契約１GB （%）
                if ($hostUsageSituationDetailsInSameEnv[$company_id]['user_count_valid'] == 0){
                    $hostUsageSituationDetailsInSameEnv[$company_id]['storage_rate'] = 0;
                }else{
                    $hostUsageSituationDetailsInSameEnv[$company_id]['storage_rate'] =
                        $hostUsageSituationDetailsInSameEnv[$company_id]['storage_sum'] / $hostUsageSituationDetailsInSameEnv[$company_id]['user_count_valid'] / 1024 * 100;
                }
                $hostUsageSituationDetailsInSameEnv[$company_id]['storage_rate_re'] = $hostUsageSituationDetailsInSameEnv[$company_id]['storage_rate'];
            }
            // ゲスト企業（ホスト企業と別環境）
            foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value){
                foreach ($hostUsageSituationDetailsInDiffEnv[$key] as $company_id => $hostUsageSituationDetailInDiffEnv){
                    // stamp
                    // 容量　×　使用容量ベース（1.3）（MB）
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_stamp'] =
                        $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_stamp'] * $use_storage_base / (1024 * 1024);
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_stamp_re'] = $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_stamp'];
                    // document
                    // 容量　×　使用容量ベース（1.3）（MB）
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_document'] =
                        $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_document'] * $use_storage_base / (1024 * 1024);
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_document_re'] = $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_document'];
                    // operation_history
                    // 履歴件数　×　平均容量　×　使用容量ベース（1.3）（MB）
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_operation_history'] =
                        $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_operation_history'] * $per_operation_history_size * $use_storage_base / (1024 * 1024);
                    // mail_size
                    // メール件数　×　平均容量　×　使用容量ベース（1.3）（MB）
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_mail'] =
                        $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_mail'] * $per_mail_size * $use_storage_base / (1024 * 1024);
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_mail_re'] = $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_mail'];
                    // attachment
                    // 容量　×　使用容量ベース（1.3）（MB）
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_attachment'] =
                        $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_attachment'] * $use_storage_base / (1024 * 1024);
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_attachment_re'] = $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_attachment'];
                    // bbs_file_size
                    // 容量　×　使用容量ベース（1.3）（MB）
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_bbs_file_size'] =
                        $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_bbs_file_size'] * $use_storage_base / (1024 * 1024);
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_bbs_file_size_re'] = $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_bbs_file_size'];
                    // disk_mail
                    // 容量　×　使用容量ベース（1.3）（MB）
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_convenient_file'] =
                        $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_convenient_file'] * $use_storage_base / (1024 * 1024);
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_convenient_file_re'] = $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_convenient_file'];
                    // schedule
                    // スケジュールレコード数　×　平均容量　×　使用容量ベース（1.3）（MB）
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_schedule'] =
                        $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_schedule'] * $per_schedule_size * $use_storage_base / (1024 * 1024);
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_schedule_re'] = $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_schedule'];
                    // storage_sum
                    // 合計値（MB）
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_sum'] =
                        $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_stamp']
                        + $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_document']
                        + $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_operation_history']
                        + $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_mail']
                        + $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_attachment']
                        + $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_bbs_file_size']
                        + $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_convenient_file']
                        + $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_schedule'];
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_sum_re'] = $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_sum'];
                    // storage_rate
                    // 合計値　÷　契約数　※１契約１GB （%）
                    if ($hostUsageSituationDetailsInDiffEnv[$key][$company_id]['user_count_valid'] == 0){
                        $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_rate'] = 0;
                    }else{
                        $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_rate'] =
                            $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_sum'] / $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['user_count_valid'] / 1024 * 100;
                    }
                    $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_rate_re'] = $hostUsageSituationDetailsInDiffEnv[$key][$company_id]['storage_rate'];
                }
            }

            // 4) 印鑑数
            $totalStamps = DB::table('mst_assign_stamp')
                ->join('mst_user', function ($join){
                    $join->on('mst_user.id', 'mst_assign_stamp.mst_user_id')
                        ->where(function ($query0){
                            $query0->where(function ($query){

                                // 富士通(K5)以外場合、有効なユーザー
                                $query->whereIn('mst_user.state_flg',[AppUtils::STATE_VALID]);

                                if (config('app.fujitsu_company_id')){
                                    // 富士通会社指定ある場合、富士通以外会社統計（富士通会社別途統計）
                                    // 富士通会社指定なし場合、会社条件不要
                                    $query->where('mst_user.mst_company_id', '<>' , config('app.fujitsu_company_id'));
                                }
                            })
                                ->orWhere(function($query1){
                                    // 無効だが、今日無効したユーザー（今日統計されたことがある）
                                    $query1->whereIn('mst_user.state_flg', [AppUtils::STATE_INVALID,AppUtils::STATE_INVALID_NOPASSWORD]);
                                    $query1->where(DB::raw("DATE_FORMAT(mst_user.invalid_at, '%Y%m%d')"), Carbon::now()->format('Ymd'));

                                    if (config('app.fujitsu_company_id')){
                                        // 富士通会社指定ある場合、富士通以外会社統計（富士通会社別途統計）
                                        // 富士通会社指定なし場合、会社条件不要
                                        $query1->where('mst_user.mst_company_id', '<>' , config('app.fujitsu_company_id'));
                                    }
                                });
                        });
                })
                ->join('mst_company', 'mst_user.mst_company_id', '=', 'mst_company.id')
                ->leftJoin('mst_stamp', 'mst_assign_stamp.stamp_id', '=', 'mst_stamp.id')
                ->select(['mst_company.id as mst_company_id', 'mst_company.company_name as company_name','mst_company.company_name_kana as company_name_kana',
                    DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_COMPANY.', 1, 0)) as count_common_stamp'),
                    DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_DEPARTMENT.', 1, 0)) as count_department_stamp'),
                    DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_NORMAL.' AND mst_stamp.stamp_division = 1, 1, 0)) as count_mst_stamp_date'),
                    DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_NORMAL.' AND mst_stamp.stamp_division = 0, 1, 0)) as count_mst_stamp_name'),
                ])
                ->whereIn('mst_assign_stamp.state_flg',[AppUtils::STATE_VALID])
                ->where('mst_user.option_flg',AppUtils::USER_NORMAL)
                ->groupBy('mst_company.id', 'company_name', 'company_name_kana');

            if (config('app.fujitsu_company_id')){
                // 富士通会社指定ある場合、富士通会社追加統計
                $totalStamps = DB::table('mst_assign_stamp')
                    ->join('mst_user', function ($join){
                        $join->on('mst_user.id', 'mst_assign_stamp.mst_user_id')
                            ->where(function ($query0){
                                $query0->where(function ($query){

                                    // 富士通(K5)場合、
                                    // 有効でパスワードが設定してあるユーザー
                                    $query->where('mst_user.mst_company_id' , config('app.fujitsu_company_id'));
                                    $query->whereNotNull('mst_user.password_change_date');
                                    $query->whereIn('mst_user.state_flg',[AppUtils::STATE_VALID]);

                                })
                                    ->orWhere(function($query1){

                                        // 無効だが、今月無効したユーザー（今月統計されたことがある）
                                        $query1->where('mst_user.mst_company_id' , config('app.fujitsu_company_id'));
                                        $query1->whereNotNull('mst_user.password_change_date');
                                        $query1->whereIn('mst_user.state_flg', [AppUtils::STATE_INVALID,AppUtils::STATE_INVALID_NOPASSWORD]);
                                        $query1->where(DB::raw("DATE_FORMAT(mst_user.invalid_at, '%Y%m%d')"), Carbon::now()->format('Ymd'));

                                    });
                            });
                    })
                    ->join('mst_company', 'mst_user.mst_company_id', '=', 'mst_company.id')
                    ->leftJoin('mst_stamp', 'mst_assign_stamp.stamp_id', '=', 'mst_stamp.id')
                    ->select(['mst_company.id as mst_company_id', 'mst_company.company_name as company_name','mst_company.company_name_kana as company_name_kana',
                        DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_COMPANY.', 1, 0)) as count_common_stamp'),
                        DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_DEPARTMENT.', 1, 0)) as count_department_stamp'),
                        DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_NORMAL.' AND mst_stamp.stamp_division = 1, 1, 0)) as count_mst_stamp_date'),
                        DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_NORMAL.' AND mst_stamp.stamp_division = 0, 1, 0)) as count_mst_stamp_name'),
                    ])
                    ->whereIn('mst_assign_stamp.state_flg',[AppUtils::STATE_VALID])
                    ->where('mst_user.option_flg',AppUtils::USER_NORMAL)
                    ->groupBy('mst_company.id', 'company_name', 'company_name_kana')
                    ->union($totalStamps)
                    ->get();
            }else{
                // 富士通会社指定なし場合、上記統計結果そのまま利用
                $totalStamps = $totalStamps
                    ->get();
            }

//            $assignedConvenientUsersForStandard = DB::table('mst_assign_stamp')
//                ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
//                ->join('mst_company','mst_user.mst_company_id', '=', 'mst_company.id')
//                ->select('mst_user.mst_company_id','mst_assign_stamp.mst_user_id')
//                ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_CONVENIENT)
//                ->whereIn('mst_assign_stamp.state_flg', [AppUtils::STATE_VALID, AppUtils::STATE_WAIT_ACTIVE])
//                ->get()
//                ->toArray();
//
//            $assignedConvenientUsersForBusiness = DB::table('mst_assign_stamp')
//                ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
//                ->join('mst_company','mst_user.mst_company_id', '=', 'mst_company.id')
//                ->select('mst_user.mst_company_id','mst_assign_stamp.mst_user_id')
//                ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_CONVENIENT)
//                ->whereIn('mst_assign_stamp.state_flg', [AppUtils::STATE_VALID, AppUtils::STATE_WAIT_ACTIVE])
//                ->groupBy('mst_user.mst_company_id','mst_assign_stamp.mst_user_id')
//                ->get()
//                ->toArray();
            $assignedConvenientStampsCount = DB::table('mst_assign_stamp')
                ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
                ->join('mst_company','mst_user.mst_company_id', '=', 'mst_company.id')
                ->select('mst_user.mst_company_id','mst_assign_stamp.mst_user_id')
                ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_CONVENIENT)
                ->where('mst_assign_stamp.state_flg', AppUtils::STATE_VALID)
                ->where('mst_user.state_flg', AppUtils::STATE_VALID)
                ->where('mst_user.option_flg', AppUtils::USER_NORMAL)
                ->get()
                ->toArray();

            foreach ($totalStamps as $totalStamp){
                $convenientStampCount = 0 ;
                $mst_company_id = $totalStamp->mst_company_id;
//                if(key_exists($mst_company_id,$companyContractEdition)){
//                    if($companyContractEdition[$totalStamp->mst_company_id] == '0'){
//                        $convenientStampCount =  count(array_filter($assignedConvenientUsersForStandard,function ($s) use($mst_company_id){
//                            return $mst_company_id == $s->mst_company_id;
//                        }));
//                    }else{
//                        $convenientStampCount =  count(array_filter($assignedConvenientUsersForBusiness,function ($s) use($mst_company_id){
//                            return $mst_company_id == $s->mst_company_id;
//                        }));
//                    }
//                }
                if(key_exists($mst_company_id,$companyContractEdition)){
                    $convenientStampCount =  count(array_filter($assignedConvenientStampsCount,function ($s) use($mst_company_id){
                        return $mst_company_id == $s->mst_company_id;
                    }));
                }
                $total_stamp = $totalStamp->count_mst_stamp_name + $totalStamp->count_department_stamp + $totalStamp->count_mst_stamp_date + $totalStamp->count_common_stamp;
                // ホスト企業
                if (key_exists($totalStamp->mst_company_id, $usageSituationDetails)){

                    $usageSituationDetails[$totalStamp->mst_company_id]['stamp_count'] = $total_stamp;
                    $usageSituationDetails[$totalStamp->mst_company_id]['stamp_over_count'] = $total_stamp - $companies[$totalStamp->mst_company_id]->upper_limit;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($totalStamp->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$totalStamp->mst_company_id]['stamp_count'] = $total_stamp;
                    $hostUsageSituationDetailsInSameEnv[$totalStamp->mst_company_id]['stamp_over_count'] = $total_stamp - $companies[$totalStamp->mst_company_id]->upper_limit;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($totalStamp->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$totalStamp->mst_company_id]['stamp_count'] = $total_stamp;
                        $hostUsageSituationDetailsInDiffEnv[$key][$totalStamp->mst_company_id]['stamp_over_count'] = $total_stamp - $companies[$totalStamp->mst_company_id]->upper_limit;
                    }
                }
            }

            // 5) タイムスタンプ数
            // 本環境のタイムスタンプ情報を計算
            $totalTimestamps = DB::table('time_stamp_info')
                ->select(['mst_company_id', DB::raw('COUNT(id) as count_timestamp')])
                ->where(DB::raw("DATE_FORMAT(create_at, '%Y-%m-%d')"), '=', $targetDay)
                ->where('app_env', '=', config('app.pac_app_env'))
                ->where('contract_server', '=', config('app.pac_contract_server'))
                ->groupBy('mst_company_id')
                ->get();

            foreach ($totalTimestamps as $totalTimestamp){
                // ホスト企業
                if (key_exists($totalTimestamp->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$totalTimestamp->mst_company_id]['timestamp_count'] = $totalTimestamp->count_timestamp;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($totalTimestamp->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationsInSameEnv[$totalTimestamp->mst_company_id]['timestamp_count'] = $totalTimestamp->count_timestamp;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($totalTimestamp->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$totalTimestamp->mst_company_id]['timestamp_count'] = $totalTimestamp->count_timestamp;
                    }
                }
            }

            // 他環境のタイムスタンプ情報を計算
            // 全環境でループ
            foreach (explode(',', config('app.server_list')) as $key){
                $env = substr($key,0,1);
                $server = substr($key,1,strlen($key)-1);
                $local_env = config('app.pac_app_env');
                $local_server = config('app.pac_contract_server');

                // 本環境以外
                if ($env != $local_env || $server != $local_server){
                    $envClient = EnvApiUtils::getAuthorizeClient($env,$server,false);
                    if ($envClient){
                        $response = $envClient->get("timestamp/countByDayAndEnv?appEnv=$local_env&contractServer=$local_server"."&targetDay=$targetDay",[]);
                        if($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                            $totalTimestampOtherEnvs = json_decode($response->getBody())->data;
                            foreach ($totalTimestampOtherEnvs as $totalTimestampOtherEnv){
                                if (key_exists($totalTimestampOtherEnv->mst_company_id, $usageSituationDetails)){
                                    $usageSituationDetails[$totalTimestampOtherEnv->mst_company_id]['timestamp_count'] += $totalTimestampOtherEnv->count_timestamp;
                                }
                                if (key_exists($totalTimestampOtherEnv->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                                    $hostUsageSituationDetailsInSameEnv[$totalTimestampOtherEnv->mst_company_id]['timestamp_count'] += $totalTimestampOtherEnv->count_timestamp;
                                }
                                if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($totalTimestampOtherEnv->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                                    $hostUsageSituationDetailsInDiffEnv[$key][$totalTimestampOtherEnv->mst_company_id]['timestamp_count'] += $totalTimestampOtherEnv->count_timestamp;
                                }
                            }
                        }else{
                            Log::channel('cron-daily')->warning('Cannot countByMonthAndEnv');
                            Log::channel('cron-daily')->warning($response->getBody());
                        }
                    }else{
                        Log::channel('cron-daily')->warning('Cannot connect to Env Api');
                    }
                }
            }

            // 6) 回覧関連
            // 当日申請数
            $totalNewRequests = Circular::join('mst_user','circular.mst_user_id','=','mst_user.id')
                ->where(DB::raw("DATE_FORMAT(applied_date, '%Y-%m-%d')"), '=', $targetDay)
                ->where('circular.edition_flg',config('app.pac_contract_app'))
                ->where('circular.env_flg',config('app.pac_app_env'))
                ->where('circular.server_flg',config('app.pac_contract_server'))
                ->select(['mst_company_id', DB::raw('count(circular.id) as request_count')])
                ->groupBy('mst_company_id')
                ->get();
            foreach ($totalNewRequests as $totalNewRequest){
                // ホスト企業
                if (key_exists($totalNewRequest->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$totalNewRequest->mst_company_id]['circular_applied_count'] = $totalNewRequest->request_count;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($totalNewRequest->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$totalNewRequest->mst_company_id]['circular_applied_count'] = $totalNewRequest->request_count;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value) {
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($totalNewRequest->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$totalNewRequest->mst_company_id]['circular_applied_count'] = $totalNewRequest->request_count;
                    }
                }
            }

            // 当日完了数＋総時間＋完了率
            $totalCompRequests = Circular::join('mst_user','circular.mst_user_id','=','mst_user.id')
                ->where(DB::raw("DATE_FORMAT(completed_date, '%Y-%m-%d')"), '=', $targetDay)
                ->where('circular.edition_flg',config('app.pac_contract_app'))
                ->where('circular.env_flg',config('app.pac_app_env'))
                ->where('circular.server_flg',config('app.pac_contract_server'))
                ->select(['mst_company_id', DB::raw('count(circular.id) as request_count,sum((unix_timestamp(completed_date) -unix_timestamp(applied_date))/60) AS total_min')])
                ->groupBy('mst_company_id')
                ->get();
            foreach ($totalCompRequests as $totalCompRequest){
                // ホスト企業
                if (key_exists($totalCompRequest->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$totalCompRequest->mst_company_id]['circular_completed_count'] = $totalCompRequest->request_count;
                    $usageSituationDetails[$totalCompRequest->mst_company_id]['circular_completed_total_time'] = $totalCompRequest->total_min;

                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($totalCompRequest->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$totalCompRequest->mst_company_id]['circular_completed_count'] = $totalCompRequest->request_count;
                    $hostUsageSituationDetailsInSameEnv[$totalCompRequest->mst_company_id]['circular_completed_total_time'] = $totalCompRequest->total_min;

                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value) {
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($totalCompRequest->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$totalCompRequest->mst_company_id]['circular_completed_count'] = $totalCompRequest->request_count;
                        $hostUsageSituationDetailsInDiffEnv[$key][$totalCompRequest->mst_company_id]['circular_completed_total_time'] = $totalCompRequest->total_min;

                    }
                }
            }

            // 社外経由数（送信）
            $multi_comp_outs = InsertUsageSituationUtils::getCircularUsageDetail(AppUtils::CIRCULAR_OUTSIDE_SEND_COUNT, $now->toString(), $targetDay);

            foreach ($multi_comp_outs as $multi_comp_out){
                // ホスト企業
                if (key_exists($multi_comp_out->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$multi_comp_out->mst_company_id]['multi_comp_out'] = $multi_comp_out->request_count;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($multi_comp_out->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$multi_comp_out->mst_company_id]['multi_comp_out'] = $multi_comp_out->request_count;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value) {
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($multi_comp_out->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$multi_comp_out->mst_company_id]['multi_comp_out'] = $multi_comp_out->request_count;
                    }
                }
            }

            // 社外経由数（受信）
            $multi_comp_ins = InsertUsageSituationUtils::getCircularUsageDetail(AppUtils::CIRCULAR_OUTSIDE_RECEIVE_COUNT, $now->toString(), $targetDay);

            foreach ($multi_comp_ins as $multi_comp_in){
                // ホスト企業
                if (key_exists($multi_comp_in->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$multi_comp_in->mst_company_id]['multi_comp_in'] = $multi_comp_in->request_count;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($multi_comp_in->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$multi_comp_in->mst_company_id]['multi_comp_in'] = $multi_comp_in->request_count;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value) {
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($multi_comp_in->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$multi_comp_in->mst_company_id]['multi_comp_in'] = $multi_comp_in->request_count;
                    }
                }
            }

            // アップロード数
            // PDF
            $upload_pdf_counts = DB::table('operation_history')
                ->join('mst_user','operation_history.user_id','=','mst_user.id')
                ->where(DB::raw("DATE_FORMAT(operation_history.create_at, '%Y-%m-%d')"), '=', $targetDay)
                ->where('auth_flg', '=', 1) // 利用者
                ->where('mst_operation_id', '=', 108) // アップロード
                ->where(function($query){
                    // pdf
                    $query->where('detail_info', 'like', '%.pdf%');
                    $query->orWhere('detail_info', 'like', '%.PDF%');
                })
                ->select(['mst_company_id', DB::raw('count(operation_history.id) as request_count')])
                ->groupBy('mst_company_id')
                ->get();

            foreach ($upload_pdf_counts as $upload_pdf_count){
                // ホスト企業
                if (key_exists($upload_pdf_count->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$upload_pdf_count->mst_company_id]['upload_count_pdf'] = $upload_pdf_count->request_count;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($upload_pdf_count->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$upload_pdf_count->mst_company_id]['upload_count_pdf'] = $upload_pdf_count->request_count;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value) {
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($upload_pdf_count->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$upload_pdf_count->mst_company_id]['upload_count_pdf'] = $upload_pdf_count->request_count;
                    }
                }
            }

            // EXCEL
            $upload_excel_counts = DB::table('operation_history')
                ->join('mst_user','operation_history.user_id','=','mst_user.id')
                ->where(DB::raw("DATE_FORMAT(operation_history.create_at, '%Y-%m-%d')"), '=', $targetDay)
                ->where('auth_flg', '=', 1) // 利用者
                ->where('mst_operation_id', '=', 108) // アップロード
                ->where(function($query){
                    // EXCEL
                    $query->Where('detail_info', 'like', '%.xls%');
                    $query->orWhere('detail_info', 'like', '%.XLS%');
                })
                ->select(['mst_company_id', DB::raw('count(operation_history.id) as request_count')])
                ->groupBy('mst_company_id')
                ->get();

            foreach ($upload_excel_counts as $upload_excel_count){
                // ホスト企業
                if (key_exists($upload_excel_count->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$upload_excel_count->mst_company_id]['upload_count_excel'] = $upload_excel_count->request_count;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($upload_excel_count->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$upload_excel_count->mst_company_id]['upload_count_excel'] = $upload_excel_count->request_count;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value) {
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($upload_excel_count->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$upload_excel_count->mst_company_id]['upload_count_excel'] = $upload_excel_count->request_count;
                    }
                }
            }

            // WORD
            $upload_word_counts = DB::table('operation_history')
                ->join('mst_user','operation_history.user_id','=','mst_user.id')
                ->where(DB::raw("DATE_FORMAT(operation_history.create_at, '%Y-%m-%d')"), '=', $targetDay)
                ->where('auth_flg', '=', 1) // 利用者
                ->where('mst_operation_id', '=', 108) // アップロード
                ->where(function($query){
                    // WORD
                    $query->Where('detail_info', 'like', '%.doc%');
                    $query->orWhere('detail_info', 'like', '%.DOC%');
                })
                ->select(['mst_company_id', DB::raw('count(operation_history.id) as request_count')])
                ->groupBy('mst_company_id')
                ->get();

            foreach ($upload_word_counts as $upload_word_count){
                // ホスト企業
                if (key_exists($upload_word_count->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$upload_word_count->mst_company_id]['upload_count_word'] = $upload_word_count->request_count;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($upload_word_count->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$upload_word_count->mst_company_id]['upload_count_word'] = $upload_word_count->request_count;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value) {
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($upload_word_count->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$upload_word_count->mst_company_id]['upload_count_word'] = $upload_word_count->request_count;
                    }
                }
            }


            // ダウンロード数
            $download_counts = DB::table('operation_history')
                ->join('mst_user','operation_history.user_id','=','mst_user.id')
                ->where(DB::raw("DATE_FORMAT(operation_history.create_at, '%Y-%m-%d')"), '=', $targetDay)
                ->where('auth_flg', '=', 1) // 利用者
                ->where('mst_operation_id', '=', 175) // ダウンロード
                ->select(['mst_company_id', DB::raw('count(operation_history.id) as request_count')])
                ->groupBy('mst_company_id')
                ->get();

            foreach ($download_counts as $download_count){
                // ホスト企業
                if (key_exists($download_count->mst_company_id, $usageSituationDetails)){
                    $usageSituationDetails[$download_count->mst_company_id]['download_count_pdf'] = $download_count->request_count;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($download_count->mst_company_id, $hostUsageSituationDetailsInSameEnv)){
                    $hostUsageSituationDetailsInSameEnv[$download_count->mst_company_id]['download_count_pdf'] = $download_count->request_count;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationDetailsInDiffEnv as $key => $value) {
                    if (isset($hostUsageSituationDetailsInDiffEnv[$key]) && key_exists($download_count->mst_company_id, $hostUsageSituationDetailsInDiffEnv[$key])){
                        $hostUsageSituationDetailsInDiffEnv[$key][$download_count->mst_company_id]['download_count_pdf'] = $download_count->request_count;
                    }
                }
            }


            // DB insert
            // 本環境　ホスト企業　ーー　当日
            DB::table('usage_situation_detail')
                ->where('target_date', $targetDay)
                ->whereNull('guest_company_id')
                ->delete();

            // データ量多すぎを防ぐため、分割
            $usageSituationDetailsLst = array_chunk($usageSituationDetails,$limit);
            foreach ($usageSituationDetailsLst as $usageSituationDetailsEach){
                DB::table('usage_situation_detail')
                    ->insert($usageSituationDetailsEach);
            }
            //stamp_info集計完了
            DB::table('stamp_info')->where('status',AppUtils::STAMP_NOT_COLLECT)->update(['status' => AppUtils::STAMP_COLLECTED]);
            DB::table('assign_stamp_info')->where('status',AppUtils::STAMP_NOT_COLLECT)->update(['status' => AppUtils::STAMP_COLLECTED]);
            // 本環境　ゲスト企業＋ホスト企業が本環境
            if (count($hostUsageSituationDetailsInSameEnv)){

                DB::table('usage_situation_detail')
                    ->where('target_date', $targetDay)
                    ->whereNotNull('guest_company_id')
                    ->where('guest_company_app_env', config('app.pac_app_env'))
                    ->where('guest_company_contract_server', config('app.pac_contract_server'))
                    ->delete();

                // データ量多すぎを防ぐため、分割
                $hostUsageSituationDetailsInSameEnvLst = array_chunk($hostUsageSituationDetailsInSameEnv,$limit);
                foreach ($hostUsageSituationDetailsInSameEnvLst as $hostUsageSituationDetailsInSameEnvEach){
                    DB::table('usage_situation_detail')
                        ->insert($hostUsageSituationDetailsInSameEnvEach);
                }
            }


            // 全環境でループ
            foreach (explode(',', config('app.server_list')) as $key) {
                $env = substr($key, 0, 1);
                $server = substr($key, 1, strlen($key)-1);
                // 本環境以外
                if ($env != config('app.pac_app_env') || $server != config('app.pac_contract_server')){
                    $envClient = EnvApiUtils::getAuthorizeClient($env,$server,false);

                    if ($envClient){
                        // usage_situation
                        if (isset($hostUsageSituationDetailsInDiffEnv[$key])){
                            $response = $envClient->post("usage-situation-detail",[
                                RequestOptions::JSON => [
                                    'usage_situation_details' => $hostUsageSituationDetailsInDiffEnv[$key],
                                    'target_date' => $targetDay
                                ],
                            ]);
                            if($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                                Log::channel('cron-daily')->warning('Cannot store transfer usage situation');
                                Log::channel('cron-daily')->warning($response->getBody());
                            }
                        }
                    }
                }
            }
            Log::channel('cron-daily')->debug('Insert InsertUsageSituationDetail finished');
        }catch(\Exception $e){
            Log::channel('cron-daily')->error('Run to InsertUsageSituationDetail failed');
            Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
        }
    }
}
