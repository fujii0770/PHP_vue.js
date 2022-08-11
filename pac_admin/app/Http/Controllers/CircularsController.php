<?php

namespace App\Http\Controllers;

use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\DepartmentUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\LongtermIndexUtils;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use DB;
use App\Models\Department;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Session;
use App\Http\Utils\MailUtils;
use App\Http\Utils\DownloadRequestUtils;
use Illuminate\Support\Arr;

class CircularsController extends AdminController
{

    private $model;
    private $department;
    
    public function __construct(Department $department)
    {
        parent::__construct();
        $this->department = $department;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $user       = $request->user();
        $action     = $request->get('action','');
        $limit      = $request->get('limit', 20);
        $orderDir   = $request->get('orderDir', "DESC");
        $useTemplate    = false;
        $finishedDate = $request->get('finishedMonthHidden', '');// 完了日時
        $status = $request->get('status');// 状態
        // PAC_5-2449 add
        if($status!=2){
            $orderBy = $request->get('orderBy', "C.final_updated_date");
            if($orderBy=='C.completed_date'){
                $orderBy='C.final_updated_date';
            }
        }else{
            $orderBy = $request->get('orderBy', "C.completed_date");
            if($orderBy=='C.final_updated_date'){
                $orderBy='C.completed_date';
            }
        }

        // PAC_5-1944 End
        $where = [ ];
        $where_arg = [];
        $where_temp = [];
        $where_arg_temp = [];
        $having= [];
        $having_arg =[];
        if($request->get('search')){
            $h = [];
            $h[]        = ' title_file_names like ?';
            $h[]        = ' title_file_names like ?';
            $h[]        = ' user_name like ?';

            $having[]        = "(".implode(" OR ", $h).")";
            $having_arg[]    = "%".$request->get('search')."% ||| %";
            $having_arg[]    = "% ||| %".$request->get('search')."%";
            $having_arg[]    = "%".$request->get('search')."%";
        }

        // filter applied_date
        if($request->get('create_fromdate')){
            $where[]        = 'DATE(C.applied_date) >= ?';
            $where_arg[]    = $request->get('create_fromdate');
        }
        if($request->get('create_todate')){
            $where[]        = 'DATE(C.applied_date) <= ?';
            $where_arg[]    = $request->get('create_todate');
        }
        
        // filter update_at
        if($request->get('update_fromdate')){
            $where[]        = 'DATE(C.final_updated_date) >= ?';
            $where_arg[]    = $request->get('update_fromdate');
        }
        if($request->get('update_todate')){
            $where[]        = 'DATE(C.final_updated_date) <= ?';
            $where_arg[]    = $request->get('update_todate');
        }

        // PAC_5-1944 回覧一覧の検索条件変更 Start
        if ($request->get('finished_fromdate')) {
            $where[]        = 'DATE(C.completed_date) >= ?';
            $where_arg[]    = $request->get('finished_fromdate');
        }
        if ($request->get('finished_todate')) {
            $where[]        = 'DATE(C.completed_date) <= ?';
            $where_arg[]    = $request->get('finished_todate');
        }
        // PAC_5-1944 End
        if ($request->get('template_fromdate') || $request->get('template_todate') 
            || $request->get('template_num') || $request->get('template_text') ){
            $useTemplate = true;
        }
        if ($request->get('template_fromdate')) {
            $where_temp[]     = 'date_data >= ?';
            $where_arg_temp[] = $request->get('template_fromdate');
        } 
        if ($request->get('template_todate')) {
            $where_temp[]     = 'date_data < ?';
            $where_arg_temp[] = $request->get('template_todate');
        }
        if ($request->get('template_num')) {
            $where_temp[]     = 'num_data = ?';
            $where_arg_temp[] = $request->get('template_num');
        }
        if ($request->get('template_text')) {
            $where_temp[]     = "text_data like ?";
            $where_arg_temp[] = "%".$request->get('template_text')."%";
        }
 
        if($request->isMethod('post') && $action){
            $cids = $request->get('cids',[]);
            if($action == "delete"){
                if ($status == CircularUtils::CIRCULATING_STATUS) {
                    $this->deleteCirculating($cids, $user, $status);
                } else {
                    $this->deletes($cids, $user, $status, $finishedDate); // PAC_5-1944 回覧一覧の検索条件変更
                }
            }
        }
    
        $company = DB::table('mst_company')
            ->where('id', $user->mst_company_id)
            ->first();
        // PAC_5-1944 回覧一覧の検索条件変更 Start
        if (Schema::hasTable("circular$finishedDate")) {
//            DB::enableQueryLog();
            $query_sub = DB::table("circular$finishedDate as C1")
                ->join("circular_user$finishedDate as U", function($join){
                    $join->on('C1.id', 'U.circular_id');
                    $join->on('U.parent_send_order', DB::raw('0'));
                    $join->on('U.child_send_order', DB::raw('0'));
                })
                ->join("circular_document$finishedDate as D", function($join) use ($user){
                    $join->on('C1.id', '=', 'D.circular_id');
                    $join->on(function($condition) use ($user){
                        $condition->on('confidential_flg', DB::raw('0'));
                        $condition->orOn(function($condition1) use ($user){
                            $condition1->on('confidential_flg', DB::raw('1'));
                            $condition1->on('create_company_id', DB::raw($user->mst_company_id));
                        });
                    });
                    $join->on(function($condition) use ($user){
                        $condition->on('origin_document_id', DB::raw('0'));
                        $condition->orOn('D.parent_send_order', 'U.parent_send_order');
                    });
                })
                ->select(DB::raw('CONCAT(U.title," ||| ",GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \')) as title_file_names'))
                ->groupBy(['C1.id', 'U.title']);
            if($action == "export" && $company->circular_list_csv && $company->circular_list_csv == 1) {
                $data_query1 = DB::table("circular_user$finishedDate as U")
                    ->select(DB::raw('GROUP_CONCAT(U.name, \'&lt;\',U.email,\'&gt;\' ORDER BY U.name,U.email ASC) as names'))
                    ->leftJoin("circular$finishedDate as C1", 'C1.id', 'U.circular_id')
                    ->where('U.del_flg', CircularUserUtils::NOT_DELETE)
                    ->groupBy('C1.id');
            } else {
                $data_query1 = DB::table("circular_user$finishedDate as U")
                    ->select(DB::raw('GROUP_CONCAT(U.name, \'&lt;\',U.email,\'&gt;\' ORDER BY U.name,U.email ASC) as names'))
                    ->leftJoin("circular$finishedDate as C1", 'C1.id', 'U.circular_id')
                    ->whereIn('U.circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS])
                    ->groupBy('C1.id');
            }

            $data_query = DB::table("circular$finishedDate as C")
                ->leftjoin('mst_user as A', 'C.mst_user_id', 'A.id')
                ->leftjoin('circular_auto_storage_history as auto_his', function ($query) use ($user) {
                    $query->on('auto_his.circular_id', 'C.id')
                        ->on('auto_his.mst_company_id', DB::raw($user->mst_company_id));
                })
                // PAC_5-2213    add C.completed_date
                ->select(DB::raw('C.id, C.applied_date, C.final_updated_date,C.completed_date, C.circular_status, A.email user_email,A.family_name, A.given_name,
                CONCAT(A.family_name, A.given_name) user_name, auto_his.result'))
                ->selectSub($data_query1->whereRaw('C1.id=C.id'),'user_names')
                ->selectSub($query_sub->whereRaw('C1.id=C.id'),'title_file_names')
                ->where('A.mst_company_id', $user->mst_company_id)
                ->whereExists(function ($query) use($finishedDate) {
                    $query->select(DB::raw(1))
                        ->from("circular_user$finishedDate as U1")
                        ->whereRaw('U1.del_flg = ?',[CircularUserUtils::NOT_DELETE])
                        ->whereRaw('C.id = U1.circular_id');
                })
                ->where('C.edition_flg', DB::raw(config('app.pac_contract_app')))
                ->where('C.env_flg', DB::raw(config('app.pac_app_env')))
                ->where('C.server_flg', DB::raw(config('app.pac_contract_server')));
            if($where){
                $data_query->whereRaw(implode(" AND ", $where), $where_arg);
            }
            if ($having){
                $data_query->havingRaw(implode(" AND ", $having), $having_arg);
            }

            if($request->get('department')){
                $department = $request->get('department');
                $multiple_department_position_flg = DB::table('mst_company')->where('id', $user->mst_company_id)->select('multiple_department_position_flg')->first()->multiple_department_position_flg ?? 0;

                $departmentList = \Illuminate\Support\Facades\DB::table('mst_department')
                    ->select('id', 'parent_id')
                    ->where('mst_company_id', $user->mst_company_id)
                    ->where('state', 1)
                    ->get()
                    ->toArray();
                $departmentIds = [];
                DepartmentUtils::getDepartmentChildIds($departmentList, $department, $departmentIds);
    
                if ($multiple_department_position_flg === 1) {
                    $data_query->leftjoin('mst_user_info as UI', 'A.id', 'UI.mst_user_id')
                        // PAC_5-1599 追加部署と役職 Start
                        ->where(function($query) use($departmentIds) {
                            $query->orWhereIn('UI.mst_department_id', $departmentIds)
                                ->orWhereIn('UI.mst_department_id_1', $departmentIds)
                                ->orWhereIn('UI.mst_department_id_2', $departmentIds);
                        });
                    // PAC_5-1599 End
                } else {
                    $data_query->leftjoin('mst_user_info as UI', 'A.id', 'UI.mst_user_id')
                        ->whereIn('UI.mst_department_id', $departmentIds);
                }
            }

            if ($status) {
                if ($status == CircularUtils::CIRCULAR_COMPLETED_STATUS) {
                    $data_query->whereIn('C.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS]);
                } else {
                    $data_query->where('C.circular_status', $status);
                }
            } else {
                $data_query->where('C.circular_status', CircularUtils::CIRCULATING_STATUS);
            }

            // PAC_5-1944 回覧一覧の検索条件変更 Start
        // 当月のデータをふるいにかける
/*        if ($finishedDateKey === '0') {
            $data_query->whereRaw("DATE_FORMAT( C.completed_date, '%Y%m' ) = ".date('Ym'));
        }*/
            // PAC_5-1944 End
            if($useTemplate) {
                $idByTemplates = DB::table('template_input_data')
                    ->select('circular_id')
                    ->whereRaw(implode(" AND ", $where_temp), $where_arg_temp)
                    ->distinct()
                    ->get();

                $ids = array();
                foreach ($idByTemplates as $value) {
                    $ids[] = $value->circular_id;
                }

                Log::debug($idByTemplates);
                Log::debug($ids);

                $data_query->whereIn('C.id', $ids);
            }

            if($orderBy == 'D.file_names'){
                $data_query = $data_query->orderBy(DB::raw("SUBSTRING_INDEX(title_file_names,' ||| ',-1)") , $orderDir);
            }else if($orderBy == 'D.title'){
                $data_query = $data_query->orderBy(DB::raw("SUBSTRING_INDEX(title_file_names,' ||| ',1)") , $orderDir);
            }else{
                $data_query = $data_query->orderBy($orderBy, $orderDir);
            }
        } else {
            $data_query = DB::table("circular")->where('id', 0)->select('id');
        }
        // PAC_5-1944 End
        $itemsCircular = collect([]);

        if($action != ""){
            if($action == "export" && $company->circular_list_csv && $company->circular_list_csv == 1) {
                $itemsCircular = $data_query->get()
                    ->each(function($item){
                        $title_file_names = explode(' ||| ',$item->title_file_names);
                        $item->title = $title_file_names[0] ?? '';
                        $item->file_names = $title_file_names[1] ?? '';
                    })
                    ->toArray();
                $this->assign('status', $status);
                $this->assign('itemsCircular', $itemsCircular);
                return $this->render('Circulars.csv');
            }
            $data_arr = $data_query->get()->each(function($item){
                $title_file_names = explode(' ||| ',$item->title_file_names);
                $item->title = $title_file_names[0] ?? '';
                $item->file_names = $title_file_names[1] ?? '';
            })->toArray();
            $page = $request->get('page','1');
            $itemsCircular =new LengthAwarePaginator(array_slice( $data_arr, ($page - 1) * $limit, $limit, false), count($data_arr), $limit);
            $itemsCircular->setPath($request->url());
            $itemsCircular->appends(request()->except('_token')); // sort params etc
        }
        $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);
        $longTermIndexName = DB::table('mst_longterm_index')
            // 通常長期保管インデックス
            ->orwhere(function($query) use ($user){
                $query->where('mst_company_id', $user->mst_company_id)
                    ->Where('template_flg', 0);
            })
            // デフォルト
            ->orwhere('mst_company_id',0);
        if($company->template_flg){
            $longTermIndexName = $longTermIndexName->orwhere(function($query) use ($user){
                $query->where('mst_company_id', $user->mst_company_id)
                    ->Where('template_flg', 1);
            });
        }
        if($company->frm_srv_flg){
            $longTermIndexName = $longTermIndexName->orwhere(function($query) use ($user){
                $query->where('mst_company_id', $user->mst_company_id)
                    ->Where('template_flg', 2);
            });
        }
        $longTermIndexName = $longTermIndexName->orderBy('sort_id', 'asc')
            ->get();
        $companyLimit = DB::table('mst_limit')
            ->where('mst_company_id', $user->mst_company_id)
            ->first();
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);
        $this->assign('orderCompleted',"C.completed_date");
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', strtolower($orderDir)=="asc"?"desc":"asc");
        $this->assign('itemsCircular', $itemsCircular);
        $this->assign('listDepartmentTree', $listDepartmentTree);
        $this->assign('company', $company);
        $this->assign('action', $request->action);
        $this->assign('status', $status);
        $this->assign('finishedDate', $finishedDate);// PAC_5-1944 回覧一覧の検索条件変更
        $this->setMetaTitle('回覧一覧');
        $this->assign('longTermIndexName', $longTermIndexName);
        $this->assign('company_limit',$companyLimit);
        return $this->render('Circulars.index');
    }
    // PAC_5-2429 S
    public function storeMultipleCircular(Request $request)
    {
        $Cids = $request->get('cids',[]);
        $indexes=$request->get('indexes',[]);
        $folder_id = $request->get('folder_id',0);//長期保管フォルダのID
        // 完了一覧
        if (isset($request['finishedMonthHidden'])) {
            // 回覧完了日時
            $finishedDateKey = $request->get('finishedMonthHidden') ? $request->get('finishedMonthHidden') : '';
        } else {    // 完了一覧以外
            $finishedDateKey = '';
        }
        try {
            if(count($Cids)){
                foreach ($Cids as $cid){
                    $circular_document=DB::table("circular_document$finishedDateKey")->where('circular_id',$cid)->first();
                    $user=DB::table('mst_user')->where('email',$circular_document->create_user)->first();
                    $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
                    if (!$company || !$company->long_term_storage_flg){
                        return response()->json([
                            'status' => false,
                            'message' => ['Cannot store Circular']
                        ]);
                    }
                    // check max_usable_capacity
                    $storage_size = DB::table('long_term_document')
                        ->where('mst_company_id', $user->mst_company_id)
                        ->select(DB::raw('sum(file_size) as storage_size'))
                        ->value('storage_size');
                    if($storage_size >= $company->max_usable_capacity * 1024 * 1024 * 1024){
                        return response()->json([
                            'status' => false,
                            'message' => ['"データ容量($company->max_usable_capacity GB)を超えています。"']
                        ]);
                    }
                    $circular_docs = DB::table("circular_document$finishedDateKey")
                        ->where('circular_id', $cid)
                        ->where(function ($query) use ($user) {
                            $query->where('confidential_flg', 0);
                            $query->orWhere(function ($query1) use ($user) {
                                $query1->where('confidential_flg', 1);
                                $query1->where('create_company_id', $user->mst_company_id);
                                $query1->where('origin_edition_flg', config('app.pac_contract_app'));
                                $query1->where('origin_env_flg', config('app.pac_app_env'));
                                $query1->where('origin_server_flg', config('app.pac_contract_server'));
                            });
                        })
                        ->select('file_name')
                        ->get();
                    $fileNames = [];

                    foreach ($circular_docs as $circular_doc){
                        $fileNames[] = $circular_doc->file_name;
                    }
                    Session::flash('fileNames', $fileNames);
                    $longTermDocument = DB::table('long_term_document')
                        ->whereIn('circular_id', $Cids)
                        ->where('mst_company_id', $user->mst_company_id)
                        ->pluck('circular_id')
                        ->toArray();
                    if (count($Cids) == 1){
                        $keyword = $request->get('keyword','');
                        if (count($longTermDocument) == 1){
                            DB::table('long_term_document')
                                ->where('circular_id', $longTermDocument[0])
                                ->where('mst_company_id', $user->mst_company_id)
                                ->update(['keyword' => $keyword,"is_del" => 0]);
                        }
                        if($company->long_term_storage_option_flg){
                            if(!$this->setIndex($indexes,$Cids[0],$user)){
                                return response()->json([
                                    'status' => false,
                                    'message' => ['長期保管インデックスの保存に失敗しました。']
                                ]);
                            }
                        }
                    }else{
                        $keyword = '';
                    }
                    $cids = array_diff($Cids, $longTermDocument);
                    if(isset($request['keyword_flg'])){
                        $keyword_flg = $request->get('keyword_flg');
                    }else{
                        $keyword_flg = NULL;
                    }
                    $returnMsg = "0";
                    $keyword='|'.$keyword.'|';
                    foreach ($cids as $cid){
                        \Artisan::call("circular:storeS3", ['circular_id' => $cid, 'company_id' => $user->mst_company_id, '--keyword' => $keyword, 'finishedDate' => $finishedDateKey, '--keyword_flg' => $keyword_flg, '--folder_id' => $folder_id]);
                        if($returnMsg == "0"){
                            $returnMsg =  str_replace(array("\r", "\n"), '', \Artisan::output());

                        }
                    }
                    if($returnMsg == "1"){
                        return response()->json([
                            'status' => false,
                            'message' => ['回覧の長期保管をできませんでした。']
                        ]);
                    } else if($returnMsg == "0"){
                        return response()->json([
                            'status' => true,
                            'message' => ['回覧の長期保管をできました。']
                        ]);
                    }else{
                        return response()->json([
                            'status' => false,
                            'message' => ['回覧の長期保管をできました。']
                        ]);
                    }
                }
            }else{
                return response()->json([
                    'status' => true,
                    'message' => ['回覧の長期保管をできました3。']
                ]);
            }
        }catch (\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json([
                'status' => false,
                'message' => ['回覧の長期保管をできました。']
            ]);
        }
    }
    protected function setIndex($indexes,$cid,$user)
    {
        try {
            $indexes = Arr::where($indexes, function ($value, $key) {
                return $value['longterm_index_id']!='' && $value['value']!='';
            });
            DB::beginTransaction();
            DB::table('longterm_index')->where('circular_id',$cid)->where('mst_company_id', $user->mst_company_id)
                ->where('mst_user_id',$user->id)->delete();
            $data=[];
            foreach ($indexes  as $k=>$value) {
                 if($value['data_type'] === LongtermIndexUtils::STRING_TYPE && strlen($value['value'])>128){
                     return false;
                 }
                $tmp= [
                    'mst_company_id' => $user->mst_company_id,
                    'mst_user_id' => $user->id,
                    'circular_id' => $cid,
                    'longterm_index_id' => $value['longterm_index_id'],
                    'create_at' => Carbon::now(),
                    'create_user' => $user->family_name . $user->given_name,
                    'num_value'=>$value['data_type'] === LongtermIndexUtils::NUMERIC_TYPE ?preg_replace('/[^.0123456789]/s', '', $value['value']):0,
                    'string_value'=>$value['data_type'] === LongtermIndexUtils::STRING_TYPE ?$value['value']:null,
                    'date_value'=>$value['data_type'] === LongtermIndexUtils::DATE_TYPE ?Carbon::parse( $value['value']??''):null
                ];
                $data[]=$tmp;
            }
            DB::table('longterm_index')->insert($data);
            DB::commit();
            return true;
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return false;
        }

    }
    public function getLongTermIndexValue(Request $request)
    {
        try {

            $user = $request->user();
            $cid = $request['cid'];
            //2664 -s
            $mst_company_id = $user->mst_company_id;
            $template_flg = DB::table('template_input_data')->where('circular_id', $cid)->exists();
            $longTermIndex = DB::table('longterm_index as li')
                ->leftJoin('mst_longterm_index as mli', 'li.longterm_index_id', '=', 'mli.id')
                ->where('li.circular_id',$cid)
                ->orwhere('mli.mst_company_id',0);
            if($template_flg){
                $longTermIndex = $longTermIndex->where(function($query) use ($mst_company_id){
                    $query->where('mli.mst_company_id', $mst_company_id)
                        ->Where('mli.template_flg', 1);
                });
            }else{
                $longTermIndex = $longTermIndex->where(function($query) use ($mst_company_id){
                    $query->where('mli.mst_company_id',$mst_company_id)
                        ->Where('mli.template_flg', 0);
                });
            }
            $data = $longTermIndex
                ->select('li.date_value','li.longterm_index_id','li.circular_id','li.string_value','li.num_value','mli.index_name','mli.data_type','mli.sort_id',DB::raw("date_format(li.date_value,'%Y-%m-%d') as date_value"))
                ->orderBy('mli.sort_id', 'asc')
                ->get();
            $frm_invoice_flg = DB::table('frm_invoice_data')->where('circular_id', $cid)->exists();
            $frm_others_flg = DB::table('frm_others_data')->where('circular_id', $cid)->exists();
            $index = DB::table('mst_longterm_index')
                ->where('circular_id',$cid)
                // 通常長期保管インデックス
                ->orwhere(function($query) use ($mst_company_id){
                    $query->where('mst_company_id',$mst_company_id)
                        ->Where('template_flg', 0);
                })
                // デフォルト
                ->orwhere('mst_company_id',0);
            // テンプレート回覧すれば
            if($template_flg){
                $index = $index->orwhere(function($query) use ($mst_company_id){
                    $query->where('mst_company_id', $mst_company_id)
                        ->Where('template_flg', 1);
                });
            }
            // 帳票回覧すれば
            if($frm_invoice_flg || $frm_others_flg){
                $index = $index->orwhere(function($query) use ($mst_company_id){
                    $query->where('mst_company_id', $mst_company_id)
                        ->Where('template_flg', 2);
                });
            }
            $index = $index->orderBy('sort_id', 'asc')
                ->select('id','data_type','circular_id','index_name')
                ->get();
            foreach ($index as $value) {
                $value->index_value = '';
            }
            // 2664 -end
            $long_term_document = DB::table('long_term_document')->where('circular_id', $cid)
                ->where('mst_company_id', $user->mst_company_id)
                ->first();
            return response()->json([
                'status' => true,
                'data'=>$data,
                'index'=>$index,
                'keyword' => optional($long_term_document)->keyword,
                'long_term_document_id' => optional($long_term_document)->id
            ]);


        }catch (\Exception $e){
            return response()->json([
                'status' => false,
                'message'=>['長期保管した文書インデックスが取得に失敗しました。']
            ]);
        }
    }
    // PAC_5-2429 E
    public function exports(Request $request){
        $user       = $request->user();
        $cids = $request->get('cids',[]);
        $fName = $request->get('fileName');

        $query_sub  = DB::table('circular as C')
            ->join('circular_user as U', function($join){
                $join->on('C.id', 'U.circular_id');
                $join->on('U.parent_send_order', DB::raw('0'));
                $join->on('U.child_send_order', DB::raw('0'));
            })
            ->join('circular_document as D', function($join) use ($user){
                $join->on('C.id', '=', 'D.circular_id');
                $join->on(function($condition) use ($user){
                    $condition->on('confidential_flg', DB::raw('0'));
                    $condition->orOn(function($condition1) use ($user){
                        $condition1->on('confidential_flg', DB::raw('1'));
                        $condition1->on('create_company_id', DB::raw($user->mst_company_id));
                    });
                });
                $join->on(function($condition) use ($user){
                    $condition->on('origin_document_id', DB::raw('0'));
                    $condition->orOn('D.parent_send_order', 'U.parent_send_order');
                });
            })
            ->whereIn('C.id', $cids)
            ->select(DB::raw('C.id, U.title, GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \') as file_names'))
            ->groupBy(['C.id', 'U.title']);

        $circulars = DB::table('circular as C')
            ->leftJoinSub($query_sub, 'D', function ($join) {
                $join->on('C.id', '=', 'D.id');
            })
            ->leftjoin('mst_user as A', 'C.mst_user_id', 'A.id')
            ->select(DB::raw('C.id, C.create_at, C.final_updated_date, C.circular_status,C.applied_date, D.file_names, A.email user_email,A.family_name,
                     A.given_name, CONCAT(A.family_name, A.given_name) user_name, D.title'))
            ->where('A.mst_company_id', $user->mst_company_id)
            ->whereIn('C.id', $cids)
            ->get()->keyBy('id');

        $cids = $circulars->keys();

        // PAC_5-1092 BEGIN ダウンロードのステータス変更処理
        DB::table('circular')->where('circular_status', 2)
            ->whereIn('id', $cids)
            ->update([
                'circular_status' => 3,
                'final_updated_date' => Carbon::now(),
            ]);
        // PAC_5-1092 END

        foreach($circulars as $circular){
            $filenames[]        = $circular->file_names;
            $subjects[]         = $circular->title;
            $creator_emails[]   = $circular->user_email;
            $creator_names[]    = $circular->family_name.$circular->given_name;
            $circular_statuss[] = isset(AppUtils::CIRCULAR_STATUS[$circular->circular_status])?AppUtils::CIRCULAR_STATUS[$circular->circular_status]:"";
        }

        Session::flash('file_names', $filenames);
        Session::flash('subject', $subjects);
        Session::flash('creator_email', $creator_emails);
        Session::flash('creator_name', $creator_names);
        Session::flash('circular_status', $circular_statuss);
        
        $circular_docs  =   DB::table('circular_document')
                    ->whereIn('circular_id', $cids)
                    ->select('id','circular_id','file_name')
                    ->get()->keyBy('id');
        $document_datas = DB::table('document_data')
                ->whereIn('circular_document_id', $circular_docs->keys())
                ->select('circular_document_id','file_data')
                ->get(); 
                
        if(count($document_datas) == 1){
            $fileName = $request->get('fileName').$circular_docs[$document_datas[0]->circular_document_id]->file_name;
            return response()->json(['status' => true, 'fileName' => $fileName, 
                'file_data' => AppUtils::decrypt( $document_datas[0]->file_data), 
                'message' => ['文書ダウンロード処理に成功しました。'.'->'.$fName]]);
        }elseif(count($document_datas) > 1){
             
            foreach($document_datas as $document_data){
                $document_id = $document_data->circular_document_id;
                if(!isset($circular_docs[$document_id])) continue;
                $circular_document = $circular_docs[$document_id];

                $circular_id = $circular_document->circular_id;
                if(!isset($circulars[$circular_id]->docs)) $circulars[$circular_id]->docs = [];

                $circulars[$circular_id]->docs[] = ['fileName' => $circular_document->file_name,
                            'data' => AppUtils::decrypt($document_data->file_data)];
            }

            $fileName = "download-circular-" . time() . ".zip";
            $path = sys_get_temp_dir()."/download-circular-" . AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) . ".zip";

            $zip = new \ZipArchive();
            $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            $fileList = array();
            foreach($circulars as $circular){
                if(isset($circular->docs)){
                    //PAC_5-998BEGIN
                    //文書が格納されるフォルダ名は「件名_申請日」（件名がなければファイル名）
                    //申請日は数字のみで西暦から秒まで。コンマ秒はなし。
                    //日付形式の変更
                    $time = date("YmdHis", strtotime($circular->applied_date));
                    //名前はありますか
                    if (!trim($circular->title)) {
                        //ファイル名の生成
                        $fileNameList = explode(',', $circular->file_names);
                        $subjectName = $fileNameList[0] . '_' . $time;
                    } else {
                        //ファイル名の生成
                        $subjectName = $circular->title . '_' . $time;
                    }
                    //ファイル名が繰り返されていますか , 繰り返されるときにサフィックスを追加する
                    if (array_key_exists($subjectName, $fileList)) {
                        $suffix = $fileList[$subjectName] + 1;
                        $fileList[$subjectName] = $suffix;
                        $subjectName = $subjectName . '_' . $suffix;
                    } else {
                        $fileList[$subjectName] = 1;
                    }
                    $zip->addEmptyDir($subjectName);
                    //PAC_5-998END
                    $countFilename = [];
                    foreach($circular->docs as $doc){

                        $filename = mb_substr($doc['fileName'], mb_strrpos($doc['fileName'],'/'));
                        $filename = mb_substr($filename, 0, mb_strrpos($doc['fileName'],'.'));

                        if(key_exists($filename, $countFilename)) {
                            $countFilename[$filename]++;
                            $filename = $filename.' ('.$countFilename[$filename].') ';
                        } else {
                            $countFilename[$filename] = 0;
                        }
                        $zip->addFromString ($subjectName.'/'.$filename.'.pdf', base64_decode($doc['data']));
                    }
                }
            }
            $zip->close();

            return response()->json(['status' => true, 'fileName' => $fileName, 
                'file_data' => \base64_encode(\file_get_contents($path)), 
                'message' => ['文書ダウンロード処理に成功しました。']]);
        }else{
            return response()->json(['status' => false,
                'message' => ['送信文書のダウンロード処理に失敗しました。']]); 
            return $this->sendError("送信文書のダウンロード処理に失敗しました。");
        }
         
    }

    /**
     * 回覧一覧削除
     * @param $cids
     * @param $user
     * @param $status
     * @param $finishedDateKey
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletes($cids, $user, $status, $finishedDateKey)
    {
        try {
            if (!$user->can(PermissionUtils::PERMISSION_CIRCULATION_LIST_DELETE)
                and !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)
            ) {
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }
            // 状態が完了
            if ($status == CircularUtils::CIRCULAR_COMPLETED_STATUS) {
                // 当月
                if (!$finishedDateKey) {
                    $finishedDates[] = '';
                    $finishedDates[] = date('Ym');
                } else {
                    // PAC_5-1944 回覧一覧の検索条件変更 Start
                    $finishedDates[] = $finishedDateKey;
                }
            } else {
                $finishedDates[] = '';
            }

            // PAC_5-1944 回覧一覧の検索条件変更 Start
            $deleted_mail_notice = false; // 当月完了回覧削除の場合、マスターテーブルとバックアップテーブル両方削除要、メール送信一回だけ。
            foreach ($finishedDates as $finishedDate) {
                $query_sub = DB::table("circular$finishedDate as C")
                    ->join("circular_user$finishedDate as U", function ($join) {
                        $join->on('C.id', 'U.circular_id');
                        $join->on('U.parent_send_order', DB::raw('0'));
                        $join->on('U.child_send_order', DB::raw('0'));
                    })
                    ->join("circular_document$finishedDate as D", function ($join) use ($user) {
                        $join->on('C.id', '=', 'D.circular_id');
                        $join->on(function ($condition) use ($user) {
                            $condition->on('confidential_flg', DB::raw('0'));
                            $condition->orOn(function ($condition1) use ($user) {
                                $condition1->on('confidential_flg', DB::raw('1'));
                                $condition1->on('create_company_id', DB::raw($user->mst_company_id));
                            });
                        });
                        $join->on(function ($condition) use ($user) {
                            $condition->on('origin_document_id', DB::raw('0'));
                            $condition->orOn('D.parent_send_order', 'U.parent_send_order');
                        });
                    })
                    ->whereIn('C.id', $cids)
                    ->select(DB::raw('C.id, U.title, GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \') as file_names'))
                    ->groupBy(['C.id', 'U.title']);

                $circulars = DB::table("circular$finishedDate as C")
                    ->leftJoinSub($query_sub, 'D', function ($join) {
                        $join->on('C.id', '=', 'D.id');
                    })
                    ->leftjoin('mst_user as A', 'C.mst_user_id', 'A.id')
                    ->select(DB::raw('C.id, C.create_at, C.final_updated_date, C.circular_status, D.file_names, A.email user_email,A.family_name, A.given_name,
                    CONCAT(A.family_name, A.given_name) user_name, D.title'))
                    ->where('A.mst_company_id', $user->mst_company_id)
                    ->whereIn('C.id', $cids)
                    ->get();

                $cids = $circulars->pluck('id')->toArray();
                // PAC_5-1944 End

                if (count($cids)) {
                    $filenames = [];
                    $subjects = [];
                    $creator_emails = [];
                    $creator_names = [];
                    $circular_statuss = [];
                    foreach ($circulars as $circular) {
                        $filenames[] = $circular->file_names;
                        $subjects[] = $circular->title;
                        $creator_emails[] = $circular->user_email;
                        $creator_names[] = $circular->family_name . $circular->given_name;
                        $circular_statuss[] = isset(AppUtils::CIRCULAR_STATUS[$circular->circular_status]) ? AppUtils::CIRCULAR_STATUS[$circular->circular_status] : "";
                    }

                    Session::flash('file_names', $filenames);
                    Session::flash('subject', $subjects);
                    Session::flash('creator_email', $creator_emails);
                    Session::flash('creator_name', $creator_names);
                    Session::flash('circular_status', $circular_statuss);
                    Session::flash('log_info', \App\Http\Utils\OperationsHistoryUtils::LOG_INFO['Circulars']['deletes']);

                    DB::beginTransaction();
                    DB::table("circular$finishedDate")->whereIn('id', $cids)->update(['circular_status' => CircularUserUtils::CIRCULAR_DELETED, 'final_updated_date' => Carbon::now(),]);

                    //PAC_5-1398 回覧中のすべての添付ファイルを削除します。
                    CircularAttachmentUtils::deleteAttachments($cids);

                    // 回覧ユーザ削除処理
                    DB::update("UPDATE circular$finishedDate AS C, circular_user$finishedDate AS CU, mst_user AS U"
                        . ' SET CU.del_flg = ' . CircularUserUtils::DELETED
                        . " ,CU.update_at = '" . Carbon::now() . "'"
                        . " ,CU.update_user = '" . $user->email . "'"
                        . ' WHERE C.id = CU.circular_id '
                        . ' AND C.mst_user_id = U.id AND U.mst_company_id = ' . $user->mst_company_id
                        . ' AND C.id IN (' . DB::raw(\implode(',', $cids)) . ') '
                    );

                    // 閲覧ユーザ削除処理
                    DB::update("UPDATE circular$finishedDate AS C, viewing_user AS V, mst_user AS U"
                        . ' SET V.del_flg = ' . CircularUserUtils::DELETED
                        . " ,V.update_at = '" . Carbon::now() . "'"
                        . " ,V.update_user = '" . $user->email . "'"
                        . ' WHERE C.id = V.circular_id '
                        . ' AND C.mst_user_id = U.id AND U.mst_company_id = ' . $user->mst_company_id
                        . ' AND C.id IN (' . DB::raw(\implode(',', $cids)) . ') '
                    );

                    // そのた環境にデータがあるか確認します
                    foreach ($cids as $circularId) {
                        $otherEnvResults = DB::table("circular_user$finishedDate")
                            ->select(['circular_id', 'env_flg', 'edition_flg', 'server_flg', 'email'])
                            ->where('circular_id', $circularId)
                            ->where(function ($query) {
                                $query->where('env_flg', '<>', config('app.pac_app_env'));
                                $query->orWhere('edition_flg', '<>', config('app.pac_contract_app'));
                                $query->orWhere('server_flg', '<>', config('app.pac_contract_server'));
                            })
                            ->where('circular_status', '<>', 0)
                            ->orderBy('id')
                            ->get();
                        // データがある場合
                        if (count($otherEnvResults) > 0) {
                            // 環境リスト
                            $envList = [];
                            foreach ($otherEnvResults as $otherEnvResult) {
                                // 処理したかどうかを判断する
                                if (!in_array($otherEnvResult->env_flg . $otherEnvResult->edition_flg . $otherEnvResult->server_flg, $envList)) {
                                    $envList[] = $otherEnvResult->env_flg . $otherEnvResult->edition_flg . $otherEnvResult->server_flg;
                                } else {
                                    continue;
                                }
                                // K5とAWSの環境判断
                                if (($otherEnvResult->env_flg != config('app.pac_app_env') || $otherEnvResult->server_flg != config('app.pac_contract_server'))
                                    && $otherEnvResult->edition_flg == config('app.pac_contract_app')) {
                                    // 環境設定
                                    $envClient = EnvApiUtils::getAuthorizeClient($otherEnvResult->env_flg, $otherEnvResult->server_flg);
                                    $transferredCircular = ["circular_id" => $circularId,
                                        "circular_env_flg" => config('app.pac_app_env'),
                                        "circular_edition_flg" => config('app.pac_contract_app'),
                                        "circular_server_flg" => config('app.pac_contract_server'),
                                        "user_email" => $user->email,
                                        "finishedDate" => $finishedDate];

                                    Log::debug("回覧削除時に、別の新環境の回覧状態変更：circular_id:" . $circularId
                                        . " edition_flag:" . config('app.pac_contract_app') . " env_flg:" . config('app.pac_app_env') . " server_flg:" . config('app.pac_contract_server'));

                                    // APIを呼び出し
                                    $response = $envClient->post("deleteOtherCircular",
                                        [RequestOptions::JSON => $transferredCircular]);
                                    // エラー発生の場合
                                    if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                                        DB::rollBack();
                                        Log::error('そのた環境にデータの削除に失敗しました');
                                        Log::error($response->getBody());
                                        throw new \Exception('そのた環境にデータの削除に失敗しました');
                                    }
                                }

                                // 現新環境判断
                                if ($otherEnvResult->edition_flg != config('app.pac_contract_app')) {
                                    $circular = DB::table("circular$finishedDate")->where('id', $circularId)->first();
                                    // tokenを取得
                                    $result = EnvApiUtils::getEditionAuthorizeClient($otherEnvResult->env_flg, $circular, $otherEnvResult);
                                    if (!$result['status']) {
                                        DB::rollBack();
                                        throw new \Exception('Cannot connect to Edition Api: ' . $result['message']);
                                    } else {
                                        $editionClient = $result['client'];
                                        $requestsTransferredCircular = ["document_id" => $circularId,
                                            "edition_flg" => config('app.pac_app_env'),
                                            "env_flg" => config('app.pac_contract_app'),
                                            "server_flg" => config('app.pac_contract_server'),
                                            "user_email" => $user->email,
                                            "user_id" => $user->id,];

                                        Log::debug("回覧削除時に、別の現行環境の回覧状態変更：circular_id:" . $circularId
                                            . " edition_flag:" . config('app.pac_contract_app') . " env_flg:" . config('app.pac_app_env') . " server_flg:" . config('app.pac_contract_server'));
                                        // APIを呼び出し
                                        $response = $editionClient->post("deleteDocuments", [
                                            RequestOptions::JSON => $requestsTransferredCircular
                                        ]);
                                        // エラー発生の場合
                                        if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                                            DB::rollBack();
                                            Log::error('そのた環境にデータの削除に失敗しました');
                                            Log::error($response->getBody());
                                            throw new \Exception('そのた環境にデータの削除に失敗しました');
                                        }
                                    }
                                }
                            }
                        }
                    }
                    // check if the company of email is using SAML Login
                    if(!$deleted_mail_notice){
                        $company = DB::table('mst_company')->where('id', $user->mst_company_id)->select('mst_company.login_type', 'mst_company.url_domain_id')->first();
                        foreach ($circulars as $circular) {
                            $data = [
                                'deleteTime' => date("Y/m/d H:i"),
                                'title' => $circular->title,
                                'fileName' => $circular->file_names,
                                'url_domain_id' => $company && ($company->login_type == AppUtils::LOGIN_TYPE_SSO) ? $company->url_domain_id : '',
                            ];

                            //利用者:回覧削除通知
                            MailUtils::InsertMailSendResume(
                            // 送信先メールアドレス
                                $circular->user_email,
                                // メールテンプレート
                                MailUtils::MAIL_DICTIONARY['CIRCULAR_DELETE_ALERT']['CODE'],
                                // パラメータ
                                json_encode($data, JSON_UNESCAPED_UNICODE),
                                // タイプ
                                AppUtils::MAIL_TYPE_USER,
                                // 件名
                                config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.SendMailDeleteCircular.subject'),
                                // メールボディ
                                trans('mail.SendMailDeleteCircular.body', $data)
                            );
                            $deleted_mail_notice = true;
                        }
                    }
                    DB::commit();
                }
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::warning($ex->getMessage() . $ex->getTraceAsString());
            $this->raiseWarning(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
        $this->raiseSuccess(__('message.success.delete_circular'));
    }

    /**
     * 回覧一覧 回覧中削除
     * @param $cids
     * @param $user
     * @param $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteCirculating($cids, $user, $status)
    {
        try {
            if (!$user->can(PermissionUtils::PERMISSION_CIRCULATION_LIST_DELETE)
                and !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)
            ) {
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }

            $query_sub = DB::table("circular as C")
                ->join("circular_user as U", function ($join) {
                    $join->on('C.id', 'U.circular_id');
                    $join->on('U.parent_send_order', DB::raw('0'));
                    $join->on('U.child_send_order', DB::raw('0'));
                })
                ->join("circular_document as D", function ($join) use ($user) {
                    $join->on('C.id', '=', 'D.circular_id');
                    $join->on(function ($condition) use ($user) {
                        $condition->on('confidential_flg', DB::raw('0'));
                        $condition->orOn(function ($condition1) use ($user) {
                            $condition1->on('confidential_flg', DB::raw('1'));
                            $condition1->on('create_company_id', DB::raw($user->mst_company_id));
                        });
                    });
                    $join->on(function ($condition) use ($user) {
                        $condition->on('origin_document_id', DB::raw('0'));
                        $condition->orOn('D.parent_send_order', 'U.parent_send_order');
                    });
                })
                ->whereIn('C.id', $cids)
                ->where('C.circular_status', $status)
                ->select(DB::raw('C.id, U.title, GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \') as file_names'))
                ->groupBy(['C.id', 'U.title']);

            $circulars = DB::table("circular as C")
                ->leftJoinSub($query_sub, 'D', function ($join) {
                    $join->on('C.id', '=', 'D.id');
                })
                ->leftjoin('mst_user as A', 'C.mst_user_id', 'A.id')
                ->select(DB::raw('C.id, C.create_at, C.final_updated_date, C.circular_status, D.file_names, A.email user_email,A.family_name, A.given_name,
                CONCAT(A.family_name, A.given_name) user_name, D.title'))
                ->where('A.mst_company_id', $user->mst_company_id)
                ->whereIn('C.id', $cids)
                ->where('C.circular_status', $status)
                ->get();

            $cids = $circulars->pluck('id')->toArray();
            // PAC_5-1944 End

            if (count($cids)) {
                $filenames = [];
                $subjects = [];
                $creator_emails = [];
                $creator_names = [];
                $circular_statuss = [];
                foreach ($circulars as $circular) {
                    $filenames[] = $circular->file_names;
                    $subjects[] = $circular->title;
                    $creator_emails[] = $circular->user_email;
                    $creator_names[] = $circular->family_name . $circular->given_name;
                    $circular_statuss[] = isset(AppUtils::CIRCULAR_STATUS[$circular->circular_status]) ? AppUtils::CIRCULAR_STATUS[$circular->circular_status] : "";
                }

                Session::flash('file_names', $filenames);
                Session::flash('subject', $subjects);
                Session::flash('creator_email', $creator_emails);
                Session::flash('creator_name', $creator_names);
                Session::flash('circular_status', $circular_statuss);
                Session::flash('log_info', \App\Http\Utils\OperationsHistoryUtils::LOG_INFO['Circulars']['deletes']);

                DB::beginTransaction();
                DB::table("circular")->whereIn('id', $cids)->update(['circular_status' => CircularUserUtils::CIRCULAR_DELETED, 'final_updated_date' => Carbon::now(),]);

                //PAC_5-1398 回覧中のすべての添付ファイルを削除します。
                CircularAttachmentUtils::deleteAttachments($cids);

                // 回覧ユーザ削除処理
                DB::update("UPDATE circular AS C, circular_user AS CU, mst_user AS U"
                    . ' SET CU.del_flg = ' . CircularUserUtils::DELETED
                    . " ,CU.update_at = '" . Carbon::now() . "'"
                    . " ,CU.update_user = '" . $user->email . "'"
                    . ' WHERE C.id = CU.circular_id '
                    . ' AND C.mst_user_id = U.id AND U.mst_company_id = ' . $user->mst_company_id
                    . ' AND C.id IN (' . DB::raw(\implode(',', $cids)) . ') '
                );

                // 閲覧ユーザ削除処理
                DB::update("UPDATE circular AS C, viewing_user AS V, mst_user AS U"
                    . ' SET V.del_flg = ' . CircularUserUtils::DELETED
                    . " ,V.update_at = '" . Carbon::now() . "'"
                    . " ,V.update_user = '" . $user->email . "'"
                    . ' WHERE C.id = V.circular_id '
                    . ' AND C.mst_user_id = U.id AND U.mst_company_id = ' . $user->mst_company_id
                    . ' AND C.id IN (' . DB::raw(\implode(',', $cids)) . ') '
                );

                // そのた環境にデータがあるか確認します
                foreach ($cids as $circularId) {
                    $otherEnvResults = DB::table("circular_user")
                        ->select(['circular_id', 'env_flg', 'edition_flg', 'server_flg', 'email'])
                        ->where('circular_id', $circularId)
                        ->where(function ($query) {
                            $query->where('env_flg', '<>', config('app.pac_app_env'));
                            $query->orWhere('edition_flg', '<>', config('app.pac_contract_app'));
                            $query->orWhere('server_flg', '<>', config('app.pac_contract_server'));
                        })
                        ->where('circular_status', '<>', 0)
                        ->orderBy('id')
                        ->get();
                    // データがある場合
                    if (count($otherEnvResults) > 0) {
                        // 環境リスト
                        $envList = [];
                        foreach ($otherEnvResults as $otherEnvResult) {
                            // 処理したかどうかを判断する
                            if (!in_array($otherEnvResult->env_flg . $otherEnvResult->edition_flg . $otherEnvResult->server_flg, $envList)) {
                                $envList[] = $otherEnvResult->env_flg . $otherEnvResult->edition_flg . $otherEnvResult->server_flg;
                            } else {
                                continue;
                            }
                            // K5とAWSの環境判断
                            if (($otherEnvResult->env_flg != config('app.pac_app_env') || $otherEnvResult->server_flg != config('app.pac_contract_server'))
                                && $otherEnvResult->edition_flg == config('app.pac_contract_app')) {
                                // 環境設定
                                $envClient = EnvApiUtils::getAuthorizeClient($otherEnvResult->env_flg, $otherEnvResult->server_flg);
                                $transferredCircular = [
                                    "circular_id" => $circularId,
                                    "circular_env_flg" => config('app.pac_app_env'),
                                    "circular_edition_flg" => config('app.pac_contract_app'),
                                    "circular_server_flg" => config('app.pac_contract_server'),
                                    "user_email" => $user->email,
                                    "finishedDate" => ''
                                ];

                                Log::debug("回覧削除時に、別の新環境の回覧状態変更：circular_id:" . $circularId
                                    . " edition_flag:" . config('app.pac_contract_app') . " env_flg:" . config('app.pac_app_env') . " server_flg:" . config('app.pac_contract_server'));

                                // APIを呼び出し
                                $response = $envClient->post("deleteOtherCircular",
                                    [RequestOptions::JSON => $transferredCircular]);
                                // エラー発生の場合
                                if (!in_array($response->getStatusCode(), [\Illuminate\Http\Response::HTTP_CREATED, \Illuminate\Http\Response::HTTP_OK])) {
                                    DB::rollBack();
                                    Log::error('そのた環境にデータの削除に失敗しました');
                                    Log::error($response->getBody());
                                    throw new \Exception('そのた環境にデータの削除に失敗しました');
                                }
                            }

                            // 現新環境判断
                            if ($otherEnvResult->edition_flg != config('app.pac_contract_app')) {
                                $circular = DB::table("circular")->where('id', $circularId)->first();
                                // tokenを取得
                                $result = EnvApiUtils::getEditionAuthorizeClient($otherEnvResult->env_flg, $circular, $otherEnvResult);
                                if (!$result['status']) {
                                    DB::rollBack();
                                    throw new \Exception('Cannot connect to Edition Api: ' . $result['message']);
                                } else {
                                    $editionClient = $result['client'];
                                    $requestsTransferredCircular = ["document_id" => $circularId,
                                        "edition_flg" => config('app.pac_app_env'),
                                        "env_flg" => config('app.pac_contract_app'),
                                        "server_flg" => config('app.pac_contract_server'),
                                        "user_email" => $user->email,
                                        "user_id" => $user->id,];

                                    Log::debug("回覧削除時に、別の現行環境の回覧状態変更：circular_id:" . $circularId
                                        . " edition_flag:" . config('app.pac_contract_app') . " env_flg:" . config('app.pac_app_env') . " server_flg:" . config('app.pac_contract_server'));
                                    // APIを呼び出し
                                    $response = $editionClient->post("deleteDocuments", [
                                        RequestOptions::JSON => $requestsTransferredCircular
                                    ]);
                                    // エラー発生の場合
                                    if (!in_array($response->getStatusCode(), [\Illuminate\Http\Response::HTTP_CREATED, \Illuminate\Http\Response::HTTP_OK])) {
                                        DB::rollBack();
                                        Log::error('そのた環境にデータの削除に失敗しました');
                                        Log::error($response->getBody());
                                        throw new \Exception('そのた環境にデータの削除に失敗しました');
                                    }
                                }
                            }
                        }
                    }
                }
                // check if the company of email is using SAML Login
                $company = DB::table('mst_company')->where('id', $user->mst_company_id)->select('mst_company.login_type', 'mst_company.url_domain_id')->first();
                foreach ($circulars as $circular) {
                    $data = [
                        'deleteTime' => date("Y/m/d H:i"),
                        'title' => $circular->title,
                        'fileName' => $circular->file_names,
                        'url_domain_id' => $company && ($company->login_type == AppUtils::LOGIN_TYPE_SSO) ? $company->url_domain_id : '',
                    ];
                
                    //利用者:回覧削除通知
                    MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                        $circular->user_email,
                        // メールテンプレート
                        MailUtils::MAIL_DICTIONARY['CIRCULAR_DELETE_ALERT']['CODE'],
                        // パラメータ
                        json_encode($data, JSON_UNESCAPED_UNICODE),
                        // タイプ
                        AppUtils::MAIL_TYPE_USER,
                        // 件名
                        config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.SendMailDeleteCircular.subject'),
                        // メールボディ
                        trans('mail.SendMailDeleteCircular.body', $data)
                    );
                }
                DB::commit();
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::warning($ex->getMessage() . $ex->getTraceAsString());
            $this->raiseWarning(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
        $this->raiseSuccess(__('message.success.delete_circular'));
    }

    /**
     * ダウンロード予約処理
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reserve(Request $request){

        $reqFileName                = $request->get('fileName', '');
        $cids                       = $request->get('cids', []);
        $finishedDate               = $request->get('finishedMonthHidden', '');
        $check_add_stamp_history    = $request->get('check_add_stamp_history', false);
        
        return DownloadRequestUtils::reserveDownload($cids, $reqFileName, $finishedDate, $check_add_stamp_history);
    }
}
