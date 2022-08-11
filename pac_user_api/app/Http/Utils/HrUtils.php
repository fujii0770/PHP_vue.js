<?php

/**
 * Created by PhpStorm.
 * User: hopdt
 * Date: 05/03/2021
 * Time: 12:11
 */

namespace App\Http\Utils;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Utils\AppUtils;

class HrUtils
{
    /**
     * 稼働・残業時間計算処理
     * @param string $workStartTime  出勤時刻
     * @param string $workEndTime    退勤時刻
     * @param        $breakTime      休憩時間
     * @param        $timeRegulation 規定情報
     * @param        $halfVacation   半休フラグ
     * @param        $workDate       業務日
     * @return mix['working_time_real', 'overtime_real', 'shift_work_kbn']
     */
    public static function calculateWorkingTimeAndOverTime($workStartTime, $workEndTime, $breakTime, $timeRegulation, $halfVacation, $workDate) {
        $workStartTime = Carbon::parse(substr($workStartTime,0, 16));       // 出勤時刻（年月日時分）
        $workEndTime   = Carbon::parse(substr($workEndTime,  0, 16));       // 退勤時刻（年月日時分）
        $workDate_ymd  = substr($workDate, 0, 4) . '-' . 
                         substr($workDate, 4, 2) . '-' . 
                         substr($workDate, 6, 2);                           // 業務日（yyyy/MM/dd）

        $workingTimeRule     = $timeRegulation['working_time_rule']; 
        $startTimeRegulation = $timeRegulation['work_start_time'];
        $endTimeRegulation   = $timeRegulation['work_end_time'];
        $overNight           = $timeRegulation['overNight'];
        $workDate_ymd_startTimeRegulation = 
            $workDate_ymd . ' ' . 
            $startTimeRegulation; // 業務日（yyyy/MM/dd）+ 規定出勤時刻

        // シフト出勤区分がフレックスの場合
        if ($timeRegulation['shift_work_kbn'] == AppUtils::SHIFT_WORK_FLEX) {
            // 半休の場合、規定勤務時間を半分にする
            if ($halfVacation) { 
                $workingTimeRule = $timeRegulation['regulations_working_hours'] / 2;
            } else {
                $workingTimeRule = $timeRegulation['regulations_working_hours'];
            }

            // 稼働時間を計算
            $data['working_time_real'] = self::getWorkingTimeReal($workEndTime, $workStartTime, $breakTime);
            // 残業時間を計算
            $data['overtime_real']     = self::getOvertimeReal($data['working_time_real'], $workingTimeRule);
        } else {
            // 規定出勤時刻と規定退勤時刻の存在チェック 
            if (!empty($startTimeRegulation) && !empty($endTimeRegulation)) {
                // 半休の場合、規定勤務時間を半分にする
                if ($halfVacation) { 
                    $workingTimeRule = $timeRegulation['working_time_rule'] / 2;
                }
                // 規定出勤時刻 >= 規定退勤時刻の場合
                if ($overNight) {
                    // 出勤時刻が規定出勤時刻～規定退勤時刻の範囲内に入っているかの判定
                    if ($workStartTime->toTimeString() >= $startTimeRegulation         || 
                        $workStartTime->toTimeString() <= $endTimeRegulation           ||
                       ($workStartTime->toTimeString() >  $endTimeRegulation           && 
                        $workStartTime->toTimeString() <  $workEndTime->toTimeString() && 
                        $workEndTime->toTimeString()   <  $startTimeRegulation)) {
                        // 出勤時刻で計算
                        $start = $workStartTime;
                    } else {
                        // 出勤時刻(日付)+規定出勤時間で計算
                        $dateStart = substr($workStartTime, 0, 10);
                        $start = Carbon::parse($dateStart.' '.$startTimeRegulation);
                    }
                } else {
                // 規定出勤時刻 <  規定退勤時刻の場合
                    // 出勤時刻が規定出勤時刻～の範囲内に入っているかの判定
                    if ($workStartTime->toTimeString() >= $startTimeRegulation  || 
                        $workStartTime->toTimeString() <  $startTimeRegulation  && 
                        $workEndTime->toTimeString()   <  $startTimeRegulation) {
                        // 業務日（yyyy/MM/dd）+ 規定出勤時刻 以前の出勤時刻かを判定
                        if ($workStartTime < $workDate_ymd_startTimeRegulation) {
                            // 業務日の日付＋規定出勤時間で計算
                            $start = Carbon::parse($workDate_ymd_startTimeRegulation);
                        } else {
                            // 出勤時刻で計算
                            $start = $workStartTime;
                        }
                    } else {
                        // 出勤時刻(日付)+規定出勤時間で計算
                        $dateStart = substr($workStartTime, 0, 10);
                        $start = Carbon::parse($dateStart.' '.$startTimeRegulation);
                    }
                }
                // 稼働時間を計算
                $data['working_time_real']  = self::getWorkingTimeReal($workEndTime, $start, $breakTime);
                // 残業時間を計算
                $data['overtime_real']      = self::getOvertimeReal($data['working_time_real'], $workingTimeRule);
            } else {
                // 稼働時間を計算
                $data['working_time_real']  = self::getWorkingTimeReal($workEndTime, $workStartTime, $breakTime);
                // 残業時間を計算
                $data['overtime_real']      = 0;
            }
        }

        $data['shift_work_kbn'] = $timeRegulation['shift_work_kbn'];
        return $data;
    }


