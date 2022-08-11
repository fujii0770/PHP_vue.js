<?php

namespace App\Http\Controllers\Reports;

use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\GwAppApiUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\InsertUsageSituationUtils;
use App\Http\Utils\PermissionUtils;
use App\Models\UsagesDaily;
use App\Models\UsageSituation;
use App\Models\UsageSituationDetail;
use App\Models\UsagesRange;
use App\Models\AssignStamp;
use App\Models\Stamp;
use App\Models\CompanyStamp;
use App\Models\Company;
use App\CompanyAdmin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Validator;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use App\Http\Utils\OperationsHistoryUtils;
use Session;
use App\Http\Utils\CircularUtils;

class ReportsUsageController extends AdminController
{

    public function __construct( )
    {
        parent::__construct();
    }

    /**
     * Display a setting for DateStamp
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = \Auth::user();
        $year = $request->get("year", date('Y'));
        $month = $request->get("month", date('m'));
        if ($year.$month.'01' === Carbon::now()->format('Ymd')) {
            $lastMonthDate = Carbon::createFromDate($year, $month, 1 )->addMonthsNoOverflow(-1);
            $year = $lastMonthDate->format('Y');
            $month = $lastMonthDate->format('m');
        }
        $range = $request->get("range", 6); // 初期化 デフォルト ６ヶ月
        $statistics_range = $request->get("statisticsRange", 0); // 初期化 デフォルト 過去30日間
        $mst_company_id = $user->hasRole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)?$request->get("company_id", null):$user->mst_company_id;
        // 無害化処理設定時はCSVダウンロード無効化するためのフラグ TODO 非同期化と無害化
        $sanitizing_flg = config('app.app_lgwan_flg');
        // total valid user
        $validUserTotal = DB::table('mst_user')->where('mst_company_id','=',$mst_company_id)->where('state_flg','=',AppUtils::STATE_VALID)->count();
        // total valid stamps
        $validStampsTotal = DB::table('mst_assign_stamp')
            ->join("mst_user","mst_user.id","=","mst_assign_stamp.mst_user_id")
            ->join("mst_company","mst_company.id","=","mst_user.mst_company_id")
            ->whereIn("mst_assign_stamp.stamp_flg",[AppUtils::STAMP_FLG_NORMAL,AppUtils::STAMP_FLG_COMPANY,AppUtils::STAMP_FLG_DEPARTMENT])
            ->whereIn('mst_assign_stamp.state_flg',[AppUtils::STATE_VALID,AppUtils::STATE_WAIT_ACTIVE])
            ->where('mst_user.state_flg',"=",AppUtils::STATE_VALID)
            ->where('mst_company.id',"=" ,$mst_company_id)
            ->count();
        $list_guest_company = DB::table('usage_situation')
            ->select('guest_company_id','guest_company_name')
            ->where('mst_company_id','=', $mst_company_id)->whereNotNull('guest_company_id')->distinct()->get();
        $company = Company::where('id', $user->mst_company_id)->first();
        if($list_guest_company->count() > 0) {
            // ホスト企業(ゲスト企業数＞0)
            $is_host_company = true;
            $this->assign('is_host_company', $is_host_company);
            $data = $this->_getDataReportNum($this->_getHostDataReport($month, $year,$mst_company_id, $mst_company_id, null));
            $summary_data=$this->_getHostSummaryData($month, $year, $mst_company_id, $mst_company_id, null,$statistics_range);

        } else {
            // 通常企業
            $is_host_company = false;
            $this->assign('is_host_company', $is_host_company);
            $data = $this->_getDataReportNum($this->_getDataReport($month, $year, $mst_company_id));
            $summary_data =  $this->_getSummaryData($month, $year, $mst_company_id,$statistics_range);

        }

       if($user->hasRole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $list_company = Company::select('id','company_name','guest_company_flg','usage_flg')->get();
            $this->assign('list_company', $list_company);
            $this->assign('mst_company_id', $mst_company_id );
            $this->assign('check_role_shachihata', true );
            $this->assign('show_longterm_storage', true );
       }else if($is_host_company){
           $hostCompany = Company::find($mst_company_id);
           $this->assign('host_company', $hostCompany->id);
           $this->assign('host_company_name', $hostCompany->company_name);
           $this->assign('host_company_usage_flg', $hostCompany->usage_flg);
           $this->assign('check_role_shachihata', false );
           $this->assign('list_guest_company', $list_guest_company );
           $this->assign('show_longterm_storage', $hostCompany->long_term_storage_flg );
       } else {
           $company = Company::find($mst_company_id);
           $this->assign('check_role_shachihata', false );
           $this->assign('show_longterm_storage', $company->long_term_storage_flg );
           $this->assign('company_id',$company->id);
           $this->assign('company_name',$company->company_name);
           $this->assign('company_usage_flg', $company->usage_flg);

       }
        $boolStampIsOver = 0;
        $strMessage = '';
        if(!$user->hasRole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            if($is_host_company){
                $data_chart = $this->_getHostDataChart($range,$user->mst_company_id, null, null);
            }else{
                $data_chart = $this->_getDataChart($range, $mst_company_id);
            }
            $arrCUTotal = Company::getCompanyStampLimitAndUserStampCount($user->mst_company_id);

            // 印面上限チェック：
            if ($company->old_contract_flg) {
                $mst_user_count = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)->where('state_flg', AppUtils::STATE_VALID)->count();
                //旧契約形態ON　&& Standarad ：上限がイセンス契約数
                //旧契約形態ON　&& Business、Business Pro、trial ：上限なし
                if ($company->contract_edition == 0 && $arrCUTotal['intUserStampCount'] > $arrCUTotal['intCompanyStampLimit']) {
                    $boolStampIsOver = 1;
                    $strMessage = sprintf(__("message.warning.stamp_limit"), $arrCUTotal['intUserStampCount'], $arrCUTotal['intCompanyStampLimit']);
                }
                //PAC_5-2476
                //旧契約形態ON　&& Bussiness : ユーザー数上限がライセンス契約数
                if ($company->contract_edition == 1 && $mst_user_count > $company->upper_limit) {
                    $boolStampIsOver = 1;
                    $strMessage = sprintf(__("message.warning.user_limit_biz"), $mst_user_count, $company->upper_limit);
                }
            } else {
                //旧契約形態OFF　&& Standarad、Business、Business Pro ：上限がイセンス契約数
                //旧契約形態OFF　&& trial ：上限なし
                if (in_array($company->contract_edition, [0, 1, 2]) && $arrCUTotal['intUserStampCount'] > $arrCUTotal['intCompanyStampLimit']) {
                    $boolStampIsOver = 1;
                    $strMessage = sprintf(__("message.warning.stamp_limit"), $arrCUTotal['intUserStampCount'], $arrCUTotal['intCompanyStampLimit']);
                }
            }

        }else{
            $data_chart = null;
        }
        $this->assign('validUserTotal',$validUserTotal);
        $this->assign('validStampsTotal',$validStampsTotal);
        $this->assign('intStampIsOver', (int)$boolStampIsOver);
        $this->assign('stringStampIsOverMessage', $strMessage);
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        $this->setMetaTitle('利用状況');
        $this->assign('data', $data);
        $this->assign('data_chart', $data_chart);
        $this->assign('summary_data',$summary_data);
        $this->assign('sanitizing_flg', $sanitizing_flg);
        $this->assign('use_angular', true);
        return $this->render('Reports.Usage.index');
    }

    public function search($year, $month,$statistics_range, $company_id = null){
        $user = \Auth::user();
        $mst_company_id = $user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)?$company_id:$user->mst_company_id;
        if ($year.$month.'01' === Carbon::now()->format('Ymd')) {
            $lastMonthDate = Carbon::createFromDate($year, $month, 1 )->addMonthsNoOverflow(-1);
            $year = $lastMonthDate->format('Y');
            $month = $lastMonthDate->format('m');
        }
        $data = $this->_getDataReportNum($this->_getDataReport($month, $year, $mst_company_id));
        $summary_data = $this->_getSummaryData($month, $year, $mst_company_id,$statistics_range);
        return response()->json(['status' => true, 'info' => $data,'summary_info' => $summary_data]);
    }

    public function searchGuestCompanyInfo($year, $month,$statisticsRange, $company_id = null, $is_guest = null){
        $user = \Auth::user();
        $host_company = $user->mst_company_id;
        if ($year.$month.'01' === Carbon::now()->format('Ymd')) {
            $lastMonthDate = Carbon::createFromDate($year, $month, 1 )->addMonthsNoOverflow(-1);
            $year = $lastMonthDate->format('Y');
            $month = $lastMonthDate->format('m');
        }
        $data = $this->_getDataReportNum($this->_getHostDataReport($month, $year, $host_company, $company_id, $is_guest));
        $summary_data = $this->_getHostSummaryData($month, $year, $host_company,$company_id,$is_guest,$statisticsRange);

        return response()->json(['status' => true, 'info' => $data,'summary_info' => $summary_data ]);
    }

    public function download($company_id = null){
        $user = \Auth::user();
        $mst_company_id = $user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)?$company_id:$user->mst_company_id;
        $data = $this->_getCsvDataReport($mst_company_id);

        return response()->json(['status' => true, 'info' => $data ]);
    }

    public function downloadGuestCompanyInfo($company_id = null, $is_guest = null){
        $user = \Auth::user();
        $host_company = $user->mst_company_id;
        $data = $this->_getCsvHostDataReport($host_company, $company_id, $is_guest);

        return response()->json(['status' => true, 'info' => $data ]);
    }

    public function downloadFileInfo($company_id = null){
        $user = \Auth::user();
        $mst_company_id = $user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)?$company_id:$user->mst_company_id;
        $data = $this->_getCsvFileStatisticsData($mst_company_id);

        return response()->json(['status' => true, 'info' => $data ]);
    }

    public function downloadGuestFileInfo($company_id = null, $is_guest = null){
        $user = \Auth::user();
        $host_company = $user->mst_company_id;
        $data = $this->_getCsvHostFileStatisticsData($host_company, $company_id, $is_guest);

        return response()->json(['status' => true, 'info' => $data ]);
    }
    public function downloadSummaryInfo($data_range,$company_id = null){
        $user = \Auth::user();
        $mst_company_id = $user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)?$company_id:$user->mst_company_id;

        $from = Carbon::yesterday()->format('Y-m-d');
        $to = Carbon::yesterday()->format('Y-m-d');
        if($data_range == 0){
            $from = Carbon::yesterday()->subDays(30)->format('Y-m-d');
        }else if($data_range == 1){
            $from = Carbon::yesterday()->subDays(60)->format('Y-m-d');
        }else if($data_range == 2){
            $from = Carbon::yesterday()->subDays(90)->format('Y-m-d');
        }else if($data_range == 3){
            $from = Carbon::yesterday()->subMonths(6)->format('Y-m-d');
        }else if($data_range == 4){
            $from = Carbon::yesterday()->subYear()->format('Y-m-d');
        }

        $data = $this->_getCsvSummaryStatisticsData($mst_company_id,$from,$to);
        $sumData = $this->_getCsvSummaryStatisticsSumData($mst_company_id,$from,$to);

        return response()->json(['status' => true, 'info' => $data,'sum_info'=>$sumData ]);
    }

    public function downloadGuestSummaryInfo($data_range,$company_id = null, $is_guest = null){
        $user = \Auth::user();
        $host_company = $user->mst_company_id;
        $from = Carbon::yesterday()->format('Y-m-d');
        $to = Carbon::yesterday()->format('Y-m-d');
        if($data_range == 0){
            $from = Carbon::yesterday()->subDays(30)->format('Y-m-d');
        }else if($data_range == 1){
            $from = Carbon::yesterday()->subDays(60)->format('Y-m-d');
        }else if($data_range == 2){
            $from = Carbon::yesterday()->subDays(90)->format('Y-m-d');
        }else if($data_range == 3){
            $from = Carbon::yesterday()->subMonths(6)->format('Y-m-d');
        }else if($data_range == 4){
            $from = Carbon::yesterday()->subYear()->format('Y-m-d');
        }
        $data = $this->_getCsvHostSummaryStatisticsData($host_company, $company_id, $is_guest,$from,$to);
        $sumData = $this->_getCsvHostSummaryStatisticsSumData($host_company, $company_id, $is_guest,$from,$to);

        return response()->json(['status' => true, 'info' => $data,'sum_info'=>$sumData ]);
    }

    public function showChart($range, $company_id = null){
        $user = \Auth::user();
        $mst_company_id = $user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)?$company_id:$user->mst_company_id;
        $data = $this->_getDataChart($range, $mst_company_id);

        return response()->json(['status' => true, 'info' => $data ]);
    }

    public function showGuestCompanyChart($range, $company_id = null, $is_guest = null){
        $user = \Auth::user();
        $host_company = $user->mst_company_id;
        $data = $this->_getHostDataChart($range, $host_company, $company_id, $is_guest);

        return response()->json(['status' => true, 'info' => $data ]);
    }

    /**
     * previewStamp
     * 印鑑情報プレビュー
     * シリアル値のバリデーションと印鑑情報取得の橋渡しを行う
     * @param [String] $serial
     * @return 印鑑情報　もしくは　エラーメッセージ
     */
    public function previewStamp($serial){
        $user = \Auth::user();
        $mst_company_id = $user->mst_company_id;
        if(!$this->_checkSerial($serial)){
            return response()->json(['status' => false,'message' => [__('正しい印鑑シリアルを入力してください')]]);
        }
        $data = $this->_getStampfromInfo($serial,$mst_company_id);
        if(is_null($data)){
            return response()->json(['status' => false,'message' => [__('印鑑情報が取得できません')]]);
        }
        return response()->json(['status' => true, 'info' => $data ]);
    }

