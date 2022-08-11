<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\UpdateUserInfoAPIRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PDF;
use Response;
use App\Models\HrTimecardDetail;
use App\Models\HrTimeCard;
use Carbon\Carbon;
use App\Http\Requests\API\CreateHrTimeCardDetailAPIRequest;
use App\Http\Requests\API\UpdateHrTimeCardDetailAPIRequest;
use App\Http\Requests\API\ExportUserWorkDetailAPIRequest;
use App\Http\Utils\HrUtils;
use App\Http\Utils\AppUtils;
use App\Http\Utils\MailUtils;
use Session;

/**
 * Class UserWorkDetailAPIController
 * @package App\Http\Controllers\API
 */

class UserWorkDetailAPIController extends AppBaseController
{
    var $table = 'hr_timecard_detail';
    var $model = null;
    var $TimeCard = null;

    public function __construct(HrTimecardDetail $hrTimecardDetail, HrTimeCard $HrTimeCard)
    {
        $this->model = $hrTimecardDetail;
        $this->TimeCard = $HrTimeCard;
    }

    /**
     * 勤務詳細初期表示－勤務詳細情報取得処理
     * GET|HEAD /getUserWorkDetail/{id}/{working_month}
     *
     * @param Request $request
     * @param int     $id            スタッフのユーザID（ログインのユーザIDではない）
     * @param int     $working_month 年月
     *
     * @return Response
     */
    public function getUserWorkDetail(Request $request, $id, $working_month)
    {
        $user  = $request->user();
        try{
            $workDetails =
                DB::table('hr_timecard_detail as D')
                ->where('D.mst_company_id', $user->mst_company_id)
                ->where('D.mst_user_id', $id)
                ->where(DB::raw('SUBSTRING(D.work_date, 1, 6)'), '=', $working_month)
                ->select ( 
                    DB::raw( 
                        'D.id,' .
                        'D.mst_company_id,' .
                        'D.mst_user_id,' .
                        'D.work_date,' .
                        'D.work_start_time,' .
                        'D.work_end_time,' .
                        'D.start_stamping,' .
                        'D.end_stamping,' .
                        'D.break_time,' .
                        'D.working_time,' .
                        'D.absent_flg,' .
                        'D.overtime,' .
                        'D.late_flg,' .
                        'D.earlyleave_flg,' .
                        'D.paid_vacation_flg,' .
                        'D.sp_vacation_flg,' .
                        'D.day_off_flg,' .
                        'D.shift_work_kbn,' .
                        'D.approval_state,' .
                        'D.approval_user,' .
                        'D.approval_date,' .
                        'D.state,' .
                        'D.memo,' .
                        'D.work_detail,' .
                        'D.admin_memo'
                    )
                )->get();
            return $this->sendResponse($workDetails, 'HRタイムカードデータの取得処理に成功しました');
        }
        catch (Exception $ex) {
            Log::error('UserWorkDetailAPIController@getUserWorkDetail:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendResponse(['status' => false, 'message' => \Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]], '');
        }
    }

    /**
     * 勤務詳細初期表示－HRユーザ情報取得処理
     * GET|HEAD /getUserHrInfo/{id}
     *
     * @param Request $request
     * @param int     $id            スタッフのユーザID（ログインのユーザIDではない）
     *
     * @return Response
     */
    public function getUserHrInfo(Request $request, $id)
    {
        // ログインユーザ情報を取得
        $user  = $request->user();

        try{
            $workDetails =
            DB::table('mst_user as U')
            ->leftJoin('mst_hr_info as I',      'I.mst_user_id', DB::raw($id))
            ->leftJoin('hr_working_hours as H', 'H.id',          'I.working_hours_id')
            ->where   ('U.mst_company_id',      $user->mst_company_id)
            ->where   ('U.id',                  $id)
            ->select ( 
                DB::raw(
                    'I.Regulations_work_start_time  as I_Regulations_work_start_time ,' .
                    'I.Regulations_work_end_time    as I_Regulations_work_end_time   ,' .
                    'I.shift1_start_time            as I_shift1_start_time           ,' .
                    'I.shift1_end_time              as I_shift1_end_time             ,' .
                    'I.shift2_start_time            as I_shift2_start_time           ,' .
                    'I.shift2_end_time              as I_shift2_end_time             ,' .
                    'I.shift3_start_time            as I_shift3_start_time           ,' .
                    'I.shift3_end_time              as I_shift3_end_time             ,' .
                    'H.regulations_work_start_time  as H_Regulations_work_start_time ,' .
                    'H.regulations_work_end_time    as H_Regulations_work_end_time   ,' .
                    'H.shift1_start_time            as H_shift1_start_time           ,' .
                    'H.shift1_end_time              as H_shift1_end_time             ,' .
                    'H.shift2_start_time            as H_shift2_start_time           ,' .
                    'H.shift2_end_time              as H_shift2_end_time             ,' .
                    'H.shift3_start_time            as H_shift3_start_time           ,' .
                    'H.shift3_end_time              as H_shift3_end_time             ,' .
                    'H.work_form_kbn                as H_work_form_kbn               ,' .
                    'H.regulations_working_hours    as H_regulations_working_hours   ,' .
                    'U.shift_flg                                                      '
                )
            )->first();
            return $this->sendResponse($workDetails, '勤務時間設定情報の取得処理に成功しました');
        }
        catch (Exception $ex) {
            Log::error('UserWorkDetailAPIController@getUserWorkDetail:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendResponse(['status' => false, 'message' => \Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]], '');
        }
    }

    /**
     * 勤務編集初期表示－勤務詳細情報取得処理
     * GET|HEAD /getUserWorkDetailByTimecard/{id}
     *
     * @param int $id 勤務詳細.id
     * @param Request $request
     *
     * @return Response
     */
    public function getUserWorkDetailByTimecard ($id, Request $request)
    {
        try {
            // ログインしているユーザ情報を取得
            $user = $request->user();
             
            $workDetails =
            DB::table('hr_timecard_detail as D')
                // ユーザー情報を取得 ※勤務詳細.id（ユニークキー）で絞り込んだユーザIDで取得
                ->leftJoin('mst_user as U', function ($join) use ($user) {
                    $join->on('U.mst_company_id', '=', DB::raw($user->mst_company_id))
                         ->on('U.id',             '=', 'D.mst_user_id');
                })
                // HRユーザー情報を取得 ※勤務詳細.id（ユニークキー）で絞り込んだユーザIDで取得
                ->leftJoin('mst_hr_info as I', 'I.mst_user_id', 'D.mst_user_id')
                ->leftJoin('hr_working_hours as H', 'H.id',     'I.working_hours_id')
                ->where('D.mst_company_id', $user->mst_company_id)
                ->where('D.id', $id)
                ->select ( 
                    DB::raw( 
                        'D.id,' .
                        'D.mst_company_id,' .
                        'D.mst_user_id,' .
                        'D.work_date,' .
                        'D.work_start_time,' .
                        'D.work_end_time,' .
                        'D.start_stamping,' .
                        'D.end_stamping,' .
                        'D.break_time,' .
                        'D.midnight_break_time,' .
                        'D.working_time,' .
                        'D.overtime,' .
                        'D.absent_flg,' .
                        'D.late_flg,' .
                        'D.earlyleave_flg,' .
                        'D.paid_vacation_flg,' .
                        'D.sp_vacation_flg,' .
                        'D.day_off_flg,' .
                        'D.shift_work_kbn,' .
                        'D.holiday_work_flg,' .
                        'D.approval_state,' .
                        'D.approval_user,' .
                        'D.approval_date,' .
                        'D.state,' .
                        'D.memo,' .
                        'D.work_detail,' .
                        'D.admin_memo,' .
                        'I.Regulations_work_start_time  as I_Regulations_work_start_time ,' .
                        'I.Regulations_work_end_time    as I_Regulations_work_end_time   ,' .
                        'I.shift1_start_time            as I_shift1_start_time           ,' .
                        'I.shift1_end_time              as I_shift1_end_time             ,' .
                        'I.shift2_start_time            as I_shift2_start_time           ,' .
                        'I.shift2_end_time              as I_shift2_end_time             ,' .
                        'I.shift3_start_time            as I_shift3_start_time           ,' .
                        'I.shift3_end_time              as I_shift3_end_time             ,' .
                        'H.regulations_work_start_time  as H_Regulations_work_start_time ,' .
                        'H.regulations_work_end_time    as H_Regulations_work_end_time   ,' .
                        'H.shift1_start_time            as H_shift1_start_time           ,' .
                        'H.shift1_end_time              as H_shift1_end_time             ,' .
                        'H.shift2_start_time            as H_shift2_start_time           ,' .
                        'H.shift2_end_time              as H_shift2_end_time             ,' .
                        'H.shift3_start_time            as H_shift3_start_time           ,' .
                        'H.shift3_end_time              as H_shift3_end_time             ,' .
                        'H.work_form_kbn                as H_work_form_kbn               ,' .
                        'H.regulations_working_hours    as H_regulations_working_hours   ,' .
                        'U.shift_flg'
                    )
                )->first();      
            return $this->sendResponse($workDetails, 'HRタイムカードデータの取得処理に成功しました');
        } catch (Exception $ex) {
            Log::error('UserWorkDetailAPIController@getUserWorkDetailByTimecard:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

	public function getUser(Request $request, $id)
	{
		try {
			//$user = DB::table('mst_user')->where('id', $id)->first();
			$user = $this->_getUser($id);
            return $this->sendResponse($user, 'ユーザー情報の取得処理に成功しました');
		} catch (Exception $ex) {
            Log::error('UserWorkDetailAPIController@getUser:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendResponse(['status' => false, 'message' => \Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]], '');
		}
	}

    public function bulkApproval(Request $request)
    {
        $user = $request->user();

        $cids = $request->get('cids');
        $now = date(now());
        $work_month ='';

        //hr_timecard_detail
        $items = $this->model->find($cids);

        DB::beginTransaction();
        try{
            $first_flg = 0;
            foreach ($items as $item) {
                if (!$first_flg){
                    $first_flg =1;
                    $mst_user_id = $item->mst_user_id;
                    $work_month = substr($item->work_date,0,6);
                }
                if($item->approval_state ==0){
                    $item->update_user = $user->getFullName();
                    $item->approval_state = 1;
                    $item->approval_user = $user->getFullName();
                    $item->approval_date = $now;
                    $item->save();
                }
            }
            $authorUser = 
            DB::table("mst_user as U")
                ->join   ("hr_timecard as T",   "U.id", "=", "T.mst_user_id")
                ->where  ('T.approval_state', 0)
                ->where('T.working_month', $work_month)
                ->where('T.mst_user_id', $mst_user_id)
                ->select ( 
                    "U.email", 
                    "U.family_name", 
                    "U.given_name", 
                    "T.working_month",
                )
                ->first();
           //hr_timecard
            $items2 = $this->TimeCard
                ->where('mst_user_id', $mst_user_id)
                ->where('working_month', $work_month)
                ->where('approval_state', 0)
                ->update(
                [
                    'update_user' => $user->getFullName()
                    ,'approval_state' => 1
                    ,'approval_user' => $user->getFullName()
                    ,'approval_date' => $now
                ]);

            if($authorUser){
                // start send mail
                $data_mail = [
                    'user_name' => $authorUser->family_name . ' ' . $authorUser->given_name,
                    'admin_name' => $user->family_name . ' ' . $user->given_name,
                    'working_month' =>  substr($work_month, 0, -2) . '/' .substr($work_month, -2),
                ];

                $data_mail_send_resume = $data_mail;
                MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                    $authorUser->email,
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
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return $this->sendResponse(['status' => false, 'message' => \Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]], '');
        }

        $res = [
            'status' => true,
            'message' => __('message.success.approval_update'),
	    ];
        return $this->sendResponse($res, '');
    }

    // 差戻し
    public function bulkRemand(Request $request){

        $user = $request->user();
        $mst_user_id = $request->get('mst_user_id');
        $work_month = $request->get('work_month');

        // 選択したユーザのhr_timecardを取得
        $workDetails = DB::table('mst_user as U')
            ->leftJoin('hr_timecard as T',    function ($join) use ($work_month) {
                $join->on('T.mst_user_id',    '=', 'U.id') 
                     ->on('T.working_month',  '=', DB::raw($work_month));
            })
            ->where   ('U.mst_company_id',    '=', DB::raw($user->mst_company_id))
            ->where   ('U.id',                '=', DB::raw($mst_user_id))
            ->first();

        DB::beginTransaction();
        try{
            // メール送信リストを作成する為、事前に更新対象データを取得する。
            $authorUsers = 
                DB::table("mst_user as U")
                    ->join   ("hr_timecard as T",   "U.id", "=", "T.mst_user_id")
                    ->join   ("mst_company as C",   "C.id", "=", "U.mst_company_id")
                    ->where  ('T.approval_state',   '<>',   '1')
                    ->where  ('T.submission_state', '=' ,   '1')
                    ->where  ('T.id',               '=' ,   DB::raw($workDetails->id))
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
                 ->where('id',               '='  ,DB::raw($workDetails->id))
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
     * post /user-work-detail/export-list
     *
     * @param ExportUserWorkDetailAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function exportListToCSV(ExportUserWorkDetailAPIRequest $request) {
        $user = $request->user();
        $columnSelect = $request->all();
        $workMonth = $columnSelect['work_month'];
		$mstUserId = $columnSelect['mst_user_id'];
        $columnSelect = $columnSelect['export_work_list_columns'];

		$tcUser = $this->_getUser($mstUserId);
        $userEmail = $tcUser->email;
        $userName = $tcUser->family_name . ' ' . $tcUser->given_name;

        try {
            $data['name'] = $userName;
            $data['email'] = $userEmail;

            $timeCardDetails = $this->model
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
            if (in_array('work_detail', $columnSelect)) {
                $headerFormat .= ',作業内容';
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
                if (in_array('break_time', $columnSelect)) {
                    $timeCard .= ',';
                    if (isset($timeCardDetail->work_start_time) && isset($timeCardDetail->work_end_time)) {
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
                    }
                }
                if (in_array('working_time', $columnSelect)) {
                    $timeCard .= ',';
                    if (isset($timeCardDetail->work_start_time) && isset($timeCardDetail->work_end_time)) {
                        if (isset($timeCardDetail->working_time)) {
                            $hours = intdiv($timeCardDetail->working_time, 60);
                            if ($hours < 10) {
                                $hours = '0'. $hours;
                            }
                            $minutes = ($timeCardDetail->working_time % 60);
                            $minutes = date('i', mktime(0, $minutes));

                            $timeCard .= ($hours .':'. $minutes);
                        }
                    }
                }
                if (in_array('overtime', $columnSelect)) {
                    $timeCard .= ',';
                    if (isset($timeCardDetail->work_start_time) && isset($timeCardDetail->work_end_time)) {
                        if (isset($timeCardDetail->overtime)) {
                            $hours = intdiv($timeCardDetail->overtime, 60);
                            if ($hours < 10) {
                                $hours = '0'. $hours;
                            }
                            $minutes = ($timeCardDetail->overtime % 60);
                            $minutes = date('i', mktime(0, $minutes));

                            $timeCard .= ($hours .':'. $minutes);
                        }
                    }
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
                if (in_array('work_detail', $columnSelect)) {
                    $timeCard .= ',';
                    if (isset($timeCardDetail->memo)) {
                        $work_detail = $timeCardDetail->work_detail;
                        $work_detail = str_replace(',', "','", $work_detail);
                        $work_detail = str_replace("\n", "（改行）", $work_detail);
                        $timeCard .= $work_detail;
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
            $timeDownload = Carbon::now()->format('YmdHis');
            $year = substr($workMonth, 0, 4) . '年';
            $month = substr($workMonth, 4, 6);
            $result['file_name'] = $year . $month . '月勤務情報_' . $timeDownload ;
            $result['time_card'] = $timeCardDetailExport;
            return $this->sendResponse($result, '勤務一覧の取得処理に成功しました。');
        } catch (Exception $ex) {
            Log::error('UserWorkDetailAPIController@exportHrWorkListToCSV:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

	private function _getUser($id)
	{
		return DB::table('mst_user')->where('id', $id)->first();
	}
    
    /**
     * Store a newly created HrTimecardDetail in storage.
     * (勤務編集画面 登録 勤務詳細登録処理)
     * POST /timecard-detail
     *
     * @param CreateHrTimeCardDetailAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function store(CreateHrTimeCardDetailAPIRequest $request)
    {
        $data = $request->all();
        $user = $request->user();

        $users_mst_user_id    = $data['users']['id'];
        $users_mst_company_id = $data['users']['mst_company_id'];
        $update_user_name     = $user->family_name . ' ' . $user->given_name;
        // data配列を丸ごとinsertしているため
        // 管理下スタッフのユーザ情報を取得後、配列から削除
        unset($data['users']);

        $clickTime = Carbon::now();
        try {
            $data['mst_company_id'] = $users_mst_company_id;
            $data['mst_user_id'] = $users_mst_user_id;
            $data['create_user'] = $update_user_name;
            $data['create_at'] = Carbon::now();
            $data['update_user'] = $update_user_name;
            $data['update_at'] = Carbon::now();
            $workingMonth = substr($data['work_date'], 0, 6);
            // Process only one flag vacation of HrTimeCardDetail
            $isVacation = true;
            $halfVacation = 0;
            $data['paid_vacation_flg'] = 0;
            $data['sp_vacation_flg'] = 0;
            $data['day_off_flg'] = 0;
            if ($data['vacation_flg'] == 'paid_vacation_flg') {
                $data['paid_vacation_flg'] = 1;
            } elseif ($data['vacation_flg'] == 'sp_vacation_flg') {
                $data['sp_vacation_flg'] = 1;
            } elseif ($data['vacation_flg'] == 'day_off_flg') {
                $data['day_off_flg'] = 1;
            } elseif ($data['vacation_flg'] == 'half_paid_vacation_flg') {
                $data['paid_vacation_flg'] = 2;
                $isVacation = false;
                $halfVacation = 1;
            } elseif ($data['vacation_flg'] == 'half_sp_vacation_flg') {
                $data['sp_vacation_flg'] = 2;
                $isVacation = false;
                $halfVacation = 1;
            } elseif ($data['vacation_flg'] == 'half_day_off_flg') {
                $data['day_off_flg'] = 2;
                $isVacation = false;
                $halfVacation = 1;
            } else {
                $isVacation = false;
            }
            unset($data['vacation_flg']);
            // 休暇記録等－有給、特休、代休
            if ($isVacation) {
                $data['work_start_time'] = null;
                $data['work_end_time'] = null;
                $this->model->insert($data);
                $timeCard = HrTimeCard::where([
                    'mst_user_id' => $users_mst_user_id,
                    'working_month' => $workingMonth
                ])->first();
                if (empty($timeCard)) {
                    $timeCard['mst_user_id'] = $users_mst_user_id;;
                    $timeCard['working_month'] = $workingMonth;
                    $timeCard['create_user'] = $update_user_name;
                    $timeCard['create_at'] = $clickTime;
                    $timeCard['update_user'] = $update_user_name;
                    $timeCard['update_at'] = $clickTime;
                    HrTimeCard::insert($timeCard);
                }
                return $this->sendSuccess('更新が完了しました。');
            }
            // Calculate Working Time Real and OverTime
            $workingTimeReal = 0;
            $overtimeReal = 0;
            $holidayWorkTimeReal = 0;
            $midnightWorkTimeReal = 0;

            if ($data['work_end_time'] && $data['work_start_time']) {
                // 休暇記録等－通常勤務、早退、遅刻、欠勤、有給（半休）、特休（半休）、代休（半休）
                if ($data['work_start_time'] > $data['work_end_time']) {
                    return $this->sendError('出勤時間＜退勤時間としてください。');
                }
                // get working _time, overtime
                $timeRegulation = HrUtils::getHrTimeRegulation($users_mst_user_id, $data['shift_work_kbn']);
                /* round down time to minute  */
                /**
                 * Carbon to convert string to Datetime
                 * format if workStartTime (Datetime) not is round down to minute (use for function leaveWork
                 * $workStartTime = Carbon::parse($data['work_start_time'])->format('Y-m-d H:i');
                 * $workEndTime = Carbon::parse($data['work_end_time'])->format('Y-m-d H:i');
                 */
                $workStartTime = $data['work_start_time'];
                $workEndTime = $data['work_end_time'];
                $timeReal = HrUtils::calculateWorkingTimeAndOverTime($workStartTime, $workEndTime, $data['break_time'], $timeRegulation, $halfVacation, $data['work_date']);
                $data['shift_work_kbn'] = $timeReal['shift_work_kbn'];
                $workingTimeReal = $timeReal['working_time_real'];
                $overtimeReal = $timeReal['overtime_real'];
                  //check holiday working
                if(!!$data['holiday_work_flg']){
                    $holidayWorkTimeReal = min($workingTimeReal, 1440);
                }
                $midnightWorkTimeReal = HrUtils::caculateMidNightWorkingTime($workStartTime, $workEndTime,  $data['midnight_break_time']);
            } else {
                // 休暇記録等－欠勤
                if ($data['work_end_time']) {
                    $data['work_start_time'] = null;
                }
                if ($data['work_start_time']) {
                    $data['work_end_time'] = null;
                }
                if (empty($data['work_end_time']) && empty($data['work_start_time'])) {
                    $data['work_end_time'] = null;
                    $data['work_start_time'] = null;
                }
                $data['shift_work_kbn'] = $data['shift_work_kbn'];
            }
            $data['working_time'] = $workingTimeReal;
            $data['overtime'] = $overtimeReal;
            $data['holiday_working_time'] = $holidayWorkTimeReal;
            $data['midnight_time'] = $midnightWorkTimeReal;
            $this->model->insert($data);

            $timeCard = HrTimeCard::where([
                'mst_user_id' => $users_mst_user_id,
                'working_month' => $workingMonth
            ])->first();
            if (empty($timeCard)) {
                $timeCard['mst_user_id'] = $users_mst_user_id;
                $timeCard['working_month'] = $workingMonth;
                $timeCard['create_user'] = $update_user_name;
                $timeCard['create_at'] = $clickTime;
                $timeCard['update_user'] = $update_user_name;
                $timeCard['update_at'] = $clickTime;
                HrTimeCard::insert($timeCard);
            }
            DB::commit();
            return $this->sendSuccess('更新が完了しました。');
        } catch (Exception $ex) {
            Log::error('UserWorkDetailAPIController@store:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Update a HrTimecardDetail in storage.
     * (勤務編集画面 更新 勤務詳細登録処理)
     * PUT /timecard-detail/{timecard-detail_id}
     *
     * @param CreateHrTimeCardDetailAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function update (CreateHrTimeCardDetailAPIRequest $request)
    {
        $data = $request->all();
        $user = $request->user();
        $clickTime = Carbon::now();

        $users_mst_user_id    = $data['users']['id'];
        $users_mst_company_id = $data['users']['mst_company_id'];
        $update_user_name     = $user->family_name . ' ' . $user->given_name;
        // data配列を丸ごとinsertしているため
        // 管理下スタッフのユーザ情報を取得後、配列から削除
        unset($data['users']);

        try {
            $timeCard['update_user'] = $update_user_name;
            $timeCard['update_at'] = Carbon::now();
            $timeCard['work_date'] = $data['work_date'];
            $timeCard['work_start_time'] = $workStartTime = $data['work_start_time'];
            $timeCard['work_end_time'] = $workEndTime = $data['work_end_time'];
            $timeCard['absent_flg'] = $data['absent_flg'];
            $timeCard['late_flg'] = $data['late_flg'];
            $timeCard['earlyleave_flg'] = $data['earlyleave_flg'];
            $timeCard['holiday_work_flg'] = $data['holiday_work_flg'];
            $timeCard['break_time'] = $data['break_time'];
            $timeCard['midnight_break_time'] = $data['midnight_break_time'];
            $timeCard['memo'] = $data['memo'];
            $timeCard['work_detail'] = $data['work_detail'];
            $timeCard['admin_memo'] = $data['admin_memo'];

            $hrTimeCardDetail = DB::table('hr_timecard_detail')
                ->where('id', $data['id'])
                ->first();
            if (empty($hrTimeCardDetail)) {
                return $this->sendError('退勤時刻の登録処理に成功しませんでした。');
            }

            // Process only one flag vacation of HrTimeCardDetail
            $timeCard['paid_vacation_flg'] = 0;
            $timeCard['sp_vacation_flg'] = 0;
            $timeCard['day_off_flg'] = 0;
            $isVacation = true;
            $halfVacation = 0;
            if (isset($data['vacation_flg'])) {
                if ($data['vacation_flg'] == 'paid_vacation_flg') {
                    $timeCard['paid_vacation_flg'] = 1;
                } elseif ($data['vacation_flg'] == 'sp_vacation_flg') {
                    $timeCard['sp_vacation_flg'] = 1;
                } elseif ($data['vacation_flg'] == 'day_off_flg') {
                    $timeCard['day_off_flg'] = 1;
                } elseif ($data['vacation_flg'] == 'half_paid_vacation_flg') {
                    $timeCard['paid_vacation_flg'] = 2;
                    $halfVacation = 1;
                    $isVacation = false;
                } elseif ($data['vacation_flg'] == 'half_sp_vacation_flg') {
                    $timeCard['sp_vacation_flg'] = 2;
                    $halfVacation = 1;
                    $isVacation = false;
                } elseif ($data['vacation_flg'] == 'half_day_off_flg') {
                    $timeCard['day_off_flg'] = 2;
                    $halfVacation = 1;
                    $isVacation = false;
                } else {
                    $isVacation = false;
                }
            }
            // 休暇記録等－有給、特休、代休
            if ($isVacation) {
                $timeCard['shift_work_kbn'] = $data['shift_work_kbn'];
                $timeCard['work_start_time'] = null;
                $timeCard['work_end_time'] = null;
                $timeCard['working_time'] = 0;
                $timeCard['overtime'] = 0;
                $timeCard['break_time'] = 0;
                $timeCard['midnight_break_time'] = 0;
                $timeCard['holiday_working_time'] = 0;
                $timeCard['earlyleave_flg'] = 0;
                $timeCard['absent_flg'] = 0;
                $timeCard['late_flg'] = 0;
                $this->model->where([
                    'id' => $data['id'],
                    'mst_user_id' => $users_mst_user_id
                ])->update($timeCard);
                 
                return $this->sendSuccess('更新が完了しました。');
            }

            $workingDay = $clickTime->isoFormat('YYYYMMDD');
            if($timeCard['work_start_time'] && $timeCard['work_end_time'] == ''){
                if($timeCard['work_date'] != $workingDay){
                    return $this->sendError('退勤時間は必須項目です。');
                }
            }
            // Calculate Working Time Real and OverTime
            $workingTimeReal = 0;
            $overtimeReal = 0;
            $holidayWorkTimeReal = 0;
            $midnightWorkTimeReal = 0;
            if ($workStartTime && $workEndTime) {
                // 休暇記録等－通常勤務、早退、遅刻、欠勤、有給（半休）、特休（半休）、代休（半休）
                if ($workStartTime > $workEndTime) {
                    return $this->sendError('出勤時間＜退勤時間としてください。');
                }
                // get TimeRegulation
                $timeRegulation = HrUtils::getHrTimeRegulation($users_mst_user_id, $data['shift_work_kbn']);

                $timeReal = HrUtils::calculateWorkingTimeAndOverTime($workStartTime, $workEndTime, $timeCard['break_time'], $timeRegulation, $halfVacation, $data['work_date']);
                $timeCard['shift_work_kbn'] = $timeReal['shift_work_kbn'];
                $workingTimeReal = $timeReal['working_time_real'];
                $overtimeReal = $timeReal['overtime_real'];
                //check holiday working
                if(!!$data['holiday_work_flg']){
                    $holidayWorkTimeReal = min($workingTimeReal, 1440);
                }
                $midnightWorkTimeReal = HrUtils::caculateMidNightWorkingTime($workStartTime, $workEndTime,  $timeCard['midnight_break_time']);
            } else {
                // 休暇記録等－欠勤
                if ($timeCard['work_end_time']) {
                    $timeCard['work_start_time'] = null;
                }
                if ($timeCard['work_start_time']) {
                    $timeCard['work_end_time'] = null;
                }
                if (empty($timeCard['work_end_time']) && empty($timeCard['work_start_time'])) {
                    $timeCard['work_end_time'] = null;
                    $timeCard['work_start_time'] = null;
                }
                $timeCard['shift_work_kbn'] = $data['shift_work_kbn'];
            }

            $timeCard['working_time'] = $workingTimeReal;
            $timeCard['overtime'] = $overtimeReal;
            $timeCard['holiday_working_time'] = $holidayWorkTimeReal;
            $timeCard['midnight_time'] = $midnightWorkTimeReal;
            $this->model->where([
                'id' => $data['id'],
                'mst_user_id' => $users_mst_user_id
            ])->update($timeCard);
            return $this->sendSuccess('更新が完了しました。');
        } catch (Exception $ex) {
            Log::error('UserWorkDetailAPIController@update:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
