<?php

namespace App\Http\Controllers\Expense;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Validator;
use App\Http\Utils\PermissionUtils;
use DB;
use App\Models\User;
use App\Models\Company;
use App\Models\EpsMAccount;
use App\Models\EpsTJournal;
use App\Http\Utils\AppUtils;
use App\Http\Utils\DownloadUtils;
use App\CompanyAdmin;
use Carbon\Carbon;

class Exp_t_journalController extends AdminController {
    private $model;
    private $model_account;
    private $model_t_journal;

    public function __construct(CompanyAdmin $model, EpsMAccount $account, EpsTJournal $journal)
    {
        parent::__construct();
        $this->model = $model;
        $this->model_account = $account;
        $this->model_t_journal = $journal;

        $this->assign('use_angular', true);
        $this->assign('show_sidebar', true);
        $this->assign('use_contain', true);
    }

    public function index(Request $request){
        $user   = \Auth::user();
        $action = $request->get('action','');

        $limit      = $request->get('limit') ? $request->get('limit') : config('app.page_limit');

        $filter_rec_from            = substr($request['rec_from'], 0, 10);  //yyyy-mm-ddを切り取る 計上日
        $filter_rec_to              = substr($request['rec_to'], 0, 10);
        $filter_expected_pay_from   = substr($request['expected_pay_from'], 0, 10);  //yyyy-mm-ddを切り取る 支払日
        $filter_expected_pay_to     = substr($request['expected_pay_to'], 0, 10);
        $filter_accountspace        = $request['accountspace'];

        //色のストライプ表示のための情報取得 eps_app_item_no単位で色を変える
        $colorQuerySub =  DB::table('eps_t_journal as C')
                    ->where('C.mst_company_id', $user->mst_company_id) //自分の会社のユーザのみが対象
                    ->whereNull('C.deleted_at');
                    ;
        //金額不一致の色を変えるための情報取得
        $sumQuerySub =  DB::table('eps_t_journal as S')
            ->where('S.mst_company_id',$user->mst_company_id)
            ->whereNull('S.deleted_at');
            ;

        // if($filter_rec_from) {
        //     $colorQuerySub->whereDate('C.rec_date', '>=', $filter_rec_from);
        //     $sumQuerySub->whereDate('S.rec_date', '>=', $filter_rec_from);
        // }
        // if($filter_rec_to) {
        //     $colorQuerySub->whereDate('C.rec_date', '<=', $filter_rec_to);
        //     $sumQuerySub->whereDate('S.rec_date', '<=', $filter_rec_to);
        // }
        // if($filter_expected_pay_from) {
        //     $colorQuerySub->whereDate('C.expected_pay_date', '>=', $filter_expected_pay_from);
        //     $sumQuerySub->whereDate('S.expected_pay_date', '>=', $filter_expected_pay_from);
        // }
        // if($filter_expected_pay_to) {
        //     $colorQuerySub->whereDate('C.expected_pay_date', '<=', $filter_expected_pay_to);
        //     $sumQuerySub->whereDate('S.expected_pay_date', '<=', $filter_expected_pay_to);
        // }
        // if($filter_accountspace) {
        //     $colorQuerySub->where(function ($query) {
        //         $query->whereNull('C.debit_account')
        //               ->orWhereRAW('C.debit_subaccount is null')
        //               ->orWhereRAW('C.credit_account is null')
        //               ->orWhereRAW('C.credit_subaccount is null');
        //     });
        //     // $sumQuerySub->where(function ($query) {
        //     //     $query->whereNull('S.debit_account')
        //     //           ->orWhereRAW('S.debit_subaccount is null')
        //     //           ->orWhereRAW('S.credit_account is null')
        //     //           ->orWhereRAW('S.credit_subaccount is null');
        //     // });
        // }

        $colorQuerySub->select(DB::raw('(ROW_NUMBER() OVER(ORDER BY C.eps_t_app_id DESC ,C.eps_t_app_item_id)) % 2 AS rownum_flg, C.eps_t_app_id, C.eps_t_app_item_id'))
                      ->groupBy('C.eps_t_app_id','C.eps_t_app_item_id')
                      ;

        $sumQuerySub->select(DB::raw('SUM(S.debit_amount) as debit_amount_sum,SUM(S.credit_amount) as credit_amount_sum, S.eps_t_app_item_id'))
                     ->groupBy('S.eps_t_app_item_id')
                      ;

        $arrApp = DB::table('eps_t_journal as D')
                ->JoinSub($colorQuerySub, 'C', function ($join) {
                        $join->on('D.eps_t_app_id', '=', 'C.eps_t_app_id');
                        $join->on('D.eps_t_app_item_id', '=', 'C.eps_t_app_item_id');
                    })
                ->JoinSub($sumQuerySub, 'S', function ($join) {
                        $join->on('D.eps_t_app_item_id', '=', 'S.eps_t_app_item_id');
                    })
                ->Join('eps_t_app_items as I', function ($join) {
                    $join->on('D.mst_company_id', '=', 'I.mst_company_id');
                    $join->on('D.eps_t_app_id', '=', 'I.t_app_id');
                    $join->on('D.eps_t_app_item_id', '=', 'I.id');
                })
                ->orderByRaw('eps_t_app_id DESC, eps_t_app_item_id')
                ->select(DB::raw('CASE WHEN S.debit_amount_sum = I.expected_pay_amt THEN \'\' ELSE \'style_red\' END AS debit_amount_style
                                , CASE WHEN S.credit_amount_sum = I.expected_pay_amt THEN \'\' ELSE \'style_red\' END AS credit_amount_style
                                , S.debit_amount_sum, S.credit_amount_sum, I.expected_pay_amt
                                , CASE C.rownum_flg WHEN 1 THEN \'even\' ELSE \'\' END AS rownum_flg, D.eps_t_app_id, D.eps_t_app_item_id, D.eps_app_item_bno, D.rec_date, I.expected_pay_date, D.debit_account, D.debit_subaccount, FORMAT(D.debit_amount,0) as debit_amount, IFNULL(D.debit_tax_div,\'DUMMY\') as debit_tax_div, FORMAT(D.debit_tax,0) as debit_tax, D.credit_account, D.credit_subaccount, FORMAT(D.credit_amount,0) as credit_amount, IFNULL(D.credit_tax_div,\'DUMMY\') as credit_tax_div, FORMAT(D.credit_tax,0) as credit_tax, D.remarks ,D.id'))
                ->where('D.mst_company_id', $user->mst_company_id) //自分の会社のユーザのみが対象
                ->whereNull('I.deleted_at')
                ->whereNull('D.deleted_at');
                                // , CASE C.rownum_flg WHEN 1 THEN \'even\' ELSE \'\' END AS rownum_flg, D.eps_t_app_id, D.eps_t_app_item_id, D.eps_app_item_bno, DATE_FORMAT(D.rec_date,\'%Y%m%d\') AS rec_date, DATE_FORMAT(I.expected_pay_date,\'%Y%m%d\') AS expected_pay_date, debit_account, debit_subaccount, FORMAT(D.debit_amount,0) as debit_amount, IFNULL(debit_tax_div,\'DUMMY\') as debit_tax_div, FORMAT(D.debit_tax,0) as debit_tax, credit_account, credit_subaccount, FORMAT(D.credit_amount,0) as credit_amount, IFNULL(credit_tax_div,\'DUMMY\') as credit_tax_div, FORMAT(D.credit_tax,0) as credit_tax, D.remarks ,D.id'))
                                
        $searchQuerySub =  DB::table('eps_t_journal as A')
            ->where('A.mst_company_id', $user->mst_company_id) //自分の会社のユーザのみが対象
            ->whereNull('A.deleted_at');
        ;

        if($filter_rec_from) {
            $searchQuerySub->whereDate('A.rec_date', '>=', $filter_rec_from);
        }
        if($filter_rec_to) {
            $searchQuerySub->whereDate('A.rec_date', '<=', $filter_rec_to);
        }
        //if($filter_expected_pay_from) {
           // $searchQuerySub->whereDate('A.expected_pay_date', '>=', $filter_expected_pay_from);
        //}
        //if($filter_expected_pay_to) {
           // $searchQuerySub->whereDate('A.expected_pay_date', '<=', $filter_expected_pay_to);
        //}
        if($filter_accountspace) {
            $searchQuerySub->where(function ($query) {
                $query->whereNull('A.debit_account')
                      ->orWhereRAW('A.debit_subaccount is null')
                      ->orWhereRAW('A.credit_account is null')
                      ->orWhereRAW('A.credit_subaccount is null');
            })
            ;
        }
        $searchQuerySub
        ->select('A.mst_company_id','A.eps_t_app_item_id')
        ->groupBy('A.mst_company_id','A.eps_t_app_item_id')
        ;
        if($searchQuerySub){
            $arrApp
            ->JoinSub($searchQuerySub, 'A', function ($join) {
                $join->on('D.mst_company_id', '=', 'A.mst_company_id');
                $join->on('D.eps_t_app_item_id', '=', 'A.eps_t_app_item_id');
            });
        }

        if($action == 'export'){
            $arrApp = $arrApp ->get();
        }else{
            $arrApp = $arrApp ->paginate($limit)->appends(request()->input());
        }

        //ダイアログで使う
        $listAccount = $this->model_account
            ->where('mst_company_id',$user->mst_company_id)
            ->whereNull('deleted_at')
            ->orderBy('display_order')
            ->pluck('account_name', 'account_name')->toArray();

        // $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
        $this->setMetaTitle("経費仕訳一覧");
        $this->assign('arrApp', $arrApp);
        $this->assign('listAccount', $listAccount);
        $this->assign('limit', $limit);
        // $this->assign('orderBy', $orderBy);
        // $this->assign('orderDir', $orderDir);

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        if($action == 'export'){
            return $this->render('Expense.csv');
        }else{
            return $this->render('Expense.t_journal_index');
        }
    }

