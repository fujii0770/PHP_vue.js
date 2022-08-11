<?php

namespace App\Http\Controllers\Expense;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Validator;
use App\Http\Utils\PermissionUtils;
use DB;
use App\Models\TimecardDetail;
use App\Models\User;
use App\Models\Company;
use App\Http\Utils\AppUtils;
use App\Http\Utils\DownloadUtils;
use App\CompanyAdmin;
use App\Http\Utils\EpsTAppFilesUtils;

class Exp_t_appController extends AdminController {
    private $model;

    public function __construct(CompanyAdmin $model)
    {
        parent::__construct();
        $this->model = $model;

        $this->assign('use_angular', true);
        $this->assign('show_sidebar', true);
        $this->assign('use_contain', true);
    }

    public function index(Request $request){
        $user   = \Auth::user();
        $action = $request->get('action','');

        $limit      = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
        $orderBy    = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir   = $request->get('orderDir') ? $request->get('orderDir'): 'desc';
        $arrOrder   = ['id' => 'D.id','user' => 'user_name'
                      ];
        $filter_before_app        = $request->get('beforeapp','');
        $filter_eps        = $request->get('eps','');

        if(!$request->has('page')){//メニューから起動された場合、初期値をセット
            $filter_before_app = '1';
            $filter_eps        = '2';
        }
        $request->merge(['beforeapp' => $filter_before_app]);
        $request->merge(['eps' => $filter_eps]);

        $id                         = $request->get('id','');
        $filter_user                = $request->get('username','');
        $filter_form_code           = $request->get('form_code', '');
        $filter_form_name           = $request->get('form_name', '');
        $filter_filing_date_from    = substr($request['filing_date_from'], 0, 10);  //yyyy-mm-ddを切り取る 提出日
        $filter_filing_date_to      = substr($request['filing_date_to'], 0, 10);
        $filter_completed_date_from = substr($request['completed_date_from'], 0, 10);  //yyyy-mm-ddを切り取る 精算日
        $filter_completed_date_to   = substr($request['completed_date_to'], 0, 10);

        $arrApp = DB::table('eps_t_app as D')
                ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'D.id',$orderDir)
                ->join('eps_m_form as F', function ($join) {
                    $join->on('D.mst_company_id', '=', 'F.mst_company_id');
                    $join->on('D.form_code', '=', 'F.form_code');
                })
                ->Join('mst_user as U', 'U.id','D.mst_user_id')
                ->select(DB::raw('D.id,F.form_type, D.form_code, F.form_name, D.target_period_from, D.target_period_to, CONCAT(U.family_name, U.given_name) as user_name,U.email, FORMAT(D.eps_amt,0) as eps_amt , D.create_at,D.suspay_date, D.diff_date ,D.purpose_name,D.filing_date,D.completed_date'))
                ->where('U.mst_company_id', $user->mst_company_id) //自分の会社のユーザのみが対象
                ->whereNull('F.deleted_at')
                ->whereNull('D.deleted_at')
                ->where(DB::raw('CONCAT(U.family_name,U.given_name)'), 'like', "%$filter_user%")
                ->where('D.id','like',"%$id%");
        if($filter_filing_date_from) {
             $arrApp->whereDate('D.filing_date', '>=', $filter_filing_date_from);
        }
        if($filter_filing_date_to) {
             $arrApp->whereDate('D.filing_date', '<=', $filter_filing_date_to);
        }
        if($filter_completed_date_from) {
            $arrApp->whereDate('D.completed_date', '>=', $filter_completed_date_from);
        }
        if($filter_completed_date_to) {
            $arrApp->whereDate('D.completed_date', '<=', $filter_completed_date_to);
        }
        
        $array_form_type = array();
        if($filter_before_app) {//事前申請
            array_push($array_form_type,$filter_before_app) ;
        }
        if($filter_eps) {//精算
            array_push($array_form_type,$filter_eps) ;
        }
        if($array_form_type){
            $arrApp->whereIn('F.form_type',$array_form_type);
        }

        $where = ['1=1'];
        $where_arg = [];

        if($filter_form_code) {
            $where[] = 'INSTR(D.form_code, ?)';
            $where_arg[] = $filter_form_code;
        }
        if($filter_form_name) {
            $where[] = 'INSTR(F.form_name, ?)';
            $where_arg[] = $filter_form_name;
        }
        $arrApp = $arrApp->whereRaw(implode(" AND ", $where), $where_arg);

        if($action == 'export'){
            $arrApp = $arrApp ->get();
        }else{
            $arrApp = $arrApp ->paginate($limit)->appends(request()->input());
        }

        $this->setMetaTitle('経費申請一覧');
        $this->assign('user_title', '経費申請一覧');

