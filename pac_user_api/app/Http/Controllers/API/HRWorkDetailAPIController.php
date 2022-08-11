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
use App\Http\Requests\API\GetHrTimeCardDetailAPIRequest;
use App\Http\Requests\API\CreateHrTimeCardDetailAPIRequest;
use App\Http\Requests\API\UpdateHrTimeCardDetailAPIRequest;
use App\Http\Requests\API\ExportMstHrTimeCardDetailAPIRequest;
use App\Http\Utils\HrUtils;
use App\Http\Utils\AppUtils;
use App\Http\Utils\MailUtils;
use Session;

/**
 * Class WorkDetailAPIController
 * @package App\Http\Controllers\API
 */

class HRWorkDetailAPIController extends AppBaseController
{
    var $table = 'hr_timecard_detail';
    var $model = null;

    public function __construct(HrTimecardDetail $hrTimecardDetail)
    {
        $this->model = $hrTimecardDetail;
    }

    /**
     * Display the specified Company.
     * GET|HEAD /getWorkDetail/{working_month}
     *
     * @param Request $request
     * @param int $working_month
     *
     * @return Response
     */
    public function getWorkDetail(Request $request, $working_month)
    {
        $user  = $request->user();
        try{
            $workDetails = DB::table('hr_timecard_detail')->where('mst_company_id', $user->mst_company_id)
            ->where('mst_user_id', $user->id)
            ->where(DB::raw('SUBSTRING(work_date, 1, 6)'), '=', $working_month)->get();

            return $this->sendResponse($workDetails, 'HRタイムカードデータの取得処理に成功しました');
        }
        catch (Exception $ex) {
            Log::error('HRWorkDetailAPIController@getWorkDetail:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getWorkList(Request $request, $working_month)
    {
        $user  = $request->user();
        try{
            $timeCard = HrTimeCard::where([
            'mst_user_id' => $user->id,
            'working_month' => $working_month
            ])->first();

            return $this->sendResponse($timeCard, 'HRタイムカードデータの取得処理に成功しました');
        }
        catch (Exception $ex) {
            Log::error('HRWorkDetailAPIController@getWorkList:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    /**
     * Display the specified HrTimecardDetail.
     * GET|HEAD /timecard-detail
     *
     * @param Request $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function index (Request $request)
    {
        try {
            $today = Carbon::now()->format('Ymd');
            $lastTimeCardDetail = $this->model->where('work_date', '<=', $today)
                ->where('mst_user_id', '=', $request->user()->id)
                ->orderBy('work_date', 'DESC')->orderBy('create_at', 'DESC')->first();

            if ($lastTimeCardDetail) {
                $previousTimeCardDetail = $this->model->where('work_date', '<', $lastTimeCardDetail->work_date)
                    ->where('mst_user_id', '=', $request->user()->id)
                    ->where('work_end_time', null)
                    ->where('work_start_time', '!=', null)
                    ->orderBy('work_date', 'DESC')->orderBy('create_at', 'DESC')->first();
            } else {
                $previousTimeCardDetail = null;
            }

            if ($previousTimeCardDetail) {
                if ($lastTimeCardDetail->work_date == $today) {
                    // check if lastTimeCardDetail is empty record
                    if (!$lastTimeCardDetail->work_start_time && !$lastTimeCardDetail->work_end_time && !$lastTimeCardDetail->paid_vacation_flg && !$lastTimeCardDetail->sp_vacation_flg && !$lastTimeCardDetail->day_off_flg) {
                        $lastTimeCardDetail = $previousTimeCardDetail;
                    }
                } else {
                    $lastTimeCardDetail = $previousTimeCardDetail;
                }
            } else {
                if ($lastTimeCardDetail){
                    // check if $lastTimeCardDetail is not for today
                    if ($lastTimeCardDetail->work_date != $today){
                        // check if $lastTimeCardDetail is day off
                        if ($lastTimeCardDetail->paid_vacation_flg || $lastTimeCardDetail->sp_vacation_flg || $lastTimeCardDetail->day_off_flg){
                            $lastTimeCardDetail = null;
                        }else if (($lastTimeCardDetail->work_start_time && $lastTimeCardDetail->work_end_time) || (!$lastTimeCardDetail->work_start_time && !$lastTimeCardDetail->work_end_time)){
                            // check if $lastTimeCardDetail have start and end time
                            $lastTimeCardDetail = null;
                        }
                    }
                }
            }
            return $this->sendResponse($lastTimeCardDetail, 'HRタイムカードデータの取得処理に成功しました');
        } catch (Exception $ex) {
            Log::error('HRWorkDetailAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 勤務詳細初期表示－HRユーザ情報取得処理
     * GET|HEAD /getWorkDetailHrInfo/{id}
     *
     * @param Request $request
     * @param int     $id            スタッフのユーザID（ログインのユーザIDではない）※未使用
     *
     * @return Response
     */
    public function getWorkDetailHrInfo(Request $request, $id)
    {
        // ログインユーザ情報を取得
        $user = $request->user();

        try{
            $workDetails =
            DB::table ('mst_user as U')
            ->leftJoin('mst_hr_info as I',      'I.mst_user_id', DB::raw($user->id))
            ->leftJoin('hr_working_hours as H', 'H.id',          'I.working_hours_id')
            ->where   ('U.mst_company_id',      $user->mst_company_id)
            ->where   ('U.id',                  $user->id)
            ->select( 
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
            Log::error('HRWorkDetailAPIController@getWorkDetailHrInfo:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendResponse(['status' => false, 'message' => \Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]], '');
        }
    }

    /**
     * 勤務編集初期表示－勤務詳細情報取得処理
     * GET|HEAD /getWorkDetailByTimecard/{id}
     *
     * @param int $id 勤務詳細.id
     * @param Request $request
     *
     * @return Response
     */
    public function getWorkDetailByTimecard ($id, Request $request)
    {
        try {
            // ログインしているユーザ情報を取得
            $user = $request->user();

            $WorkDetail = 
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
                        'D.holiday_work_flg,' .
                        'D.shift_work_kbn,' .
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
            return $this->sendResponse($WorkDetail, 'HRタイムカードデータの取得処理に成功しました');
        } catch (Exception $ex) {
            Log::error('HRWorkDetailAPIController@getWorkDetailByTimecard:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
        $clickTime = Carbon::now();
        try {
            $data['mst_company_id'] = $user->mst_company_id;
            $data['mst_user_id'] = $user->id;
            $data['create_user'] = $user->email;
            $data['create_at'] = Carbon::now();
            $data['update_user'] = $user->email;
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
                    'mst_user_id' => $user->id,
                    'working_month' => $workingMonth
                ])->first();
                if (empty($timeCard)) {
                    $timeCard['mst_user_id'] = $user->id;
                    $timeCard['working_month'] = $workingMonth;
                    $timeCard['create_user'] = $user->email;
                    $timeCard['create_at'] = $clickTime;
                    $timeCard['update_user'] = $user->email;
                    $timeCard['update_at'] = $clickTime;
                    HrTimeCard::insert($timeCard);
                }
                return $this->sendSuccess('更新が完了しました。');
            }

            // 稼働時間と残業時間を計算
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
                $timeRegulation = HrUtils::getHrTimeRegulation($user->id, $data['shift_work_kbn']);
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
            $data['holiday_working_time'] = $holidayWorkTimeReal;
            $data['midnight_time'] = $midnightWorkTimeReal;
            $data['overtime'] = $overtimeReal;
            $this->model->insert($data);

            $timeCard = HrTimeCard::where([
                'mst_user_id' => $user->id,
                'working_month' => $workingMonth
            ])->first();
            if (empty($timeCard)) {
                $timeCard['mst_user_id'] = $user->id;
                $timeCard['working_month'] = $workingMonth;
                $timeCard['create_user'] = $user->email;
                $timeCard['create_at'] = $clickTime;
                $timeCard['update_user'] = $user->email;
                $timeCard['update_at'] = $clickTime;
                HrTimeCard::insert($timeCard);
            }
            DB::commit();
            return $this->sendSuccess('更新が完了しました。');
        } catch (Exception $ex) {
            Log::error('HRWorkDetailAPIController@store:' . $ex->getMessage().$ex->getTraceAsString());
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
        try {
            $timeCard['update_user'] = $user->email;
            $timeCard['update_at'] = Carbon::now();
            $timeCard['work_date'] = $data['work_date'];
            $timeCard['work_start_time'] = $workStartTime = $data['work_start_time'];
            $timeCard['work_end_time'] = $workEndTime = $data['work_end_time'];
            $timeCard['absent_flg'] = $data['absent_flg'];
            $timeCard['late_flg'] = $data['late_flg'];
            $timeCard['holiday_work_flg'] = $data['holiday_work_flg'];
            $timeCard['midnight_break_time'] = $data['midnight_break_time'];
            $timeCard['earlyleave_flg'] = $data['earlyleave_flg'];
            $timeCard['break_time'] = $data['break_time'];
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
                $timeCard['holiday_working_time'] = 0;
                $timeCard['midnight_break_time'] = 0;
                $timeCard['earlyleave_flg'] = 0;
                $timeCard['absent_flg'] = 0;
                $timeCard['late_flg'] = 0;
                $this->model->where([
                    'id' => $data['id'],
                    'mst_user_id' => $user->id
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
                $timeRegulation = HrUtils::getHrTimeRegulation($user->id, $data['shift_work_kbn']);

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
            $timeCard['holiday_working_time'] = 0;
            $timeCard['holiday_working_time'] = $holidayWorkTimeReal;
            $timeCard['midnight_time'] = $midnightWorkTimeReal;
            $timeCard['working_time'] = $workingTimeReal;
            $timeCard['overtime'] = $overtimeReal;
            $this->model->where([
                'id' => $request->timecard_detail,
                'mst_user_id' => $user->id
            ])->update($timeCard);
            return $this->sendSuccess('更新が完了しました。');
        } catch (Exception $ex) {
            Log::error('HRWorkDetailAPIController@update:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function registerNewTimeCardDetail(Request $request) { 
        $input = $request->all();
        $user = $request->user();
        $clickTime = Carbon::now();

        $workDate = $clickTime->isoFormat('YYYYMMDD');
        $existTimeCardDetail = DB::table('hr_timecard_detail')
            ->where('work_date', $workDate)
            ->where('mst_user_id', $user->id)
            ->first();
        if (!empty($existTimeCardDetail) &&
            ($existTimeCardDetail->work_end_time || $existTimeCardDetail->paid_vacation_flg === 1 || $existTimeCardDetail->sp_vacation_flg === 1 || $existTimeCardDetail->day_off_flg === 1 )) {
            return $this->sendError('レコードが存在しました。');
        }

        if ($input['insertType'] === 'startWork') {
            $hrInfo = DB::table('mst_hr_info')->where('mst_user_id', $user->id)
                ->orderBy('create_at', 'DESC')->first();
            $lateFlg = 0;
            if (!empty($existTimeCardDetail) && ($existTimeCardDetail->paid_vacation_flg === 2 || $existTimeCardDetail->sp_vacation_flg === 2 || $existTimeCardDetail->day_off_flg === 2)) {
                if ($existTimeCardDetail->paid_vacation_flg === 2) {
                    $data['paid_vacation_flg'] = 2;
                } elseif ($existTimeCardDetail->sp_vacation_flg ===2) {
                    $data['sp_vacation_flg'] = 2;
                } elseif ($existTimeCardDetail->day_off_flg === 2) {
                    $data['day_off_flg'] = 2;
                }
                $breakTime = 0;
            } else {
                if (!empty($hrInfo)) {
                    if (isset($input['shift_work_kbn']) && $input['shift_work_kbn'] == 1){
                        $start_time = $hrInfo->shift1_start_time;
                        $end_time = $hrInfo->shift1_end_time;
                    }elseif (isset($input['shift_work_kbn']) && $input['shift_work_kbn'] == 2){
                        $start_time = $hrInfo->shift2_start_time;
                        $end_time = $hrInfo->shift2_end_time;
                    } elseif (isset($input['shift_work_kbn']) && $input['shift_work_kbn'] == 3){
                        $start_time = $hrInfo->shift3_start_time;
                        $end_time = $hrInfo->shift3_end_time;
                    }else{
                        $start_time = $hrInfo->Regulations_work_start_time;
                        $end_time = $hrInfo->Regulations_work_end_time;
                    }

                    if ($start_time < $end_time) { // work during the day
                        if ($clickTime->toTimeString() > $start_time && $clickTime->toTimeString() < $end_time) {
                            $lateFlg = 1;
                        }
                    } else if ($start_time > $end_time) { // work over night
                        if ($clickTime->toTimeString() > $start_time || $clickTime->toTimeString() < $end_time) {
                            $lateFlg = 1;
                            if ($clickTime->toTimeString() < $end_time) {
                                $workDate = $clickTime->copy()->subDay()->isoFormat('YYYYMMDD');
                            }
                        }
                    }
                    $breakTime = $hrInfo->break_time;
                } else {
                    $breakTime = config('app.break_time_default');
                }
            }
            $data['work_start_time'] = $clickTime;
            $data['start_stamping'] = $clickTime;
            $data['break_time'] = $breakTime;
            $data['late_flg'] = $lateFlg;
        } elseif ($input['insertType'] === 'absentWork') {
            $data['absent_flg'] = 1;
            $data['working_time'] = 0;
            $data['overtime'] = 0;
        } elseif ($input['insertType'] === 'onPaid') {
            $data['paid_vacation_flg'] = 1;
            Session::put('onPaid', true);
        } elseif ($input['insertType'] === 'onSpecialHoliday') {
            Session::put('onSpecialHoliday', true);
            $data['sp_vacation_flg'] = 1;
        } elseif ($input['insertType'] === 'onSubstituteHoliday') {
            Session::put('onSubstituteHoliday', true);
            $data['day_off_flg'] = 1;
        } elseif ($input['insertType'] === 'onHalfPaid') {
            Session::put('onHalfPaid', true);
            $data['paid_vacation_flg'] = 2;
            $data['late_flg'] = 0;
            $data['break_time'] = 0;
        } elseif ($input['insertType'] === 'onHalfSpecialHoliday') {
            Session::put('onHalfSpecialHoliday', true);
            $data['sp_vacation_flg'] = 2;
            $data['late_flg'] = 0;
            $data['break_time'] = 0;
        } elseif ($input['insertType'] === 'onHalfSubstituteHoliday') {
            Session::put('onHalfSubstituteHoliday', true);
            $data['day_off_flg'] = 2;
            $data['late_flg'] = 0;
            $data['break_time'] = 0;
        }
        if (isset($input['memo']) && trim($input['memo'])){
            $data['memo'] = trim($input['memo']);
        }
        if (isset($input['work_detail']) && trim($input['work_detail'])){
            $data['work_detail'] = trim($input['work_detail']);
        }
        if (isset($input['holiday_work_flg'])){
            $data['holiday_work_flg'] = $input['holiday_work_flg'];
        }
        try {
            DB::beginTransaction();
            $data['mst_company_id'] = $user->mst_company_id;
            $data['mst_user_id'] = $user->id;
            if (!empty($existTimeCardDetail)) {
                $data['update_user'] = $user->email;
                $data['update_at'] = $clickTime;
                if ($input['insertType'] !== 'startWork') unset($data['work_start_time']);
                $this->model->where([
                    'id' => $existTimeCardDetail->id,
                    'mst_company_id' =>  $existTimeCardDetail->mst_company_id,
                    'mst_user_id' => $existTimeCardDetail->mst_user_id
                ])->update($data);
            } else {
                $data['shift_work_kbn'] = $input['shift_work_kbn'] ?? 0;
                $data['work_date'] = $workDate;
                $data['update_user'] = $user->email;
                $data['update_at'] = $clickTime;
                $data['create_user'] = $user->email;
                $data['create_at'] = $clickTime;
                $this->model->insert($data);
            }

            $workingMonth = substr($workDate, 0, 6);
            $timeCard = HrTimeCard::where([
                'mst_user_id' => $user->id,
                'working_month' => $workingMonth
            ])->first();
            if (empty($timeCard)) {
                $timeCard['mst_user_id'] = $user->id;
                $timeCard['working_month'] = $workingMonth;
                $timeCard['update_user'] = $user->email;
                $timeCard['update_at'] = $clickTime;
                $timeCard['create_user'] = $user->email;
                $timeCard['create_at'] = $clickTime;
                HrTimeCard::insert($timeCard);
            }
            DB::commit();

            // response message
            if ($input['insertType'] === 'startWork') {
                return $this->sendResponse($data, '出勤時刻を登録しました。');
            } elseif ($input['insertType'] === 'absentWork') {
                return $this->sendResponse($data, '欠勤を登録しました。');
            } elseif ($input['insertType'] === 'onPaid' || $input['insertType'] === 'onHalfPaid') {
                return $this->sendResponse($data, '有給を登録しました。');
            } elseif ($input['insertType'] === 'onSpecialHoliday' || $input['insertType'] === 'onHalfSpecialHoliday') {
                return $this->sendResponse($data, '特休を登録しました。');
            } elseif ($input['insertType'] === 'onSubstituteHoliday' || $input['insertType'] === 'onHalfSubstituteHoliday') {
                return $this->sendResponse($data, '代休を登録しました。');
            }
        } catch (Exception $ex) {
            DB::rollback();
            Log::error('HRWorkDetailAPIController@registerNewTimeCardDetail:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function breakWork(UpdateHrTimeCardDetailAPIRequest $request) {
        $data = $request->all();
        $user = $request->user();

        $breakNow = Carbon::now();
        $breakType = $data['type'];
        $timeNumber = $data['break_number'];
        unset($data['type'],$data['break_number']);

        $hrTimeCardDetail = DB::table('hr_timecard_detail')
            ->where('id', $data['id'])
            ->first();
        if (empty($hrTimeCardDetail)) {
            return $this->sendError('退勤時刻の登録処理に成功しませんでした。');
        }

        $targetName = 'break'.$timeNumber.'_'.$breakType.'_time';
        if($breakType == 'end'){
            $breakTime = 0;
            $midNightBreakTime = 0;
            $hrTimeCardDetail->$targetName = $breakNow;
            //calculate break sum time
            for($i = 1; $i <= 5 ; $i++){
                $start_key = 'break'.$i.'_start_time';
                $end_key = 'break'.$i.'_end_time';
                $breakStart = $hrTimeCardDetail->$start_key;
                $breakEnd = $hrTimeCardDetail->$end_key;
                if($breakStart && $breakEnd){
                    if($hrTimeCardDetail->$start_key && $hrTimeCardDetail->work_end_time && ($hrTimeCardDetail->$end_key > $hrTimeCardDetail->work_end_time)) {
                        $breakEnd = $hrTimeCardDetail->work_end_time;
                    }
                }else{
                    break;
                }
                $calBreakTime = HrUtils::calculateBreakTime($breakStart, $breakEnd);
                $breakTime += $calBreakTime['break_time'];
                $midNightBreakTime += $calBreakTime['midnight_break_time'];
            }
            // get default break time
            $hrInfo = DB::table('mst_hr_info')->where('mst_user_id', $user->id)
            ->orderBy('create_at', 'DESC')->first();
            if (!empty($hrInfo)) {
                $breakTime += $hrInfo->break_time;
            } else {
                $breakTime += config('app.break_time_default');
            }
            $data['break_time'] = $breakTime;
            $data['midnight_break_time'] = $midNightBreakTime;
        }

        if (isset($data['memo']) && !trim($data['memo'])){
            unset($data['memo']);
        }
        if (isset($data['work_detail']) && !trim($data['work_detail'])){
            unset($data['work_detail']);
        }
        try {
            DB::beginTransaction();
            $data[$targetName] = $breakNow;
            $data['update_user'] = $user->email;
            $data['update_at'] = Carbon::now();
            $this->model->where([
                'id' => $request->id,
                'mst_company_id' =>  $user->mst_company_id,
                'mst_user_id' => $user->id
            ])->update($data);

            DB::commit();
            return $this->sendResponse($data, '休憩を更新しました。');
        } catch (Exception $ex) {
            DB::rollback();
            Log::error('HRWorkDetailAPIController@breakWork:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function leaveWork(UpdateHrTimeCardDetailAPIRequest $request) {
        $data = $request->all();
        $user = $request->user();

        $endTime = Carbon::now();
        $data['shift_work_kbn'] = $data['shift_work_kbn'] ?? AppUtils::SHIFT_WORK_NORMAL;
        // get working_time_rule, Regulations_work_end/start_time
        $timeRegulation = HrUtils::getHrTimeRegulation($user->id, $data['shift_work_kbn']);

        $hrTimeCardDetail = DB::table('hr_timecard_detail')
            ->where('id', $data['id'])
            ->first();
        if (empty($hrTimeCardDetail)) {
            return $this->sendError('退勤時刻の登録処理に成功しませんでした。');
        }
        $halfVacation = 0;
        if ($hrTimeCardDetail->paid_vacation_flg === 2 || $hrTimeCardDetail->sp_vacation_flg === 2 || $hrTimeCardDetail->day_off_flg === 2) {
            $halfVacation = 1;
        }
        $breakTime = 0;
        $midNightBreakTime = 0;
        //calculate break sum time
        for($i = 1; $i <= 5 ; $i++){
            $start_key = 'break'.$i.'_start_time';
            $end_key = 'break'.$i.'_end_time';
            $breakStart = $hrTimeCardDetail->$start_key;
            $breakEnd = $hrTimeCardDetail->$end_key;
            if(!($breakStart && $breakEnd)){
                if($hrTimeCardDetail->$start_key && !$hrTimeCardDetail->$end_key && $endTime) {
                    $data[$end_key] = $breakEnd = $endTime;
                }else{
                    break;
                }
            }
            $calBreakTime = HrUtils::calculateBreakTime($breakStart, $breakEnd);
            $breakTime += $calBreakTime['break_time'];
            $midNightBreakTime += $calBreakTime['midnight_break_time'];
        }

        //get default break time
        $hrInfo = DB::table('mst_hr_info')->where('mst_user_id', $user->id)
        ->orderBy('create_at', 'DESC')->first();

        if (!empty($hrInfo)) {
            $breakTime += $hrInfo->break_time;
        } else {
            $breakTime += config('app.break_time_default');
        }
        $hrTimeCardDetail->break_time = $data['break_time'] = $breakTime;
        $data['midnight_break_time'] = $midNightBreakTime;
        // Calculate Working Time Real and OverTime
        $timeReal = HrUtils::calculateWorkingTimeAndOverTime($hrTimeCardDetail->work_start_time, $endTime ->format('Y-m-d H:i'), $hrTimeCardDetail->break_time, $timeRegulation, $halfVacation, $hrTimeCardDetail->work_date);
        $workingTimeReal = $timeReal['working_time_real'];
        $overtimeReal = $timeReal['overtime_real'];
        // Calculate Holiday Working Time Real and Mid Night work Time Real
        $holidayWorkTimeReal = 0;
        if(!!$data['holiday_work_flg']){
            $holidayWorkTimeReal = min($workingTimeReal, 1440);
        }
        $midnightWorkTimeReal = HrUtils::caculateMidNightWorkingTime($hrTimeCardDetail->work_start_time, $endTime,  $midNightBreakTime);
        $hrInfo_existed = $timeRegulation['hrInfo_existed'];
        $earlyFlg = 0;
        if ($hrTimeCardDetail->paid_vacation_flg === 2 || $hrTimeCardDetail->sp_vacation_flg === 2 || $hrTimeCardDetail->day_off_flg === 2) {
            $earlyFlg = 0;
        } else {
            if ($workingTimeReal < $timeRegulation['working_time_rule']) {
                if ($hrInfo_existed) {
                    $endTimeRegulation = $timeRegulation['work_start_time'];
                    $startTimeRegulation = $timeRegulation['work_end_time'];
                    $overNight = $timeRegulation['overNight'];
                    if ($overNight) {
                        if ($endTime->toTimeString() < $endTimeRegulation || $endTime->toTimeString() > $startTimeRegulation) {
                            $earlyFlg = 1;
                        }
                    } else {
                        if ($endTime->toTimeString() > $startTimeRegulation && $endTime->toTimeString() < $endTimeRegulation) {
                            $earlyFlg = 1;
                        }
                    }
                }
            }
        }

        if (isset($data['memo']) && !trim($data['memo'])){
            unset($data['memo']);
        }
        if (isset($data['work_detail']) && !trim($data['work_detail'])){
            unset($data['work_detail']);
        }
        try {
            DB::beginTransaction();
            $data['shift_work_kbn'] = $timeReal['shift_work_kbn'];
            $data['work_end_time'] = $endTime;
            $data['end_stamping'] = $endTime;
            $data['earlyleave_flg'] = $earlyFlg;
            $data['working_time'] = $workingTimeReal;
            $data['holiday_working_time'] =  $holidayWorkTimeReal;
            $data['midnight_time'] = $midnightWorkTimeReal;
            $data['overtime'] = $overtimeReal;
            $data['update_user'] = $user->email;
            $data['update_at'] = Carbon::now();
            $this->model->where([
                'id' => $request->id,
                'mst_company_id' =>  $user->mst_company_id,
                'mst_user_id' => $user->id
            ])->update($data);

            DB::commit();
            return $this->sendResponse($data, '退勤時刻を登録しました。');
        } catch (Exception $ex) {
            DB::rollback();
            Log::error('HRWorkDetailAPIController@leaveWork:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
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
    public function exportHrWorkListToCSV(ExportMstHrTimeCardDetailAPIRequest $request) {
        $user = $request->user();
        $columnSelect = $request->all();
        $workMonth = $columnSelect['work_month'];
        $columnSelect = $columnSelect['export_work_list_columns'];
        $userEmail = $user->email;
        $userName = $user->family_name . ' ' . $user->given_name;

        try {
            $data['name'] = $userName;
            $data['email'] = $userEmail;
            $timeCardDetails = $this->model
                ->where([
                    'mst_user_id' => $user->id,
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
                if (in_array('work_detail', $columnSelect)) {
                    $timeCard .= ',';
                    if (isset($timeCardDetail->work_detail)) {
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
            Log::error('WorkListAPIController@exportHrWorkListToCSV:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * get the work time list   shift1,2,3
     * @param Request $request
     * @return mixed
     */
    function workTime(Request $request){
        $user = $request->user();
        try {
            $info = DB::table('mst_hr_info')->where('mst_user_id',$user->id)->first();

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendResponse(['info' => $info], 'ユーザー情報の取得処理に成功しました。');
    }
    function showWorkTime(Request $request,$id){
        $user = $request->user();
        try {
            $info = DB::table('hr_working_hours')->where("id",$id)->where('mst_company_id',$user->mst_company_id)->first();

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendResponse(['info' => $info], 'ユーザー情報の取得処理に成功しました。');
    }








    /**
     * HR勤怠連絡メール設定情報取得処理
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getHrMailSetting(Request $request)
    {
        $user  = $request->user();
        try{
            $mail_setting = DB::table('hr_mail_setting')
                ->where('mst_user_id',$user->id)
                ->first();
            if($mail_setting){
                $mail_setting->name = $user->family_name . ' ' . $user->given_name;
                $mail_setting->email = $user->email;
            }
            return $this->sendResponse($mail_setting, __('message.success.hr_mail_setting.get'));
        }
        catch (Exception $ex) {
            Log::error('UserWorkDetailAPIController@getHrMailSetting:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(__('message.false.hr_mail_setting.get'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * HR勤怠連絡メール設定情報更新処理
     *
     * @param Request $request
     * @return mixed
     */
    public function updateHrMailSetting(Request $request)
    {
        try {
            $user  = $request->user();
            $email_lists = $request->get('emailList',[]);
            if(count($email_lists) == 0){
                return $this->sendError(__('message.false.hr_mail_setting.contact_count_min'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }else if(count($email_lists) > 5){
                return $this->sendError(__('message.false.hr_mail_setting.contact_count_max'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $hr_mail_setting = [];
            for ($index = 1;$index<=5;$index++){
                if(!empty($email_lists[$index-1])){
                    $hr_mail_setting['mail_address_'.$index] = $email_lists[$index-1]['email'];
                    $hr_mail_setting['name_'.$index] = $email_lists[$index-1]['name'];
                }else{
                    $hr_mail_setting['mail_address_'.$index] = '';
                    $hr_mail_setting['name_'.$index] = '';
                }
            }
            $hr_mail_setting['text_1'] = $request->get('text1')?:'';
            $hr_mail_setting['text_2'] = $request->get('text2')?:'';
            $hr_mail_setting['text_3'] = $request->get('text3')?:'';
            $hr_mail_setting['signature'] = $request->get('signature')?:'';
            DB::beginTransaction();
            $mail_setting = DB::table('hr_mail_setting')
                ->where('mst_user_id',$user->id)
                ->first();
            if($mail_setting){
                $hr_mail_setting['update_user'] = $user->email;
                $hr_mail_setting['update_at'] = Carbon::now();
                DB::table('hr_mail_setting')
                    ->where('mst_user_id',$user->id)
                    ->update($hr_mail_setting);
            }else{
                $hr_mail_setting['create_user'] = $user->email;
                $hr_mail_setting['create_at'] = Carbon::now();
                $hr_mail_setting['mst_user_id'] = $user->id;
                DB::table('hr_mail_setting')
                    ->insert($hr_mail_setting);
            }
            DB::commit();
            return $this->sendSuccess(__('message.success.hr_mail_setting.update'));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.hr_mail_setting.update'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * HR勤怠連絡メール送信
     *
     * @param Request $request
     * @return mixed
     */
    public function hrMailSend(Request $request)
    {
        try {
            $user  = $request->user();
            $emails = $request->get('emails');
            if (!$emails) {
                return $this->sendError(__('message.false.hr_mail_setting.contact_count_min'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $text = $request->get('text');
            $signature = $request->get('signature');

            $data_mail = [
                'text' => $text,
                'signature' =>  $signature,
                'name' => $user->family_name . ' ' . $user->given_name,
                'email' => $user->email,
            ];
            $data_mail_send_resume = $data_mail;
            $data_mail_send_resume['text'] = nl2br($text);
            $data_mail_send_resume['signature'] = nl2br($signature);

            MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                $emails,
                // メールテンプレート
                MailUtils::MAIL_DICTIONARY['SEND_HR_WORK_NOTICE']['CODE'],
                // パラメータ
                json_encode($data_mail,JSON_UNESCAPED_UNICODE),
                // タイプ
                AppUtils::MAIL_TYPE_USER,
                // 件名
                trans('mail.prefix.user') . trans('mail.SendHrWorkNotice.subject'),
                // メールボディ
                trans('mail.SendHrWorkNotice.body', $data_mail_send_resume)
            );

            return $this->sendSuccess(__('message.success.hr_mail_setting.send'));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.hr_mail_setting.send'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