    /**
     * 稼働時間を計算
     * @param string $workEndTime   対象退勤時刻
     * @param string $workStartTime 対象出勤時刻
     * @param int    $breakTime     休憩時間
     * @return workingTimeReal   稼働時間
     */
    private static function getWorkingTimeReal($workEndTime, $workStartTime, $breakTime) {
        $workingTimeReal = 0;
        if ($workEndTime > $workStartTime) {
            $workingTimeReal = ($workEndTime->diffInMinutes($workStartTime)) - $breakTime;
            if ($workingTimeReal < 0) {
                $workingTimeReal = 0;
            }
        } else {
            $workingTimeReal = 0;
        }
        return $workingTimeReal;
    }

    /**
     * 残業時間を計算
     * @param int $workingTimeReal 対象稼働時間
     * @param int $workingTimeRule 対象規定勤務時間
     * @return overtimeReal   稼働時間
     */
    private static function getOvertimeReal($workingTimeReal, $workingTimeRule) {
        $overtimeReal = 0;
        if ($workingTimeReal > $workingTimeRule) {
            $overtimeReal = $workingTimeReal - $workingTimeRule;
        } else {
            $overtimeReal = 0;
        }
        return $overtimeReal;
    }

    /**
     * 勤務情報取得処理
     * @param int $userId
     * @param int $shiftWorkKbn
     *
     * @return mix['shift_work_kbn', 'overtime_unit', 'working_time_rule', 'work_start_time', 'work_end_time', 'break_time', 'hrInfo_existed', 'overNight']
     */
    public static function getHrTimeRegulation($userId, $shiftWorkKbn) {

        // 勤務時間設定取得
        $hrTimeWorkInfo          = self::getHrWorkingTimesInfoArrays($userId, $shiftWorkKbn);
        $data['shift_work_kbn']            = $hrTimeWorkInfo['shift_work_kbn'];
        $data['regulations_working_hours'] = $hrTimeWorkInfo['regulations_working_hours'];
        $data['work_form_kbn']             = $hrTimeWorkInfo['work_form_kbn'];
        
        if (!empty($hrTimeWorkInfo)) {
            $endTimeRegulation   = $hrTimeWorkInfo['work_end_time'];
            $startTimeRegulation = $hrTimeWorkInfo['work_start_time'];
            $breakTimeRegulation = $hrTimeWorkInfo['break_time'];
            $overTimeUnit        = $hrTimeWorkInfo['overtime_unit'];
            $hrInfo_existed      = 1;
        } else {
            $endTimeRegulation   = config('app.Regulations_work_end_time_default');
            $startTimeRegulation = config('app.Regulations_work_start_time_default');
            $breakTimeRegulation = config('app.break_time_default');
            $overTimeUnit        = config('app.overtime_unit_default');
            $hrInfo_existed      = 0;
        }
        // 規定勤務時間計算
        if (!empty($startTimeRegulation) || !empty($endTimeRegulation)) {
            if ($startTimeRegulation < $endTimeRegulation) {
                // 規定勤務時間 = 規定退勤時刻と規定出勤時刻の差分 - 休憩時間
                $workingTimeRule = (Carbon::parse($endTimeRegulation)->diffInMinutes(Carbon::parse($startTimeRegulation)) - $breakTimeRegulation);
                $overNight       = 0;
            } else {
                // 規定勤務時間１ = 規定出勤時刻と23:59:59の差分 + 1 
                $halfStartTime   = Carbon::parse($startTimeRegulation)->diffInMinutes(Carbon::parse($startTimeRegulation)->copy()->endOfDay()) + 1;
                // 規定勤務時間２ = 規定退勤時刻と00:00:00の差分
                $halfEndTime     = Carbon::parse($endTimeRegulation)->diffInMinutes(Carbon::parse($endTimeRegulation)->copy()->startOfDay());
                // 規定勤務時間 = 規定勤務時間１ + 規定勤務時間２ - 休憩時間
                $workingTimeRule = ($halfStartTime + $halfEndTime - $breakTimeRegulation);
                $overNight       = 1;
            }
        } else {
            // 規定出勤時刻と規定退勤時刻が設定されていない場合
            $workingTimeRule    = 0; // 規定勤務時間
            $overNight          = 0;
        }

        $data['overtime_unit']      = $overTimeUnit;
        $data['working_time_rule']  = $workingTimeRule;
        $data['work_start_time']    = $startTimeRegulation;
        $data['work_end_time']      = $endTimeRegulation;
        $data['break_time']         = $breakTimeRegulation;
        $data['hrInfo_existed']     = $hrInfo_existed;
        $data['overNight']          = $overNight;

        return $data;
    }