    public function show($id, Request $request)
    {
        //$idにはeps_app_noが設定されている
        $user  = \Auth::user();
        $eps_t_app_item_id  = $request->get('eps_t_app_item_id') ? $request->get('eps_t_app_item_id') : 0;
        
            $item = DB::table('eps_t_app_items as D')
            ->join('eps_t_app as A', function ($join) {
                $join->on('D.mst_company_id', '=', 'A.mst_company_id');
                $join->on('D.t_app_id', '=', 'A.id');
            })
            ->join('eps_m_form as F', function ($join) {
                $join->on('A.mst_company_id', '=', 'F.mst_company_id');
                $join->on('A.form_code', '=', 'F.form_code');
            })
            ->leftJoin('mst_user as U', 'U.id','A.mst_user_id')
            ->leftJoin('mst_user_info as I', 'A.mst_user_id','I.mst_user_id')
            ->leftJoin('mst_department as E', 'I.mst_department_id','E.id')
            ->leftjoin('circular as C', function ($join) {
                $join->on('C.id', '=', 'A.circular_id');
            })
            ->select(DB::raw('D.t_app_id, D.id, DATE_FORMAT(A.completed_date,\'%Y%m\') AS completed_date, F.form_type, CASE WHEN A.completed_date is not null THEN \'3\' ELSE C.circular_status END as circular_status, A.form_code, F.form_name, A.purpose_name, E.department_name, A.mst_user_id, DATE_FORMAT(A.target_period_from,\'%Y/%m/%d\') AS target_period_from, DATE_FORMAT(A.target_period_to,\'%Y/%m/%d\') AS target_period_to, CONCAT(U.family_name, U.given_name) as user_name, A.form_dtl, format(A.suspay_amt, 0) as suspay_amt, format(A.eps_amt, 0) as eps_amt, format(A.eps_diff, 0) as eps_diff, A.create_at, DATE_FORMAT(A.suspay_date,\'%Y/%m/%d\') AS suspay_date, DATE_FORMAT(A.diff_date,\'%Y/%m/%d\') AS diff_date
                             ,D.wtsm_name, FORMAT(D.expected_pay_amt,0) as expected_pay_amt, DATE_FORMAT(D.expected_pay_date,\'%Y/%m/%d\') as expected_pay_date, D.numof_ppl, D.remarks'))
            ->where('D.mst_company_id', $user->mst_company_id)
            ->where('D.id', $eps_t_app_item_id)
            ->whereNull('D.deleted_at')
            ->whereNull('A.deleted_at')
            ->whereNull('F.deleted_at')
            ->first();

            $item2 = DB::table('eps_t_app_files as D')
            ->where('D.mst_company_id', $user->mst_company_id)
            ->where('D.t_app_items_id', $eps_t_app_item_id)
            ->whereNull('D.deleted_at')
            ->get();

            $item3 = DB::table('eps_t_journal')
            ->select(DB::raw('id, eps_t_app_id, eps_t_app_item_id, rec_date, debit_rec_dept, debit_account, debit_subaccount, TRUNCATE(debit_amount,0) as debit_amount, debit_tax_div, debit_tax_rate , TRUNCATE(debit_tax,0) as debit_tax
                             ,credit_rec_dept, credit_account, credit_subaccount, TRUNCATE(credit_amount,0) as credit_amount, credit_tax_div, credit_tax_rate, TRUNCATE(credit_tax,0) as credit_tax, remarks, version'))
            ->where('mst_company_id', $user->mst_company_id)
            ->where('id', $id)
            ->first();
            
            return response()->json(['status' => true, 'item' => $item, 'item2' => $item2, 'item3' => $item3]);
    }

    function update($id, Request $request){
        // Log::debug($request->all());
        $user = \Auth::user();        
        if(!$user->can(PermissionUtils::PERMISSION_EXPENSE_JOURNAL_LIST_UPDATE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $item_info_add = $request->get('item_add');
        //INSERT処理 START
        if(isset($item_info_add)){
            $item_add = new $this->model_t_journal;
            $item_add->fill($item_info_add);
            $item_add->mst_company_id = $user->mst_company_id;
            $item_add->create_user = $user->getFullName();
            $item_add->update_user = $user->getFullName();

            $validator = Validator::make($item_info_add, $this->model_t_journal->rules());
            if ($validator->fails())
            {
                $message = $validator->messages();
                $message_all = $message->all();
                return response()->json(['status' => false,'message' => $message_all]);
            }

            //eps_app_item_bnoの決定
            $eps_t_j = DB::table('eps_t_journal')
                    ->select(DB::raw('MAX(eps_app_item_bno) + 1 AS max_bno'))
                    ->where('mst_company_id', $user->mst_company_id) 
                    ->where('eps_t_app_id', $item_add->eps_t_app_id) 
                    ->where('eps_t_app_item_id', $item_add->eps_t_app_item_id) 
                    ->first();
            $item_add->eps_app_item_bno = $eps_t_j->max_bno;
        }
        //INSERT処理 END

        $item_post  = $request->get('item');

        //UPDATE処理 START
        if(isset($item_post)){
                $checkCompany = DB::table('mst_admin')
                ->where('mst_company_id',$user->mst_company_id)
                ->where('id', $user->id)
                ->count();

            if ($checkCompany == 0) {
                Log::error('mst_company_id='.$user->mst_company_id.'id='.$user->id);
                return response()->json(['status' => false,'message' => [__('message.false.update_t_journal')]]);
            }

            // $item->fill($item_post);
            // $item->update_user = $user->getFullName();

            //versionの取得
            $d_version =  $this->model_t_journal
                ->where('mst_company_id',$user->mst_company_id)
                ->where('id', $id)
                ->select(DB::raw('version'))
                ->first();

            if ($d_version->version != $item_post['version']) {
                return response()->json(['status' => false,'message' => [__('message.false.master_version')]]);
            }

            $item_post['update_user'] = $user->getFullName();
            $item_post['update_at'] = Carbon::now();
            $item_post['version'] = $d_version->version + 1;

            //未入力項目をバリデーションチェックに通さないための考慮
            if(isset($item_post['rec_date'])){
                $item_post_2['rec_date'] = $item_post['rec_date'];
            }
            if(isset($item_post['debit_account'])){
                $item_post_2['debit_account'] = $item_post['debit_account'];
            }
            if(isset($item_post['debit_subaccount'])){
                $item_post_2['debit_subaccount'] = $item_post['debit_subaccount'];
            }
            if(isset($item_post['debit_amount'])){
                $item_post_2['debit_amount'] = $item_post['debit_amount'];
            }
            if(isset($item_post['debit_tax_div'])){
                $item_post_2['debit_tax_div'] = $item_post['debit_tax_div'];
            }
            if(isset($item_post['debit_tax'])){
                $item_post_2['debit_tax'] = $item_post['debit_tax'];
            }
            if(isset($item_post['credit_account'])){
                $item_post_2['credit_account'] = $item_post['credit_account'];
            }
            if(isset($item_post['credit_subaccount'])){
                $item_post_2['credit_subaccount'] = $item_post['credit_subaccount'];
            }
            if(isset($item_post['credit_amount'])){
                $item_post_2['credit_amount'] = $item_post['credit_amount'];
            }
            if(isset($item_post['credit_tax_div'])){
                $item_post_2['credit_tax_div'] = $item_post['credit_tax_div'];
            }
            if(isset($item_post['credit_tax'])){
                $item_post_2['credit_tax'] = $item_post['credit_tax'];
            }
            if(isset($item_post['remarks'])){
                $item_post_2['remarks'] = $item_post['remarks'];
            }
            $item_post_2['update_user'] = $item_post['update_user'];
            $item_post_2['update_at'] = $item_post['update_at'];
            $item_post_2['version'] = $item_post['version'];

            //バリデーションだけeloquentを使う。saveメソッドはidが無いと使えないのでupdateメソッドを使う。
            $validator = Validator::make($item_post_2, $this->model_t_journal->rules());        
            if ($validator->fails())
            {
                $message = $validator->messages();
                $message_all = $message->all();
                return response()->json(['status' => false,'message' => $message_all]);
            }
        }
        //UPDATE処理 END

        DB::beginTransaction();
        try{
            //登録
            if(isset($item_add)){
                $item_add->save();
            }
            //更新
            $this->model_t_journal
                 ->where('mst_company_id',$user->mst_company_id)
                 ->where('id', $id)
                 ->update($item_post);
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        //金額合計チェック
        $eps_t_app_id = $request->get('eps_t_app_id');
        $eps_t_app_item_id = $request->get('eps_t_app_item_id');

        $journal_sum =  $this->model_t_journal
            ->where('mst_company_id',$user->mst_company_id)
            ->where('eps_t_app_item_id',$eps_t_app_item_id)
            ->whereNull('deleted_at')
            ->groupBy('eps_t_app_item_id')
            ->select(DB::raw('SUM(debit_amount) as debit_amount_sum,SUM(credit_amount) as credit_amount_sum'))
            ->first();

        $eps_t_app_items = DB::table('eps_t_app_items')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('id',$eps_t_app_item_id)
            ->whereNull('deleted_at')
            ->select('expected_pay_amt')
            ->first();

        $message = array();
        if($journal_sum->debit_amount_sum != $eps_t_app_items->expected_pay_amt){
            $message[] = '警告：更新完了しましたが、借方金額の合計('.floor($journal_sum->debit_amount_sum).')と用途の金額('.floor($eps_t_app_items->expected_pay_amt).')が一致していません。ご確認ください。';
        }
        if($journal_sum->credit_amount_sum != $eps_t_app_items->expected_pay_amt){
            $message[] = '警告：更新完了しましたが、貸方金額の合計('.floor($journal_sum->credit_amount_sum).')と用途の金額('.floor($eps_t_app_items->expected_pay_amt).')一致していません。ご確認ください。';
        }
        if(!empty($message)){
            return response()->json(['status' => 'warning','version' => $item_post['version'],'message' => $message]);
        }

        return response()->json(['status' => true, 'version' => $item_post['version'], 'message' => [__('message.success.update_t_journal')]
            ]);
    }

    public function delete($id,Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_EXPENSE_JOURNAL_LIST_DELETE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        DB::beginTransaction();
        try {
            $this->model_t_journal
            ->where('mst_company_id',$user->mst_company_id)
            ->where('id', $id)
            ->update([
               'deleted_at'     => Carbon::now(),
               'update_user'   => $user->getFullName(),
               'update_at'     => Carbon::now()
            ]);
            DB::commit();
            return response()->json(['status' => true,'message' => [__('message.success.delete_t_journal')]]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
                return response()->json(['status' => false, 'message' => 'message.false.delete_t_journal']);
        }
    }

}