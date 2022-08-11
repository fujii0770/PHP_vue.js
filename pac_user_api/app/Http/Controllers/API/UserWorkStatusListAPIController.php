<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\HrTimeCard;
use App\Models\HrInfo;
use App\Models\User;
use App\Models\TimecardDetail;
use App\Http\Requests\API\GetHrUserWorkStatusListAPIRequest;
use App\Http\Requests\API\UpdateHrUserWorkListAPIRequest;
use App\Http\Requests\API\ExportJoinWkListToPDFAPIRequest;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\CircularOperationHistoryUtils;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Response;

class UserWorkStatusListAPIController extends AppBaseController
{
    private $model;
    private $hr_timecard_detail;
    private $hr_timecard;
    private $mst_user;

    public function __construct(HrInfo $model, TimecardDetail $hr_timecard_detail, HrTimeCard $hr_timecard, User $mst_user)
    {
        $this->model = $model;
        $this->hr_timecard_detail = $hr_timecard_detail;
        $this->hr_timecard = $hr_timecard;
        $this->mst_user = $mst_user;
    }

    /**
     * Display a listing of the users HrTimeCard for HR-Administrator.
     * GET|HEAD /work-list
     *
     * @param GetHrUserWorkStatusListAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function index(GetHrUserWorkStatusListAPIRequest $request)
    {
        $strNowDate = Carbon::now()->format('Ymd');
        $showDisplayDay = 365*20; // 7300日間 20年間
        $user = $request->user();
		$userId = $user->id;
        $query  =  null;

        // get list user
        $limitPage = isset($data['limit']) ? $data['limit'] : 12;
        $limit = AppUtils::normalizeLimit($limitPage, 12);
        $orderBy    = $request->get('orderBy') ? $request->get('orderBy') : 'work_date';
        $orderDir   = $request->get('orderDir') ? $request->get('orderDir'): 'DESC';
        $arrOrder   = [
            'work_date'           => 'work_date',
            'user_name'           => 'user_name',
            'work_start_time_flg' => 'start_time',
            'work_end_time_flg'   => 'end_time',
            'late_flg'            => 'late_flg',
            'earlyleave_flg'      => 'earlyleave_flg',
            'paid_vacation_flg'   => 'paid_vacation_flg',
            'sp_vacation_flg'     => 'sp_vacation_flg',
            'day_off_flg'         => 'day_off_flg'
        ];

        $filter_date_from           = $request->get('working_date_from','');
        $filter_date_to             = $request->get('working_date_to','');
        $filter_user_name           = $request->get('user_name','');
        $filter_work_start_time_flg = $request->get('work_start_time_flg',''); 
        $filter_work_end_time_flg   = $request->get('work_end_time_flg','');
        $filter_late_flg            = $request->get('late_flg',''); 
        $filter_earlyleave_flg      = $request->get('earlyleave_flg',''); 
        $filter_paid_vacation_flg   = $request->get('paid_vacation_flg',''); 
        $filter_sp_vacation_flg     = $request->get('sp_vacation_flg',''); 
        $filter_day_off_flg         = $request->get('day_off_flg','');  

        try 
        {
            // 日付テーブルを作成する
            // 当日以前から「$showDisplayDay」日間作成する
            $subQuery = "" .
                " SELECT " . 
                "     DATE_FORMAT('" .$strNowDate. "' + 0 - INTERVAL seq_no DAY,'%Y%m%d') AS date " . 
                " FROM (" . 
                "     SELECT @seq_no := 0 AS seq_no " . 
                "     UNION " . 
                "     SELECT @seq_no := @seq_no + 1 AS seq_no " . 
                "     FROM   information_schema.COLUMNS LIMIT ".$showDisplayDay.") tmp";

            $query = DB::table(DB::raw('('.$subQuery.') AS X'))
                // 管理下のスタッフを取得する
                ->leftJoin('hr_admin_has_users as A', function ($join) use ($user) {
                    $join->on('A.mst_company_id',    '=', DB::raw($user->mst_company_id))
                         ->on('A.admin_mst_user_id', '=', DB::raw($user->id))
                         ->on('A.del_flg',           '=', DB::raw(1));
                })
                // 管理下のスタッフのユーザ情報を取得する
                ->leftJoin('mst_user as U', function ($join) use ($user) {
                    $join->on('U.id',                '=', 'A.user_mst_user_id')
                         ->on('U.mst_company_id',    '=', DB::raw($user->mst_company_id));
                })
                // 管理下のスタッフのタイムカードヘッダを取得
                ->leftJoin('hr_timecard as T', function ($join) {
                    $join->on('T.mst_user_id',       '=', 'A.user_mst_user_id') 
                         ->on(DB::raw('SUBSTRING(X.date, 1, 6)'), '=', 'T.working_month'); // 日付テーブルの日付と年月で紐付け
                })
                // 管理下のスタッフのタイムカード明細を取得
                ->leftJoin('hr_timecard_detail as D', function ($join) {
                    $join->on('U.mst_company_id',    '=', 'D.mst_company_id') 
                        ->on('U.id',   '=', 'D.mst_user_id')
                        ->on(DB::raw('SUBSTRING(D.work_date, 1, 6)'), '=', 'T.working_month') // タイムカードヘッダの年月と紐付け
                        ->on('X.date', '=', 'D.work_date'); // 日付テーブルの日付と年月日で紐付け
                })
                ->select(
                    DB::raw(
                        'T.id, ' . 
                        'U.id as mst_user_id, '. 
                        'CONCAT(U.family_name, U.given_name) as user_name, '. 
                        "IfNull(T.working_month, SUBSTRING(X.date, 1, 6))   as working_month, " . 
                        "IfNull(D.work_date,               X.date)          as work_date, " . 
                        'D.work_start_time,' .
                        'D.work_end_time,' .
                        'D.late_flg,' .
                        'D.earlyleave_flg,' .
                        'D.paid_vacation_flg,' .
                        'D.sp_vacation_flg,' .
                        'D.day_off_flg,' .
                        "DATE_FORMAT(IfNull(D.work_start_time,''),'%H:%i') start_time," .
                        "DATE_FORMAT(IfNull(D.work_end_time,''),  '%H:%i') end_time   "
                    )
                )
                ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'work_date',$orderDir)
                ->orderBy('mst_user_id', 'ASC')
                ; 
            if($filter_date_from){
                $query->whereRaw("IfNull(D.work_date, X.date) >= '" . $filter_date_from . "'"); 
            }
            if($filter_date_to){
                $query->whereRaw("IfNull(D.work_date, X.date) <= '" . $filter_date_to . "'"); 
            }
            if($filter_late_flg != ''){
                $query->where('D.late_flg',          $filter_late_flg);
            }
            if($filter_earlyleave_flg != ''){
                $query->where('D.earlyleave_flg',    $filter_earlyleave_flg);
            }
            if($filter_paid_vacation_flg != ''){
                $query->where('D.paid_vacation_flg', $filter_paid_vacation_flg);
            }
            if($filter_sp_vacation_flg != ''){
                $query->where('D.sp_vacation_flg',   $filter_sp_vacation_flg);
            }
            if($filter_day_off_flg != ''){
                $query->where('D.day_off_flg',       $filter_day_off_flg);
            }
            if($filter_work_start_time_flg != ''){
                if($filter_work_start_time_flg == '1'){
                    $query->whereRaw("D.work_start_time IS NULL ");
                } else {
                    $query->whereRaw("D.work_start_time IS NOT NULL ");
                }
            }
            if($filter_work_end_time_flg != ''){
                if($filter_work_end_time_flg == '1'){
                    $query->whereRaw("D.work_end_time IS NULL ");
                } else {
                    $query->whereRaw("D.work_end_time IS NOT NULL ");
                }
            }
            if($filter_user_name != ''){
                $query->where(DB::raw('CONCAT(U.family_name,U.given_name)'), 'like', "%$filter_user_name%");
            }
            
            //Log::debug("■ｗｗｗｗｗｗｗｗｗｗｗｗｗｗｗｗｗｗｗｗｗｗｗｗｗｗ".$query->toSql()); 
            $workList = $query->paginate($limit)->appends(request()->input());
            return $this->sendResponse($workList, '勤務表一覧の取得処理に成功しました。');
        } catch (Exception $ex) {
            Log::error('WorkListAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
