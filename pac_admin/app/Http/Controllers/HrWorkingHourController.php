<?php

namespace App\Http\Controllers;

use App\Models\HrWorkHours;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HrWorkingHourController extends AdminController
{
    private $model;

    public function __construct(HrWorkHours $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /*
     * 就労時間管理
     */
    public function index(Request $request){
        $user=\Auth::user();
        $definition_name=$request->get('definition_name');
        $work_form_kbn=$request->get('work_form_kbn');
        // リクエスト取得
        $limit       = $request->get('limit')       ? $request->get('limit')   : config('app.page_limit');
        $orderBy     = $request->get('orderBy')     ? $request->get('orderBy') : 'id';
        $where      = ['1 = 1'];
        $where_arg  = [];

        if($definition_name || $definition_name==0){
            $where[]        = 'definition_name like ?';
            $where_arg[]    = '%'.$definition_name.'%';
        }
        if($work_form_kbn >=0 && $work_form_kbn!=NULL){
            $where[]        = 'work_form_kbn = ?';
            $where_arg[]    = $work_form_kbn;
        }


        $arrHistory=$this->model
            ->whereRaw(implode(" AND ", $where), $where_arg)
            ->where("mst_company_id",$user->mst_company_id)
            ->orderBy($orderBy,'desc')
            ->paginate($limit)
            ->appends(request()->input());



        $this->setMetaTitle('就労時間管理');
        $this->assign('user_title', '管理者');
        $this->assign('arrHistory', $arrHistory);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        return $this->render('HrWorkingHour.index');
    }
    /*
     * show
     */
    public function show($id){
        $user=\Auth::user();
         try{
             $arrHistory=$this->model
                 ->where("id",base64_decode($id))
                 ->where('mst_company_id',$user->mst_company_id)
                 ->first();
             return response()->json(['status'=>true,'item'=>$arrHistory]);
         }
         catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }

    /*
     * 就労時間管理
     * 就労時間管理-更新
     */
    public function update($id, Request $request){
        $user=\Auth::user();
        $data=$request->get('item',[]);
        $data['mst_company_id'] = $user->mst_company_id;
        $data['work_form_kbn'] = isset($data['work_form_kbn']) ? $data['work_form_kbn'] : 0;
        $data['regulations_work_start_time']=isset($data['regulations_work_start_time'])?$this->stringtime($data['regulations_work_start_time']):null;
        $data['regulations_work_end_time']=isset($data['regulations_work_end_time'])?$this->stringtime($data['regulations_work_end_time']):null;
        $data['shift1_start_time']=isset($data['shift1_start_time'])?$this->stringtime($data['shift1_start_time']):null;
        $data['shift1_end_time']=isset($data['shift1_end_time'])?$this->stringtime($data['shift1_end_time']):null;
        $data['shift2_start_time']=isset($data['shift2_start_time'])?$this->stringtime($data['shift2_start_time']):null;
        $data['shift2_end_time']=isset($data['shift2_end_time'])?$this->stringtime($data['shift2_end_time']):null;
        $data['shift3_start_time']=isset($data['shift3_start_time'])?$this->stringtime($data['shift3_start_time']):null;
        $data['shift3_end_time']=isset($data['shift3_end_time'])?$this->stringtime($data['shift3_end_time']):null;
        $timenuma=array($data['regulations_work_start_time'],$data['regulations_work_end_time']);
        $timenumb=array($data['shift1_end_time'],$data['shift1_start_time']);
        $timenumc=array($data['shift2_end_time'],$data['shift2_start_time']);
        $timenumd=array($data['shift3_end_time'],$data['shift3_start_time']);
        $data['regulations_working_hours']=isset($data['regulations_working_hours']) ? $data['regulations_working_hours'] : null;
        if(sizeof(array_filter($timenuma)) ==1 || sizeof(array_filter($timenumb))==1
            || sizeof(array_filter($timenumc))==1 || sizeof(array_filter($timenumd)) == 1){
            return response()->json(['status' => false,
                'message' => [__('message.false.pairstime_hr_user')]
            ]);
        }
        $validator = Validator::make($data, $this->model->rules());
        if ($validator->fails())
        {
            return response()->json(['status' => false,'message' => [__('message.warning.hr_working_kbn')]]);
        }
        if(($data['work_form_kbn']==0) && (sizeof(array_filter($timenuma))<=1)){
            return response()->json(['status' => false,'message' => [__('message.warning.hr_working_must')]]);
        }
        if(($data['work_form_kbn']==1) && (sizeof(array_filter($timenumb))<=1)){
            return response()->json(['status' => false,'message' => [__('message.warning.hr_working_shift_must')]]);
        }
        if($data['work_form_kbn']==2 && $data['regulations_working_hours']==null ){
            return response()->json(['status' => false,'message' => [__('message.warning.hr_working_hour_must')]]);
        }
        switch($data['work_form_kbn']){
            case 0:
                $data['shift1_start_time']=null;
                $data['shift1_end_time']=null;
                $data['shift2_start_time']=null;
                $data['shift2_end_time']=null;
                $data['shift3_start_time']=null;
                $data['shift3_end_time']=null;
                $data['regulations_working_hours']=null;
              break;
            case 1:
                $data['regulations_work_start_time']=null;
                $data['regulations_work_end_time']=null;
                $data['regulations_working_hours']=null;
            break;
            case 2:
                $data['shift1_start_time']=null;
                $data['shift1_end_time']=null;
                $data['shift2_start_time']=null;
                $data['shift2_end_time']=null;
                $data['shift3_start_time']=null;
                $data['shift3_end_time']=null;
                $data['regulations_work_start_time']=null;
                $data['regulations_work_end_time']=null;
                $data['regulations_working_hours']=$data['regulations_working_hours']*60;
            break;
        }

        try{
            $this->model
                ->where('id',base64_decode($id))
                ->where('mst_company_id',$data['mst_company_id'])
                ->update([
                    'definition_name'=>$data['definition_name'],
                    'work_form_kbn'=>$data['work_form_kbn'],
                    'regulations_work_start_time'=>$data['regulations_work_start_time'],
                    'regulations_work_end_time'=>$data['regulations_work_end_time'],
                    'shift1_start_time'=>$data['shift1_start_time'],
                    'shift1_end_time'=>$data['shift1_end_time'],
                    'shift2_start_time'=>$data['shift2_start_time'],
                    'shift2_end_time'=>$data['shift2_end_time'],
                    'shift3_start_time'=>$data['shift3_start_time'],
                    'shift3_end_time'=>$data['shift3_end_time'],
                    'regulations_working_hours'=>$data['regulations_working_hours'],
                    'overtime_unit'=>$data['overtime_unit'],
                    'break_time'=>$data['break_time'],
                    'update_user'=>$user->getFullName(),
                    'update_at'=>date('Y-m-d H:h:i')
                ]);
            return response()->json(['status' => true,'message' => [__('message.success.update_hr_working')]]);
        }catch(\Exception $e){

            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }

    public function insert(Request $request){
        $user=\Auth::user();
        $data=$request->get('item',[]);
        $data['mst_company_id'] = $user->mst_company_id;
        $data['work_form_kbn'] = isset($data['work_form_kbn']) ? $data['work_form_kbn'] : 0;
        $data['regulations_work_start_time']=isset($data['regulations_work_start_time'])?$this->stringtime($data['regulations_work_start_time']):null;
        $data['regulations_work_end_time']=isset($data['regulations_work_end_time'])?$this->stringtime($data['regulations_work_end_time']):null;
        $data['shift1_start_time']=isset($data['shift1_start_time'])?$this->stringtime($data['shift1_start_time']):null;
        $data['shift1_end_time']=isset($data['shift1_end_time'])?$this->stringtime($data['shift1_end_time']):null;
        $data['shift2_start_time']=isset($data['shift2_start_time'])?$this->stringtime($data['shift2_start_time']):null;
        $data['shift2_end_time']=isset($data['shift2_end_time'])?$this->stringtime($data['shift2_end_time']):null;
        $data['shift3_start_time']=isset($data['shift3_start_time'])?$this->stringtime($data['shift3_start_time']):null;
        $data['shift3_end_time']=isset($data['shift3_end_time'])?$this->stringtime($data['shift3_end_time']):null;
        $data['create_at']=date('Y-m-d H:h:i');
        $data['create_user']=$user->getFullName();
        $data['regulations_working_hours']=isset($data['regulations_working_hours']) ? $data['regulations_working_hours'] : null;
        $timenuma=array($data['regulations_work_start_time'],$data['regulations_work_end_time']);
        $timenumb=array($data['shift1_end_time'],$data['shift1_start_time']);
        $timenumc=array($data['shift2_end_time'],$data['shift2_start_time']);
        $timenumd=array($data['shift3_end_time'],$data['shift3_start_time']);
        if(sizeof(array_filter($timenuma)) ==1 || sizeof(array_filter($timenumb))==1 || sizeof(array_filter($timenumc))==1 || sizeof(array_filter($timenumd)) == 1){
            return response()->json(['status' => false,
                'message' => [__('message.false.pairstime_hr_user')]
            ]);
        }
        if(sizeof(array_filter($timenuma))>=2){
            $data['work_form_kbn']='0';
        }
        else if(sizeof(array_filter($timenumb))>=2 || sizeof(array_filter($timenumc))>=2 ||sizeof(array_filter($timenumd))>=2){
            $data['work_form_kbn']='1';
        }
        else if($data['regulations_working_hours'] >'0'){
            $data['work_form_kbn']='2';
        }

        $validator = Validator::make($data, $this->model->rules());
        if ($validator->fails())
        {
            return response()->json(['status' => false,'message' => [__('message.warning.hr_working_kbn')]]);
        }
        if(($data['work_form_kbn']==0) && (sizeof(array_filter($timenuma))<=1)){
            return response()->json(['status' => false,'message' => [__('message.warning.hr_working_must')]]);
        }
        if(($data['work_form_kbn']==1) && (sizeof(array_filter($timenumb))<=1)){
            return response()->json(['status' => false,'message' => [__('message.warning.hr_working_shift_must')]]);
        }
        if($data['work_form_kbn']==2 && $data['regulations_working_hours']==null ){
            return response()->json(['status' => false,'message' => [__('message.warning.hr_working_hour_must')]]);
        }
        switch($data['work_form_kbn']){
            case 0:
                $data['shift1_start_time']=null;
                $data['shift1_end_time']=null;
                $data['shift2_start_time']=null;
                $data['shift2_end_time']=null;
                $data['shift3_start_time']=null;
                $data['shift3_end_time']=null;
                $data['regulations_working_hours']=null;
                break;
            case 1:
                $data['regulations_work_start_time']=null;
                $data['regulations_work_end_time']=null;
                $data['regulations_working_hours']=null;
                break;
            case 2:
                $data['shift1_start_time']=null;
                $data['shift1_end_time']=null;
                $data['shift2_start_time']=null;
                $data['shift2_end_time']=null;
                $data['shift3_start_time']=null;
                $data['shift3_end_time']=null;
                $data['regulations_work_start_time']=null;
                $data['regulations_work_end_time']=null;
                $data['regulations_working_hours']=$data['regulations_working_hours']*60;
                break;
        }


        try{
            $id=$this->model->insertGetId($data);
            return response()->json(['status' => true,'message' => [__('message.success.create_hr_working')],'id'=>$id]);
        }catch(\Exception $e){

            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }

    protected  function stringtime($time){
          return strtotime($time)?$time:null;;
    }


}
