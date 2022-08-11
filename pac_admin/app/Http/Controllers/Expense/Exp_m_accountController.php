<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\AdminController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EpsMAccount;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
    
class Exp_m_accountController extends AdminController
{

    private $model_user;
    private $model_account;

    public function __construct(User $model_user, EpsMAccount $model_account)
    {
        parent::__construct();
        $this->model_user = $model_user;
        $this->model_account = $model_account;
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
        $arrOrder = ['display_order' => 'P.display_order','account_name' => 'P.account_name', 'remarks' => 'P.remarks',
        ];

        $filter_account_name = $request->get('account_name', '');
        $filter_remarks = $request->get('remarks', '');

        $arrHistory = DB::table('eps_m_account as P')
            ->orderBy(isset($arrOrder[$orderBy]) ? $arrOrder[$orderBy] : 'P.mst_company_id', $orderDir)
            ->select(DB::raw('P.mst_company_id, P.account_name, P.remarks, P.display_order'))
            ->where('P.mst_company_id', $user->mst_company_id)
            ->whereNull('P.deleted_at')
            ;

        $where = ['1=1'];
        $where_arg = [];

        if($filter_account_name) {
            $where[] = 'INSTR(P.account_name, ?)'; 
            $where_arg[] = $filter_account_name;
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

        $this->setMetaTitle("勘定科目管理");
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        return $this->render('Expense.account_index');
    }

    public function bulkUsage(Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_DELETE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $ids = $request->get('cids', []);
  
        $message = array();
        $checkPresence = DB::table('eps_m_account as D')
                ->join('eps_m_journal_config as C', function ($join) {
                    $join->on('D.mst_company_id', '=', 'C.mst_company_id');
                    $join->on('D.account_name', '=', 'C.account_name');
                })
            ->where('D.mst_company_id',$user->mst_company_id)
            ->whereIn('D.account_name', $ids)
            ->whereNull('C.deleted_at')
            ->count();

        if ($checkPresence > 0) {
            return response()->json(['status' => false,'message' => ['仕訳設定で勘定科目が使用されているので削除できません。']]);
        }

        $checkPresence = DB::table('eps_m_account as D')
                ->join('eps_t_journal as C', function ($join) {
                    $join->on('D.mst_company_id', '=', 'C.mst_company_id');
                    $join->on('D.account_name', '=', 'C.debit_account')
                         ->orOn('D.account_name', '=', 'C.credit_account');
                })
            ->where('D.mst_company_id',$user->mst_company_id)
            ->whereIn('D.account_name', $ids)
            ->whereNull('C.deleted_at')
            ->count();

        if ($checkPresence > 0) {
            return response()->json(['status' => false,'message' => ['経費仕訳一覧で勘定科目が使用されているので削除できません。']]);
        }

        if (count($ids)) {
            DB::beginTransaction();
            try {
                DB::table('eps_m_account')
                    ->where('mst_company_id',$user->mst_company_id)
                    ->whereIn('account_name', $ids)
                    ->update([
                        'deleted_at' => Carbon::now(),
                        'update_user'   => $user->getFullName(),
                        'update_at'     => Carbon::now()
                    ]);
                DB::commit();
                return response()->json(['status' => true,'message' => [__('message.success.delete_account')]]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage() . $e->getTraceAsString());
                    return response()->json(['status' => false, 'message' => 'message.false.delete_account']);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'message.false.delete_account']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($account_name)
    {
        $user   = \Auth::user();

        $item = DB::table('mst_company as C')
            ->leftJoin('eps_m_account as P', 'C.id','P.mst_company_id')
            ->select(DB::raw('C.id,P.mst_company_id, P.account_name, P.remarks, P.deleted_at, P.create_at, P.create_user, P.update_at, P.update_user, P.version'))
            ->where('C.id',$user->mst_company_id)
            ->where('P.account_name',$account_name)
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
        $account_name = "";
        if(isset($item_info['account_name'])){
            $account_name = $item_info['account_name'];
        }
 
        $checkCompany = DB::table('mst_admin')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('id', $user->id)
            ->count();

        if ($checkCompany == 0) {
            Log::error('mst_company_id='.$user->mst_company_id.'id='.$user->id);
            return response()->json(['status' => false,'message' => [__('message.false.register_account')]]);
        }

        //キー重複チェック
        $checkDuplicate = DB::table('eps_m_account')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('account_name',$account_name)
            ->whereNull('deleted_at')
            ->count();

        if ($checkDuplicate > 0) {
            Log::error('mst_company_id='.$user->mst_company_id.'account_name='.$account_name);
            return response()->json(['status' => false,'message' => [__('message.false.name_repeated')]]);
        }

        //削除済レコード存在チェック
        $checkDeleted = DB::table('eps_m_account')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('account_name',$account_name)
            ->count();

        //並び順の取得
        $d_order = DB::table('eps_m_account')
            ->where('mst_company_id',$user->mst_company_id)
            ->select(DB::raw('MAX(display_order) + 1 AS display_order'))
            ->first()
            ;
        //初回    
        if(!DB::table('eps_m_account')->where('mst_company_id',$user->mst_company_id)->exists()){
            $d_order->display_order = 0;
        }
        
        $item = new $this->model_account;
        $item->fill($item_info);
        $item->mst_company_id = $user->mst_company_id;
        $item->display_order  = $d_order->display_order;
        $item->deleted_at  = null;
        $item->create_user = $user->getFullName();
        $item->update_user = $user->getFullName();

        $validator = Validator::make($item_info, $this->model_account->rules());
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        DB::beginTransaction();
        try{
            if($checkDeleted>0){
                $this->model_account
                ->where('mst_company_id',$user->mst_company_id)
                ->where('account_name', $account_name)
                ->update([
                   'remarks'       => $item_info['remarks'],
                   'deleted_at'    => null,
                   'update_user'   => $user->getFullName(),
                   'update_at'     => Carbon::now(),
               ]);
            }else{
                $item->save();
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'account_name' => $account_name, 'message' => [__('message.false.register_account')]]);
        }
        return response()->json(['status' => true, 'account_name' => $account_name, 'message' => [__('message.success.register_account')]]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Response
     */
    public function update($account_name, Request $request)
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
            return response()->json(['status' => false,'message' => [__('message.false.update_account')]]);
        }

        $validator = Validator::make($item_info, $this->model_account->rules());
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        //versionの取得
        $d_version = DB::table('eps_m_account')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('account_name',$account_name)
            ->select(DB::raw('version'))
            ->first()
            ;

        if ($d_version->version != $item_info['version']) {
            return response()->json(['status' => false,'message' => [__('message.false.master_version')]]);
        }
    
        DB::beginTransaction();
        try{
            $this->model_account
                 ->where('mst_company_id',$user->mst_company_id)
                 ->where('account_name', $account_name)
                 ->update([
                    'remarks'       => $item_info['remarks'],
                    'update_user'   => $user->getFullName(),
                    'update_at'     => Carbon::now(),
                    'version'       => $d_version->version + 1
                ]);

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'item' => $item_info, 'message' => [__('message.false.update_account')]]);
        }
        //更新結果をダイアログに反映するための取得
        $item_info =
        $this->model_account
        ->where('mst_company_id',$user->mst_company_id)
        ->where('account_name', $account_name)
        ->first()
        ;
        return response()->json(['status' => true, 'item' => $item_info, 'message' => [__('message.success.update_account')]]);
    }
}
