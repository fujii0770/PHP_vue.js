<?php

namespace App\Http\Controllers;

use App\Http\Utils\AppUtils;
use App\Http\Utils\DownloadUtils;
use App\Http\Utils\MailUtils;
use App\Http\Utils\PermissionUtils;
use App\Http\Utils\RosterListControllerUtils;
use App\Models\HrInfo;
use App\Models\HrTimeCard;
use App\Models\TimecardDetail;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class RosterListController extends AdminController {
    private $model;
    private $hr_timecard_detail;
    private $hr_timecard;
    private $mst_user;

    public function __construct(HrInfo $model, TimecardDetail $hr_timecard_detail, HrTimeCard $hr_timecard, User $mst_user)
    {
        parent::__construct();
        $this->model = $model;
        $this->hr_timecard_detail = $hr_timecard_detail;
        $this->hr_timecard = $hr_timecard;
        $this->mst_user = $mst_user;
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $user   = \Auth::user();
        $query  =  null;
        $action = $request->get('action','');

         // get list user
        $limit      = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
        $orderBy    = $request->get('orderBy') ? $request->get('orderBy') : 'time';
        $orderDir   = $request->get('orderDir') ? $request->get('orderDir'): 'desc';
        $arrOrder   = ['user' => 'user_name','email' => 'U.email',
                       'month_from' => 'H.Regulations_work_start_time','endTime' => 'H.Regulations_work_end_time'
                      ];

        $filter_user                = $request->get('username','');
        $filter_start               = $request->get('working_month_start','');
        $filter_end                 = $request->get('working_month_end','');

        //yyyy-mmからyyyymmに変換
        if ($filter_start){
            $filter_start = substr($filter_start,0,4).substr($filter_start,5,2);
        }
        if ($filter_end){
            $filter_end = substr($filter_end,0,4).substr($filter_end,5,2);
        }

            if(!$user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_HISTORY_VIEW)){
                //$this->raiseWarning(__('message.not_permission_access'));
                //return redirect()->route('home');
            }

            $query = DB::table('hr_timecard as T')
                ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'T.working_month',$orderDir)
                ->leftJoin('mst_user as U', 'U.id','T.mst_user_id')
                ->select(DB::raw('T.id, U.id as mst_user_id, CONCAT(U.family_name, U.given_name) as user_name,U.email, T.working_month, T.submission_state, T.approval_state, T.update_at'))
                ->where(DB::raw('CONCAT(U.family_name,U.given_name)'), 'like', "%$filter_user%")
                ->where(DB::raw('CONCAT(U.family_name,U.given_name)'), 'like', "%$filter_user%")
                ->where('U.mst_company_id', $user->mst_company_id)
                ;
                if($filter_start){
                    $query->where('T.working_month', '>=', $filter_start);
                }
                if($filter_end){
                    $query->where('T.working_month', '<=', $filter_end);
                }
                if($request->get('approval_state')){
                    $query->where('T.approval_state', $request->get('approval_state'));
                }

            if($action == 'export'){
                $query = $query ->get();
            }else{
                $query = $query ->paginate($limit)->appends(request()->input());
            }

            $this->setMetaTitle('勤務表一覧');

            $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";

        $this->assign('query', $query);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);
         
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        if($action == 'export'){
            return $this->render('RosterList.csv');
        }else{
            return $this->render('RosterList.index');
        }
 

    }

    public function update(Request $request){
        $user = \Auth::user();  

        $cids = $request->get('cids',[]);
        $isApproval = $request->get('isApproval','true');
        $items = [];
        if(count($cids)){
            $items = DB::table('mst_user as U')            
            ->leftJoin('hr_timecard as T', 'U.id','T.mst_user_id')
            ->where('U.mst_company_id',$user->mst_company_id)
            ->whereIn('T.id', $cids)
            ->get();
        }
        if(!count($items)){
            return response()->json(['status' => false,'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        DB::beginTransaction();
        try{
            if ($isApproval){
                DB::table('hr_timecard')
                    ->where('approval_state','<>','1')
                    ->whereIn('id', $cids)
                    ->update(['approval_state' => '1',
                        'approval_user'  => $user->getFullName(),
                        'approval_date'  => date(now()),
                        'update_user'  => $user->getFullName(),
                        'update_at'  => date(now())
                    ]);

                $items = $this->hr_timecard->find($cids);
                foreach ($items as $item) {
                    DB::table('hr_timecard_detail')
                        ->where('mst_user_id', $item->mst_user_id)
                        ->where(DB::raw('substring(work_date, 1, 6)'), $item->working_month)
                        ->where('approval_state','<>','1')
                        ->update(['approval_state' => '1',
                            'approval_user'  => $user->getFullName(),
                            'approval_date'  => date(now()),
                            'update_user'  => $user->getFullName(),
                            'update_at'  => date(now())
                        ]);
                }
            }else{
                $mailData = ['return_user' => $user->getFullName(), 'last_updated_email' => $user->email];

                $authorUsers = DB::table("mst_user")->join("hr_timecard","mst_user.id", "=","hr_timecard.mst_user_id")
                    ->join("mst_company","mst_company.id", "=","mst_user.mst_company_id")
                    ->where('hr_timecard.approval_state','<>','1')
                    ->where('hr_timecard.submission_state','=','1')
                    ->whereIn('hr_timecard.id', $cids)
                    ->select("mst_user.email", "mst_user.email", "mst_user.family_name", "mst_user.given_name", "hr_timecard.working_month", "mst_company.login_type", "mst_company.url_domain_id")
                    ->get();

                DB::table('hr_timecard')
                    ->where('approval_state','<>','1')
                    ->where('submission_state','=','1')
                    ->whereIn('id', $cids)
                    ->update(['submission_state' => '0',
                        'submission_date'  => null,
                        'update_user'  => $user->getFullName(),
                        'update_at'  => date(now())
                    ]);

                foreach ($authorUsers as $authorUser) {
                    $mailData['title'] = substr_replace($authorUser->working_month, '/', 4, 0);
                    $mailData['receiver_name'] = $authorUser->family_name . ' ' . $authorUser->given_name;
                    $mailData['mail_name'] = substr_replace($authorUser->working_month, '/', 4, 0);;
                    $mailData['author_email'] = $authorUser->family_name . ' ' . $authorUser->given_name;
                    $mailData['url_domain_id'] = ($authorUser->login_type == AppUtils::LOGIN_TYPE_SSO) ? $authorUser->url_domain_id : '';
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
            }
            DB::commit();
            $successMsg = $isApproval?__('message.success.approval_update'):__('message.success.sent_back_update');
            return response()->json(['status' => true,'message' => [$successMsg]]);
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }

    public function export(Request $request)
    {
        try {
            $user               = $request->user();
            $mst_user           = $this->mst_user;
            $hr_timecard        = $this->hr_timecard;
            $hr_timecard_detail = $this->hr_timecard_detail;
            $outputList         = $request->get('outputList');
            $hr_timecard_ids    = $request->get('timecard_ids');
            
            // ファイル名
            $file_name = RosterListControllerUtils::getRosterListCsvFileName($mst_user, $hr_timecard, $hr_timecard_detail, $outputList, $hr_timecard_ids);
            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\RosterListControllerUtils', 'getRosterListData', $file_name,
                $mst_user, $hr_timecard, $hr_timecard_detail, $request->all()
            );

            if(!($result === true)){
                return response()->json([
                    'status' => false,
                    'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]
                ]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                                ['attribute' => $file_name])]
            ]);

        } catch (\Throwable $th) {
            Log::error($th->getMessage().$th->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $th->getMessage()])]]);
        }
    }
}