        $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
        $this->assign('arrApp', $arrApp);
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
            return $this->render('Expense.csv');
        }else{
            return $this->render('Expense.app_index');
        }
    }

    public function show($id)
    {
        $user  = \Auth::user();
            $item = DB::table('eps_t_app as D')
            ->join('eps_m_form as F', function ($join) {
                $join->on('D.mst_company_id', '=', 'F.mst_company_id');
                $join->on('D.form_code', '=', 'F.form_code');
            })
            ->leftJoin('mst_user as U', 'U.id','D.mst_user_id')
            ->leftJoin('mst_user_info as I', 'D.mst_user_id','I.mst_user_id')
            ->leftJoin('mst_department as E', 'I.mst_department_id','E.id')
            ->leftjoin('circular as C', function ($join) {
                $join->on('C.id', '=', 'D.circular_id');
            })
            ->select(DB::raw('D.id, DATE_FORMAT(D.completed_date,\'%Y%m\') AS completed_date, F.form_type, CASE WHEN D.completed_date is not null THEN \'3\' ELSE C.circular_status END as circular_status, D.form_code, F.form_name, D.purpose_name, E.department_name, D.mst_user_id, DATE_FORMAT(D.target_period_from,\'%Y/%m/%d\') AS target_period_from, DATE_FORMAT(D.target_period_to,\'%Y/%m/%d\') AS target_period_to, CONCAT(U.family_name, U.given_name) as user_name, D.form_dtl, format(D.suspay_amt, 0) as suspay_amt, format(D.eps_amt, 0) as eps_amt, format(D.eps_diff, 0) as eps_diff, D.create_at, DATE_FORMAT(D.suspay_date,\'%Y/%m/%d\') AS suspay_date, DATE_FORMAT(D.diff_date,\'%Y/%m/%d\') AS diff_date'))
            ->where('D.mst_company_id', $user->mst_company_id)
            ->whereNull('F.deleted_at')
            ->where('D.id', $id)
            ->first();

            $item2 = DB::table('eps_t_app_files as D')
            ->where('D.mst_company_id', $user->mst_company_id)
            ->where('D.t_app_id', $id)
            ->whereNull('D.deleted_at')
            ->get();

            return response()->json(['status' => true, 'item' => $item, 'item2' => $item2 ]);
    }

    /**
     * List
     */
    function indexDetail($id, Request $request){
        $user = \Auth::user();

        $page = $request->get('page') ? $request->get('page') : 1;
        $limit = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir'): 'desc';

        $items = DB::table('eps_t_app_items')
            ->orderBy($orderBy,$orderDir)
            ->where('mst_company_id', $user->mst_company_id)
            ->where('t_app_id', $id)
            ->whereNull('deleted_at')
            ->select(DB::raw('id, t_app_id,wtsm_name, DATE_FORMAT(expected_pay_date,\'%Y%m/%d\') AS expected_pay_date, remarks, FORMAT((unit_price * quantity),0) as amount, submit_method '))
            ->get();

        return response()->json(['status' => true, 'items' => $items ]);
    }
    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show2($id, Request $request)
    {
        $user   = \Auth::user();

        $item = DB::table('eps_t_app_items')
            ->select(DB::raw('wtsm_name, DATE_FORMAT(expected_pay_date,\'%m/%d\') AS expected_pay_date, FORMAT((unit_price * quantity),0) as amount,numof_ppl,remarks'))
            ->where('mst_company_id', $user->mst_company_id)
            ->where('id',$id)
            ->first();

        $item2 = DB::table('eps_t_app_files')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('t_app_items_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('id','asc')
            ->get();

            return response()->json(['status' => true, 'item' => $item , 'item2' => $item2 ]);
    }

    /**
     * 添付ファイルのダウンロード
     * @param Request $request
     * @throws \Exception
     */
    public function reserve(Request $request){
        $user = $request->user();
        $cid  = $request->get('cid','');

        $eps_t_app_files = DB::table('eps_t_app_files')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('id',$cid)
            ->first();

        try{
            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\EpsTAppFilesUtils', 'getEpsTAppFileData', $eps_t_app_files->original_file_name,
                $cid
            );

            if(!($result === true)){
                return response()->json([
                    'status' => false,
                    'message' =>    [__('message.false.attachment_request.download_attachment', ['attribute' => $result])]
                ]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__('message.success.attachment_request.download_attachment')]
            ]);
        }catch (\Exception $ex){
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json([
                'status' => false,
                'message' =>    [__('message.false.attachment_request.download_attachment', ['attribute' => $ex->getMessage()])]
            ]);
        }

    }

}