    /**
     * 勤務時間設定取得処理
     * @param int $userId
     * @param int $shiftWorkKbn     シフト出勤区分
     * 
     * @return mix['shift_work_kbn','work_start_time','work_end_time']
     */
    private static function getHrWorkingTimesInfoArrays($userId, $shiftWorkKbn) {
        
        $hrInfo         = null;
        $hrWorkingHours = null;

        // HRユーザー情報取得
        $hrInfo = 
        DB::table('mst_hr_info')
            ->where('mst_user_id', $userId)
            ->orderBy('create_at', 'DESC')
            ->first();
        if (!empty($hrInfo)) {
            // 就労時間IDチェック
            if (!empty($hrInfo->working_hours_id)) {
                // HR就労時間管理取得
                $hrWorkingHours = 
                DB::table('hr_working_hours')
                    ->where('id', $hrInfo->working_hours_id)
                    ->orderBy('create_at', 'DESC')
                    ->first();
            }
        }

        // HRユーザー情報またはHR就労時間管理から勤務時間設定を取得
        $wkHrInfoWorkingHours = self::getHrInfoWorkingHours($hrInfo, $hrWorkingHours);

        // 勤務時間設定情報有り
        if (!empty($wkHrInfoWorkingHours)) {

            // シフト出勤区分がフレックスの場合
            if ($shiftWorkKbn == AppUtils::SHIFT_WORK_FLEX) { 
                $data['shift_work_kbn']        = AppUtils::SHIFT_WORK_FLEX; 
                $data['work_start_time']       = "";
                $data['work_end_time']         = "";
            } else {
                // シフト出勤区分別に規定出勤時刻、規定退勤時刻を配列化
                $arr['0']['0'] = self::getTime($wkHrInfoWorkingHours['Regulations_work_start_time']); // 通常勤務 開始
                $arr['0']['1'] = self::getTime($wkHrInfoWorkingHours['Regulations_work_end_time']  ); // 通常勤務 終了
                $arr['1']['0'] = self::getTime($wkHrInfoWorkingHours['shift1_start_time']          ); // シフト勤務1 開始
                $arr['1']['1'] = self::getTime($wkHrInfoWorkingHours['shift1_end_time']            ); // シフト勤務1 終了
                $arr['2']['0'] = self::getTime($wkHrInfoWorkingHours['shift2_start_time']          ); // シフト勤務2 開始
                $arr['2']['1'] = self::getTime($wkHrInfoWorkingHours['shift2_end_time']            ); // シフト勤務2 終了
                $arr['3']['0'] = self::getTime($wkHrInfoWorkingHours['shift3_start_time']          ); // シフト勤務3 開始
                $arr['3']['1'] = self::getTime($wkHrInfoWorkingHours['shift3_end_time']            ); // シフト勤務3 終了

                if (empty($arr[strval($shiftWorkKbn)]['0']) && empty($arr[strval($shiftWorkKbn)]['1'])) {
                    // 該当無しの場合 強制的に0:通常勤務
                    $data['shift_work_kbn']        = AppUtils::SHIFT_WORK_NORMAL; 
                    $data['work_start_time']       = "";
                    $data['work_end_time']         = "";  
                } else {
                    $data['shift_work_kbn']        = $shiftWorkKbn;
                    $data['work_start_time']       = $arr[strval($shiftWorkKbn)]['0'];
                    $data['work_end_time']         = $arr[strval($shiftWorkKbn)]['1'];
                }
            }
            $data['overtime_unit']             = $wkHrInfoWorkingHours['overtime_unit'];
            $data['break_time']                = $wkHrInfoWorkingHours['break_time'];
            $data['regulations_working_hours'] = $wkHrInfoWorkingHours['regulations_working_hours'];
            $data['work_form_kbn']             = $wkHrInfoWorkingHours['work_form_kbn'];
        } else {
        // 勤務時間設定情報無し
            // mst_hr_info データ無しの場合 強制的に0:通常勤務
            $data['shift_work_kbn']            = AppUtils::SHIFT_WORK_NORMAL; 
            $data['work_start_time']           = "";
            $data['work_end_time']             = "";  
            $data['overtime_unit']             = 0;
            $data['break_time']                = 0;
            $data['regulations_working_hours'] = "";
            $data['work_form_kbn']             = "";
        }
        return $data;
    }

