<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\AdminController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EpsMPurpose;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class Exp_m_purposeController extends AdminController
{

    private $model_user;
    private $model_purpose;

    public function __construct(User $model_user, EpsMPurpose $model_purpose)
    {
        parent::__construct();
        $this->model_user = $model_user;
        $this->model_purpose = $model_purpose;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        $action = $request->get('action', '');
        $arrHistory = null;

        // get list user
        $limit = AppUtils::normalizeLimit($request->get('limit'), config('app.page_limit'));
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'display_order';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir') : 'desc';
        $arrOrder = ['display_order' => 'P.display_order','purpose_name' => 'P.purpose_name', 'describe' => 'P.describe', 'remarks' => 'P.remarks',
        ];

        $filter_purpose_name = $request->get('purpose_name', '');
        $filter_describe = $request->get('describe', '');
        $filter_remarks = $request->get('remarks', '');

        $arrHistory = DB::table('eps_m_purpose as P')
            ->orderBy(isset($arrOrder[$orderBy]) ? $arrOrder[$orderBy] : 'P.mst_company_id', $orderDir)
            ->select(DB::raw('P.mst_company_id, P.purpose_name, P.describe, P.remarks, P.display_order'))
            ->where('P.mst_company_id', $user->mst_company_id)
            ;

        $where = ['1=1'];
        $where_arg = [];

        if($filter_purpose_name) {
            $where[] = 'INSTR(P.purpose_name, ?)'; 
            $where_arg[] = $filter_purpose_name;
        }
        if($filter_describe) {
            $where[] = 'INSTR(P.describe, ?)';
            $where_arg[] = $filter_describe;
        }
        if($filter_remarks) {
            $where[] = 'INSTR(P.remarks, ?)';
            $where_arg[] = $filter_remarks;
        }
        $arrHistory = $arrHistory->whereRaw(implode(" AND ", $where), $where_arg);
        $arrHistory = $arrHistory ->paginate($limit)->appends(request()->input());

        $orderDir = strtolower($orderDir) == "asc" ? "desc" : "asc";

        $this->assign('arrHistory', $arrHistory);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);

        $this->setMetaTitle("目的管理");
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        return $this->render('Expense.purpose_index');
    }

    public function bulkUsage(Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_DELETE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $ids = $request->get('cids', []);
  
        if (count($ids)) {
            DB::beginTransaction();
            try {
                DB::table('eps_m_purpose')
                    ->where('mst_company_id',$user->mst_company_id)
                    ->whereIn('purpose_name', $ids)
                    ->delete();
                DB::commit();
                return response()->json(['status' => true,'message' => [__('message.success.delete_purpose')]]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage() . $e->getTraceAsString());
                    return response()->json(['status' => false, 'message' => 'message.false.delete_purpose']);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'message.false.delete_purpose']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($purpose_name)
    {
        $user   = \Auth::user();

        $item = DB::table('mst_company as C')
            ->leftJoin('eps_m_purpose as P', 'C.id','P.mst_company_id')
            ->select(DB::raw('C.id,P.mst_company_id, P.purpose_name, P.describe, P.remarks, P.create_at, P.create_user, P.update_at, P.update_user, P.version'))
            ->where('C.id',$user->mst_company_id)
            ->where('P.purpose_name',$purpose_name)
            ->first();

        return response()->json(['status' => true, 'item' => $item ]);

    }

    /**
     * Register the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Response
     */
    public function store(Request $request)
    {
        $user   = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_CREATE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $item_info = $request->get('item');
        $purpose_name = "";
        if(isset($item_info['purpose_name'])){
            $purpose_name = $item_info['purpose_name'];
        }
            
        $checkCompany = DB::table('mst_admin')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('id', $user->id)
            ->count();
        if ($checkCompany == 0) {
            Log::error('mst_company_id='.$user->mst_company_id.'id='.$user->id);
            return response()->json(['status' => false,'message' => [__('message.false.register_purpose')]]);
        }

        //キー重複チェック
        $checkDuplicate = DB::table('eps_m_purpose')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('purpose_name',$purpose_name)
            ->count();
        
        if ($checkDuplicate > 0) {
            Log::error('mst_company_id='.$user->mst_company_id.'purpose_name='.$purpose_name);
            return response()->json(['status' => false,'message' => [__('message.false.name_repeated')]]);
        }

        //並び順の取得
        $d_order = DB::table('eps_m_purpose')
            ->where('mst_company_id',$user->mst_company_id)
            ->select(DB::raw('MAX(display_order) + 1 AS display_order'))
            ->first()
            ;
        //初回    
        if(!DB::table('eps_m_purpose')->where('mst_company_id',$user->mst_company_id)->exists()){
            $d_order->display_order = 0;
        }

        $item = new $this->model_purpose;
        $item->fill($item_info);
        $item->mst_company_id = $user->mst_company_id;
        $item->display_order  = $d_order->display_order;
        $item->create_user = $user->getFullName();
        $item->update_user = $user->getFullName();
        $validator = Validator::make($item_info, $this->model_purpose->rules());
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }
        DB::beginTransaction();
        try{
            $item->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'purpose_name' => $purpose_name, 'message' => [__('message.false.register_purpose')]]);
        }
        return response()->json(['status' => true, 'purpose_name' => $purpose_name, 'message' => [__('message.success.register_purpose')]]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Response
     */
    public function update($purpose_name, Request $request)
    {
        $user   = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_UPDATE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $item_info = $request->get('item');

        $checkCompany = DB::table('mst_admin')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('id', $user->id)
            ->count();

        if ($checkCompany == 0) {
            return response()->json(['status' => false,'message' => [__('message.false.update_purpose')]]);
        }

        $validator = Validator::make($item_info, $this->model_purpose->rules());
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        //versionの取得
        $d_version = DB::table('eps_m_purpose')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('purpose_name',$purpose_name)
            ->select(DB::raw('version'))
            ->first()
            ;

        if ($d_version->version != $item_info['version']) {
            return response()->json(['status' => false,'message' => [__('message.false.master_version')]]);
        }
    
        DB::beginTransaction();
        try{
            $this->model_purpose
                 ->where('mst_company_id',$user->mst_company_id)
                 ->where('purpose_name', $purpose_name)
                 ->update([
                    'describe'      => $item_info['describe'],
                    'remarks'       => $item_info['remarks'],
                    'update_user'   => $user->getFullName(),
                    'update_at'     => Carbon::now(),
                    'version'       => $d_version->version + 1
                ]);

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'item' => $item_info, 'message' => [__('message.false.update_purpose')]]);
        }
        //更新結果をダイアログに反映するための取得
        $item_info =
        $this->model_purpose
        ->where('mst_company_id',$user->mst_company_id)
        ->where('purpose_name', $purpose_name)
        ->first()
        ;
        return response()->json(['status' => true, 'item' => $item_info, 'message' => [__('message.success.update_purpose')]]);
    }
}
