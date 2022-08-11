<?php

namespace App\Console\Commands;
use App\Http\Utils\AppUtils;
use App\Http\Utils\InsertUsageSituationUtils;
use App\Http\Utils\MailUtils;
use App\Models\UsageSituation;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\EnvApiUtils;
use GuzzleHttp\RequestOptions;
use App\Models\Circular;
use App\Models\UsagesDaily;
use App\Models\UsagesRange;


class InsertUsageSituation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usage_situation:insert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert Usage situation';

    private $coefficient = 1.3;

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
        Log::channel('cron-daily')->debug("Insert InsertUsageSituation start");

        try{
            $now = Carbon::now();
            $targetMonth = $now->format('Ym');
            $targetDay = $now->format('Y-m-d');
            $todayStart = Carbon::today();
            $todayEnd = Carbon::tomorrow();
            $rangeStart = [1=>Carbon::today()->subMonth(1)->addDays(1), 3=>Carbon::today()->subMonth(3)->addDays(1), 6=>Carbon::today()->subMonth(6)->addDays(1)];
            $rangeEnd = Carbon::tomorrow();
            $rank_counts = 10; // ファイル容量が多い利用者(TOP10)
            $range_months = [1,3,6]; // 対象期間(1:１ヶ月|3:３ヶ月|6:６ヶ月)
//            $app_env = config('app.pac_app_env');
            $limit = 100; // 毎回テーブル登録レコード数

            $companies = DB::table('mst_company')
                ->select(['id', 'guest_company_flg', 'company_name', 'company_name_kana', 'mst_company_id as host_company_id',
                    'host_app_env', 'host_contract_server', 'domain','contract_edition','timestamps_count','option_contract_flg','option_contract_count','upper_limit','stamp_flg', 'state', 'timestamp_notified_flg', 'convenient_upper_limit'])
                ->get()->keyBy('id');

            $usageSituations = [];
            $companyContractEdition = [];
            $hostUsageSituationsInSameEnv = [];
            $hostUsageSituationsInDiffEnv = [];
            // ファイル容量
            $usagesRange = [];
            $hostUsagesRangeInSameEnv = [];
            $hostUsagesRangeInDiffEnv = [];
            // 日別申請件数
            $usagesDaily = [];
            $hostUsagesDailyInSameEnv = [];
            $hostUsagesDailyInDiffEnv = [];

            $timestampsNotifyCompanyList = [];

            $companyConfigList = [];

            $guest_users = DB::table('guest_user')
                ->select(['id','email','create_company_id'])
                ->where(function($query) use ($targetMonth){
                    $query->where(DB::raw("DATE_FORMAT(create_at, '%Y%m')"), '>=', $targetMonth);
                })
                ->get();
            $guest_users_count_by_company = $guest_users->groupBy('create_company_id')->toArray();
            // init
            foreach ($companies as $company){
                $same_domains = preg_split('/\r\n|\r|\n/', $company->domain);
                if(isset($guest_users_count_by_company[$company->id])) {
                    $guest_users_company_id = $guest_users_count_by_company[$company->id];
                } else {
                    $guest_users_company_id = [];
                }
                $guest_users_company_id = collect($guest_users_company_id);
                $same_domain_number = $guest_users_company_id->filter(function ($guest_user, $key) use ($same_domains) {
                    $explode_domain = explode('@', $guest_user->email);
                    $guest_user_domain = '@'.array_pop($explode_domain);
                    return in_array($guest_user_domain, $same_domains);
                })->count();
                $usageSituations[$company->id] = ['mst_company_id' => $company->id,
                    'target_month' => $targetMonth,
                    'company_name' => $company->company_name,
                    'company_name_kana' => $company->company_name_kana,
                    'user_total_count' => 0,
                    'total_name_stamp' => 0,
                    'total_date_stamp' => 0,
                    'total_common_stamp' => 0,
                    'total_time_stamp' => 0,
                    'max_date' => Carbon::today()->format('Y/m/d'),
                    'storage_use_capacity' =>0,
                    'create_user' => 'System',
                    'create_at' => $now,
                    'guest_company_id' => null,
                    'guest_company_name' => null,
                    'guest_company_name_kana' => null,
                    'guest_company_app_env' => 0,
                    'guest_company_contract_server' => 0,
                    'same_domain_number' => $same_domain_number?$same_domain_number:0,
                    'guest_user_total_count' => $guest_users_company_id->count(),
                    'total_valid_stamp' => 0,
                    'timestamps_count' => $company->timestamps_count,
                    'convenient_upper_limit' => $company->convenient_upper_limit,
                    'total_convenient_stamp' => 0,
                    'total_contract_count' =>  $company->option_contract_flg ? $company->option_contract_count : $company->upper_limit,
                    'total_option_contract_count' => $company->option_contract_count,
                ];
                // usages_daily
                $usagesDaily[$company->id] = [
                    'mst_company_id' => $company->id,
                    'date' => $targetDay,
                    'company_name' => $company->company_name,
                    'company_name_kana' => $company->company_name_kana,
                    'new_requests' => 0,
                    'create_at' => $now,
                    'guest_company_id' => null,
                    'guest_company_name' => null,
                    'guest_company_name_kana' => null,
                    'guest_company_app_env' => 0,
                    'guest_company_contract_server' => 0,
                ];

                $companyContractEdition[$company->id] = $company->contract_edition;

                if ($company->guest_company_flg){
                    // ゲスト企業
                    if ($company->host_app_env == config('app.pac_app_env') && $company->host_contract_server == config('app.pac_contract_server')){
                        //　ゲスト／ホスト企業が同環境
                        $hostUsageSituationsInSameEnv[$company->id] = ['mst_company_id' => $company->host_company_id,
                            'target_month' => $targetMonth,
                            'company_name' => $companies[$company->host_company_id]->company_name,
                            'company_name_kana' => $companies[$company->host_company_id]->company_name_kana,
                            'user_total_count' => 0,
                            'total_name_stamp' => 0,
                            'total_date_stamp' => 0,
                            'total_common_stamp' => 0,
                            'total_time_stamp' => 0,
                            'max_date' => Carbon::today()->format('Y/m/d'),
                            'storage_use_capacity' =>0,
                            'same_domain_number' => 0,
                            'create_user' => 'System',
                            'create_at' => $now,
                            'guest_company_id' => $company->id,
                            'guest_company_name' => $company->company_name,
                            'guest_company_name_kana' => $company->company_name_kana,
                            'guest_company_app_env' => config('app.pac_app_env'),
                            'guest_company_contract_server' => config('app.pac_contract_server'),
                            'total_valid_stamp' => 0,
                            'timestamps_count' => $company->timestamps_count,
                            'convenient_upper_limit' => $company->convenient_upper_limit,
                            'total_convenient_stamp' => 0,
                            'total_contract_count' =>  $company->option_contract_flg ? $company->option_contract_count : $company->upper_limit,
                            'total_option_contract_count' => $company->option_contract_count,
                        ];
                        // usages_daily  host company in same env
                        $hostUsagesDailyInSameEnv[$company->id] = [
                            'mst_company_id' => $company->host_company_id,
                            'date' => $targetDay,
                            'company_name' => $companies[$company->host_company_id]->company_name,
                            'company_name_kana' => $companies[$company->host_company_id]->company_name_kana,
                            'new_requests' => 0,
                            'create_at' => $now,
                            'guest_company_id' => $company->id,
                            'guest_company_name' => $company->company_name,
                            'guest_company_name_kana' => $company->company_name_kana,
                            'guest_company_app_env' => config('app.pac_app_env'),
                            'guest_company_contract_server' => config('app.pac_contract_server'),
                        ];
                    }else{
                        //　ゲスト／ホスト企業が別環境
                        $hostUsageSituationsInDiffEnv[$company->host_app_env.$company->host_contract_server][$company->id] = ['mst_company_id' => $company->host_company_id,
                            'user_total_count' => 0,
                            'total_name_stamp' => 0,
                            'total_date_stamp' => 0,
                            'total_common_stamp' => 0,
                            'total_time_stamp' => 0,
                            'max_date' => Carbon::today()->format('Y/m/d'),
                            'storage_use_capacity' =>0,
                            'same_domain_number' => 0,
                            'create_user' => 'System',
                            'create_at' => $now,
                            'guest_company_id' => $company->id,
                            'guest_company_name' => $company->company_name,
                            'guest_company_name_kana' => $company->company_name_kana,
                            'guest_company_app_env' => config('app.pac_app_env'),
                            'guest_company_contract_server' => config('app.pac_contract_server'),
                            'total_valid_stamp' => 0,
                            'timestamps_count' => $company->timestamps_count,
                            'convenient_upper_limit' => $company->convenient_upper_limit,
                            'total_convenient_stamp' => 0,
                            'total_contract_count' =>  $company->option_contract_flg ? $company->option_contract_count : $company->upper_limit,
                            'total_option_contract_count' => $company->option_contract_count,
                        ];
                        // usages_daily in diff env
                        $hostUsagesDailyInDiffEnv[$company->host_app_env.$company->host_contract_server][$company->id] = [
                            'mst_company_id' => $company->host_company_id,
                            'date' => $targetDay,
                            'new_requests' => 0,
                            'create_at' => $now,
                            'guest_company_id' => $company->id,
                            'guest_company_name' => $company->company_name,
                            'guest_company_name_kana' => $company->company_name_kana,
                            'guest_company_app_env' => config('app.pac_app_env'),
                            'guest_company_contract_server' => config('app.pac_contract_server'),
                        ];
                    }
                }
                if ($company->stamp_flg === 1 && $company->guest_company_flg === 0 && $company->timestamps_count > 0
                    && $company->contract_edition !== AppUtils::CONTRACT_EDITION_TRIAL) {
                    $timestampsNotifyCompanyList[$company->id] = clone $company;
                }
                /*$companyConfigList[$company->id] = [
                    'option_contract_flg' => $company->option_contract_flg,
                    'option_contract_count' => $company->option_contract_count,
                ];*/
            }

            // オプション利用者総数
            /*$optionUsers = DB::table('mst_user')
                ->select(['mst_company_id', DB::raw('COUNT(mst_user.id) as count_user')])
                ->where(function ($query){

                    // 富士通(K5)以外場合、有効なユーザー
                    $query->where('state_flg',AppUtils::STATE_VALID);
                    $query->where('option_flg',AppUtils::USER_OPTION);

                    if (config('app.fujitsu_company_id')){
                        // 富士通会社指定ある場合、富士通以外会社統計（富士通会社別途統計）
                        // 富士通会社指定なし場合、会社条件不要
                        $query->where('mst_company_id', '<>' , config('app.fujitsu_company_id'));
                    }
                })
                ->groupBy('mst_company_id');

            if (config('app.fujitsu_company_id')){
                // 富士通会社指定ある場合、富士通会社追加統計
                $optionUsers = DB::table('mst_user')
                    ->select(['mst_company_id', DB::raw('COUNT(mst_user.id) as count_user')])
                    ->where('mst_company_id' , config('app.fujitsu_company_id'))
                    ->where(function ($query){
                        // 富士通(K5)場合、
                        // 有効でパスワードが設定してあるユーザー
                        $query->whereNotNull('password_change_date');
                        $query->where('state_flg',AppUtils::STATE_VALID);
                        $query->where('option_flg',AppUtils::USER_OPTION);

                    })
                    // 削除したユーザー集計対象外
                    ->groupBy('mst_company_id')
                    ->union($optionUsers)
                    ->get();
            }else{
                // 富士通会社指定なし場合、上記統計結果そのまま利用
                $optionUsers = $optionUsers
                    ->get();
            }

            foreach ($optionUsers as $optionUser){
                // ホスト企業
                if (array_key_exists($optionUser->mst_company_id, $usageSituations)){
                    if (array_key_exists($optionUser->mst_company_id, $companyConfigList) && $companyConfigList[$optionUser->mst_company_id]['option_contract_flg'] == 1 && $optionUser->count_user > 0){
                        $usageSituations[$optionUser->mst_company_id]['total_contract_count'] = $companyConfigList[$optionUser->mst_company_id]['option_contract_count'];
                    }
                }
                // ゲスト企業（ホスト企業と同環境）
                if (array_key_exists($optionUser->mst_company_id, $hostUsageSituationsInSameEnv)){
                    if (array_key_exists($optionUser->mst_company_id, $companyConfigList) && $companyConfigList[$optionUser->mst_company_id]['option_contract_flg'] == 1 && $optionUser->count_user > 0){
                        $hostUsageSituationsInSameEnv[$optionUser->mst_company_id]['total_contract_count'] = $companyConfigList[$optionUser->mst_company_id]['option_contract_count'];
                    }
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationsInDiffEnv[$key]) && array_key_exists($optionUser->mst_company_id, $hostUsageSituationsInDiffEnv[$key])){
                        if (array_key_exists($optionUser->mst_company_id, $companyConfigList) && $companyConfigList[$optionUser->mst_company_id]['option_contract_flg'] == 1 && $optionUser->count_user > 0){
                            $hostUsageSituationsInDiffEnv[$key][$optionUser->mst_company_id]['total_contract_count'] = $companyConfigList[$optionUser->mst_company_id]['option_contract_count'];
                        }
                    }
                }
            }*/

            $totalUsers = DB::table('mst_user')
                ->select(['mst_company_id', DB::raw('COUNT(mst_user.id) as count_user')])
                ->where(function ($query){

                    // 富士通(K5)以外場合、有効なユーザー
                    $query->where('state_flg',AppUtils::STATE_VALID);
                    $query->where('option_flg',AppUtils::USER_NORMAL);

                    if (config('app.fujitsu_company_id')){
                        // 富士通会社指定ある場合、富士通以外会社統計（富士通会社別途統計）
                        // 富士通会社指定なし場合、会社条件不要
                        $query->where('mst_company_id', '<>' , config('app.fujitsu_company_id'));
                    }
                })
//                ->orWhere(function($query1){
                    //PAC_3-236前日有効ユーザー数
                    // 無効だが、今日無効にしたユーザー（今日統計されたことがある）
//                    $query1->whereIn('state_flg', [AppUtils::STATE_INVALID,AppUtils::STATE_INVALID_NOPASSWORD]);
//                    $query1->where(DB::raw("DATE_FORMAT(invalid_at, '%Y%m%d')"), Carbon::now()->format('Ymd'));

//                    if (config('app.fujitsu_company_id')){
//                        // 富士通会社指定ある場合、富士通以外会社統計（富士通会社別途統計）
//                        // 富士通会社指定なし場合、会社条件不要
//                        $query1->where('mst_company_id', '<>' , config('app.fujitsu_company_id'));
//                    }
//                })
                // 削除したユーザー集計対象外
//                ->orWhere(function($query2){
//                    // 削除だが、今日削除したユーザー（今日統計されたことがある）
//                    $query2->where('state_flg', AppUtils::STATE_DELETE);
//                    $query2->where(DB::raw("DATE_FORMAT(delete_at, '%Y%m%d')"), Carbon::now()->format('Ymd'));
//
//                    if (config('app.fujitsu_company_id')){
//                        // 富士通会社指定ある場合、富士通以外会社統計（富士通会社別途統計）
//                        // 富士通会社指定なし場合、会社条件不要
//                        $query2->where('mst_company_id', '<>' , config('app.fujitsu_company_id'));
//                    }
//                })
                ->groupBy('mst_company_id');

            if (config('app.fujitsu_company_id')){
                // 富士通会社指定ある場合、富士通会社追加統計
                $totalUsers = DB::table('mst_user')
                    ->select(['mst_company_id', DB::raw('COUNT(mst_user.id) as count_user')])
                    ->where('mst_company_id' , config('app.fujitsu_company_id'))
                    ->where(function ($query){
                        // 富士通(K5)場合、
                        // 有効でパスワードが設定してあるユーザー
                        $query->whereNotNull('password_change_date');
                        $query->where('state_flg',AppUtils::STATE_VALID);
                        $query->where('option_flg',AppUtils::USER_NORMAL);

                    })
                    ->orWhere(function($query1){
                        //PAC_3-236前日有効ユーザー数
                        // 無効だが、今日無効したユーザー（今日統計されたことがある）
//                        $query1->whereNotNull('password_change_date');
//                        $query1->whereIn('state_flg', [AppUtils::STATE_INVALID,AppUtils::STATE_INVALID_NOPASSWORD]);
//                        $query1->where(DB::raw("DATE_FORMAT(invalid_at, '%Y%m%d')"), Carbon::now()->format('Ymd'));
                    })
                    // 削除したユーザー集計対象外
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
                if (key_exists($totalUser->mst_company_id, $usageSituations)){
                    $usageSituations[$totalUser->mst_company_id]['user_total_count'] = $totalUser->count_user;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($totalUser->mst_company_id, $hostUsageSituationsInSameEnv)){
                    $hostUsageSituationsInSameEnv[$totalUser->mst_company_id]['user_total_count'] = $totalUser->count_user;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationsInDiffEnv[$key]) && key_exists($totalUser->mst_company_id, $hostUsageSituationsInDiffEnv[$key])){
                        $hostUsageSituationsInDiffEnv[$key][$totalUser->mst_company_id]['user_total_count'] = $totalUser->count_user;
                    }
                }
            }

            //PAC_3-236 有効の印面の合計

            $totalValidStamps=DB::table('mst_assign_stamp')
                ->join("mst_user","mst_user.id","=","mst_assign_stamp.mst_user_id")
                ->join("mst_company","mst_company.id","=","mst_user.mst_company_id")
                ->whereIn("mst_assign_stamp.stamp_flg",[AppUtils::STAMP_FLG_NORMAL,AppUtils::STAMP_FLG_COMPANY,AppUtils::STAMP_FLG_DEPARTMENT])
                ->whereIn('mst_assign_stamp.state_flg',[AppUtils::STATE_VALID,AppUtils::STATE_WAIT_ACTIVE])
                ->where('mst_user.state_flg',"=",1)
                ->where('mst_user.option_flg',AppUtils::USER_NORMAL)
                ->select(['mst_user.mst_company_id',DB::raw('COUNT(*) as total')])
                ->groupBy('mst_user.mst_company_id')
                ->get()
                ->keyBy('mst_company_id');

            foreach ($totalValidStamps as $totalValidStampsCompany){
                // ホスト企業
                if (key_exists($totalValidStampsCompany->mst_company_id, $usageSituations)){
                    $usageSituations[$totalValidStampsCompany->mst_company_id]['total_valid_stamp'] = $totalValidStampsCompany->total;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($totalValidStampsCompany->mst_company_id, $hostUsageSituationsInSameEnv)){
                    $hostUsageSituationsInSameEnv[$totalValidStampsCompany->mst_company_id]['total_valid_stamp'] = $totalValidStampsCompany->total;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationsInDiffEnv[$key]) && key_exists($totalValidStampsCompany->mst_company_id, $hostUsageSituationsInDiffEnv[$key])){
                        $hostUsageSituationsInDiffEnv[$key][$totalValidStampsCompany->mst_company_id]['total_valid_stamp'] = $totalValidStampsCompany->total;
                    }
                }
            }

            // 本環境のタイムスタンプ情報を計算
            $totalTimestamps = DB::table('time_stamp_info')
                ->select(['mst_company_id', DB::raw('COUNT(id) as count_timestamp')])
                ->where(DB::raw("DATE_FORMAT(create_at, '%Y%m')"), '=', $targetMonth)
                ->where('app_env', '=', config('app.pac_app_env'))
                ->where('contract_server', '=', config('app.pac_contract_server'))
                ->groupBy('mst_company_id')
                ->get();

            foreach ($totalTimestamps as $totalTimestamp){
                // ホスト企業
                if (key_exists($totalTimestamp->mst_company_id, $usageSituations)){
                    $usageSituations[$totalTimestamp->mst_company_id]['total_time_stamp'] = $totalTimestamp->count_timestamp;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($totalTimestamp->mst_company_id, $hostUsageSituationsInSameEnv)){
                    $hostUsageSituationsInSameEnv[$totalTimestamp->mst_company_id]['total_time_stamp'] = $totalTimestamp->count_timestamp;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationsInDiffEnv[$key]) && key_exists($totalTimestamp->mst_company_id, $hostUsageSituationsInDiffEnv[$key])){
                        $hostUsageSituationsInDiffEnv[$key][$totalTimestamp->mst_company_id]['total_time_stamp'] = $totalTimestamp->count_timestamp;
                    }
                }
            }

            // 他環境のタイムスタンプ情報を計算
            foreach (explode(',', config('app.server_list')) as $key){
                $env = substr($key,0,1);
                $server = substr($key,1,strlen($key)-1);
                $local_env = config('app.pac_app_env');
                $local_server = config('app.pac_contract_server');

                // 本環境以外
                if ($env != $local_env || $server != $local_server){
                    $envClient = EnvApiUtils::getAuthorizeClient($env,$server,false);
                    if ($envClient){
                        $response = $envClient->get("timestamp/countByMonthAndEnv?appEnv=$local_env&contractServer=$local_server"."&targetMonth=$targetMonth",[]);
                        if($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                            $totalTimestampOtherEnvs = json_decode($response->getBody())->data;
                            foreach ($totalTimestampOtherEnvs as $totalTimestampOtherEnv){
                                if (key_exists($totalTimestampOtherEnv->mst_company_id, $usageSituations)){
                                    $usageSituations[$totalTimestampOtherEnv->mst_company_id]['total_time_stamp'] += $totalTimestampOtherEnv->count_timestamp;
                                }
                                if (key_exists($totalTimestampOtherEnv->mst_company_id, $hostUsageSituationsInSameEnv)){
                                    $hostUsageSituationsInSameEnv[$totalTimestampOtherEnv->mst_company_id]['total_time_stamp'] += $totalTimestampOtherEnv->count_timestamp;
                                }
                                if (isset($hostUsageSituationsInDiffEnv[$key]) && key_exists($totalTimestampOtherEnv->mst_company_id, $hostUsageSituationsInDiffEnv[$key])){
                                    $hostUsageSituationsInDiffEnv[$key][$totalTimestampOtherEnv->mst_company_id]['total_time_stamp'] += $totalTimestampOtherEnv->count_timestamp;
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
                                    //PAC_3-236前日有効ユーザー数
                                    // 無効だが、今日無効したユーザー（今日統計されたことがある）
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
                    DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_COMPANY .', 1, 0)) as count_common_stamp'),
                    DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_CONVENIENT .', 1, 0)) as count_convenient_stamp'),
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

                                    });
                            });
                    })
                    ->join('mst_company', 'mst_user.mst_company_id', '=', 'mst_company.id')
                    ->leftJoin('mst_stamp', 'mst_assign_stamp.stamp_id', '=', 'mst_stamp.id')
                    ->select(['mst_company.id as mst_company_id', 'mst_company.company_name as company_name','mst_company.company_name_kana as company_name_kana',
                        DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_COMPANY .', 1, 0)) as count_common_stamp'),
                        DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_CONVENIENT .', 1, 0)) as count_convenient_stamp'),
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

            foreach ($totalStamps as $totalStamp){
                // ホスト企業
                if (key_exists($totalStamp->mst_company_id, $usageSituations)){
                    $usageSituations[$totalStamp->mst_company_id]['total_name_stamp'] = $totalStamp->count_mst_stamp_name;
                    $usageSituations[$totalStamp->mst_company_id]['total_date_stamp'] = $totalStamp->count_department_stamp + $totalStamp->count_mst_stamp_date;
                    $usageSituations[$totalStamp->mst_company_id]['total_convenient_stamp'] = $totalStamp->count_convenient_stamp;
                    $usageSituations[$totalStamp->mst_company_id]['total_common_stamp'] = $totalStamp->count_common_stamp;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($totalStamp->mst_company_id, $hostUsageSituationsInSameEnv)){
                    $hostUsageSituationsInSameEnv[$totalStamp->mst_company_id]['total_name_stamp'] = $totalStamp->count_mst_stamp_name;
                    $hostUsageSituationsInSameEnv[$totalStamp->mst_company_id]['total_date_stamp'] = $totalStamp->count_department_stamp + $totalStamp->count_mst_stamp_date;
                    $hostUsageSituationsInSameEnv[$totalStamp->mst_company_id]['total_convenient_stamp'] = $totalStamp->count_convenient_stamp;
                    $hostUsageSituationsInSameEnv[$totalStamp->mst_company_id]['total_common_stamp'] = $totalStamp->count_common_stamp;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationsInDiffEnv[$key]) && key_exists($totalStamp->mst_company_id, $hostUsageSituationsInDiffEnv[$key])){
                        $hostUsageSituationsInDiffEnv[$key][$totalStamp->mst_company_id]['total_name_stamp'] = $totalStamp->count_mst_stamp_name;
                        $hostUsageSituationsInDiffEnv[$key][$totalStamp->mst_company_id]['total_date_stamp'] = $totalStamp->count_department_stamp + $totalStamp->count_mst_stamp_date;
                        $hostUsageSituationsInDiffEnv[$key][$totalStamp->mst_company_id]['total_convenient_stamp'] = $totalStamp->count_convenient_stamp;
                        $hostUsageSituationsInDiffEnv[$key][$totalStamp->mst_company_id]['total_common_stamp'] = $totalStamp->count_common_stamp;
                    }
                }
            }

            // 長期保管ディスク使用容量
            $totalStorages = DB::table('long_term_document')
                ->select(['mst_company_id', DB::raw('SUM(file_size) as storage_usage')])
                ->groupBy('mst_company_id')
                ->get();
            foreach ($totalStorages as $totalStorage){
                $usageStorage = round($totalStorage->storage_usage/1024 * $this->coefficient);

                // ホスト企業
                if (key_exists($totalStorage->mst_company_id, $usageSituations)){
                    $usageSituations[$totalStorage->mst_company_id]['storage_use_capacity'] = $usageStorage>0?$usageStorage:0;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($totalStorage->mst_company_id, $hostUsageSituationsInSameEnv)){
                    $hostUsageSituationsInSameEnv[$totalStorage->mst_company_id]['storage_use_capacity'] = $usageStorage>0?$usageStorage:0;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsageSituationsInDiffEnv as $key => $value){
                    if (isset($hostUsageSituationsInDiffEnv[$key]) && key_exists($totalStorage->mst_company_id, $hostUsageSituationsInDiffEnv[$key])){
                        $hostUsageSituationsInDiffEnv[$key][$totalStorage->mst_company_id]['storage_use_capacity'] = $usageStorage>0?$usageStorage:0;
                    }
                }
            }
            // 対象期間(1:１ヶ月|3:３ヶ月|6:６ヶ月)
            foreach ($range_months as $range_month){
                $user_usages = InsertUsageSituationUtils::getMonthsUsageSituation($range_month,$rangeStart[$range_month], $rangeEnd,$now->toString());
                $company_disk_usages = [];
                $user_usages->filter(function ($user_usage) use(&$company_disk_usages){
                    if ($company_disk_usages && isset($company_disk_usages[$user_usage->mst_company_id]) && count($company_disk_usages[$user_usage->mst_company_id]) > 10){
                        return false;
                    }
                    $company_disk_usages[$user_usage->mst_company_id][] =  $user_usage;
                });

                // ファイル容量が多い利用者
                foreach($companies as $company){
                    if (!isset($company_disk_usages[$company->id])) continue;
                    foreach ($company_disk_usages[$company->id] as $key => $disk_usage){
                        // ホスト企業
                        $usagesRange[$range_month.'-'.$disk_usage->mst_user_id] = [
                            'mst_company_id' => $company->id,
                            'email' => $disk_usage->email,
                            'company_name' => $companies[$company->id]->company_name,
                            'company_name_kana' => $companies[$company->id]->company_name_kana,
                            'disk_usage_rank' => $key + 1,
                            'range' => $range_month,
                            'disk_usage' => $disk_usage->datasize>0?$disk_usage->datasize:0,
                            'create_at' => $now,
                            'guest_company_id' => null,
                            'guest_company_name' => null,
                            'guest_company_name_kana' => null,
                            'guest_company_app_env' => 0,
                            'guest_company_contract_server' => 0,
                        ];

                        if ($company->guest_company_flg){
                            if ($company->host_app_env == config('app.pac_app_env') && $company->host_contract_server == config('app.pac_contract_server')){
                                // ゲスト企業（ホスト企業と同環境）
                                $hostUsagesRangeInSameEnv[$range_month.'-'.$disk_usage->mst_user_id] = [
                                    'mst_company_id' => $company->host_company_id,
                                    'company_name' => $companies[$company->host_company_id]->company_name,
                                    'company_name_kana' => $companies[$company->host_company_id]->company_name_kana,
                                    'email' => $disk_usage->email,
                                    'disk_usage_rank' => $key + 1,
                                    'range' => $range_month,
                                    'disk_usage' => $disk_usage->datasize>0?$disk_usage->datasize:0,
                                    'create_at' => $now,
                                    'guest_company_id' => $company->id,
                                    'guest_company_name' => $company->company_name,
                                    'guest_company_name_kana' => $company->company_name_kana,
                                    'guest_company_app_env' => config('app.pac_app_env'),
                                    'guest_company_contract_server' => config('app.pac_contract_server'),
                                ];
                            }else{
                                // ゲスト企業（ホスト企業と別環境）
                                $hostUsagesRangeInDiffEnv[$company->host_app_env.$company->host_contract_server][$range_month.'-'.$disk_usage->mst_user_id] = [
                                    'mst_company_id' => $company->host_company_id,
                                    'email' => $disk_usage->email,
                                    'disk_usage_rank' => $key + 1,
                                    'range' => $range_month,
                                    'disk_usage' => $disk_usage->datasize>0?$disk_usage->datasize:0,
                                    'create_at' => $now,
                                    'guest_company_id' => $company->id,
                                    'guest_company_name' => $company->company_name,
                                    'guest_company_name_kana' => $company->company_name_kana,
                                    'guest_company_app_env' => config('app.pac_app_env'),
                                    'guest_company_contract_server' => config('app.pac_contract_server'),
                                ];
                            }
                        }
                    }
                }
            }

            // 当日申請数
            $totalNewRequests = Circular::join('mst_user','circular.mst_user_id','=','mst_user.id')
                ->where('circular.create_at','>=',$todayStart)
                ->where('circular.create_at','<',$todayEnd)
                ->select(['mst_company_id', DB::raw('count(circular.id) as request_count')])
                ->groupBy('mst_company_id')
                ->get();
            foreach ($totalNewRequests as $totalNewRequest){
                // ホスト企業
                if (key_exists($totalNewRequest->mst_company_id, $usagesDaily)){
                    $usagesDaily[$totalNewRequest->mst_company_id]['new_requests'] = $totalNewRequest->request_count;
                }
                // ゲスト企業（ホスト企業と同環境）
                if (key_exists($totalNewRequest->mst_company_id, $hostUsagesDailyInSameEnv)){
                    $hostUsagesDailyInSameEnv[$totalNewRequest->mst_company_id]['new_requests'] = $totalNewRequest->request_count;
                }
                // ゲスト企業（ホスト企業と別環境）
                foreach ($hostUsagesDailyInDiffEnv as $key => $value) {
                    if (isset($hostUsagesDailyInDiffEnv[$key]) && key_exists($totalNewRequest->mst_company_id, $hostUsagesDailyInDiffEnv[$key])){
                        $hostUsagesDailyInDiffEnv[$key][$totalNewRequest->mst_company_id]['new_requests'] = $totalNewRequest->request_count;
                    }
                }
            }

            // usages_range
            UsageSituation::where('target_month', $targetMonth)->whereNull('guest_company_id')->delete();
            // データ量多すぎを防ぐため、分割
            $usageSituationsLst = array_chunk($usageSituations,$limit);
            foreach ($usageSituationsLst as $usageSituationsEach){
                UsageSituation::insert($usageSituationsEach);
            }

            // usages_range
            UsagesRange::whereNull('guest_company_id')->delete();
            // データ量多すぎを防ぐため、分割
            $usagesRangeLst = array_chunk($usagesRange,$limit);
            foreach ($usagesRangeLst as $usagesRangeEach){
                UsagesRange::insert($usagesRangeEach);
            }

            // usages_daily
            UsagesDaily::where('date',$targetDay)->whereNull('guest_company_id')->delete();
            // データ量多すぎを防ぐため、分割
            $usagesDailyLst = array_chunk($usagesDaily,$limit);
            foreach ($usagesDailyLst as $usagesDailyEach){
                UsagesDaily::insert($usagesDailyEach);
            }

            // 本環境　ゲスト企業＋ホスト企業が本環境
            // usage_situation
            if (count($hostUsageSituationsInSameEnv)){

                DB::table('usage_situation')
                    ->where('target_month', $targetMonth)
                    ->whereNotNull('guest_company_id')
                    ->where('guest_company_app_env', config('app.pac_app_env'))
                    ->where('guest_company_contract_server', config('app.pac_contract_server'))
                    ->delete();

                // データ量多すぎを防ぐため、分割
                $hostUsageSituationsInSameEnvLst = array_chunk($hostUsageSituationsInSameEnv,$limit);
                foreach ($hostUsageSituationsInSameEnvLst as $hostUsageSituationsInSameEnvEach){
                    UsageSituation::insert($hostUsageSituationsInSameEnvEach);
                }
            }

            // usages_range
            if (count($hostUsagesRangeInSameEnv)){

                UsagesRange::whereNotNull('guest_company_id')
                    ->where('guest_company_app_env', config('app.pac_app_env'))
                    ->where('guest_company_contract_server', config('app.pac_contract_server'))
                    ->delete();

                // データ量多すぎを防ぐため、分割
                $hostUsagesRangeInSameEnvLst = array_chunk($hostUsagesRangeInSameEnv,$limit);
                foreach ($hostUsagesRangeInSameEnvLst as $hostUsagesRangeInSameEnvEach){
                    UsagesRange::insert($hostUsagesRangeInSameEnvEach);
                }
            }

            // usages_daily
            if (count($hostUsagesDailyInSameEnv)){

                UsagesDaily::where('date', $targetDay)
                    ->whereNotNull('guest_company_id')
                    ->where('guest_company_app_env', config('app.pac_app_env'))
                    ->where('guest_company_contract_server', config('app.pac_contract_server'))
                    ->delete();

                // データ量多すぎを防ぐため、分割
                $hostUsagesDailyInSameEnvLst = array_chunk($hostUsagesDailyInSameEnv,$limit);
                foreach ($hostUsagesDailyInSameEnvLst as $hostUsagesDailyInSameEnvEach){
                    UsagesDaily::insert($hostUsagesDailyInSameEnvEach);
                }
            }