    protected function _getHostStatisticsData($host_company, $company_id, $is_guest,$statisticsRange){
        $to = Carbon::now()->subDay()->format('Y-m-d');
        // 過去30日間
        if($statisticsRange == 0){
            $from = Carbon::now()->subDays(31)->format('Y-m-d');
        }else if($statisticsRange == 1){
            $from = Carbon::now()->subDays(61)->format('Y-m-d');
        }else if($statisticsRange == 2){
            $from = Carbon::now()->subDays(91)->format('Y-m-d');
        }else if($statisticsRange == 3){
            $from = Carbon::now()->subDay()->subMonths(6)->format('Y-m-d');
        }else{
            $from = Carbon::now()->subDay()->subYears(1)->format('Y-m-d');
        }

        $query = UsageSituationDetail::whereBetween('target_date',[$from,$to]);
        if ($company_id){
            if($company_id == -1){
                $query->groupBy('target_date')->selectRaw('SUM(user_count_valid)  as user_count_valid,
                            SUM(storage_sum)  as storage_sum,
                            SUM(storage_sum_re)  as storage_sum_re,
                            SUM(stamp_count)  as stamp_count,
                            SUM(stamp_over_count)  as stamp_over_count,
                            SUM(timestamp_count)  as timestamp_count,
                            SUM(timestamp_leftover_count)  as timestamp_leftover_count,
                            SUM(stamp_contract)  as stamp_contract,
                            SUM(user_count_activity)  as user_count_activity,
                            SUM(circular_applied_count)  as circular_applied_count,
                            SUM(circular_completed_count)  as circular_completed_count,
                            SUM(circular_completed_total_time)  as circular_completed_total_time,
                            SUM(multi_comp_out)  as multi_comp_out,
                            SUM(multi_comp_in)  as multi_comp_in,
                            target_date
                            ')
                    ->where('mst_company_id', $host_company)
                    ->whereNotNull('guest_company_id');
            }else if($is_guest){
                // ゲスト企業
                $query->where('mst_company_id', $host_company)->where('guest_company_id', $company_id);
            } else {
                // ホスト企業
                $query->where('mst_company_id', $host_company)->whereNull('guest_company_id');
            }
        }else{
            // 全企業
            $query->groupBy('target_date')->selectRaw('SUM(user_count_valid)  as user_count_valid,
                            SUM(storage_sum)  as storage_sum,
                            SUM(storage_sum_re)  as storage_sum_re,
                            SUM(stamp_count)  as stamp_count,
                            SUM(stamp_over_count)  as stamp_over_count,
                            SUM(timestamp_count)  as timestamp_count,
                            SUM(timestamp_leftover_count)  as timestamp_leftover_count,
                            SUM(stamp_contract)  as stamp_contract,
                            SUM(user_count_activity)  as user_count_activity,
                            SUM(circular_applied_count)  as circular_applied_count,
                            SUM(circular_completed_count)  as circular_completed_count,
                            SUM(circular_completed_total_time)  as circular_completed_total_time,
                            SUM(multi_comp_out)  as multi_comp_out,
                            SUM(multi_comp_in)  as multi_comp_in,
                            target_date
                            ');
        }

        return $query->orderBy('target_date')->get();

    }
    protected function _getStatisticsData($statisticsRange,$mst_company_id){
        $to = Carbon::now()->subDay()->format('Y-m-d');
        // 過去30日間
        if($statisticsRange == 0){
            $from = Carbon::now()->subDays(31)->format('Y-m-d');
        }else if($statisticsRange == 1){
            $from = Carbon::now()->subDays(61)->format('Y-m-d');
        }else if($statisticsRange == 2){
            $from = Carbon::now()->subDays(91)->format('Y-m-d');
        }else if($statisticsRange == 3){
            $from = Carbon::now()->subDay()->subMonths(6)->format('Y-m-d');
        }else{
            $from = Carbon::now()->subDay()->subYears(1)->format('Y-m-d');
        }

        $query = UsageSituationDetail::whereBetween('target_date',[$from,$to])->whereNull('guest_company_id');

        if($mst_company_id){
            $query = $query->where('mst_company_id',$mst_company_id);
        }else{
            // 全企業
            $query->groupBy('target_date')->selectRaw('SUM(user_count_valid)  as user_count_valid,
                            SUM(storage_sum)  as storage_sum,
                            SUM(storage_sum_re)  as storage_sum_re,
                            SUM(stamp_count)  as stamp_count,
                            SUM(stamp_over_count)  as stamp_over_count,
                            SUM(timestamp_count)  as timestamp_count,
                            SUM(timestamp_leftover_count)  as timestamp_leftover_count,
                            SUM(stamp_contract)  as stamp_contract,
                            SUM(user_count_activity)  as user_count_activity,
                            SUM(circular_applied_count)  as circular_applied_count,
                            SUM(circular_completed_count)  as circular_completed_count,
                            SUM(circular_completed_total_time)  as circular_completed_total_time,
                            SUM(multi_comp_out)  as multi_comp_out,
                            SUM(multi_comp_in)  as multi_comp_in,
                            target_date
                            ');
        }

        return $query->orderBy('target_date')->get();

    }
    protected function _getCsvFileStatisticsData($mst_company_id){
        $from = Carbon::now()->subDays(91)->format('Y-m-d');
        $to = Carbon::now()->subDay()->format('Y-m-d');

        $query = UsageSituationDetail::whereBetween('target_date',[$from,$to])->whereNull('guest_company_id');

        if($mst_company_id){
            $query = $query->where('mst_company_id',$mst_company_id);
        }
        $query->groupBy('target_date')->selectRaw('SUM(upload_count_pdf)  as upload_count_pdf,
                            SUM(upload_count_excel)  as upload_count_excel,
                            SUM(upload_count_word)  as upload_count_word,
                            SUM(download_count_pdf)  as download_count_pdf,
                            target_date
                            ');

        return $query->orderBy('target_date')->get();

    }
    protected function _getCsvSummaryStatisticsData($mst_company_id,$from,$to){

        $query = UsageSituationDetail::whereBetween('target_date',[$from,$to])->whereNull('guest_company_id');

        if($mst_company_id){
            $query = $query->where('mst_company_id',$mst_company_id);
        }
        $query->groupBy('target_date')->selectRaw("SUM(stamp_contract) as stamp_contract,
                            SUM(stamp_count)  as stamp_count,
                            SUM(user_count_valid)  as user_count_valid,
                            SUM(user_count_activity)  as user_count_activity,
                            SUM(stamp_over_count)  as stamp_over_count,
                            target_date
                            ");

        return $query->orderBy('target_date')->get();

    }
    protected function _getCsvHostSummaryStatisticsData($host_company, $company_id, $is_guest,$from,$to){

        $query = UsageSituationDetail::whereBetween('target_date',[$from,$to])->whereNull('guest_company_id');

        if ($company_id){
            if($company_id == -1){
                $query->where('mst_company_id', $host_company)
                    ->whereNotNull('guest_company_id');
            } else if($is_guest){
                // ゲスト企業
                $query->where('mst_company_id', $host_company)->where('guest_company_id', $company_id);
            } else {
                // ホスト企業
                $query->where('mst_company_id', $host_company)->whereNull('guest_company_id');
            }
        }
        $query->groupBy('target_date')->selectRaw("SUM(stamp_contract)  as stamp_contract,
                            SUM(stamp_count)  as stamp_count,
                            SUM(user_count_valid)  as user_count_valid,
                            SUM(user_count_activity)  as user_count_activity,
                            SUM(stamp_over_count)  as stamp_over_count,
                            target_date
                            ");

        return $query->orderBy('target_date')->get();

    }
    protected function _getCsvSummaryStatisticsSumData($mst_company_id,$from,$to){

        $query = UsageSituationDetail::whereBetween('target_date',[$from,$to])->whereNull('guest_company_id');

        if($mst_company_id){
            $query = $query->where('mst_company_id',$mst_company_id);
        }
        $query->groupBy('target_date')->selectRaw("SUM(circular_applied_count)  as circular_applied_count,
                            SUM(circular_completed_count)  as circular_completed_count,
                            SUM(circular_completed_total_time)  as circular_completed_total_time,
                            SUM(multi_comp_out)  as multi_comp_out,
                            SUM(multi_comp_in)  as multi_comp_in,
                            target_date
                            ");

        return $query->orderBy('target_date')->get();

    }
    protected function _getCsvHostSummaryStatisticsSumData($host_company, $company_id, $is_guest,$from,$to){

        $query = UsageSituationDetail::whereBetween('target_date',[$from,$to])->whereNull('guest_company_id');

        if ($company_id){
            if($company_id == -1){
                $query->where('mst_company_id', $host_company)
                    ->whereNotNull('guest_company_id');
            } else if($is_guest){
                // ゲスト企業
                $query->where('mst_company_id', $host_company)->where('guest_company_id', $company_id);
            } else {
                // ホスト企業
                $query->where('mst_company_id', $host_company)->whereNull('guest_company_id');
            }
        }
        $query->groupBy('target_date')->selectRaw("SUM(circular_applied_count)  as circular_applied_count,
                            SUM(circular_completed_count)  as circular_completed_count,
                            SUM(circular_completed_total_time)  as circular_completed_total_time,
                            SUM(multi_comp_out)  as multi_comp_out,
                            SUM(multi_comp_in)  as multi_comp_in,
                            target_date
                            ");

        return $query->orderBy('target_date')->get();

    }
    protected function _getFileStatisticsData($mst_company_id){
        $from = Carbon::now()->subDays(31)->format('Y-m-d');
        $to = Carbon::now()->subDay()->format('Y-m-d');

        $query = UsageSituationDetail::whereBetween('target_date',[$from,$to])->whereNull('guest_company_id');

        if($mst_company_id){
            $query = $query->where('mst_company_id',$mst_company_id);
        }

        $query->selectRaw(' SUM(upload_count_pdf)  as upload_count_pdf,
                            SUM(upload_count_excel)  as upload_count_excel,
                            SUM(upload_count_word)  as upload_count_word,
                            SUM(download_count_pdf)  as download_count_pdf
                            ');

        return $query->first();

    }
    protected function _getHostFileStatisticsData($host_company, $company_id, $is_guest){
        $from = Carbon::now()->subDays(31)->format('Y-m-d');
        $to = Carbon::now()->subDay()->format('Y-m-d');

        $query = UsageSituationDetail::whereBetween('target_date',[$from,$to]);
        if ($company_id){
            if($company_id == -1){
                $query->where('mst_company_id', $host_company)
                    ->whereNotNull('guest_company_id');
            } else if($is_guest){
                // ゲスト企業
                $query->where('mst_company_id', $host_company)->where('guest_company_id', $company_id);
            } else {
                // ホスト企業
                $query->where('mst_company_id', $host_company)->whereNull('guest_company_id');
            }
        }

        $query->selectRaw(' SUM(upload_count_pdf)  as upload_count_pdf,
                            SUM(upload_count_excel)  as upload_count_excel,
                            SUM(upload_count_word)  as upload_count_word,
                            SUM(download_count_pdf)  as download_count_pdf
                            ');

        return $query->first();

    }
    protected function _getCsvHostFileStatisticsData($host_company, $company_id, $is_guest){
        $from = Carbon::now()->subDays(91)->format('Y-m-d');
        $to = Carbon::now()->subDay()->format('Y-m-d');

        $query = UsageSituationDetail::whereBetween('target_date',[$from,$to]);
        if ($company_id){
            if($company_id == -1){
                $query->where('mst_company_id', $host_company)
                    ->whereNotNull('guest_company_id');
            } else if($is_guest){
                // ゲスト企業
                $query->where('mst_company_id', $host_company)->where('guest_company_id', $company_id);
            } else {
                // ホスト企業
                $query->where('mst_company_id', $host_company)->whereNull('guest_company_id');
            }
        }
        $query->groupBy('target_date')->selectRaw('SUM(upload_count_pdf)  as upload_count_pdf,
                            SUM(upload_count_excel)  as upload_count_excel,
                            SUM(upload_count_word)  as upload_count_word,
                            SUM(download_count_pdf)  as download_count_pdf,
                            target_date
                            ');

        return $query->orderBy('target_date')->get();

    }

    /**
     * @param null $mst_company_id
     */
    protected function _getSummaryData($month, $year, $mst_company_id = null,$statisticsRange){

        $query = UsageSituationDetail::where('target_date', Carbon::now()->subDay()->format('Y-m-d'));
        $query->whereNull('guest_company_id');
        if ($mst_company_id){
            // 通常企業検索
            $query->where('mst_company_id', $mst_company_id);
        }else{
            // 全企業
            $query->select(DB::raw('SUM(user_count_valid)  as user_count_valid,
                            SUM(storage_sum)  as storage_sum,
                            SUM(storage_sum_re)  as storage_sum_re,
                            SUM(stamp_count)  as stamp_count,
                            SUM(stamp_contract)  as stamp_contract,
                            SUM(stamp_over_count)  as stamp_over_count,
                            SUM(timestamp_count)  as timestamp_count,
                            SUM(timestamp_leftover_count)  as timestamp_leftover_count
                            '));
        }

        $info = $query->first();
        if($query->count() == 0){
            $info = [
                'user_count_valid'=>0,
                'storage_sum'=>0,
                'storage_sum_re'=>0,
                'stamp_contract'=>0,
                'stamp_count'=>0,
                'stamp_over_count'=>0,
                'timestamp_count'=>0,
                'timestamp_leftover_count'=>0
            ];
        }

        //PAC_5-2679 S
        $data = $this->_getDataReport($month, $year, $mst_company_id);
        $info['storage_sum_re'] = $info['storage_sum_re'] + round($data->storage_use_capacity / 1024, 1);
        //PAC_5-2679 E
        
        if($info['user_count_valid'] == 0){
            $info['storage_rate'] = 0;
        }else{
            $info['storage_rate'] = round($info['storage_sum_re']/($info['user_count_valid'] *1024)*100,2);
        }
        if($info['stamp_over_count'] < 0){
            $info['stamp_over_count'] = 0 ;
        }

        $info['data_list'] =  $this->_getOneMonthData($mst_company_id);
        $info['statistics_info'] = $this->_getStatisticsData($statisticsRange,$mst_company_id);
        $info['file_statistics_info'] = $this->_getFileStatisticsData($mst_company_id);
        return $info;
    }
    protected function _getHostOneMonthData($host_company, $company_id, $is_guest){
        $query = UsageSituationDetail::whereBetween('target_date', [Carbon::now()->subDays(31)->format('Y-m-d'),Carbon::now()->subDay()->format('Y-m-d')]);
        if($company_id){
            if($company_id == -1){
                $query->groupBy('target_date')->selectRaw('SUM(user_count_valid)  as user_count_valid,
                            SUM(storage_sum)  as storage_sum,
                            SUM(storage_sum_re)  as storage_sum_re,
                            SUM(stamp_count)  as stamp_count,
                            SUM(stamp_over_count)  as stamp_over_count,
                            SUM(timestamp_count)  as timestamp_count,
                            SUM(timestamp_leftover_count)  as timestamp_leftover_count,
                            target_date
                            ')
                    ->where('mst_company_id', $host_company)
                    ->whereNotNull('guest_company_id');
            }else if($is_guest){
                // ゲスト企業
                $query->where('mst_company_id', $host_company)->where('guest_company_id', $company_id);
            }else{
                // ホスト企業
                $query->where('mst_company_id', $host_company)->whereNull('guest_company_id');
            }
        }else{
            // 全企業
            $query->groupBy('target_date')->selectRaw('SUM(user_count_valid)  as user_count_valid,
                            SUM(storage_sum)  as storage_sum,
                            SUM(storage_sum_re)  as storage_sum_re,
                            SUM(stamp_count)  as stamp_count,
                            SUM(stamp_over_count)  as stamp_over_count,
                            SUM(timestamp_count)  as timestamp_count,
                            SUM(timestamp_leftover_count) as timestamp_leftover_count,
                            target_date
                            ');
        }

        return $query->orderBy('target_date')->get();
    }
    protected function _getOneMonthData($host_company){
        $query = UsageSituationDetail::whereBetween('target_date', [Carbon::now()->subDays(31)->format('Y-m-d'),Carbon::now()->subDay()->format('Y-m-d')]);
        $query->whereNull('guest_company_id');
        if($host_company){
            $query->where('mst_company_id', $host_company);
        }else{
            // 全企業
            $query->groupBy('target_date')->selectRaw('SUM(user_count_valid)  as user_count_valid,
                            SUM(storage_sum)  as storage_sum,
                            SUM(storage_sum_re)  as storage_sum_re,
                            SUM(stamp_count)  as stamp_count,
                            SUM(stamp_over_count)  as stamp_over_count,
                            SUM(timestamp_count)  as timestamp_count,
                            SUM(timestamp_leftover_count)  as timestamp_leftover_count,
                            target_date
                            ');
        }

        return $query->orderBy('target_date')->get();
    }

    protected function _getDataReport($month, $year, $mst_company_id = null){
        $target = intval($year.$month);

        $query = UsageSituation::where('target_month', $target);
        if ($mst_company_id){
            // 通常企業検索
            $query->where('mst_company_id', $mst_company_id);
        }else{
            // 全企業
            $query->select(DB::raw('CAST(SUM(IF(total_option_contract_count=0,0,user_total_count)) as UNSIGNED) as user_total_count,
                            CAST(SUM(total_name_stamp) as UNSIGNED) as total_name_stamp,
                            CAST(SUM(total_date_stamp) as UNSIGNED) as total_date_stamp,
                            CAST(SUM(total_common_stamp) as UNSIGNED) as total_common_stamp,
                            CAST(SUM(total_time_stamp) as UNSIGNED) as total_time_stamp,
                            CAST(SUM(storage_use_capacity) as UNSIGNED) as storage_use_capacity,
                            CAST(SUM(guest_user_total_count) as UNSIGNED) as guest_user_total_count,
                            CAST(SUM(same_domain_number) as UNSIGNED) as same_domain_number,
                            CAST(SUM(timestamps_count) as UNSIGNED) as timestamps_count,
                            CAST(SUM(total_contract_count) as UNSIGNED) as total_contract_count,
                            CAST(SUM(total_option_contract_count) as UNSIGNED) as total_option_contract_count,
                            CAST(SUM(convenient_upper_limit) as UNSIGNED) as convenient_upper_limit,
                            CAST(SUM(total_convenient_stamp) as UNSIGNED) as total_convenient_stamp,
                            MAX(max_date) as max_date'));
        }
        $query->whereNull('guest_company_id');

        $info = $query->first();
        if ($info){
            if (!$info->user_total_count){
                $info->user_total_count = 0;
            }
            if (!$info->total_name_stamp){
                $info->total_name_stamp = 0;
            }
            if (!$info->total_date_stamp){
                $info->total_date_stamp = 0;
            }
            if (!$info->total_common_stamp){
                $info->total_common_stamp = 0;
            }
            if (!$info->total_time_stamp){
                $info->total_time_stamp = 0;
            }
            if (!$info->storage_use_capacity){
                $info->storage_use_capacity = 0;
            }
            if (!$info->guest_user_total_count){
                $info->guest_user_total_count = 0;
            }
            if (!$info->same_domain_number){
                $info->same_domain_number = 0;
            }
            if (!$info->timestamps_count){
                $info->timestamps_count = 0;
            }
            if (!$info->total_contract_count){
                $info->total_contract_count = 0;
            }
            if (!$info->total_option_contract_count){
                $info->total_option_contract_count = 0;
            }
            if (!$info->convenient_upper_limit) {
                $info->convenient_upper_limit = 0;
            }
            if (!$info->total_convenient_stamp){
                $info->total_convenient_stamp = 0;
            }
        }else{
            $info = new UsageSituation();
            $info->mst_company_id = $mst_company_id;
            $info->user_total_count = 0;
            $info->total_name_stamp = 0;
            $info->total_date_stamp = 0;
            $info->total_common_stamp = 0;
            $info->total_time_stamp = 0;
            $info->storage_use_capacity = 0;
            $info->guest_user_total_count = 0;
            $info->same_domain_number = 0;
            $info->timestamps_count = 0;
            $info->total_contract_count = 0;
            $info->total_option_contract_count = 0;
            $info->max_date = null;
            $info->convenient_upper_limit = 0;
            $info->total_convenient_stamp = 0;
        }
        return $info;
    }
    //PAC_5-2679 Start
    protected function _getDataReportNum($data)
    {
        if ($data->storage_use_capacity > 100) {
            $data->storage_use_capacity = round($data->storage_use_capacity / 1024, 1);

       if ($data->storage_use_capacity > 100) {
                $data->storage_use_capacity = round($data->storage_use_capacity / 1024, 1) . ' GB';
            } else {
                $data->storage_use_capacity .= 'MB';
            }
        } else {
            $data->storage_use_capacity .= 'KB';
        }

        return $data;
    }
    // PAC_5-2679 End
    protected function _getHostSummaryData($month, $year, $host_company, $company_id, $is_guest,$statisticsRange){
        $query = UsageSituationDetail::where('target_date', Carbon::now()->subDay()->format('Y-m-d'));
        if ($company_id){
            if($company_id == -1){
                // ゲスト企業の合計
                $query->select(DB::raw('SUM(user_count_valid)  as user_count_valid,
                            SUM(storage_sum)  as storage_sum,
                            SUM(storage_sum_re)  as storage_sum_re,
                            SUM(stamp_count)  as stamp_count,
                            SUM(stamp_contract)  as stamp_contract,
                            SUM(stamp_over_count)  as stamp_over_count,
                            SUM(timestamp_count)  as timestamp_count,
                            SUM(timestamp_leftover_count)  as timestamp_leftover_count
                            '))
                    ->where('mst_company_id', $host_company)
                    ->whereNotNull('guest_company_id');
            } else if($is_guest){
                // ゲスト企業
                $query->where('mst_company_id', $host_company)->where('guest_company_id', $company_id);
            } else {
                // ホスト企業
                $query->where('mst_company_id', $host_company)->whereNull('guest_company_id');
            }

        } else {
            // 全企業
            $query->select(DB::raw('SUM(user_count_valid)  as user_count_valid,
                            SUM(storage_sum)  as storage_sum,
                            SUM(storage_sum_re)  as storage_sum_re,
                            SUM(stamp_count)  as stamp_count,
                            SUM(stamp_contract)  as stamp_contract,
                            SUM(stamp_over_count)  as stamp_over_count,
                            SUM(timestamp_count)  as timestamp_count,
                            SUM(timestamp_leftover_count)  as timestamp_leftover_count
                            '))
                ->where('mst_company_id', $host_company);
        }
        $info = $query->first();
        if($query->count() == 0){
            $info = [
                'user_count_valid'=>0,
                'storage_sum'=>0,
                'storage_sum_re'=>0,
                'stamp_contract'=>0,
                'stamp_count'=>0,
                'stamp_over_count'=>0,
                'timestamp_count'=>0,
                'timestamp_leftover_count'=>0,
            ];
        }

        //PAC_5-2679 S
        $data = $this->_getHostDataReport($month, $year, $host_company, $company_id, $is_guest);
        $data->storage_use_capacity = round($data->storage_use_capacity / 1024, 1);
        $info['storage_sum_re'] = $info['storage_sum_re'] + $data->storage_use_capacity;
        //PAC_5-2679 E
        
        if($info['user_count_valid'] == 0){
            $info['storage_rate'] = 0;
        }else{
            $info['storage_rate'] = round($info['storage_sum_re']/($info['user_count_valid'] *1024)*100,2);
        }

        if($info['stamp_over_count'] < 0){
            $info['stamp_over_count'] = 0 ;
        }

        $info['data_list'] =  $this->_getHostOneMonthData($host_company,$company_id,$is_guest);
        $info['statistics_info'] = $this->_getHostStatisticsData($host_company,$company_id,$is_guest,$statisticsRange);
        $info['file_statistics_info'] = $this->_getHostFileStatisticsData($host_company,$company_id,$is_guest);
        return $info;
    }

    protected function _getHostDataReport($month, $year, $host_company, $company_id, $is_guest){
        $target = intval($year.$month);
        $query = UsageSituation::where('target_month', $target);

        if ($company_id){
            if($company_id == -1){
                // ゲスト企業の合計
                $query->select(DB::raw('CAST(SUM(IF(total_option_contract_count=0,0,user_total_count)) as UNSIGNED) as user_total_count,
                            CAST(SUM(total_name_stamp) as UNSIGNED) as total_name_stamp,
                            CAST(SUM(total_date_stamp) as UNSIGNED) as total_date_stamp,
                            CAST(SUM(total_common_stamp) as UNSIGNED) as total_common_stamp,
                            CAST(SUM(total_time_stamp) as UNSIGNED) as total_time_stamp,
                            CAST(SUM(storage_use_capacity) as UNSIGNED) as storage_use_capacity,
                            CAST(SUM(guest_user_total_count) as UNSIGNED) as guest_user_total_count,
                            CAST(SUM(same_domain_number) as UNSIGNED) as same_domain_number,
                            CAST(SUM(timestamps_count) as UNSIGNED) as timestamps_count,
                            CAST(SUM(total_contract_count) as UNSIGNED) as total_contract_count,
                            CAST(SUM(total_option_contract_count) as UNSIGNED) as total_option_contract_count,
                            CAST(SUM(convenient_upper_limit) as UNSIGNED) as convenient_upper_limit,
                            CAST(SUM(total_convenient_stamp) as UNSIGNED) as total_convenient_stamp,
                            MAX(max_date) as max_date'))
                    ->where('mst_company_id', $host_company)
                    ->whereNotNull('guest_company_id');
            } else if($is_guest){
                // ゲスト企業
                $query->where('mst_company_id', $host_company)->where('guest_company_id', $company_id);
            } else {
                // ホスト企業
                $query->where('mst_company_id', $host_company)->whereNull('guest_company_id');
            }

        } else {
            // 全企業
            $query->select(DB::raw('CAST(SUM(IF(total_option_contract_count=0,0,user_total_count)) as UNSIGNED) as user_total_count,
                            CAST(SUM(total_name_stamp) as UNSIGNED) as total_name_stamp,
                            CAST(SUM(total_date_stamp) as UNSIGNED) as total_date_stamp,
                            CAST(SUM(total_common_stamp) as UNSIGNED) as total_common_stamp,
                            CAST(SUM(total_time_stamp) as UNSIGNED) as total_time_stamp,
                            CAST(SUM(storage_use_capacity) as UNSIGNED) as storage_use_capacity,
                            CAST(SUM(guest_user_total_count) as UNSIGNED) as guest_user_total_count,
                            CAST(SUM(same_domain_number) as UNSIGNED) as same_domain_number,
                            CAST(SUM(timestamps_count) as UNSIGNED) as timestamps_count,
                            CAST(SUM(total_contract_count) as UNSIGNED) as total_contract_count,
                            CAST(SUM(total_option_contract_count) as UNSIGNED) as total_option_contract_count,
                            CAST(SUM(convenient_upper_limit) as UNSIGNED) as convenient_upper_limit,
                            CAST(SUM(total_convenient_stamp) as UNSIGNED) as total_convenient_stamp,
                            MAX(max_date) as max_date'))
                ->where('mst_company_id', $host_company);
        }



        $info = $query->first();
        if ($info){
            if (!$info->user_total_count){
                $info->user_total_count = 0;
            }
            if (!$info->total_name_stamp){
                $info->total_name_stamp = 0;
            }
            if (!$info->total_date_stamp){
                $info->total_date_stamp = 0;
            }
            if (!$info->total_common_stamp){
                $info->total_common_stamp = 0;
            }
            if (!$info->total_time_stamp){
                $info->total_time_stamp = 0;
            }
            if (!$info->storage_use_capacity){
                $info->storage_use_capacity = 0;
            }
            if (!$info->guest_user_total_count){
                $info->guest_user_total_count = 0;
            }
            if (!$info->same_domain_number){
                $info->same_domain_number = 0;
            }
            if (!$info->timestamps_count){
                $info->timestamps_count = 0;
            }
            if (!$info->total_contract_count){
                $info->total_contract_count = 0;
            }
            if (!$info->total_option_contract_count){
                $info->total_option_contract_count = 0;
            }
        }else{
            $info = new UsageSituation();
            $info->mst_company_id = $company_id;
            $info->user_total_count = 0;
            $info->total_name_stamp = 0;
            $info->total_date_stamp = 0;
            $info->total_common_stamp = 0;
            $info->total_time_stamp = 0;
            $info->storage_use_capacity = 0;
            $info->guest_user_total_count = 0;
            $info->same_domain_number = 0;
            $info->timestamps_count = 0;
            $info->total_contract_count = 0;
            $info->total_option_contract_count = 0;
            $info->max_date = null;
        }

        return $info;
    }

    protected function _getCsvDataReport($mst_company_id = null){
        $now = Carbon::now()->subDay();
        $last_year = Carbon::now()->subDay()->subYear();
        $last_year_target = intval(($last_year->format('Y')).($last_year->format('m')));
        $infos = [];
        while(true){
            $year = $now->format('Y');
            $month = $now->format('m');
            $target = intval($year.$month);
            if($target < $last_year_target){
                break;
            }
            $info = $this->_getDataReportNum($this->_getDataReport($month, $year, $mst_company_id));
            $info['target'] = $target;
            $infos[] = $info;

            $now->subMonth();
        }

        return $infos;
    }

    protected function _getCsvHostDataReport($host_company, $company_id, $is_guest){
        $now = Carbon::now()->subDay();
        $last_year = Carbon::now()->subDay()->subYear();
        $last_year_target = intval(($last_year->format('Y')).($last_year->format('m')));
        $infos = [];
        while(true){
            $year = $now->format('Y');
            $month = $now->format('m');
            $target = intval($year.$month);
            if($target < $last_year_target){
                break;
            }
            $info = $this->_getDataReportNum($this->_getHostDataReport($month, $year, $host_company, $company_id, $is_guest));
            $info['target'] = $target;
            $infos[] = $info;

            $now->subMonth();
        }

        return $infos;
    }

    protected function _getDataChart($range, $mst_company_id){
        $disk_usage_email = [];
        $disk_usage_value = [];
        $month_requests_month = [];
        $month_requests_cnt = [];
        // usages_range
        $info_range = UsagesRange::where('range', $range)
            ->where('mst_company_id', $mst_company_id)
            ->whereNull('guest_company_id')
            ->whereNotNull('disk_usage_rank')
            ->select(['email','disk_usage'])
            ->orderBy('disk_usage_rank', 'desc')
            ->limit(10)
            ->get();
        foreach ($info_range as $item){
            $disk_usage_email[] = $item->email;
            $disk_usage_value[] = $item->disk_usage;
        }

        // usages_daily
        $date = Carbon::today();
        for($i=0; $i<=$range; $i++){
            if($i != 0){
                $date->subMonth();
            }
            $date_ym = $date->format('Ym');
            $info = UsagesDaily::whereNull('guest_company_id')
                ->where('mst_company_id', $mst_company_id)
                ->where(DB::raw("DATE_FORMAT(date, '%Y%m')"), '=', $date_ym)
                ->selectRaw('SUM(new_requests) as requests_cnt')
                ->first();
            $month_requests_month[] = $date->format('m');
            if($info->requests_cnt){
                $month_requests_cnt[] = $info->requests_cnt;
            }else{
                $month_requests_cnt[] = 0;
            }
        }

        return ['disk_usage_email'=>$disk_usage_email, 'disk_usage_value'=>$disk_usage_value, 'month_requests_month'=>array_reverse($month_requests_month), 'month_requests_cnt'=>array_reverse($month_requests_cnt)];
    }

    protected function _getHostDataChart($range, $host_company, $company_id, $is_guest){
        $disk_usage_email = [];
        $disk_usage_value = [];
        $month_requests_month = [];
        $month_requests_cnt = [];
        $rank_counts = 10; // ランク形式で記録する件数(上位n件)
        $date = Carbon::today();
        // usages_range
        $query_range = UsagesRange::where('range', $range)
            ->whereNotNull('disk_usage_rank');

        if ($company_id) {
            if ($company_id == -1) {
                // ゲスト企業の合計
                $query_range->where('mst_company_id', $host_company)
                    ->whereNotNull('guest_company_id');

                // usage_daily
                for($i=0; $i<=$range; $i++){
                    if($i != 0){
                        $date->subMonth();
                    }
                    $date_ym = $date->format('Ym');
                    $info = UsagesDaily::whereNotNull('guest_company_id')
                        ->where('mst_company_id', $host_company)
                        ->where(DB::raw("DATE_FORMAT(date, '%Y%m')"), '=', $date_ym)
                        ->selectRaw('SUM(new_requests) as requests_cnt')
                        ->first();
                    $month_requests_month[] = $date->format('m');
                    if($info->requests_cnt){
                        $month_requests_cnt[] = $info->requests_cnt;
                    }else{
                        $month_requests_cnt[] = 0;
                    }
                }
            }else if($is_guest){
                // ゲスト企業
                $query_range->where('mst_company_id', $host_company)->where('guest_company_id', $company_id);

                // usage_daily
                for($i=0; $i<=$range; $i++){
                    if($i != 0){
                        $date->subMonth();
                    }
                    $date_ym = $date->format('Ym');
                    $info = UsagesDaily::where('mst_company_id', $host_company)
                        ->where('guest_company_id', $company_id)
                        ->where(DB::raw("DATE_FORMAT(date, '%Y%m')"), '=', $date_ym)
                        ->selectRaw('SUM(new_requests) as requests_cnt')
                        ->first();
                    $month_requests_month[] = $date->format('m');
                    if($info->requests_cnt){
                        $month_requests_cnt[] = $info->requests_cnt;
                    }else{
                        $month_requests_cnt[] = 0;
                    }
                }
            } else {
                $query_range->where('mst_company_id', $host_company)
                    ->whereNull('guest_company_id');

                // usage_daily
                for($i=0; $i<=$range; $i++){
                    if($i != 0){
                        $date->subMonth();
                    }
                    $date_ym = $date->format('Ym');
                    $info = UsagesDaily::where('mst_company_id', $host_company)
                        ->whereNull('guest_company_id')
                        ->where(DB::raw("DATE_FORMAT(date, '%Y%m')"), '=', $date_ym)
                        ->selectRaw('SUM(new_requests) as requests_cnt')
                        ->first();
                    $month_requests_month[] = $date->format('m');
                    if($info->requests_cnt){
                        $month_requests_cnt[] = $info->requests_cnt;
                    }else{
                        $month_requests_cnt[] = 0;
                    }
                }
            }
        }else{
            // 全企業
            $query_range->where('mst_company_id', $host_company);

            for($i=0; $i<=$range; $i++){
                if($i != 0){
                    $date->subMonth();
                }
                $date_ym = $date->format('Ym');
                $info = UsagesDaily::where('mst_company_id', $host_company)
                    ->where(DB::raw("DATE_FORMAT(date, '%Y%m')"), '=', $date_ym)
                    ->selectRaw('SUM(new_requests) as requests_cnt')
                    ->first();
                $month_requests_month[] = $date->format('m');
                if($info->requests_cnt){
                    $month_requests_cnt[] = $info->requests_cnt;
                }else{
                    $month_requests_cnt[] = 0;
                }
            }
        }

        $info_range = $query_range->select(['email','disk_usage'])
            ->orderBy('disk_usage', 'asc')
            ->limit($rank_counts)
            ->get();
        foreach ($info_range as $item){
            $disk_usage_email[] = $item->email;
            $disk_usage_value[] = $item->disk_usage;
        }

        return ['disk_usage_email'=>$disk_usage_email, 'disk_usage_value'=>$disk_usage_value, 'month_requests_month'=>array_reverse($month_requests_month), 'month_requests_cnt'=>array_reverse($month_requests_cnt)];
    }

    function _processCountStamp($listStampAssign){
        $listStampCommonID = [];
        $listStampMasterID = [];
        $stampCommon   = $stampName  =  $stampDate    = 0;
        $stampsAssign  = [];

        foreach($listStampAssign as $stampAssign){
            $_user_id       = $stampAssign['mst_user_id'];
            $_stamp_id      = $stampAssign['stamp_id'];
            $_stamp_flg     = $stampAssign['stamp_flg'];
            if($_stamp_flg == 0) $listStampMasterID[] = $_stamp_id;
            else{
                $stampCommon ++;
                $listStampCommonID[] = $_stamp_id;
            }
            $stampsAssign[$_stamp_flg][] = $_stamp_id;
        }

        // TODO PAC_5-290
        // get listStampsCommon for all user in list
        $listStampsCommon = CompanyStamp::whereIn('id', $listStampCommonID)
                        ->select('id','stamp_division')->get()->pluck('stamp_division', 'id')->toArray();
        // get listStampsMaster for all user in list
        $listStampsMaster = Stamp::whereIn('id', $listStampMasterID)
        ->select('id','stamp_division')->get()->pluck('stamp_division', 'id')->toArray();

        if(isset($stampsAssign[0]) AND count($stampsAssign[0])){
            foreach($stampsAssign[0] as $_stamp_id){
                if($listStampsMaster[$_stamp_id] == 0) $stampName ++;
                else $stampDate ++;
            }
        }

        if(isset($stampsAssign[1]) AND count($stampsAssign[1])){
            foreach($stampsAssign[1] as $_stamp_id){
                if($listStampsCommon[$_stamp_id] == 0) $stampName ++;
                else $stampDate ++;
            }
        }
        return \compact('stampCommon', 'stampName','stampDate');
    }

    /**
     * _getStampfromInfo
     * プレビュー用印鑑画像データの取得
     *
     * @param [String] $serial
     * @param [Integer] $mst_company_id
     * @return stamp_info
     */
    protected function _getStampfromInfo($serial,$mst_company_id){
        $stamp_info = DB::table('stamp_info as si')
            ->join('mst_user as u','u.email','si.email')
            ->select('si.stamp_image','si.email','si.name')
            ->where('serial', $serial)
            ->where('u.mst_company_id',$mst_company_id)
            ->first();

        $HasCompany = DB::table('mst_company_stamp')
            ->where('mst_company_id', $mst_company_id)
            ->where('serial', $serial)
            ->where('del_flg',0)
            ->count();
        $HasDepartment = DB::table('mst_company_stamp')
            ->where('mst_company_id', $mst_company_id)
            ->where('serial', $serial)
            ->where('del_flg',0)
            ->count();

        if ($stamp_info){
            if($HasCompany){
                $stamp_info->name = '共通印';
                $stamp_info->email = '共通印';
            }else if($HasDepartment){
                $stamp_info->name = '部署名日付印';
                $stamp_info->email = '部署名日付印';
            }
        }

        return $stamp_info;
    }

    protected function _checkSerial($serial){
        if(!preg_match("/^[0-9a-zA-Z#?!@$%^&*-]{1,40}$/", $serial)){
            return false;
        };
        return true;
    }
    public function getCsvDataAdmin(Request $request){
        $company_id    = $request->get('company_id');
        $from_date    = $request->get('from_date');
        $to_date      = $request->get('to_date');

        $arrOrder   = ['user' => 'user_name','time' => 'H.create_at', 'status' => 'H.result',
            'type' =>'H.mst_operation_id','screen' => 'H.mst_display_id','ipAddress' => 'H.ip_address',
            'email' => 'U.email','adminDepartment' => 'U.department_name',
            'userDepartment' => 'D.department_name','position' => 'P.position_name'];

        $where      = ['1 = 1'];
        $where_arg  = [];

        if($from_date != '9999-99-99'){
            $where[]        = 'Date(H.create_at) >= ?';
            $where_arg[]    = $from_date;
        }

        if($to_date != '9999-99-99'){
            $where[]        = 'Date(H.create_at) <= ?';
            $where_arg[]    = $to_date;
        }

        $arrHistory = DB::table('operation_history as H')
            ->orderBy(isset($arrOrder['time'])?$arrOrder['time']:'H.id','desc')
            ->leftJoin('mst_admin as U', 'H.user_id','U.id')
            ->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, CONCAT(U.family_name, U.given_name) as user_name,U.email, U.department_name'))
            ->where('auth_flg', OperationsHistoryUtils::HISTORY_FLG_ADMIN)
            ->where('U.mst_company_id', $company_id)
            ->whereRaw(implode(" AND ", $where), $where_arg) ->get();

        $arrOperation_info = DB::table('mst_operation_info')->where('role', OperationsHistoryUtils::HISTORY_FLG_ADMIN)->select('info','id')->pluck('info','id');

        Session::flash('file_name', 'adminlog.csv');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.'adminlog.csv'.'');
        $output = fopen('php://output', 'w');
        fputs( $output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );

        foreach ($arrHistory as $item){
            $row = [
                $item->create_at,
                $item->email ,
                $item->user_name,
                $item->department_name,
                $arrOperation_info[$item->mst_operation_id],
            ];
            fputcsv($output, $row);
        }
        fclose($output);
    }

    public function getCsvDataUser(Request $request){

        $company_id    = $request->get('company_id');
        $from_date    = $request->get('from_date');
        $to_date      = $request->get('to_date');

        $arrOrder   = ['user' => 'user_name','time' => 'H.create_at', 'status' => 'H.result',
            'type' =>'H.mst_operation_id','screen' => 'H.mst_display_id','ipAddress' => 'H.ip_address',
            'email' => 'U.email','adminDepartment' => 'U.department_name',
            'userDepartment' => 'D.department_name','position' => 'P.position_name'];

        $where      = ['1 = 1'];
        $where_arg  = [];

        if($from_date != '9999-99-99'){
            $where[]        = 'Date(H.create_at) >= ?';
            $where_arg[]    = $from_date;
        }

        if($to_date != '9999-99-99'){
            $where[]        = 'Date(H.create_at) <= ?';
            $where_arg[]    = $to_date;
        }

        $arrHistory = DB::table('operation_history as H')
            ->orderBy(isset($arrOrder['time'])?$arrOrder['time']:'H.id','desc')
            ->leftJoin('mst_user as U', 'H.user_id','U.id')
            ->leftJoin('mst_user_info as UI', 'UI.mst_user_id','U.id')
            ->leftJoin('mst_department as D', 'UI.mst_department_id','D.id')
            ->leftJoin('mst_position as P', 'UI.mst_position_id','P.id')
            ->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, CONCAT(U.family_name, U.given_name) as user_name,U.email, D.department_name, P.position_name'))
            ->where('auth_flg', OperationsHistoryUtils::HISTORY_FLG_USER)
            ->where('U.mst_company_id', $company_id)
            ->whereRaw(implode(" AND ", $where), $where_arg)->get();

        $arrOperation_info = DB::table('mst_operation_info')->where('role', OperationsHistoryUtils::HISTORY_FLG_USER)->select('info','id')->pluck('info','id');

        Session::flash('file_name', 'userlog.csv');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.'userlog.csv'.'');
        $output = fopen('php://output', 'w');
        fputs( $output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );

        foreach ($arrHistory as $item){
            $row = [
                $item->create_at,
                $item->email ,
                $item->user_name,
                $item->department_name,
                $item->position_name,
                $arrOperation_info[$item->mst_operation_id],
                $item->ip_address,
            ];
            fputcsv($output, $row);
        }
        fclose($output);

        return;
    }

    /**
     * getCsvStampRegister
     * 捺印履歴　CSV出力ファイル
     * 対象月の自社捺印履歴をCSVファイルに出力する
     * 対象課題: PAC_5-1415
     * ※出力対象
     *  '捺印時刻','ユーザー名','メールアドレス','所属部署','印鑑シリアルNo','ファイル名','文書名'
     * @param Request $request
     * @return void
     */
    public function getCsvStampRegister(Request $request){
        $user = \Auth::user();
        $company_id = $user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)?$request->get("company_id", null):$user->mst_company_id;
        $select_month = $request->get('select_month');
        $serial = $request->get('serial');

        if(!$select_month){
            return response()->json(['status' => false,'message' => [__('対象月を指定してください')]]);
        }

        $query_sub_cuser = DB::table('circular_user as cu')
            ->select(DB::raw('circular_id AS s_cid, MAX(create_at) AS max_create_at'))
            ->groupBy('circular_id');

        $query_sub_title = DB::table('circular_user as cu')
            ->joinSub($query_sub_cuser, 's_cu', function ($join) {
                $join->on('s_cu.s_cid', '=', 'cu.circular_id')
                     ->on('s_cu.max_create_at', '=', 'cu.create_at');
            })
            ->select('circular_id AS circular_id','title AS circular_name');

            $StampHistory = DB::table('circular_operation_history as coh')
            ->join('join mst_user as su', 'su.email', '=', 'coh.operation_email')
            ->leftJoin('mst_user_info as sui', 'sui.mst_user_id', '=', 'su.id')
            ->leftJoin('mst_department as sd', 'sui.mst_department_id', '=', 'sd.id')
            ->leftJoin('stamp_info as si', 'si.circular_operation_id', '=', 'coh.id')
            ->joinSub($query_sub_title, 'title', function ($join) {
                $join->on('title.circular_id', '=', 'coh.circular_id');
                $join->on('title.email', '=', 'coh.operation_email');
            })
            ->select('coh.create_at','coh.operation_name','coh.operation_email','com.department_name','si.serial','si.file_name','title.circular_name')
            ->where('coh.circular_status',OperationsHistoryUtils::CIRCULAR_IMPRINT_STATUS)
            ->where('su.mst_company_id',$company_id)
            ->where('su.state_flg',AppUtils::STATE_VALID)
            ->whereRaw('Date(coh.create_at) like ?', '%'.$select_month.'%')
            ->orderBy('coh.create_at','desk')
            ->orderBy('si.serial','ask')
            ->orderBy('si.file_name','ask');
        if($serial && $this->_checkSerial($serial)){
            $StampHistory = $StampHistory->where('serial',$serial);
        }
        $StampHistory = $StampHistory->get();

        Session::flash('file_name', 'StampLog'.$select_month.'.csv');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.'StampLog'.$select_month.'.csv'.'');
        $output = fopen('php://output', 'w');
        fputs( $output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );

        $header = [
            '捺印時刻',
            'ユーザー名',
            'メールアドレス',
            '所属部署',
            '印鑑シリアル',
            'ファイル名',
            '文書名'
        ];
        fputcsv($output, $header);

        foreach ($StampHistory as $item){

            if(!trim($item->circular_name)){
                $circular_name = $item->file_name;
            }else{
                $circular_name = $item->circular_name;
            }
            $row = [
                $item->create_at,
                $item->operation_name ,
                $item->operation_email,
                $item->department_name,
                $item->serial,
                $item->file_name,
                $circular_name,
            ];
            fputcsv($output, $row);
        }
        fclose($output);

        return;
    }
    public function reSituation($company_id){

        // 再計算企業、存在チェック
        $company = DB::table('mst_company')->where('id', $company_id)->first();
        if(!$company){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        // 再計算企業の前日レコード、存在チェック
        $targetDay = Carbon::yesterday()->format('Y-m-d');
        $usageSituationDetail = DB::table('usage_situation_detail')
            ->where('target_date', $targetDay)
            ->where('mst_company_id', $company_id)
            ->whereNull('guest_company_id')
            ->first();
        if(!$usageSituationDetail){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        // インプリンツ容量(本環境)
        $stamp_storage_sizes_current = DB::table('stamp_info')
            ->join('mst_user', 'stamp_info.email', 'mst_user.email')
            ->where('stamp_info.mst_assign_stamp_id', '!=', 0)
            ->where('mst_user.mst_company_id', $company_id)
            ->select(DB::raw('SUM(length(stamp_info.stamp_image))as length_sum'))
            ->get();

        $storage_stamp = 0;
        foreach($stamp_storage_sizes_current as $stamp_storage_sizes_current_item){
            $storage_stamp += $stamp_storage_sizes_current_item->length_sum;
        }

        // インプリンツ容量(その他の環境)
        $stamp_storage_sizes_other = DB::table('assign_stamp_info')
            ->join('mst_assign_stamp', 'assign_stamp_info.assign_stamp_id', 'mst_assign_stamp.id')
            ->join('mst_stamp', 'mst_assign_stamp.stamp_id', 'mst_stamp.id')
            ->join('mst_user', 'mst_assign_stamp.mst_user_id', 'mst_user.id')
            ->select(DB::raw('SUM(length(mst_stamp.stamp_image))as length_sum'))
            ->where('mst_user.mst_company_id', $company_id)
            ->get();

        foreach($stamp_storage_sizes_other as $stamp_storage_sizes_other_item){
            $storage_stamp += $stamp_storage_sizes_other_item->length_sum;
        }
        $now = Carbon::now();
        // ドキュメントデータ容量
        $document_storage_sizes = InsertUsageSituationUtils::getCircularUsageDetail(AppUtils::CIRCULAR_DOCUMENT_DATA_SIZE, $now->toString(),'',$company_id);

        $storage_document = 0;
        foreach($document_storage_sizes as $document_storage_size){
            $storage_document += $document_storage_size->storage_size;
        }

        //添付ファイルデータ容量
        $attachment_storage_sizes = InsertUsageSituationUtils::getCircularUsageDetail(AppUtils::CIRCULAR_ATTACHMENT_DATA_SIZE, $now->toString(),'',$company_id);
        $storage_attachment = 0;
        foreach ($attachment_storage_sizes as $attachment_storage_size){
            $storage_attachment += $attachment_storage_size->storage_size;
        }
        //ファイルメール便容量
        $disk_mail_sizes = DB::table('disk_mail as dm')
            ->join('disk_mail_file as dmf', 'dm.id', 'dmf.disk_mail_id')
            ->join('mst_user as mu', 'dm.mst_user_id', 'mu.id')
            ->select(DB::raw('mu.mst_company_id,sum(dmf.file_size) as storage_size'))
            ->where('mu.mst_company_id',$company_id)
            ->groupBy(['mu.mst_company_id'])
            ->get();
        $storage_disk_mail = 0;
        foreach ($disk_mail_sizes as $disk_mail_size){
            $storage_disk_mail += $disk_mail_size->storage_size;
        }

        // 操作ログ容量 0：管理者
        $admin_operation_history_storage_cnts = DB::table('operation_history')
            ->join('mst_admin', function($query){
                $query->on('mst_admin.id', '=', 'operation_history.user_id');
            })
            ->select(DB::raw('count(1) as history_cnt, mst_admin.mst_company_id'))
            ->where('operation_history.auth_flg', AppUtils::OPERATION_HISTORY_AUTH_FLG_ADMIN)
            ->where('mst_admin.mst_company_id', $company_id)
            ->groupBy('mst_admin.mst_company_id')
            ->get();

        // 操作ログ容量 1：利用者
        $user_operation_history_storage_cnts =  DB::table('operation_history')
            ->join('mst_user', function($query){
                $query->on('mst_user.id', '=', 'operation_history.user_id');
            })
            ->select(DB::raw('count(1) as history_cnt, mst_user.mst_company_id'))
            ->where('operation_history.auth_flg', AppUtils::OPERATION_HISTORY_AUTH_FLG_USER)
            ->where('mst_user.mst_company_id', $company_id)
            ->groupBy('mst_user.mst_company_id')
            ->get();

        $storage_operation_history = 0;
        foreach($admin_operation_history_storage_cnts as $admin_operation_history_storage_cnt){
            $storage_operation_history += $admin_operation_history_storage_cnt->history_cnt;
        }

        foreach($user_operation_history_storage_cnts as $user_operation_history_storage_cnt){
            $storage_operation_history += $user_operation_history_storage_cnt->history_cnt;
        }

        // メール容量
        $mail_storage_cnts = DB::table('mail_send_resume')
            ->where('mail_send_resume.mst_company_id', '!=', '0')
            ->where('mail_send_resume.mst_company_id', $company_id)
            ->groupBy('mst_company_id')
            ->select(DB::raw('count(1) as mail_cnt, mail_send_resume.mst_company_id'))
            ->get();

        $storage_mail = 0;
        foreach($mail_storage_cnts as $mail_storage_cnt){
            $storage_mail = $mail_storage_cnt->mail_cnt;
        }

        // 企業の掲示板容量集計
        $company_total_bbs_file_size = DB::table("bbs")
            ->Join('mst_user','bbs.mst_user_id','mst_user.id')
            ->where('mst_user.mst_company_id', $company_id)
            ->select(DB::raw('SUM(bbs.total_file_size) as total_bbs_file_size'))
            ->get();

        $storage_bbs_file_size = 0;
        foreach($company_total_bbs_file_size as $total_bbs_file_size){
            $storage_bbs_file_size = $total_bbs_file_size->total_bbs_file_size;
        }

        $gw_use=config('app.gw_use');
        $gw_domin=config('app.gw_domain');
        $storage_schedule = 0;
        if ($gw_use == 1 && $gw_domin) {
            // GWスケジューラ容量
            $count_schedule_cnts = GwAppApiUtils::getCountSchedule();
            if ($count_schedule_cnts === false) {
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to GW Api']
                ]);
            }
            foreach ($count_schedule_cnts as $count_schedule_cnt) {
                if ($count_schedule_cnt['company_id'] == $company_id) {
                    $storage_schedule = $count_schedule_cnt['count_schedule'];
                }
            }
        }

        // 容量系再編集
        $per_operation_history_size = config('app.per_operation_history_size'); // 履歴ごとの容量(B)
        $per_mail_size = config('app.per_mail_size'); // メールごとの容量(B)
        $per_schedule_size = config('app.per_schedule_size'); // スケジュールレコードごとの容量(B)
        $use_storage_base = config('app.use_storage_base'); // 使用容量ベース
        // stamp
        // 容量　×　使用容量ベース（1.3）（MB）
        $storage_stamp = $storage_stamp * $use_storage_base / (1024 * 1024);

        // document
        // 容量　×　使用容量ベース（1.3）（MB）
        $storage_document = $storage_document * $use_storage_base / (1024 * 1024);

        // operation_history
        // 履歴件数　×　平均容量　×　使用容量ベース（1.3）（MB）
        $storage_operation_history = $storage_operation_history * $per_operation_history_size * $use_storage_base / (1024 * 1024);

        // mail_size
        // メール件数　×　平均容量　×　使用容量ベース（1.3）（MB）
        $storage_mail = $storage_mail * $per_mail_size * $use_storage_base / (1024 * 1024);

        // attachment
        // 容量　×　使用容量ベース（1.3）（MB）
        $storage_attachment = $storage_attachment * $use_storage_base / (1024 * 1024);

        // bbs_file_size
        // 容量　×　使用容量ベース（1.3）（MB）
        $storage_bbs_file_size = $storage_bbs_file_size * $use_storage_base / (1024 * 1024);

        //disk_mail
        // 容量　×　使用容量ベース（1.3）（MB）
        $storage_disk_mail = $storage_disk_mail * $use_storage_base / (1024 * 1024);

        // schedule
        // スケジュールレコード数　×　平均容量　×　使用容量ベース（1.3）（MB）
        $storage_schedule = $storage_schedule * $per_schedule_size * $use_storage_base / (1024 * 1024);
        
        $storage_sum = $storage_stamp + $storage_document + $storage_operation_history + $storage_mail + $storage_attachment + $storage_bbs_file_size + $storage_disk_mail + $storage_schedule;
        // ユーザー数
        $user_valid_num = DB::table('mst_user')
            ->join('mst_user_info', 'mst_user.id', '=', 'mst_user_info.mst_user_id')
            ->where('mst_company_id' , $company_id)
            ->where(function ($query) use ($company_id){
                if(config('app.fujitsu_company_id') && config('app.fujitsu_company_id') == $company_id){
                    // 富士通(K5)場合、
                    // 有効でパスワードが設定してあるユーザー
                    $query->whereNotNull('password_change_date');
                }
                $query->whereIn('state_flg',[AppUtils::STATE_VALID]);
            })
            ->where(function ($query) {
                $query->where('mst_user.option_flg', AppUtils::USER_NORMAL)
                    ->orWhere(function ($query){
                        $query->where('mst_user.option_flg', AppUtils::USER_OPTION)
                            ->where('mst_user_info.gw_flg', 1);
                    });
            })
            ->count();

        //総利用容量
        $total_utilized_capacity = $user_valid_num + $company->add_file_limit;
        $storage_rate = $storage_sum / $total_utilized_capacity / 1024 * 100;
        
        //　再計算値更新
        DB::table('usage_situation_detail')
            ->where('target_date', $targetDay)
            ->where('mst_company_id', $company_id)
            ->whereNull('guest_company_id')
            ->update([
                'storage_stamp_re'=>$storage_stamp
                ,'storage_document_re'=>$storage_document
                ,'storage_operation_history_re'=>$storage_operation_history
                ,'storage_mail_re'=>$storage_mail
                ,'storage_attachment_re'=>$storage_attachment
                ,'storage_convenient_file_re'=>$storage_disk_mail
                ,'storage_sum_re'=>$storage_sum
                ,'storage_rate_re'=>$storage_rate
                ,'storage_bbs_file_size_re'=>$storage_bbs_file_size
                ,'storage_schedule_re'=>$storage_schedule
                ,'user_count_valid'=>$total_utilized_capacity
                ,'update_at'=>Carbon::now()
            ]);

        return response()->json(['status' => true ]);
    }

}
