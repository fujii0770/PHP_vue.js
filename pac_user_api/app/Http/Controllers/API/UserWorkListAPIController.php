<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\HrTimeCard;
use App\Models\HrInfo;
use App\Models\User;
use App\Models\HrTimecardDetail;
use App\Models\TimecardDetail;
use App\Http\Requests\API\GetHrUserWorkListAPIRequest;
use App\Http\Requests\API\UpdateHrUserWorkListAPIRequest;
use App\Http\Requests\API\ExportJoinWkListToPDFAPIRequest;
use App\Http\Requests\API\ExportMstHrTimeCardDetailAPIRequest;
use App\Http\Utils\AppUtils;
use App\Http\Utils\MailUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\CircularOperationHistoryUtils;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Response;
use PDF;

class UserWorkListAPIController extends AppBaseController
{
    private $model;
    private $hr_timecard_detail;
    private $hr_timecard;
    private $mst_user;

    public function __construct(HrInfo $model, HrTimecardDetail $hr_timecard_detail, HrTimeCard $hr_timecard, User $mst_user)
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
     * @param GetHrUserWorkListAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function index(GetHrUserWorkListAPIRequest $request)
    {
        $user   = $request->user();
		$userId = $user->id;
        $query  = null;

         // get list user
        $limitPage = isset($data['limit']) ? $data['limit'] : 12;
        $limit = AppUtils::normalizeLimit($limitPage, 12);
        $orderBy    = $request->get('orderBy') ? $request->get('orderBy') : 'working_month';
        $orderDir   = $request->get('orderDir') ? $request->get('orderDir'): 'DESC';
        $arrOrder   = [
            'working_month'     => 'working_month',
            'user_name'         => 'user_name',
            'working_time'      => 'working_time',
            'working_day_count' => 'working_day_count',
            'approval_date'     => 'approval_date'
        ];
 
        $filter_work_month        = $request->get('working_month','');
        $filter_user_name         = $request->get('user_name','');
        $filter_approval          = $request->get('approval_state',''); 
        $filter_working_hour      = $request->get('working_hour','');
        $filter_working_hour_type = $request->get('working_hour_type','');
        $filter_workday           = $request->get('workday','');
        $filter_workday_type      = $request->get('workday_type','');

        try {
            // 管理下のスタッフを取得する
			$query = DB::table('hr_admin_has_users as A')
                // 管理下スタッフのユーザ情報を取得
                ->leftJoin('mst_user as U',            function ($join) use ($user) {
                    $join->on('U.mst_company_id', '=', DB::raw($user->mst_company_id))
                         ->on('U.id',             '=', 'A.user_mst_user_id');
                })
                // 管理下のスタッフの勤務詳細情報を取得
                ->leftJoin('hr_timecard_detail as D',  function ($join) {
                    $join->on('D.mst_company_id', '=', 'U.mst_company_id') 
                         ->on('D.mst_user_id',    '=', 'U.id');
                })
                // 管理下のスタッフの勤務詳細情報を取得
                ->leftJoin('hr_timecard as T',    function ($join) {
                    $join->on('T.mst_user_id',    '=', 'U.id') 
                         ->on('T.working_month',  '=', DB::raw('SUBSTRING(D.work_date, 1, 6)')); // 勤務テーブルの年月と紐付け
                })
                ->groupBy(DB::raw(
                    'T.id,                                           ' .
                    'U.id,                                           ' . 
                    'T.working_month,                                ' .
                    'CONCAT(U.family_name, U.given_name),            ' . 
                    "DATE_FORMAT(IfNull(T.approval_date,''),'%H:%i') " 
                ))
                // 'T.submission_state,                 ' . 
                // 'T.approval_state,                   ' . 
                ->select(DB::raw(
                    'T.id,                                                          ' .
                    'U.id as mst_user_id,                                           ' . 
                    'T.working_month,                                               ' . 
                    'T.submission_state,                                            ' .
                    "CONCAT(U.family_name, ' ', U.given_name) as user_name,         " . 
                    "DATE_FORMAT(IfNull(T.approval_date,''),'%Y/%m/%d %H:%i') approval_date, " . 
                    'TRUNCATE(SUM(D.working_time) / 60, 2) as working_time,         ' . 
                    'COUNT(D.working_time) as working_day_count                     '
                ))
                // 'T.submission_state,                                    ' . 
                // 'T.approval_state,                                      ' .
                ->where   ('A.mst_company_id',    '=', DB::raw($user->mst_company_id))
                ->where   ('A.admin_mst_user_id', '=', DB::raw($user->id))
                ->where   ('A.del_flg',           '=', DB::raw(1))
                ->whereRaw("(D.working_time IS NOT NULL AND D.working_time > 0)")
                ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'T.working_month',$orderDir)
                ;
                if($filter_work_month){
                    $query->where('T.working_month', '=', $filter_work_month);
                }
                if($filter_user_name != ''){
                    $query->where(DB::raw('CONCAT(U.family_name,U.given_name)'), 'like', "%$filter_user_name%");
                } 
                if($filter_approval != ''){
                    $query->where('T.approval_state', '=', $filter_approval);
                }
                if($filter_working_hour_type != '' && is_numeric($filter_working_hour)){
                    if($filter_working_hour_type == '1'){
                        $query->havingRaw('TRUNCATE(SUM(D.working_time) / 60, 2) >= ?', [$filter_working_hour]);
                    } elseif($filter_working_hour_type == '2'){
                        $query->havingRaw('TRUNCATE(SUM(D.working_time) / 60, 2)  < ?', [$filter_working_hour]);
                    }
                }
                if($filter_workday_type != '' && is_numeric($filter_workday)){
                    if($filter_workday_type == '1'){
                        $query->havingRaw('COUNT(D.working_time) >= ?', [$filter_workday]); 
                    } elseif($filter_workday_type == '2'){
                        $query->havingRaw('COUNT(D.working_time)  < ?', [$filter_workday]);
                    }
                } 
                
            $workList = $query->paginate($limit)->appends(request()->input());
            return $this->sendResponse($workList, '勤務表一覧の取得処理に成功しました。');
        } catch (Exception $ex) {
            Log::error('UserWorkListAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // 一括承認
    public function bulkApproval(UpdateHrUserWorkListAPIRequest $request){

        $user = $request->user();

        // 選択したユーザのhr_timecard.idのリストを取得
        $cids = $request->get('cids',[]);
        
        // 存在チェック
        $items = [];
        if(count($cids)){
            $items = DB::table('mst_user as U')            
            ->leftJoin('hr_timecard as T', 'U.id','T.mst_user_id')
            ->where(   'U.mst_company_id', $user->mst_company_id)
            ->whereIn('T.id', $cids)
            ->get();
        }
        if(!count($items)){
            return $this->sendResponse(['status' => false,'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        DB::beginTransaction();
        try{

            // メール送信リストを作成する為、事前に更新対象データを取得する。
            $authorUsers = 
            DB::table("mst_user as U")
                ->join   ("hr_timecard as T",   "U.id", "=", "T.mst_user_id")
                ->where  ('T.approval_state',   '<>',   '1')
                ->whereIn('T.id', $cids)
                ->select ( 
                    "U.email", 
                    "U.family_name", 
                    "U.given_name", 
                    "T.working_month",
                )
                ->get();
            // 勤務情報を更新
            DB::table('hr_timecard')
                ->where('approval_state','<>','1')
                ->whereIn('id', $cids)
                ->update([
                    'approval_state' => '1',
                    'approval_user'  => $user->getFullName(),
                    'approval_date'  => date(now()),
                    'update_user'  => $user->getFullName(),
                    'update_at'  => date(now())
                ]);

            // 勤務情報に紐づく勤務情報詳細を更新
            $items = $this->hr_timecard->find($cids);
            foreach ($items as $item) {
                DB::table('hr_timecard_detail')
                    ->where('mst_user_id', $item->mst_user_id)
                    ->where(DB::raw('substring(work_date, 1, 6)'), $item->working_month)
                    ->where('approval_state','<>','1')
                    ->update([
                        'approval_state' => '1',
                        'approval_user'  => $user->getFullName(),
                        'approval_date'  => date(now()),
                        'update_user'  => $user->getFullName(),
                        'update_at'  => date(now())
                    ]);
            }
            foreach ($authorUsers as $row) { 
                // start send mail
                $data_mail = [
                    'user_name' => $row->family_name . ' ' . $row->given_name,
                    'admin_name' => $user->family_name . ' ' . $user->given_name,
                    'working_month' =>  substr($item->working_month, 0, -2) . '/' .substr($item->working_month, -2),
                ];

                $data_mail_send_resume = $data_mail;
                MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                    $row->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['SEND_APPROVAL_WORK_DETAIL_MAIL']['CODE'],
                    // パラメータ
                    json_encode($data_mail,JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_USER,
                    // 件名
                    trans('mail.prefix.user') . trans('mail.SendApprovalWorkDetailMail.subject'),
                    // メールボディ
                    trans('mail.SendApprovalWorkDetailMail.body', $data_mail_send_resume)
                );
            }
            DB::commit();
			$res = [
                'status' => true,
                'message' => __('message.success.approval_update'),
			];
            return $this->sendResponse($res, '');
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return $this->sendResponse(['status' => false, 'message' => \Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] );
        }
    }

    // 差戻し
    public function bulkRemand(UpdateHrUserWorkListAPIRequest $request){

        $user = $request->user();

        // 選択したユーザのhr_timecard.idのリストを取得
        $cids = $request->get('cids',[]);

        // 存在チェック
        $items = [];
        if(count($cids)){
            $items = DB::table('mst_user as U')            
                ->leftJoin('hr_timecard as T', 'U.id','T.mst_user_id')
                ->where('U.mst_company_id',$user->mst_company_id)
                ->whereIn('T.id', $cids)
                ->get();
        }
        if(!count($items)){
            return $this->sendResponse(['status' => false,'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        DB::beginTransaction();
        try{
            // メール送信リストを作成する為、事前に更新対象データを取得する。
            $authorUsers = 
                DB::table("mst_user as U")
                    ->join   ("hr_timecard as T",   "U.id", "=", "T.mst_user_id")
                    ->join   ("mst_company as C",   "C.id", "=", "U.mst_company_id")
                    ->where  ('T.approval_state',   '<>',   '1')
                    ->where  ('T.submission_state', '=' ,   '1')
                    ->whereIn('T.id', $cids)
                    ->select ( 
                        "U.email", 
                        "U.family_name", 
                        "U.given_name", 
                        "T.working_month", 
                        "C.login_type", 
                        "C.url_domain_id"
                    )
                    ->get();

            // 勤務情報を取得しました。
            DB::table('hr_timecard')
                ->where('approval_state'  , '<>' ,'1')
                ->where('submission_state', '='  ,'1')
                ->whereIn('id', $cids)
                ->update([
                    'submission_state' => '0',
                    'submission_date'  => null,
                    'update_user'      => \Auth::user()->getFullName(),
                    'update_at'        => date(now())
                ]);

            foreach ($authorUsers as $row) { 

                // send mail remand
                $data_mail = [
                    'user_name' => $row->family_name . ' ' . $row->given_name,
                    'admin_name' => $user->family_name . ' ' . $user->given_name,
                    'working_month' =>  substr($row->working_month, 0, -2) . '/' .substr($row->working_month, -2),
                ];

                $data_mail_send_resume = $data_mail;
                MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                    $row->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['SEND_REMAND_WORK_DETAIL_MAIL']['CODE'],
                    // パラメータ
                    json_encode($data_mail,JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_USER,
                    // 件名
                    trans('mail.prefix.user') . trans('mail.SendRemandWorkDetailMail.subject'),
                    // メールボディ
                    trans('mail.SendRemandWorkDetailMail.body', $data_mail_send_resume)
                );
            };

			$res = [
                'status' => true,
                'message' => __('message.success.submission_state_update'),
			];
            
            // Log::debug("■■■■■ここまで来ているか？".  $row->working_month);
            DB::commit();

            return $this->sendResponse($res, '');
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return $this->sendResponse(['status' => false, 'message' => \Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] );
        }
    }

    /**
     * Export HrTimecardDetail to file csv.
     * post /time-card-detail/export-work-list
     *
     * @param ExportMstHrTimeCardDetailAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function exportListToCSV(ExportMstHrTimeCardDetailAPIRequest $request) {
        
        $user = $request->user();
        $columnSelect = $request->all();
        $workMonth = $columnSelect['work_month'];
		$mstUserId = $columnSelect['mst_user_id'];
        $columnSelect = $columnSelect['export_work_list_columns'];

        $userEmail = $user->email;
        $userName = $user->family_name . ' ' . $user->given_name;

        try {
            $data['name']    = $userName;
            $data['email']   = $userEmail;
            $timeCardDetails = $this->hr_timecard_detail
                ->where([
                    'mst_user_id' => $mstUserId,
                    ['work_date', 'LIKE', $workMonth . '%' ]
                ])->exportWorkListCSV()->orderBy('work_date', 'asc')->get();

            $timeCardDetailExport = [];

            // Create header format file export
            $headerFormat = 'ユーザ名,メールアドレス';
            if (in_array('work_date', $columnSelect)) {
                $headerFormat .= ',業務日(yyyymmdd)';
            }
            if (in_array('work_start_time', $columnSelect)) {
                $headerFormat .= ',出勤時間(yyyymmdd hh:nn)';
            }
            if (in_array('work_end_time', $columnSelect)) {
                $headerFormat .= ',退勤時間(yyyymmdd hh:nn)';
            }
            if (in_array('break_time', $columnSelect)) {
                $headerFormat .= ',休憩時間(nn)';
            }
            if (in_array('working_time', $columnSelect)) {
                $headerFormat .= ',稼働時間';
            }
            if (in_array('overtime', $columnSelect)) {
                $headerFormat .= ',残業時間';
            }
            if (in_array('absent_flg', $columnSelect)) {
                $headerFormat .= ',欠勤フラグ';
            }
            if (in_array('late_flg', $columnSelect)) {
                $headerFormat .= ',遅刻フラグ';
            }
            if (in_array('earlyleave_flg', $columnSelect)) {
                $headerFormat .= ',早退フラグ';
            }
            if (in_array('paid_vacation_flg', $columnSelect)) {
                $headerFormat .= ',有給フラグ';
            }
            if (in_array('sp_vacation_flg', $columnSelect)) {
                $headerFormat .= ',特休フラグ';
            }
            if (in_array('day_off_flg', $columnSelect)) {
                $headerFormat .= ',代休フラグ';
            }
            if (in_array('memo', $columnSelect)) {
                $headerFormat .= ',備考';
            }
            if (in_array('admin_memo', $columnSelect)) {
                $headerFormat .= ',管理者コメント';
            }
            array_push($timeCardDetailExport, $headerFormat);

            // Process data before export
            foreach ($timeCardDetails as $timeCardDetail) {
                $timeCard = $userName . ',';
                $timeCard .= $userEmail;
                if (in_array('work_date', $columnSelect)) {
                    $timeCard .= ',' . $timeCardDetail->work_date;
                }
                if (in_array('work_start_time', $columnSelect)) {
                    $timeCard .= ',';
                    if (isset($timeCardDetail->work_start_time)) {
                        $workStartTime = $timeCardDetail->work_start_time->format('Ymd H:i');
                        $timeCard .= $workStartTime;
                    }
                }
                if (in_array('work_end_time', $columnSelect)) {
                    $timeCard .= ',';
                    if (isset($timeCardDetail->work_end_time)) {
                        $workEndTime = $timeCardDetail->work_end_time->format('Ymd H:i');
                        $timeCard .= $workEndTime;
                    }
                }
                if (in_array('break_time', $columnSelect) && isset($timeCardDetail->work_start_time) && isset($timeCardDetail->work_end_time)) {
                    $timeCard .= ',';
                    if (isset($timeCardDetail->break_time)) {
                        if($timeCardDetail->break_time > 0){
                            $timeCard .= $timeCardDetail->break_time;
                        }
                        else if ($timeCardDetail->break_time == 0){
                            if (isset($timeCardDetail->work_start_time) && isset($timeCardDetail->work_end_time)) {
                                $timeCard .= 0;
                            }
                        }
                    }
                } else if (in_array('break_time', $columnSelect)){
                    $timeCard .= ',';
                }
                if (in_array('working_time', $columnSelect) && isset($timeCardDetail->work_start_time) && isset($timeCardDetail->work_end_time)) {
                    $timeCard .= ',';
                    if (isset($timeCardDetail->working_time)) {
                        $hours = intdiv($timeCardDetail->working_time, 60);
                        if ($hours < 10) {
                            $hours = '0'. $hours;
                        }
                        $minutes = ($timeCardDetail->working_time % 60);
                        $minutes = date('i', mktime(0, $minutes));

                        $timeCard .= ($hours .':'. $minutes);
                    }
                } else if (in_array('working_time', $columnSelect)){
                    $timeCard .= ',';
                }
                if (in_array('overtime', $columnSelect) && isset($timeCardDetail->work_start_time) && isset($timeCardDetail->work_end_time)) {
                    $timeCard .= ',';
                    if (isset($timeCardDetail->overtime)) {
                        $hours = intdiv($timeCardDetail->overtime, 60);
                        if ($hours < 10) {
                            $hours = '0'. $hours;
                        }
                        $minutes = ($timeCardDetail->overtime % 60);
                        $minutes = date('i', mktime(0, $minutes));

                        $timeCard .= ($hours .':'. $minutes);
                    }
                } else if (in_array('overtime', $columnSelect)){
                    $timeCard .= ',';
                }
                if (in_array('absent_flg', $columnSelect)) {
                    $timeCard .= ',';
                    if ($timeCardDetail->absent_flg == 1) {
                        $timeCard .= '欠勤';
                    }
                }
                if (in_array('late_flg', $columnSelect)) {
                    $timeCard .= ',';
                    if ($timeCardDetail->late_flg == 1) {
                        $timeCard .= '遅刻';
                    }
                }
                if (in_array('earlyleave_flg', $columnSelect)) {
                    $timeCard .= ',';
                    if ($timeCardDetail->earlyleave_flg == 1) {
                        $timeCard .= '早退';
                    }
                }
                if (in_array('paid_vacation_flg', $columnSelect)) {
                    $timeCard .= ',';
                    if ($timeCardDetail->paid_vacation_flg == 1) {
                        $timeCard .= '有給';
                    }
                    else if ($timeCardDetail->paid_vacation_flg == 2) {
                        $timeCard .= '有給（半休）';
                    }
                }
                if (in_array('sp_vacation_flg', $columnSelect)) {
                    $timeCard .= ',';
                    if ($timeCardDetail->sp_vacation_flg == 1) {
                        $timeCard .= '特休';
                    }
                    else if ($timeCardDetail->sp_vacation_flg == 2) {
                        $timeCard .= '特休（半休）';
                    }
                }
                if (in_array('day_off_flg', $columnSelect)) {
                    $timeCard .= ',';
                    if ($timeCardDetail->day_off_flg == 1) {
                        $timeCard .= '代休';
                    }
                    else if ($timeCardDetail->day_off_flg == 2) {
                        $timeCard .= '代休（半休）';
                    }
                }
                if (in_array('memo', $columnSelect)) {
                    $timeCard .= ',';
                    if (isset($timeCardDetail->memo)) {
                        $memo = $timeCardDetail->memo;
                        $memo = str_replace(',', "','", $memo);
                        $memo = str_replace("\n", "（改行）", $memo);
                        $timeCard .= $memo;
                    }
                }
                if (in_array('admin_memo', $columnSelect)) {
                    $timeCard .= ',';
                    if (isset($timeCardDetail->admin_memo)) {
                        $adminMemo = $timeCardDetail->admin_memo;
                        $adminMemo = str_replace(',', "','", $adminMemo);
                        $timeCard .= $adminMemo;
                    }
                }
                array_push($timeCardDetailExport, $timeCard);
            }
            $timeDownload        = Carbon::now()->format('YmdHis');
            $year                = substr($workMonth, 0, 4) . '年';
            $month               = substr($workMonth, 4, 6);
            $result['file_name'] = $year . $month . '月勤務情報_' . $timeDownload ;
            $result['time_card'] = $timeCardDetailExport;
            return $this->sendResponse($result, '勤務一覧の取得処理に成功しました。');
        } catch (Exception $ex) {
            Log::error('WorkListAPIController@exportHrWorkListToCSV:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
