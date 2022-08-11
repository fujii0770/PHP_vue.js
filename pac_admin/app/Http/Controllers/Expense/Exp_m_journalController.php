<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\AdminController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EpsMJournalConfig;
use App\Models\EpsMPurpose;
use App\Models\EpsMWtsm;
use App\Models\EpsMAccount;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class Exp_m_journalController extends AdminController
{

    private $model_user;
    private $model_journal;
    private $model_purpose;
    private $model_wtsm;
    private $model_account;

    public function __construct(User $model_user, EpsMJournalConfig $model_journal,EpsMPurpose $purpose, EpsMWtsm $wtsm, EpsMAccount $account)
    {
        parent::__construct();
        $this->model_user = $model_user;
        $this->model_journal = $model_journal;
        $this->model_purpose = $purpose;
        $this->model_wtsm = $wtsm;
        $this->model_account = $account;
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
        $filter_purpose_name = $request->get('purpose_name', '');
        $arrHistory = null;

        $listPurposeTop = array('汎用' => '汎用');
        $listPurposeDetail = $this->model_purpose
            ->where('mst_company_id',$user->mst_company_id)
            ->orderBy('display_order')
            ->pluck('purpose_name', 'purpose_name')
            ->toArray();
        $listPurpose = array_merge($listPurposeTop,$listPurposeDetail);

        $listWtsm = $this->model_wtsm
            ->where('mst_company_id',$user->mst_company_id)
            ->orderBy('display_order')
            ->pluck('wtsm_name', 'wtsm_name')->toArray();

        $listAccount = $this->model_account
            ->where('mst_company_id',$user->mst_company_id)
            ->whereNull('deleted_at')
            ->orderBy('display_order')
            ->pluck('account_name', 'account_name')->toArray();

        // get list user
        $limit = AppUtils::normalizeLimit($request->get('limit'), config('app.page_limit'));
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'display_order';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir') : 'desc';
        $arrOrder = ['display_order' => 'P.display_order','wtsm_name' => 'P.wtsm_name', 'account_name' => 'P.account_name', 'sub_account_name' => 'P.sub_account_name', 'remarks' => 'P.remarks', 'criteria' => 'P.criteria',
        ];

        $when_sign ="";
        $when_cond ="";
        //WHEN句生成
        foreach(AppUtils::INEQUALITY_SIGN as $key => $value){
            $when_sign = $when_sign.' WHEN \''.$key.'\' THEN \''.$value.'\' ' ;
        }
        foreach(AppUtils::DETAIL_COND as $key => $value){
            $when_cond = $when_cond.' WHEN \''.$key.'\' THEN \''.$value.'\' ' ;
        }

        $arrHistory = DB::table('eps_m_journal_config as P')
            ->orderBy(isset($arrOrder[$orderBy]) ? $arrOrder[$orderBy] : 'P.mst_company_id', $orderDir)
            ->select(DB::raw('P.id, P.mst_company_id, P.purpose_name, P.wtsm_name, P.account_name, sub_account_name
            , P.criteria
            , CONVERT(JSON_UNQUOTE(P.criteria->"$.amount"),SIGNED) AS criteria_amount
            , CASE JSON_UNQUOTE(P.criteria->"$.amount_sign") '.$when_sign.' END AS criteria_amount_sign_value
            , JSON_UNQUOTE(P.criteria->"$.amount_sign") as criteria_amount_sign
            , CONVERT(JSON_UNQUOTE(P.criteria->"$.people"),SIGNED) AS criteria_people
            , CONVERT(JSON_UNQUOTE(P.criteria->"$.amount_people"),SIGNED) AS criteria_amount_people
            , CASE JSON_UNQUOTE(P.criteria->"$.amount_people_sign") '.$when_sign.' END AS criteria_amount_people_sign_value
            , JSON_UNQUOTE(P.criteria->"$.amount_people_sign")  AS criteria_amount_people_sign
            , JSON_UNQUOTE(P.criteria->"$.detail") AS criteria_detail
            , CASE JSON_UNQUOTE(P.criteria->"$.detail_cond") '.$when_cond.' END AS criteria_detail_cond_value
            , JSON_UNQUOTE(P.criteria->"$.detail_cond") AS criteria_detail_cond
            , P.remarks, P.display_order, P.memo, P.create_at, P.create_user, P.update_at, P.update_user, P.version'))
            ->where('P.mst_company_id', $user->mst_company_id)
            ->whereNull('P.deleted_at')
            ;

        $where = ['1=1'];
        $where_arg = [];

        if(!$filter_purpose_name) {
            $filter_purpose_name = '汎用';
            $request->merge(['purpose_name' => $filter_purpose_name]);
        }
        $where[] = 'INSTR(P.purpose_name, ?)'; 
        $where_arg[] = $filter_purpose_name;

        $arrHistory = $arrHistory->whereRaw(implode(" AND ", $where), $where_arg);
        //条件ソートボタンが押された時の考慮
        if($orderBy=='criteria'){
            $arrHistory = $arrHistory->orderBy( 'criteria_amount_sign_value', $orderDir)
                                     ->orderBy( 'criteria_amount', $orderDir)
                                     ->orderBy( 'criteria_people', $orderDir)
                                     ->orderBy( 'criteria_amount_people_sign_value', $orderDir)
                                     ->orderBy( 'criteria_amount_people', $orderDir)
                                     ->orderBy( 'criteria_detail', $orderDir)
                                     ->orderBy( 'criteria_detail_cond_value', $orderDir);
        }

        $arrHistory = $arrHistory ->paginate($limit)->appends(request()->input());

        $orderDir = strtolower($orderDir) == "asc" ? "desc" : "asc";

        $this->assign('listPurpose', $listPurpose);
        $this->assign('listWtsm', $listWtsm);
        $this->assign('listAccount', $listAccount);
        $this->assign('arrHistory', $arrHistory);
        $this->assign('purpose_name', $filter_purpose_name);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);

        $this->setMetaTitle("仕訳設定");
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        return $this->render('Expense.journal_index');
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
                // DB::table('eps_m_journal_config')
                //     ->where('mst_company_id',$user->mst_company_id)
                //     ->whereIn('id', $ids)
                //     ->delete();
                DB::table('eps_m_journal_config')
                    ->where('mst_company_id',$user->mst_company_id)
                    ->whereIn('id', $ids)
                    ->update([
                        'deleted_at' => Carbon::now(),
                        'update_user'   => $user->getFullName(),
                        'update_at'     => Carbon::now()
                    ]);
                DB::commit();
                return response()->json(['status' => true,'message' => [__('message.success.delete_journal')]]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage() . $e->getTraceAsString());
                    return response()->json(['status' => false, 'message' => 'message.false.delete_journal']);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'message.false.delete_journal']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $user   = \Auth::user();

        //WHEN句生成
        $when_sign ="";
        $when_cond ="";
        foreach(AppUtils::INEQUALITY_SIGN as $key => $value){
            $when_sign = $when_sign.' WHEN \''.$key.'\' THEN \''.$value.'\' ' ;
        }
        foreach(AppUtils::DETAIL_COND as $key => $value){
            $when_cond = $when_cond.' WHEN \''.$key.'\' THEN \''.$value.'\' ' ;
        }

        //showメソッド,updateメソッドのSELECT句と同じ　改修する時はそちらも直すこと
        $item = DB::table('mst_company as C')
            ->leftJoin('eps_m_journal_config as P', 'C.id','P.mst_company_id')
            ->select(DB::raw('P.id, P.mst_company_id, P.purpose_name, P.wtsm_name, P.account_name,P.sub_account_name
                , P.criteria
                , CONVERT(JSON_UNQUOTE(P.criteria->"$.amount"),SIGNED) AS criteria_amount
                , CASE JSON_UNQUOTE(P.criteria->"$.amount_sign") '.$when_sign.' END AS criteria_amount_sign_value
                , JSON_UNQUOTE(P.criteria->"$.amount_sign") as criteria_amount_sign
                , CONVERT(JSON_UNQUOTE(P.criteria->"$.people"),SIGNED) AS criteria_people
                , CONVERT(JSON_UNQUOTE(P.criteria->"$.amount_people"),SIGNED) AS criteria_amount_people
                , CASE JSON_UNQUOTE(P.criteria->"$.amount_people_sign") '.$when_sign.' END AS criteria_amount_people_sign_value
                , JSON_UNQUOTE(P.criteria->"$.amount_people_sign")  AS criteria_amount_people_sign
                , JSON_UNQUOTE(P.criteria->"$.detail") AS criteria_detail
                , CASE JSON_UNQUOTE(P.criteria->"$.detail_cond") '.$when_cond.' END AS criteria_detail_cond_value
                , JSON_UNQUOTE(P.criteria->"$.detail_cond") AS criteria_detail_cond
                , P.remarks, P.display_order, P.memo, P.create_at, P.create_user, P.update_at, P.update_user, P.version'))
            ->where('P.mst_company_id', $user->mst_company_id)
            ->where('C.id',$user->mst_company_id)
            ->where('P.id',$id)
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
    public function store($id, Request $request)
    {
        $user   = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_CREATE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $item_info = $request->get('item');
 
        $checkCompany = DB::table('mst_admin')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('id', $user->id)
            ->count();

        if ($checkCompany == 0) {
            Log::error('mst_company_id='.$user->mst_company_id.'id='.$user->id);
            return response()->json(['status' => false,'message' => [__('message.false.register_journal')]]);
        }

        //並び順の取得
        $d_order = DB::table('eps_m_journal_config')
            ->where('mst_company_id',$user->mst_company_id)
            ->select(DB::raw('MAX(display_order) + 1 AS display_order'))
            ->first()
            ;
        //初回    
        if(!DB::table('eps_m_journal_config')->where('mst_company_id',$user->mst_company_id)->exists()){
            $d_order->display_order = 0;
        }

        $array_criteria = array();
        if(isset($item_info['criteria_amount'])){
            $array_criteria = array_merge($array_criteria,array('amount'=>$item_info['criteria_amount']));
            $array_criteria = array_merge($array_criteria,array('amount_sign'=>$item_info['criteria_amount_sign']));
        }
        if(isset($item_info['criteria_people'])){
            $array_criteria = array_merge($array_criteria,array('people'=>$item_info['criteria_people']));
        }
        if(isset($item_info['criteria_amount_people'])){
            $array_criteria = array_merge($array_criteria,array('amount_people'=>$item_info['criteria_amount_people']));
            $array_criteria = array_merge($array_criteria,array('amount_people_sign'=>$item_info['criteria_amount_people_sign']));
        }
        if(isset($item_info['criteria_detail'])){
            $array_criteria = array_merge($array_criteria,array('detail'=>$item_info['criteria_detail']));
            $array_criteria = array_merge($array_criteria,array('detail_cond'=>$item_info['criteria_detail_cond']));
        }
        

        $item = new $this->model_journal;
        $item->fill($item_info);
        $item->criteria = json_encode($array_criteria);
        $item->mst_company_id = $user->mst_company_id;
        $item->display_order  = $d_order->display_order;
        $item->create_user = $user->getFullName();
        $item->update_user = $user->getFullName();
        $item->id = 0;

        //remarksが、備考に翻訳されてしまうので、個別にバリデーションチェック
        if(mb_strlen($item->remarks) > 100 ){
            return response()->json(['status' => false,'message' => ['摘要は、100文字以下で指定してください。']]);
        }

        if(isset($item_info['criteria_detail_cond'])){
            if($item_info['criteria_detail_cond'] == 2 && count(explode(',', $item_info['criteria_detail'])) < 2 ){
                return response()->json(['status' => false,'message' => ['詳細欄で「のいずれかを含む」を選択した場合、カンマ区切りで２つ以上の要素を入力してください。例：現金,小切手']]);
            }
        }

        $validator = Validator::make($item_info, $this->model_journal->rules());
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        //uniqueキー重複チェック
        $checkDuplicate = DB::table('eps_m_journal_config')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('purpose_name',$item->purpose_name)
            ->where('wtsm_name',$item->wtsm_name)
            ->where('account_name',$item->account_name)
            ->whereNull('deleted_at')
            ->count();

        if ($checkDuplicate > 0) {
            return response()->json(['status' => false,'message' => [__('message.false.name_repeated_journal')]]);
        }

        DB::beginTransaction();
        try{
            $item->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'id' => $id, 'message' => [__('message.false.register_journal')]]);
        }
        return response()->json(['status' => true, 'id' => $id, 'message' => [__('message.success.register_journal')]]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Response
     */
    public function update($id, Request $request)
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
            return response()->json(['status' => false,'message' => [__('message.false.update_journal')]]);
        }

        //versionの取得
        $d_version = DB::table('eps_m_journal_config')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('id',$id)
            ->select(DB::raw('version'))
            ->first()
            ;

        if ($d_version->version != $item_info['version']) {
            return response()->json(['status' => false,'message' => [__('message.false.master_version')]]);
        }
    
        $array_criteria = array();
        if(isset($item_info['criteria_amount'])){
            $array_criteria = array_merge($array_criteria,array('amount'=>$item_info['criteria_amount']));
            $array_criteria = array_merge($array_criteria,array('amount_sign'=>$item_info['criteria_amount_sign']));
        }
        if(isset($item_info['criteria_people'])){
            $array_criteria = array_merge($array_criteria,array('people'=>$item_info['criteria_people']));
        }
        if(isset($item_info['criteria_amount_people'])){
            $array_criteria = array_merge($array_criteria,array('amount_people'=>$item_info['criteria_amount_people']));
            $array_criteria = array_merge($array_criteria,array('amount_people_sign'=>$item_info['criteria_amount_people_sign']));
        }
        if(isset($item_info['criteria_detail'])){
            $array_criteria = array_merge($array_criteria,array('detail'=>$item_info['criteria_detail']));
            $array_criteria = array_merge($array_criteria,array('detail_cond'=>$item_info['criteria_detail_cond']));
        }
        
        $item = $this->model_journal->find($id);
        $item->fill($item_info);
        $item->criteria = json_encode($array_criteria);
        $item->update_user = $user->getFullName();

        //remarksが、備考に翻訳されてしまうので、個別にバリデーションチェック
        if(mb_strlen($item->remarks) > 100 ){
            return response()->json(['status' => false,'message' => ['摘要は、100文字以下で指定してください。']]);
        }

        if($item_info['criteria_detail_cond'] == 2 && count(explode(',', $item_info['criteria_detail'])) < 2 ){
            return response()->json(['status' => false,'message' => ['詳細欄で「のいずれかを含む」を選択した場合、カンマ区切りで２つ以上の要素を入力してください。例：現金,小切手']]);
        }

        $validator = Validator::make($item_info, $this->model_journal->rules());
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        //uniqueキー重複チェック
        $checkDuplicate = DB::table('eps_m_journal_config')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('purpose_name',$item->purpose_name)
            ->where('wtsm_name',$item->wtsm_name)
            ->where('account_name',$item->account_name)
            ->where('id','<>', $id)
            ->whereNull('deleted_at')
            ->count();

        if ($checkDuplicate > 0) {
            return response()->json(['status' => false,'message' => [__('message.false.name_repeated_journal')]]);
        }

        DB::beginTransaction();
        try{
            $item->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'item' => $item_info, 'message' => [__('message.false.update_journal')]]);
        }

        //WHEN句生成
        $when_sign ="";
        $when_cond ="";
        foreach(AppUtils::INEQUALITY_SIGN as $key => $value){
            $when_sign = $when_sign.' WHEN \''.$key.'\' THEN \''.$value.'\' ' ;
        }
        foreach(AppUtils::DETAIL_COND as $key => $value){
            $when_cond = $when_cond.' WHEN \''.$key.'\' THEN \''.$value.'\' ' ;
        }
        //更新結果をダイアログに反映するための取得
        $item = DB::table('mst_company as C')
        ->leftJoin('eps_m_journal_config as P', 'C.id','P.mst_company_id')
        ->select(DB::raw('P.id, P.mst_company_id, P.purpose_name, P.wtsm_name, P.account_name,P.sub_account_name
                , P.criteria
                , CONVERT(JSON_UNQUOTE(P.criteria->"$.amount"),SIGNED) AS criteria_amount
                , CASE JSON_UNQUOTE(P.criteria->"$.amount_sign") '.$when_sign.' END AS criteria_amount_sign_value
                , JSON_UNQUOTE(P.criteria->"$.amount_sign") as criteria_amount_sign
                , CONVERT(JSON_UNQUOTE(P.criteria->"$.people"),SIGNED) AS criteria_people
                , CONVERT(JSON_UNQUOTE(P.criteria->"$.amount_people"),SIGNED) AS criteria_amount_people
                , CASE JSON_UNQUOTE(P.criteria->"$.amount_people_sign") '.$when_sign.' END AS criteria_amount_people_sign_value
                , JSON_UNQUOTE(P.criteria->"$.amount_people_sign")  AS criteria_amount_people_sign
                , JSON_UNQUOTE(P.criteria->"$.detail") AS criteria_detail
                , CASE JSON_UNQUOTE(P.criteria->"$.detail_cond") '.$when_cond.' END AS criteria_detail_cond_value
                , JSON_UNQUOTE(P.criteria->"$.detail_cond") AS criteria_detail_cond
                , P.remarks, P.display_order, P.memo, P.create_at, P.create_user, P.update_at, P.update_user, P.version'))
        ->where('C.id',$user->mst_company_id)
        ->where('P.id',$id)
        ->first()
        ;

        return response()->json(['status' => true, 'item' => $item, 'message' => [__('message.success.update_journal')]]);
    }
}