    /**
     * HRユーザー情報またはHR就労時間管理から勤務時間設定を取得
     * @param mst_hr_info      $hrInfo         HRユーザー情報
     * @param hr_working_hours $hrWorkingHours HR就労時間管理
     * @param int $shiftWorkKbn
     * 
     * @return mix['shift_work_kbn','work_start_time','work_end_time']
     */
    private static function getHrInfoWorkingHours($hrInfo, $hrWorkingHours) {
        $data = null;

        // 勤務時間設定変数の初期値設定
        $data['Regulations_work_start_time'] = ""; // 規定業務開始時刻
        $data['Regulations_work_end_time']   = ""; // 規定業務終了時刻
        $data['shift1_start_time']           = ""; // シフト1開始時刻
        $data['shift1_end_time']             = ""; // シフト1終了時刻
        $data['shift2_start_time']           = ""; // シフト2開始時刻 
        $data['shift2_end_time']             = ""; // シフト2終了時刻 
        $data['shift3_start_time']           = ""; // シフト3開始時刻
        $data['shift3_end_time']             = ""; // シフト3終了時刻
        $data['overtime_unit']               = 0;  // 残業発生時間単位
        $data['break_time']                  = 0;  // 休憩時間
        $data['regulations_working_hours']   = ""; // 勤務形態区分 0:通常 1:シフト 2:フレックス ※HR就労時間管理専用
        $data['work_form_kbn']               = ""; // 規定就労時間                            ※HR就労時間管理専用
        
        // HRユーザー情報が有り
        if (!empty($hrInfo)) {

            // HRユーザー情報の勤務時間設定有無フラグ取得
            $hrInfoWorkingTimesExists = self::isHrInfoWorkingTimesExists($hrInfo);

            // HR就労時間管理が有り
            if (!empty($hrWorkingHours)) {

                // HRユーザー情報の勤務時間設定有り
                if ($hrInfoWorkingTimesExists) {
                    $data['Regulations_work_start_time'] = $hrInfo->Regulations_work_start_time;
                    $data['Regulations_work_end_time']   = $hrInfo->Regulations_work_end_time; 
                    $data['shift1_start_time']           = $hrInfo->shift1_start_time;  
                    $data['shift1_end_time']             = $hrInfo->shift1_end_time;   
                    $data['shift2_start_time']           = $hrInfo->shift2_start_time;
                    $data['shift2_end_time']             = $hrInfo->shift2_end_time;  
                    $data['shift3_start_time']           = $hrInfo->shift3_start_time;
                    $data['shift3_end_time']             = $hrInfo->shift3_end_time;   
                    $data['overtime_unit']               = $hrInfo->overtime_unit;
                    $data['break_time']                  = $hrInfo->break_time;
                    $data['regulations_working_hours']   = "";
                    $data['work_form_kbn']               = "";
                } else {
                // HRユーザー情報の勤務時間設定無し

                    // HR就労時間管理の勤務時間設定有無フラグ取得
                    $hrWorkingHoursWorkingTimesExists = self::isHrWorkingHoursWorkingTimesExists($hrWorkingHours);

                    if ($hrWorkingHoursWorkingTimesExists || 
                       ($hrWorkingHours->work_form_kbn == AppUtils::WORK_FROM_FLEX && !empty($hrWorkingHours->regulations_working_hours))) { 
                    // HR就労時間管理の勤務時間設定が有り
                        $data['Regulations_work_start_time'] = $hrWorkingHours->regulations_work_start_time;
                        $data['Regulations_work_end_time']   = $hrWorkingHours->regulations_work_end_time; 
                        $data['shift1_start_time']           = $hrWorkingHours->shift1_start_time;  
                        $data['shift1_end_time']             = $hrWorkingHours->shift1_end_time;   
                        $data['shift2_start_time']           = $hrWorkingHours->shift2_start_time;
                        $data['shift2_end_time']             = $hrWorkingHours->shift2_end_time;  
                        $data['shift3_start_time']           = $hrWorkingHours->shift3_start_time;
                        $data['shift3_end_time']             = $hrWorkingHours->shift3_end_time;   
                        $data['overtime_unit']               = $hrWorkingHours->overtime_unit;
                        $data['break_time']                  = $hrWorkingHours->break_time;
                        $data['regulations_working_hours']   = $hrWorkingHours->regulations_working_hours;
                        $data['work_form_kbn']               = $hrWorkingHours->work_form_kbn;
                    } else {
                    // HR就労時間管理の勤務時間設定が無し
                        // 初期値設定のまま
                    }
                }
            } else { 
            // HR就労時間管理が無い場合
                // HRユーザー情報の勤務時間設定有り
                if ($hrInfoWorkingTimesExists) {
                    $data['Regulations_work_start_time'] = $hrInfo->Regulations_work_start_time;
                    $data['Regulations_work_end_time']   = $hrInfo->Regulations_work_end_time; 
                    $data['shift1_start_time']           = $hrInfo->shift1_start_time;  
                    $data['shift1_end_time']             = $hrInfo->shift1_end_time;   
                    $data['shift2_start_time']           = $hrInfo->shift2_start_time;
                    $data['shift2_end_time']             = $hrInfo->shift2_end_time;  
                    $data['shift3_start_time']           = $hrInfo->shift3_start_time; 
                    $data['shift3_end_time']             = $hrInfo->shift3_end_time;   
                    $data['overtime_unit']               = $hrInfo->overtime_unit;
                    $data['break_time']                  = $hrInfo->break_time;
                    $data['regulations_working_hours']   = "";
                    $data['work_form_kbn']               = "";
                } else {
                // HRユーザー情報の勤務時間設定無し
                    // 初期値設定のまま
                }
            }
        } else { 
        // HRユーザー情報が無い場合
            // 初期値設定のまま
        }   
        return $data;
    }

