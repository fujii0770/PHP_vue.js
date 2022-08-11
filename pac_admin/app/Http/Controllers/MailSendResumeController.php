<?php

namespace App\Http\Controllers;

use App\Http\Utils\AppUtils;
use GuzzleHttp\RequestOptions;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Session;
use App\Jobs\SendEmail;


class MailSendResumeController extends AdminController
{


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the Company
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $action = $request->get('action','');
        $limit = config('app.page_limit');

        $mailList = [];

        if($action != ""){
            $mailList = DB::table('mail_send_resume')
                        ->orderBy('mail_send_resume.create_at','desc')
                        ->leftjoin('mst_company','mail_send_resume.mst_company_id','mst_company.id')
                        ->where('mail_send_resume.state','!=',AppUtils::MAIL_STATE_DELAY);
            // 企業ID
            if(trim(request('mst_company_id','')) !== ''){
                $mailList = $mailList->where('mail_send_resume.mst_company_id',$request->mst_company_id);
            }
            // 宛先
            if(trim(request('to_email','')) !== ''){
                $mailList = $mailList->where('mail_send_resume.to_email', 'like', '%' . $request->to_email . '%');
            }
            // 機能
            if($request->template){
                $mailList = $mailList->where('mail_send_resume.template',$request->template);
            }
            // 送信状態
            if(trim(request('state','')) !== ''){
                $mailList = $mailList->where('mail_send_resume.state',$request->state);
            }
            // 送信日時/時刻(from)
            if($request->create_at_start){
                $mailList = $mailList->where('mail_send_resume.create_at','>=',$request->create_at_start);
            }
            // 送信日時/時刻(to)
            if($request->create_at_end){
                $mailList = $mailList->where('mail_send_resume.create_at','<=',$request->create_at_end);
            }
            // リクエスト日時/時刻(from)
            if($request->update_at_start){
                $mailList = $mailList->where('mail_send_resume.update_at','>=',$request->update_at_start);
            }
            // リクエスト日時/時刻(to)
            if($request->update_at_end){
                $mailList = $mailList->where('mail_send_resume.update_at','<=',$request->update_at_end);
            }

            $mailList = $mailList
                ->select([
                    'mail_send_resume.id as id',
                    'mail_send_resume.update_at as update_at',
                    'mst_company.company_name as company_name',
                    'mail_send_resume.mst_company_id as mst_company_id',
                    'mail_send_resume.to_email as to_email',
                    'mail_send_resume.template as template',
                    'mail_send_resume.create_at as create_at',
                    'mail_send_resume.state as state',
                    'mail_send_resume.send_times as send_times',
                ])
                ->paginate($limit)->appends(request()->input());
        }

        $this->assign('mailList', $mailList);

        $this->setMetaTitle("送信状況");

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        
        return $this->render('MailSendResume.index');
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $item = DB::table('mail_send_resume')->find($id);

        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        return response()->json(['status' => true, 'showMailDetail' => $item]);
    }

    public function mailResend($id)
    {
        $user = \Auth::user();

        $item = DB::table('mail_send_resume')->find($id);

        // 存在チェック
        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        // 送信待ちに更新
        DB::table('mail_send_resume')
            ->where('id',$id)
            ->update([
                'state' => 0,
//                'update_user'=> 'Admin',
                'update_at' => Carbon::now()
            ]);

        return response()->json(['status' => true,'message' => [__('message.success.update_common_address')]]);


    }

}