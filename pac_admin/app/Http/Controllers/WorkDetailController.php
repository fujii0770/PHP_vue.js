<?php

namespace App\Http\Controllers;

use App\Http\Utils\AppUtils;
use App\Http\Utils\MailUtils;
use App\Models\HrTimeCard;
use App\Models\TimecardDetail;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use stdClass;

class WorkDetailController extends Controller {
    private $model;
    private $TimeCard;
    private $model_user;

    public function __construct(TimecardDetail $model,HrTimeCard $HrTimeCard, User $model_user)
    {
        parent::__construct();
        $this->model = $model;
        $this->TimeCard = $HrTimeCard;
        $this->model_user = $model_user;

        $this->assign('use_angular', true);
        $this->assign('show_sidebar', true);
        $this->assign('use_contain', true);
    }

    public function index(Request $request){
        $user   = \Auth::user();
        $action = $request->get('action','');

        $timecard_id = $request->get('timecard_id','');
        $mst_user_id = $request->get('mst_user_id','');
        $work_month  = $request->get('work_month','');
        $limit       = 31;

        //勤務一覧から遷移してきた場合、$timecard_idが設定されている; 
        if($timecard_id){
            $arrTimCard2 = DB::table('hr_timecard_detail as D')
                ->Join('hr_timecard as H', function ($join) {
                    $join->on('D.mst_user_id', '=', 'H.mst_user_id');
                    $join->on(DB::raw('substring(D.work_date, 1, 6)'), '=', 'H.working_month');
                })
                ->leftJoin('mst_user as U', 'U.id','D.mst_user_id')
                ->leftJoin('mst_hr_info as I', 'I.mst_user_id', 'D.mst_user_id')
                ->select(DB::raw('
                    substring(D.work_date, 1, 6) as work_month,
                    D.id as detail_id,
                    H.id as header_id,
                    D.work_date,
                    D.work_start_time,
                    D.work_end_time,
                    D.break_time,
                    D.working_time,
                    CONCAT(U.family_name, U.given_name) as user_name,
                    U.email,
                    D.late_flg,
                    D.earlyleave_flg,
                    D.paid_vacation_flg,
                    D.sp_vacation_flg,
                    D.day_off_flg,
                    D.approval_state,
                    D.memo,
                    D.work_detail,
                    D.shift_work_kbn,
                    I.shift1_start_time,
                    I.shift1_end_time,
                    I.shift2_start_time,
                    I.shift2_end_time,
                    I.shift3_start_time,
                    I.shift3_end_time'))
                ->where('H.id', $timecard_id)
                ->get();
                
                $TimeCardKey  = $this->TimeCard->find($timecard_id);
                $mst_user_id = $TimeCardKey->mst_user_id;
                $work_month  = $TimeCardKey->working_month;
        }else{
            $arrTimCard2 = DB::table('hr_timecard_detail as D')
                ->Join('hr_timecard as H', function ($join) {
                    $join->on('D.mst_user_id', '=', 'H.mst_user_id');
                    $join->on(DB::raw('substring(D.work_date, 1, 6)'), '=', 'H.working_month');
                })
                ->leftJoin('mst_user as U', 'U.id','D.mst_user_id')
                ->leftJoin('mst_hr_info as I', 'I.mst_user_id', 'D.mst_user_id')
                ->select(DB::raw('
                    substring(D.work_date, 1, 6) as work_month,
                    D.id as detail_id,
                    H.id as header_id,
                    D.work_date,
                    D.work_start_time,
                    D.work_end_time,
                    D.break_time,
                    D.working_time,
                    CONCAT(U.family_name, U.given_name) as user_name,
                    U.email,
                    D.late_flg,
                    D.earlyleave_flg,
                    D.paid_vacation_flg,
                    D.sp_vacation_flg,
                    D.day_off_flg,
                    D.approval_state,
                    D.memo,
                    D.work_detail, 
                    D.shift_work_kbn,
                    I.shift1_start_time,
                    I.shift1_end_time,
                    I.shift2_start_time,
                    I.shift2_end_time,
                    I.shift3_start_time,
                    I.shift3_end_time,'))
                ->where('U.mst_company_id', $mst_user_id) 
                ->where('U.id', $user->mst_company_id) //自分の会社のユーザのみが対象
                ->where(DB::raw('substring(D.work_date, 1, 6)'),$request->get('work_month',''))
                ->get();
                ;
        }

        //利用者名の取得
        $item_user = $this->model_user->find($mst_user_id);
        $username = $item_user->family_name."  ".$item_user->given_name ;

                $carbon_start = Carbon::create(substr($work_month,0,4), substr($work_month,4,2), 1, 0, 0, 0);
                $carbon_end   = Carbon::create(substr($work_month,0,4), substr($work_month,4,2), 1, 0, 0, 0);
                $period = CarbonPeriod::create($carbon_start->startOfMonth(), $carbon_end->endOfMonth());
                $week = array('日', '月', '火', '水', '木', '金', '土');

                foreach($period as $date)
                {
                    $hit_flg =0;
                    $stdObj = new stdClass();
                    foreach ($arrTimCard2 as $arrTimCard_row) {
                        if ($date->format('md') == substr($arrTimCard_row->work_date,4,4)){
                            $stdObj->detail_id         = $arrTimCard_row->detail_id;
                            //曜日の取得
                            $dateweek = date('w',strtotime($arrTimCard_row->work_date));
                            $stdObj->work_date         = date("d日", strtotime($arrTimCard_row->work_date)).'（'.$week[$dateweek]."）";
                            $stdObj->work_start_time   = $arrTimCard_row->work_start_time;
                            $stdObj->work_start_time_day   = $arrTimCard_row->work_start_time;
                            //日付超過している場合
                            if (substr($arrTimCard_row->work_date,4,4) != date("md", strtotime($arrTimCard_row->work_end_time))){
                                $stdObj->work_end_time     = date("H:i", strtotime($arrTimCard_row->work_end_time))."(".date("m/d", strtotime($arrTimCard_row->work_end_time)).")";
                            }else{
                                $stdObj->work_end_time     = date("H:i", strtotime($arrTimCard_row->work_end_time));
                            }
                            //休憩時間変換
                            if($arrTimCard_row->break_time){
                                $br_hh = sprintf('%02d', floor($arrTimCard_row->break_time/60));
                                $br_mm = sprintf('%02d', $arrTimCard_row->break_time%60);
                                $stdObj->break_time        = $br_hh.":".$br_mm;
                            }else{
                                $stdObj->break_time        = "";
                            }
                            //稼働時間変換
                            if($arrTimCard_row->working_time){
                                $br_hh = sprintf('%02d', floor($arrTimCard_row->working_time/60));
                                $br_mm = sprintf('%02d', $arrTimCard_row->working_time%60);
                                $stdObj->working_time      = $br_hh.":".$br_mm;
                            }else{
                                $stdObj->working_time      = "";
                            }
                            $stdObj->approval_state    = $arrTimCard_row->approval_state;
                            $stdObj->memo              = $arrTimCard_row->memo;
                            $stdObj->work_detail              = $arrTimCard_row->work_detail;
                            $hit_flg = 1;
 
                            //休暇等の編集
                            $vacation_etc = "";
                            $haihun = "";
                            if($arrTimCard_row->late_flg){//遅刻
                              $vacation_etc = $vacation_etc.$haihun.\App\Http\Utils\AppUtils::LATE_FLG[$arrTimCard_row->late_flg]; 
                              $haihun = "-";
                            }
                            if($arrTimCard_row->earlyleave_flg){//早退
                              $vacation_etc = $vacation_etc.$haihun.\App\Http\Utils\AppUtils::EARLYLEAVE_FLG[$arrTimCard_row->earlyleave_flg]; 
                              $haihun = "-";
                            }
                            if($arrTimCard_row->paid_vacation_flg){//有給休暇
                              $vacation_etc = $vacation_etc.$haihun.\App\Http\Utils\AppUtils::PAID_VACATION_FLG[$arrTimCard_row->paid_vacation_flg]; 
                              $haihun = "-";
                            }
                            if($arrTimCard_row->sp_vacation_flg){//特別休暇
                               $vacation_etc = $vacation_etc.$haihun.\App\Http\Utils\AppUtils::SP_VACATION_FLG[$arrTimCard_row->sp_vacation_flg]; 
                               $haihun = "-";
                            }
                            if($arrTimCard_row->day_off_flg){//代休
                               $vacation_etc = $vacation_etc.$haihun.\App\Http\Utils\AppUtils::DAY_OFF_FLG[$arrTimCard_row->day_off_flg]; 
                            }
   
                            $stdObj->vacation_etc = $vacation_etc;
                            $stdObj->shift_time = '';
                            if($arrTimCard_row->shift_work_kbn){
                                $shift_key_start = 'shift' . $arrTimCard_row->shift_work_kbn . '_start_time';
                                $shift_key_end = 'shift' . $arrTimCard_row->shift_work_kbn . '_end_time';
                                if($arrTimCard_row->$shift_key_start && $arrTimCard_row->$shift_key_end){
                                    $stdObj->shift_time =  substr($arrTimCard_row->$shift_key_start, 0, -3) . '-' .  substr($arrTimCard_row->$shift_key_end, 0, -3);
                                }
                            }
                        }
                    }
                    if (!$hit_flg){//空行の作成
                        $stdObj->detail_id= 0;
                        $dateweek = $date->format('w');
                        $stdObj->work_date = $date->format('d日').'（'.$week[$dateweek].'）';
                    }
                    $arrTimeCard[] = $stdObj;
                }

            if($action == 'export'){
                $arrTimeCard2 = $arrTimeCard2 ->get();
            }
            
        $this->assign('arrTimCard2', $arrTimCard2);
        $this->assign('username', $username);
        $this->setMetaTitle('勤務詳細');
        $this->assign('user_title', '管理者');
        $this->assign('mst_user_id', $mst_user_id);
        $this->assign('timecard_id', $timecard_id);
        $this->assign('work_month', $work_month);
        $this->assign('arrTimeCard', $arrTimeCard);

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        if($action == 'export'){
            return $this->render('OperationHistory.csv');
        }else{
            return $this->render('WorkDetail.index');
        }
    }

    public function bulkApproval(Request $request)
    {
        $user = \Auth::user();

        $chks = $request->get('chks');
        $isApproval = $request->get('isApproval','true');
        $now = date(now());
        $work_month ='';

        //hr_timecard_detail
        $items = $this->model->find($chks);

        DB::beginTransaction();
        try{
            if ($isApproval){
                $first_flg = 0;
                foreach ($items as $item) {
                    if (!$first_flg){
                        $first_flg =1;
                        $mst_user_id = $item->mste_user_id;
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
                //hr_timecard
                $items2 = $this->TimeCard
                    ->where('mst_user_id', $mst_user_id)
                    ->where('working_month', $work_month)
                    ->where('approval_state', 0)
                    ->update(['update_user' => $user->getFullName()
                        ,'approval_state' => 1
                        ,'approval_user' => $user->getFullName()
                        ,'approval_date' => $now
                    ]);
            }else{
                foreach ($items as $item) {
                    $mst_user_id = $item->mst_user_id;
                    $work_month = substr($item->work_date,0,6);
                    break;
                }
                //hr_timecard
                $this->TimeCard
                    ->where('mst_user_id', $mst_user_id)
                    ->where('working_month', $work_month)
                    ->where('approval_state', 0)
                    ->where('submission_state', 1)
                    ->update([
                        'submission_state' => '0',
                        'submission_date'  => null,
                        'update_user'  => $user->getFullName(),
                        'update_at'  => date(now())
                    ]);

                $authorUser = $this->model_user->find($mst_user_id);
                $company = DB::table('mst_company')->where('id', $authorUser->mst_company_id)->select('mst_company.login_type', 'mst_company.url_domain_id')->first();

                $mailData = ['return_user' => $user->getFullName(), 'last_updated_email' => $user->email];
                $mailData['title'] = substr_replace($work_month, '/', 4, 0);
                $mailData['receiver_name'] = $authorUser->getFullName();
                $mailData['mail_name'] = substr_replace($work_month, '/', 4, 0);;
                $mailData['author_email'] = $authorUser->getFullName();
                $mailData['url_domain_id'] = $company && ($company->login_type == AppUtils::LOGIN_TYPE_SSO) ? $company->url_domain_id : '';

                MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                    $authorUser->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['HR_WORK_REPORT_SEND_BACK_NOTIFY']['CODE'],
                    // パラメータ
                    json_encode($mailData, JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_USER,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.hr_work_report_sendback_template.subject', $mailData),
                    // メールボディ
                    trans('mail.hr_work_report_sendback_template.body', $mailData)
                );
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        $successMsg = $isApproval?__('message.success.approval_update'):__('message.success.sent_back_update');

        return response()->json(['status' => true,'message' => [$successMsg]]);
    }

    public function show($id)
    {
        $user  = \Auth::user();

        $item = DB::table('hr_timecard_detail as D')
            ->leftJoin('mst_user as U', 'U.id','D.mst_user_id')
            ->leftJoin('mst_user_info as Ui', 'Ui.mst_user_id', 'D.mst_user_id')
            ->leftJoin('mst_department as Dp', 'Dp.id', 'Ui.mst_department_id')
            ->leftJoin('mst_position as P', 'P.id', 'Ui.mst_position_id')
            ->select(DB::raw('CONCAT(U.family_name, U.given_name) as user_name, U.email, D.*, Dp.department_name, P.position_name'))
            ->where('D.id', $id)
            ->first();

        return response()->json(['status' => true, 'item' => $item ]);
    }

    function update($id, Request $request){
        $user = \Auth::user();

        $item_post = $request->get('item');
        $item = $this->model->find($id);

        $validator = Validator::make($item_post, $this->model->rules());
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        $item->fill($item_post);
        $item->update_user = $user->getFullName();
        //  変換しないとダメみたい
        $item->work_start_time = date("Y-m-d H:i:s", strtotime($item_post['work_start_time']));
        $item->work_end_time = date("Y-m-d H:i:s", strtotime($item_post['work_end_time']));
        //  承認済にした場合必要だな（済じゃなくしたらnull入れた方がいい？）
        if( $item_post['approval_state'] == 1 ) {
            $item->approval_user = $user->getFullName();
            $item->approval_date = date(now());
        }

        DB::beginTransaction();
        try{
            $item->save();

            //hr_timecardの0更新
            if( $item_post['approval_state'] == 0 ) {
                DB::table('hr_timecard as D')
                ->where('D.mst_user_id', $item_post['mst_user_id'])
                ->where('working_month', substr( $item_post['work_date'], 0, 6 ))
                ->where('approval_state', '1')
                ->update(['approval_state' => '0',
                          'approval_user'  => NULL,
                          'approval_date'  => NULL,
                          'update_user'  => \Auth::user()->getFullName(),
                          'update_at'  => date(now())
                        ]);
            }

            //  月のデータが全部承認になったら親のフラグ立てるんだって
            $this->checkApproval($item_post['mst_user_id'], substr( $item_post['work_date'], 0, 6 ));

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true, 'id' => $item->id, 'message' => [__('message.success.attendance_update')]]);
    }

    private function checkApproval($userid, $yyyymm) :void
    {
        $items = $this->model->where('mst_user_id', $userid)
            ->where('work_date', 'like', "%$yyyymm%")
            ->where('approval_state', '!=', 1)
            ->get();
        //  月のデータは全部ある前提だから1:承認済以外のが取れないなら全部1:承認済と判断できる
        if( $items->count() <= 0 ) {
            DB::table('hr_timecard as D')
                ->where('D.mst_user_id', $userid)
                ->where('working_month', $yyyymm)
                ->where('approval_state', '0')
                ->update(['approval_state' => '1',
                          'approval_user'  => \Auth::user()->getFullName(),
                          'approval_date'  => date(now()),
                          'update_user'  => \Auth::user()->getFullName(),
                          'update_at'  => date(now())
                        ]);
        };
    }
}
