<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\AdminController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EpsMWtsm;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class Exp_m_wtsmController extends AdminController
{

    private $model_user;
    private $model_wtsm;

    public function __construct(User $model_user, EpsMWtsm $model_wtsm)
    {
        parent::__construct();
        $this->model_user = $model_user;
        $this->model_wtsm = $model_wtsm;
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
        $arrOrder = ['display_order' => 'P.display_order','wtsm_name' => 'P.wtsm_name', 'describe' => 'P.wtsm_describe', 'remarks' => 'P.remarks',
        ];

        $filter_wtsm_name = $request->get('wtsm_name', '');
        $filter_describe = $request->get('wtsm_describe', '');
        $filter_remarks = $request->get('remarks', '');

        $arrHistory = DB::table('eps_m_wtsm as P')
            ->orderBy(isset($arrOrder[$orderBy]) ? $arrOrder[$orderBy] : 'P.mst_company_id', $orderDir)
            ->select(DB::raw('P.mst_company_id, P.wtsm_name, P.wtsm_describe, P.num_people_option, P.num_people_describe, P.detail_option, P.detail_describe, P.tax_option, P.voucher_option, P.remarks, P.display_order'))
            ->where('P.mst_company_id', $user->mst_company_id)
            ;

        $where = ['1=1'];
        $where_arg = [];

        if($filter_wtsm_name) {
            $where[] = 'INSTR(P.wtsm_name, ?)'; 
            $where_arg[] = $filter_wtsm_name;
        }
        if($filter_describe) {
            $where[] = 'INSTR(P.wtsm_describe, ?)';
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

        $this->setMetaTitle("用途管理");
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        return $this->render('Expense.wtsm_index');
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
                DB::table('eps_m_wtsm')
                    ->where('mst_company_id',$user->mst_company_id)
                    ->whereIn('wtsm_name', $ids)
                    ->delete();
                DB::commit();
                return response()->json(['status' => true,'message' => [__('message.success.delete_wtsm')]]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage() . $e->getTraceAsString());
                    return response()->json(['status' => false, 'message' => 'message.false.delete_wtsm']);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'message.false.delete_wtsm']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($wtsm_name)
    {
        $user   = \Auth::user();
        $item = DB::table('mst_company as C')
            ->leftJoin('eps_m_wtsm as P', 'C.id','P.mst_company_id')
            ->select(DB::raw('C.id,P.mst_company_id, P.wtsm_name, P.wtsm_describe, P.num_people_option, P.num_people_describe, P.detail_option, P.detail_describe, P.tax_option, P.voucher_option, P.remarks, P.create_at, P.create_user, P.update_at, P.update_user, P.version'))
            ->where('C.id',$user->mst_company_id)
            ->where('P.wtsm_name',$wtsm_name)
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
        $wtsm_name = "";
        if(isset($item_info['wtsm_name'])){
            $wtsm_name = $item_info['wtsm_name'];
        }
 
        $checkCompany = DB::table('mst_admin')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('id', $user->id)
            ->count();

        if ($checkCompany == 0) {
            Log::error('mst_company_id='.$user->mst_company_id.'id='.$user->id);
            return response()->json(['status' => false,'message' => [__('message.false.register_wtsm')]]);
        }

        //キー重複チェック
        $checkDuplicate = DB::table('eps_m_wtsm')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('wtsm_name',$wtsm_name)
            ->count();

        if ($checkDuplicate > 0) {
            Log::error('mst_company_id='.$user->mst_company_id.'wtsm_name='.$wtsm_name);
            return response()->json(['status' => false,'message' => [__('message.false.name_repeated')]]);
        }

        //並び順の取得
        $d_order = DB::table('eps_m_wtsm')
            ->where('mst_company_id',$user->mst_company_id)
            ->select(DB::raw('MAX(display_order) + 1 AS display_order'))
            ->first()
            ;
        //初回    
        if(!DB::table('eps_m_wtsm')->where('mst_company_id',$user->mst_company_id)->exists()){
            $d_order->display_order = 0;
        }

        $item = new $this->model_wtsm;
        $item->fill($item_info);
        $item->mst_company_id = $user->mst_company_id;
        $item->display_order  = $d_order->display_order;
        $item->create_user = $user->getFullName();
        $item->update_user = $user->getFullName();

        $validator = Validator::make($item_info, $this->model_wtsm->rules());
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
            return response()->json(['status' => false, 'wtsm_name' => $wtsm_name, 'message' => [__('message.false.register_wtsm')]]);
        }
        return response()->json(['status' => true, 'wtsm_name' => $wtsm_name, 'message' => [__('message.success.register_wtsm')]]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Response
     */
    public function update($wtsm_name, Request $request)
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
            return response()->json(['status' => false,'message' => [__('message.false.update_wtsm')]]);
        }

        $validator = Validator::make($item_info, $this->model_wtsm->rules());
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        //versionの取得
        $d_version = DB::table('eps_m_wtsm')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('wtsm_name',$wtsm_name)
            ->select(DB::raw('version'))
            ->first()
            ;

        if ($d_version->version != $item_info['version']) {
            return response()->json(['status' => false,'message' => [__('message.false.master_version')]]);
        }
    
        DB::beginTransaction();
        try{
            $this->model_wtsm
                 ->where('mst_company_id',$user->mst_company_id)
                 ->where('wtsm_name', $wtsm_name)
                 ->update([
                    'wtsm_describe'       => $item_info['wtsm_describe'],
                    'num_people_option'   => $item_info['num_people_option'],
                    'num_people_describe' => $item_info['num_people_describe'],
                    'detail_option'       => $item_info['detail_option'],
                    'detail_describe'     => $item_info['detail_describe'],
                    'tax_option'          => $item_info['tax_option'],
                    'voucher_option'      => $item_info['voucher_option'],
                    'remarks'             => $item_info['remarks'],
                    'update_user'         => $user->getFullName(),
                    'update_at'           => Carbon::now(),
                    'version'             => $d_version->version + 1
                ]);

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'item' => $item_info, 'message' => [__('message.false.update_wtsm')]]);
        }
        //更新結果をダイアログに反映するための取得
        $item_info =
        $this->model_wtsm
        ->where('mst_company_id',$user->mst_company_id)
        ->where('wtsm_name', $wtsm_name)
        ->first()
        ;
        return response()->json(['status' => true, 'item' => $item_info, 'message' => [__('message.success.update_wtsm')]]);
    }
}