    /**
     * HRユーザー情報、HR就労時間管理 勤務時間設定有り無し判定フラグ取得処置
     * @param baseTable $baseTable HRユーザー情報またはHR就労時間管理
     * 
     * @return isWorkingTimesExists true:勤務時間設定有り false:勤務時間設定無し
     */
    private static function isHrInfoWorkingTimesExists($baseTable) {
        // 規定業務時刻、シフト1時刻、シフト2時刻、シフト3時刻のいずれかが入力されているかチェック
        if ((!empty($baseTable->Regulations_work_start_time) && !empty($baseTable->Regulations_work_start_time)) || 
            (!empty($baseTable->shift1_start_time)           && !empty($baseTable->shift1_end_time)            ) || 
            (!empty($baseTable->shift2_start_time)           && !empty($baseTable->shift2_end_time)            ) || 
            (!empty($baseTable->shift3_start_time)           && !empty($baseTable->shift3_end_time)            )) { 
            return true ; // 勤務時間設定有り
        } else {
            return false; // 勤務時間設定無し
        }          
    }
    /**
     * HRユーザー情報、HR就労時間管理 勤務時間設定有り無し判定フラグ取得処置
     * @param baseTable $baseTable HRユーザー情報またはHR就労時間管理
     * 
     * @return isWorkingTimesExists true:勤務時間設定有り false:勤務時間設定無し
     */
    private static function isHrWorkingHoursWorkingTimesExists($baseTable) {
        // 規定業務時刻、シフト1時刻、シフト2時刻、シフト3時刻のいずれかが入力されているかチェック
        if ((!empty($baseTable->regulations_work_start_time) && !empty($baseTable->regulations_work_start_time)) || 
            (!empty($baseTable->shift1_start_time)           && !empty($baseTable->shift1_end_time)            ) || 
            (!empty($baseTable->shift2_start_time)           && !empty($baseTable->shift2_end_time)            ) || 
            (!empty($baseTable->shift3_start_time)           && !empty($baseTable->shift3_end_time)            )) { 
            return true ; // 勤務時間設定有り
        } else {
            return false; // 勤務時間設定無し
        }          
    }
    