//            $serverEnvApi = config('app.server_env_api');
            // 全環境でループ
            foreach (explode(',', config('app.server_list')) as $key) {
//            foreach ($serverEnvApi as $key => $value){
                $env = substr($key, 0, 1);
                $server = substr($key, 1, strlen($key)-1);
                // 本環境以外
                if ($env != config('app.pac_app_env') || $server != config('app.pac_contract_server')){
                    $envClient = EnvApiUtils::getAuthorizeClient($env,$server,false);

                    if ($envClient){
                        // usage_situation
                        if (isset($hostUsageSituationsInDiffEnv[$key])){
                            $response = $envClient->post("usage-situation",[
                                RequestOptions::JSON => [
                                    'usage_situations' => $hostUsageSituationsInDiffEnv[$key],
                                    'target_month' => $targetMonth
                                ],
                            ]);
                            if($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                                Log::channel('cron-daily')->warning('Cannot store transfer usage situation');
                                Log::channel('cron-daily')->warning($response->getBody());
                            }
                        }

                        // usages_daily
                        if (isset($hostUsagesDailyInDiffEnv[$key])){
                            $response = $envClient->post("usages-daily",[
                                RequestOptions::JSON => [
                                    'usages_daily' => $hostUsagesDailyInDiffEnv[$key],
                                    'date' => $targetDay
                                ],
                            ]);
                            if($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                                Log::channel('cron-daily')->warning('Cannot store transfer usage daily');
                                Log::channel('cron-daily')->warning($response->getBody());
                            }
                        }

                        // usages_range
                        if (isset($hostUsagesRangeInDiffEnv[$key])){
                            $response = $envClient->post("usages-range",[
                                RequestOptions::JSON => [
                                    'usages_range' => $hostUsagesRangeInDiffEnv[$key]
                                ],
                            ]);
                            if($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                                Log::channel('cron-daily')->warning('Cannot store transfer usage range');
                                Log::channel('cron-daily')->warning($response->getBody());
                            }
                        }
                    }
                }
            }

            Log::channel('cron-daily')->debug('Send Timestamps Notify In InsertUsageSituation Start');

            $mail_data = [];
            $company_ids = [];
            foreach ($usageSituations as $mst_company_id => $item) {
                // ホスト企業
                if (array_key_exists($mst_company_id, $timestampsNotifyCompanyList)){
                    $notifyCompany = clone $timestampsNotifyCompanyList[$mst_company_id];
                    $used_timestamp = (int)$item['total_time_stamp'];
                    $timestamps_count = (int)$notifyCompany->timestamps_count;

                    if ($used_timestamp > 0){
                        $data = [
                            'mst_company_id' => $notifyCompany->id,
                            'company_name' => $notifyCompany->company_name,
                            'timestamps_count' => $timestamps_count - $used_timestamp,
                            'cloud_link' => config('app.timestamp_order_url')
                        ];

                        if (($timestamps_count - $used_timestamp) / $timestamps_count <= 0.2 && $notifyCompany->timestamp_notified_flg === 0) {
                            $data['type'] = 'less';
                            $company_ids[] = $notifyCompany->id;
                        } else {
                            continue;
                        }
                        $mail_data[] = $data;
                    }
                }
            }
            unset($data);
            unset($notifyCompany);

            if (!empty($mail_data)) {
                $admin_list = DB::table('mst_admin')->select('mst_company_id', 'family_name', 'given_name', 'email')
                    ->whereIn('mst_company_id', $company_ids)
                    ->where('role_flg', DB::raw(1))
                    ->where('state_flg', AppUtils::STATE_VALID)
                    ->get()
                    ->toArray();
                foreach ($admin_list as $admin_user) {
                    if (isset($timestampsNotifyCompanyList[$admin_user->mst_company_id])) {
                        $timestampsNotifyCompanyList[$admin_user->mst_company_id]->admin_user = clone $admin_user;
                    }
                }
                foreach ($mail_data as $data) {
                    $notifyCompany = clone $timestampsNotifyCompanyList[$data['mst_company_id']];
                    if (!isset($notifyCompany->admin_user) || empty($notifyCompany->admin_user) || !$notifyCompany->state) {
                        continue;
                    }
                    $data['admin_name'] = $notifyCompany->admin_user->family_name . $notifyCompany->admin_user->given_name;
                    if ($data['type'] === 'less'){
                        MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                            $notifyCompany->admin_user->email,
                            // メールテンプレート
                            MailUtils::MAIL_DICTIONARY['SEND_TIMESTAMPS_COUNT_LESS_REMIND_MAIL']['CODE'],
                            // パラメータ
                            json_encode($data, JSON_UNESCAPED_UNICODE),
                            // タイプ
                            AppUtils::MAIL_TYPE_ADMIN,
                            // 件名
                            config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.send_timestamps_count_less_remind_mail.subject'),
                            // メールボディ
                            trans('mail.send_timestamps_count_less_remind_mail.body', $data)
                        );

                        DB::table('mst_company')->where('id', $data['mst_company_id'])->update(['timestamp_notified_flg' => 1]);
                    }
                }
            }
            Log::channel('cron-daily')->debug('Send Timestamps Notify In InsertUsageSituation Finished');
            unset($timestampsNotifyCompanyList);
            Log::channel('cron-daily')->debug('Insert InsertUsageSituation finished');
        }catch(\Exception $e){
            Log::channel('cron-daily')->error('Run to InsertUsageSituation failed Or Timestamps Notify failed');
            Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
    }
}