    // 時間形式で無ければnullを返す
    private static function getTime($time){
        return strtotime($time)?$time:null;
    }
      /**
     * Midnight working time calculate
     * @param string $workStartTime
     * @param string $workEndTime
     * 
     * @return int midnight_time
     */
    public static function caculateMidNightWorkingTime($workStartTime, $workEndTime, $midnightBreakTime) {
        $midnight_time = 0;
        $startTime = Carbon::parse($workStartTime);
        $endTime =  Carbon::parse($workEndTime);
        $countDays =  $startTime->diffInDays($endTime);

        for($i = 0; $i <= $countDays; $i++){
            $startMidNightTime = $startTime->copy()->addDays($i);
            $todayAt22h = Carbon::parse($startMidNightTime->format('Y-m-d') . ' 22:00:00');
            $tomorowAt5h = Carbon::parse($startMidNightTime->copy()->addDay()->format('Y-m-d') . ' 05:00:00');
            $todayAt5h = Carbon::parse($startMidNightTime->copy()->format('Y-m-d') . ' 05:00:00');
            
            //reset startMidNightTime from 2nd day
            if($i > 0){
                $startMidNightTime = $todayAt22h->copy();
            }

            if($startMidNightTime < $todayAt5h){
                if($endTime < $todayAt5h){
                    $midnight_time += $startMidNightTime->diffInMinutes($endTime);
                }else{
                    $midnight_time += $startMidNightTime->diffInMinutes($todayAt5h);
                }
            }
            if($endTime > $todayAt22h){
                if($startMidNightTime >= $todayAt22h ){
                    if($endTime > $tomorowAt5h){
                        $midnight_time += $startMidNightTime->diffInMinutes($tomorowAt5h);
                    }else{
                        $midnight_time += $startMidNightTime->diffInMinutes($endTime);
                    }
                }else{
                    if($endTime > $tomorowAt5h){
                        $midnight_time += $todayAt22h->diffInMinutes($tomorowAt5h);
                    }else{
                        $midnight_time += $todayAt22h->diffInMinutes($endTime);
                    }
                }
            }
        }
        return $midnight_time - $midnightBreakTime;
    }

     /**
     * calculate break time and midnight break time
     * @param        $breakStartTime      start break time
     * @param        $breakEndTime        end brak time
     * @return mix['break_time', 'midnight_break_time']
     */
    public static function calculateBreakTime($breakStartTime, $breakEndTime) {
        $midNightBreakTime = 0;
        $breakTime = 0;
        $todayAt22h = Carbon::parse(Carbon::parse($breakStartTime)->copy()->format('Y-m-d') . ' 22:00:00');
        $todayAt05h = Carbon::parse(Carbon::parse($breakStartTime)->copy()->format('Y-m-d') . ' 05:00:00');
        $tomorowAt05h = Carbon::parse(Carbon::parse($breakStartTime)->copy()->addDay()->format('Y-m-d') . ' 05:00:00');
        if($breakStartTime && $breakEndTime){
            $cStart = Carbon::parse($breakStartTime)->startOfMinute();
            $cEnd = Carbon::parse($breakEndTime)->startOfMinute();
            if($cStart <  $todayAt05h || $cEnd  >  $todayAt22h){
                if(($cStart <  $todayAt05h && $cEnd  <=  $todayAt05h) || ($cStart >=  $todayAt22h && $cEnd > $todayAt22h)){
                    if($cEnd > $tomorowAt05h){
                        $midNightBreakTime = $cStart->diffInMinutes($tomorowAt05h);
                    }else{
                        $midNightBreakTime = $cStart->diffInMinutes($cEnd);
                    }
                }else if($cStart <  $todayAt05h && $cEnd  >  $todayAt05h){
                    $midNightBreakTime = $cStart->diffInMinutes($todayAt05h);
                }else if($cStart <  $todayAt22h && $cEnd  >  $todayAt22h){
                    if($cEnd > $tomorowAt05h){
                        $midNightBreakTime = $todayAt22h->diffInMinutes($tomorowAt05h);
                    }else{
                        $midNightBreakTime = $todayAt22h->diffInMinutes($cEnd);
                    }
                }
            } 
            $breakTime = Carbon::parse($cStart)->diffInMinutes(Carbon::parse($cEnd));
        }
        $data['break_time'] = $breakTime;
        $data['midnight_break_time'] = $midNightBreakTime;

        return $data;
    }

}
