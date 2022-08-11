<?php

namespace App\Http\Controllers;

use App\Http\Utils\DepartmentUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController; 
use Illuminate\Support\Facades\Validator;
use DB;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use App\Models\Department;
use App\Models\Position;
use App\Models\DepartmentCsv;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Session;
use App\Http\Utils\CommonUtils;
use App\Models\Company;

class DepartmentTitleController extends AdminController
{

    private $department;
    private $position;
    private $departmentCsv;
    
    public function __construct(Department $department, Position $position, DepartmentCsv $departmentCsv)
    {
        parent::__construct();
        $this->department   = $department;
        $this->position     = $position;
        $this->departmentCsv     = $departmentCsv;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $user       = $request->user();
        $action     = $request->get('action');
        // 無害化処理設定時はCSVダウンロード無効化するためのフラグ TODO 非同期化と無害化
        $sanitizing_flg = Company::where('id', $user->mst_company_id)
                                ->first()->sanitizing_flg;
        // get department
        $itemsDepartment = $this->department                
                ->where('mst_company_id',$user->mst_company_id)
                ->select('id','parent_id' ,'state', 'department_name as name', 'department_name as sort_name' ,'display_no')
                ->get()
                ->map(function ($sort_name) {
                    $sort_name->sort_name = str_replace(\App\Http\Utils\AppUtils::STR_KANJI, \App\Http\Utils\AppUtils::STR_SUUJI, $sort_name->sort_name);

                    return $sort_name;
                })
                ->sortBy('display_no');

        if($action == 'export-department'){
            $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);
            $this->assign('itemsDepartment', $itemsDepartment);
            $this->assign('listDepartmentTree', $listDepartmentTree);
            return $this->render('DepartmentTitle.csv');
        }

        // get department CSV
        $departmentCsvOrderBy    = $request->get('departmentCsvOrderBy') ? $request->get('departmentCsvOrderBy'): 'request_date';
        $departmentCsvOrderDir   = $request->get('departmentCsvOrderDir') ? $request->get('departmentCsvOrderDir'): 'desc';
        $departmentDownloadCsv = $this->departmentCsv
                    ->where('mst_company_id', $user->mst_company_id)
                    ->orderBy($departmentCsvOrderBy, $departmentCsvOrderDir)->get();
 
        $orderDir   = $request->get('orderDir') ? $request->get('orderDir'): 'asc';
        $limit      = $request->get('limit', 10);

        $itemsPosition = $this->position
                ->select('id' , 'position_name' , 'position_name as sort_name' ,'display_no')
                ->where('mst_company_id',$user->mst_company_id)
                ->get()
                ->map(function ($sort_name) {
                    $sort_name->sort_name = str_replace(\App\Http\Utils\AppUtils::STR_KANJI, \App\Http\Utils\AppUtils::STR_SUUJI, $sort_name->sort_name);

                    return $sort_name;
                });

        if(strtolower($orderDir) == 'asc'){
            $itemsPosition = $itemsPosition->sortBy('sort_name');
        }else{
            $itemsPosition = $itemsPosition->sortByDesc('sort_name');
        }

        if($action == 'export-position'){
            $itemsPositionCsv = $itemsPosition;
            $this->assign('itemsPositionCsv', $itemsPositionCsv);
        }

        // count
        $dataCount = count($itemsPosition);
        // from
        $page      = $request->get('page')?$request->get('page'):1;
        $dataFrom = ($page-1) * $limit + 1;
        // to
        $dataTo = $page * $limit;
        if($dataTo > $dataCount){
            $dataTo = $dataCount;
        }

        if($dataCount){
            // 役職存在する場合

            $chunks = $itemsPosition->chunk($limit);
            // 最終ページレコード削除対応
            if(!isset($chunks[$page-1])){
                $page = count($chunks);
            }
            $itemsPosition = $chunks[$page-1];
            // currentPage
            $currentPage = $page;
            // lastPage
            $lastPage = count($chunks);
        }else{
            // 役職存在しない場合

            // currentPage
            $currentPage = $page;
            // lastPage
            $lastPage = 1;
        }
        $itemsPosition=collect($itemsPosition)->sortBy('display_no');
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
       
        $departmentCsvOrderDir = strtolower($departmentCsvOrderDir)=="asc"?"desc":"asc";
        $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";

        $this->assign('itemsDepartment', \App\Http\Utils\CommonUtils::arrToTree($itemsDepartment));
        $this->assign('departmentDownloadCsv', $departmentDownloadCsv);
        $this->assign('itemsPosition', $itemsPosition);
        $this->assign('departmentCsvOrderBy', $departmentCsvOrderBy);
        $this->assign('departmentCsvOrderDir', $departmentCsvOrderDir);
        $this->assign('orderDir', $orderDir);
        $this->assign('dataCount', $dataCount);
        $this->assign('dataFrom', $dataFrom);
        $this->assign('dataTo', $dataTo);
        $this->assign('currentPage', $currentPage);
        $this->assign('lastPage', $lastPage);
        $this->assign('sanitizing_flg', $sanitizing_flg);
        $this->setMetaTitle('部署・役職');

        if($action == 'export-position'){
            return $this->render('DepartmentTitle.csv');
        }else{
            return $this->render('DepartmentTitle.index');
        }

    }

    public function show($id, Request $request){
        $user   = $request->user();
        $type   = $request->get('type');
        if($type == 'department'){
            $item = $this->department->where('mst_company_id', $user->mst_company_id)->find($id);
        }else $item = $this->position->where('mst_company_id', $user->mst_company_id)->find($id);
     
        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        return response()->json(['status' => true, 'item' => $item]);
    }
    
    public function store(Request $request){

        $user   = $request->user();
        $type   = $request->get('type');
        $item_info = $request->get('item');
        $tree = '';
        if($type == 'department'){
            $item = new $this->department;
            $item_info['state'] = \App\Http\Utils\AppUtils::DEFAULT_DEPARTMENT_STATE;

            // 部署名必須チェック
            if(!isset($item_info['department_name']) || !$item_info['department_name']){
                return response()->json(['status' => false, 'message' => ['部署名を指定してください。']]);
            }

            // 重複チェック
            $Department = $this->department
                ->where('mst_company_id',$user->mst_company_id)
                ->where('parent_id',$item_info['parent_id'])
                ->where('department_name',$item_info['department_name'])
                ->where('state',\App\Http\Utils\AppUtils::DEFAULT_DEPARTMENT_STATE)
                ->first();
            if ($Department) {
                return response()->json(['status' => false, 'message' => ['部署名が既に存在します。']]);
            }

            if($item_info['parent_id']){
                $parent = $this->department->where('mst_company_id', $user->mst_company_id)->find($item_info['parent_id']);
                if(!$parent){
                    return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
                }
                $parent_department = $this->department->where('id',$item_info['parent_id'])->first();
                if ($parent_department){
                    $tree = $parent_department->tree;
                }
                Session::flash('parent_name', $parent->department_name);
            }else Session::flash('parent_name', "");
            
        }else{
            $item = new $this->position;
            $item_info['state'] = \App\Http\Utils\AppUtils::DEFAULT_POSITION_STATE;

            // 重複チェック
            $Position = $this->position
                ->where('mst_company_id',$user->mst_company_id)
                ->where('position_name',$item_info['position_name'])
                ->where('state',\App\Http\Utils\AppUtils::DEFAULT_POSITION_STATE)
                ->first();
            if ($Position) {
                return response()->json(['status' => false, 'message' => ['役職名が既に存在します。']]);
            }
        }
        
        $item->fill($item_info);
        $item->mst_company_id = $user->mst_company_id;
        $item->create_user = $user->getFullName();
        $item->update_user = $user->getFullName();
        
        DB::beginTransaction();
        try{
            $item->save();
            if($type == 'department'){
                $item->tree = $tree . $item->id . ',';
                $item->save();
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.create_'.$type)] ]);
        }

        return response()->json(['status' => true, 'id' => $item->id, 
                'message' => [__('message.success.create_'.$type)]
        ]);
    }
    
    public function update($id, Request $request){
        $user   = $request->user();
        $type   = $request->get('type');
        $item_info = $request->get('item');
        $old_tree = '';
        if($type == 'department'){
            // 部署名必須チェック
            if(!isset($item_info['department_name']) || !$item_info['department_name']){
                return response()->json(['status' => false, 'message' => ['部署名を指定してください。']]);
            }

            // 重複チェック
            $Department = $this->department
                ->where('mst_company_id',$user->mst_company_id)
                ->where('parent_id',$item_info['parent_id'])
                ->where('department_name',$item_info['department_name'])
                ->where('state',\App\Http\Utils\AppUtils::DEFAULT_DEPARTMENT_STATE)
                ->first();
            if ($Department) {
                return response()->json(['status' => false, 'message' => ['部署名が既に存在します。']]);
            }

            $item = $this->department->where('mst_company_id', $user->mst_company_id)->find($id);
            $old_tree = $item->tree;
        }else {

            // 重複チェック
            $Position = $this->position
                ->where('mst_company_id',$user->mst_company_id)
                ->where('position_name',$item_info['position_name'])
                ->where('state',\App\Http\Utils\AppUtils::DEFAULT_POSITION_STATE)
                ->first();
            if ($Position) {
                return response()->json(['status' => false, 'message' => ['役職名が既に存在します。']]);
            }

            $item = $this->position->where('mst_company_id', $user->mst_company_id)->find($id);
        }

        if(!$item || $item_info['mst_company_id'] != $user->mst_company_id){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        $item->fill($item_info);
        $item->update_user = $user->getFullName();

        DB::beginTransaction();
        try{
            $item->save();
            $trees = DepartmentUtils::getChangeChildTree($user->mst_company_id, $item->parent_id, $item->id,$old_tree);
            foreach ($trees as $id => $tree) {
                DB::table('mst_department')->where('id',$id)
                    ->update([
                        'tree' => $tree,
                        'update_user' => $user->getFullName()
                    ]);
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.update_'.$type)] ]);
        }

        return response()->json(['status' => true, 'id' => $item->id,
                'message' => [__('message.success.update_'.$type)]
            ]);
    }

    public function destroy($id, Request $request){
        $user   = $request->user();
        $type   = $request->get('type');
        $item_info = $request->get('item');
        DB::beginTransaction();
        try{
            if($type == 'department'){
                $item = $this->department->where('mst_company_id', $user->mst_company_id)->find($id);
                if(!$item){
                    return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
                }
                Session::flash('name', $item->department_name);

                // 企業配下全部署
                $delDepLst = $this->department
                    ->where('mst_company_id', $user->mst_company_id)
                    ->where('tree', 'like', "$item->tree%")
                    ->get()->pluck('id');

                // 部署レコード削除
                DB::table('mst_department')
                    ->whereIn('id',$delDepLst)
                    ->delete();

                // 削除した部署配下のユーザー：部署なしに更新
                DB::table('mst_user_info')
                    ->whereIn('mst_department_id',$delDepLst)
                    ->update(['mst_department_id' => null]);

            }else{
                $item = $this->position->where('mst_company_id', $user->mst_company_id)->find($id);
                if(!$item){
                    return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
                }
                Session::flash('name', $item->position_name);

                // 削除した役職配下のユーザー：部署なしに更新
                DB::table('mst_user_info')
                    ->where('mst_position_id',$id)
                    ->update(['mst_position_id' => null]);

                $item->delete();
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.delete_'.$type)] ]);
        }

        return response()->json(['status' => true,  
            'message' => [__('message.success.delete_'.$type)]
        ]);
    }

    /**
     * サブ部門を取得する
     * @param $id 親部門ID
     * @param $depComLst 会社内全部署
     */
    public function getChildDepartments($id,$depComLst){
        $childDep = array();
        foreach ($depComLst as $department_id => $department){
            if($id == $department['parent_id']){
                // 指定部署の直下子部署
                $childDep[] = $department['id'];
                // 指定部署の直下子部署の子部署
                $GrandDep = $this->getChildDepartments($department['id'],$depComLst);
                $childDep = array_merge($childDep, $GrandDep);
            }
        }
        return $childDep;
    }

    public function addDepartmentDownload(Request $request){
        $user   = $request->user();
        
        $item = new $this->departmentCsv;
        $item->mst_company_id   = $user->mst_company_id;
        $item->state            = \App\Http\Utils\AppUtils::DEPARTMENT_CSV_BEFORE;
        $item->mst_user_id   = $user->id;
  
        DB::beginTransaction();
        try{
            $item->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        Artisan::call('department:exportcsv', ['recordID' => $item->id, 'mst_company_id' => $user->mst_company_id]);

        Session::flash('message', "CSVダウンロードのリクエストを受け付けました。");
        return redirect()->back();
    }

    public function departmentDownload($id, Request $request){
        $user  = $request->user();
        $item  = $this->departmentCsv->find($id);
        if($user->mst_company_id != $item->mst_company_id){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            $res = redirect()->back()->withInput();
            \Session::driver()->save();
            $res->send();
            exit;
        }
        Session::flash('file_name', $item->file_name);
        header('Content-Type: text/csv; charset=utf-8');                
        header('Content-Disposition: attachment; filename='.$item ->file_name.'');
        $output = fopen('php://output', 'w');
        fputs( $output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );
        $item->contents = \json_decode($item->contents);
        foreach ($item->contents as $item){
            fputcsv($output, $item);
        }
        fclose($output);
    }

    public function deleteDepartmentDownload($id, Request $request){
        $user  = $request->user();
        $item  = $this->departmentCsv->find($id);
        if($user->mst_company_id != $item->mst_company_id){
            return response()->json(['status' => false,
                'message' => [__('message.warning.not_permission_access')]
            ]);
        }

        $item = $this->departmentCsv->find($id);
        $item->delete();
        Session::flash('file_name', $item->file_name);
        return response()->json(['status' => true,  
            'message' => [__('CSVファイルを削除しました。')]
        ]);

    }

    public function importPos(Request $request){
        if ($request->hasFile('file')) {
            $user   = $request->user();

            $file = $request->file('file');
            $path = $file->getRealPath();
            $csv_data = array_map('str_getcsv', file($path)); // doc csv
            // code対応 start
            $str = file_get_contents($file);
            $code = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5', 'SJIS'));

            if ($code == 'CP936' || $code == 'SJIS' || $code == 'SJIS-win') {
                $csv_data = CommonUtils::convertCode('SJIS-win', 'UTF-8', $csv_data);
            }

            $total = count($csv_data);
            $num_insert = 0;
            $num_update = 0;
            $num_error = 0;
            $num_normal = 0;
            $arrReason = [];

            $positions = $this->position
                ->where('mst_company_id', $user->mst_company_id)
                ->pluck('position_name')->toArray();

            if($total){
                $mapPosition = [];

                DB::beginTransaction();
                try {

                    foreach($csv_data as $i => $row) {

                        $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);

                        if ((!is_array($row) && !trim($row)) || (count(array_filter($row)) == 0)) {
                            continue;
                        }elseif($i == 0){
                            // 先頭行

                            if(strpos($row[0], $bom) === 0){
                                // 先頭行BOM削除
                                $row[0] = ltrim($row[0], $bom);
                                if(!trim($row[0])){
                                    continue;
                                }
                            }
                        }

                        if (count($row) > 1){
                            $num_error++;
                            $arrReason[] = '行が正しくありません';
                            continue;
                        }
                        $item = [
                            'mst_company_id'=>$user->mst_company_id,
                            'position_name'=>$row[0],
                            'state'=>AppUtils::DEFAULT_POSITION_STATE,
                            'create_user'=>$user->getFullName(),
                            'update_user'=>$user->getFullName(),
                            'create_at' => Carbon::now(),
                            'update_at' => Carbon::now()
                        ];

                        $validator = Validator::make($item, [
                            'position_name' => 'required|max:256'
                        ]);

                        if ($validator->fails()){
                            $message = $validator->messages();
                            $message_all = $message->all();
                            $arrReason[] = $message_all;
                            $num_error ++;

                            if($message->has('position_name') == true){
                                $detail_error['col'] = 1;
                                $detail_error['name_error'] = '役職名';
                            }
                        }elseif (in_array($row[0], $positions)){
                            $num_error++;
                            $arrReason[] = 'その役職はすでに使われています';
                            continue;
                        }else{
                            array_push($mapPosition,$item);
                        }
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollBack();
                    $num_error ++;
                    $arrReason[] = $e->getMessage();
                }

                if(!count($arrReason)){
                    $num_insert = count($mapPosition);
                    if($num_insert){
                        DB::table('mst_position')->insert($mapPosition);
                    }
                }
            }
        }
        $num_normal = $num_insert + $num_update;
        return response()->json(['status' => (count($arrReason) == 0), 'total'=>$total,  'num_insert' => $num_insert,
            'num_update' => $num_update, 'num_error' => $num_error, 'message' => $arrReason , 'num_normal' => $num_normal]);
    }

    public function importDep(Request $request){

        if ($request->hasFile('file')) {
            $user   = $request->user();

            $file = $request->file('file');
            $path = $file->getRealPath();
            $csv_data = array_map('str_getcsv', file($path)); // doc csv
            // code対応 start
            $str = file_get_contents($file);
            $code = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5', 'SJIS'));

            if ($code == 'CP936' || $code == 'SJIS' || $code == 'SJIS-win') {
                $csv_data = CommonUtils::convertCode('SJIS-win', 'UTF-8', $csv_data);
            }

            $total = count($csv_data);
            $num_insert = 0;
            $num_update = 0;
            $num_error = 0;
            $num_normal = 0;
            $arrReason = [];

            if($total){

                DB::beginTransaction();
                try {

                    foreach($csv_data as $i => $row) {

                        $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);

                        if ((!is_array($row) && !trim($row)) || (count(array_filter($row)) == 0)) {
                            continue;
                        }elseif($i == 0){
                            // 先頭行

                            if(strpos($row[0], $bom) === 0){
                                // 先頭行BOM削除
                                $row[0] = ltrim($row[0], $bom);
                            }
                        }


                        if (count($row) != 3){
                            // 項目数が3ではない
                            $num_error++;
                            $arrReason[] = '行が正しくありません';
                            continue;
                        }

                        // 動作モード
                        $ope = $row[2];
                        if ($ope != '1' && $ope != '2'){
                            // 動作モードが1、2以外
                            $num_error++;
                            $arrReason[] = '動作モードが正しくありません';
                            continue;
                        }

                        // 部署ID
                        $opeid = $row[0];

                        if ($ope == '1' && $opeid != ''){
                            // 登録＋ID指定あり
                            $num_error++;
                            $arrReason[] = '部署IDが正しくありません(動作モードが登録の場合、指定しないでください)';
                            continue;

                        }elseif ($ope == '2' && $opeid == ''){
                            // 更新＋ID指定なし
                            $num_error++;
                            $arrReason[] = '部署IDが正しくありません(動作モードが更新の場合、指定してください)';
                            continue;

                        }

                        $Departmentnames = explode(\App\Http\Utils\AppUtils::SPERATOR_SPLIT, $row[1]);

                        if ($ope == '1'){
                            // 登録
                            $parent_id = 0;
                            foreach($Departmentnames as $Departmentname){
                                $record = $this->department
                                    ->where('parent_id',$parent_id)
                                    ->where('department_name',$Departmentname)
                                    ->where('mst_company_id',$user->mst_company_id)
                                    ->where('state','1')
                                    ->first();
                                if(!$record){
                                    $item = [
                                        'mst_company_id'=>$user->mst_company_id,
                                        'parent_id'=>$parent_id,
                                        'state'=>AppUtils::DEFAULT_POSITION_STATE,
                                        'department_name'=>$Departmentname,
                                        'create_user'=>$user->getFullName(),
                                        'update_user'=>$user->getFullName(),
                                        'create_at' => Carbon::now(),
                                        'update_at' => Carbon::now(),
                                    ];

                                    $validator = Validator::make($item, [
                                        'department_name' => 'required|max:256'
                                    ]);

                                    if ($validator->fails()){
                                        $message = $validator->messages();
                                        $message_all = $message->all();
                                        $arrReason[] = $message_all;
                                        $num_error ++;

                                        if($message->has('department_name') == true){
                                            $detail_error['col'] = 1;
                                            $detail_error['name_error'] = '部署名';
                                            break 2;
                                        }
                                    }

                                    $parent_id = DB::table('mst_department')->insertGetId($item);
                                    $num_insert ++;
                                }else{
                                    $parent_id = $record->id;
                                }
                            }

                        }else{
                            // 更新
                            $bak_id = 99999;
                            $bak_parent_id = 99999;
                            for($j = count($Departmentnames) - 1; $j > -1; $j--){
                                if($j == count($Departmentnames) - 1){
                                    // ID指定部署
                                    $record = $this->department
                                        ->where('id',$opeid)
                                        ->where('mst_company_id',$user->mst_company_id)
                                        ->where('state','1')
                                        ->first();
                                    if(!$record){
                                        // err 更新⇒レコードなし
                                        DB::rollBack();
                                        $num_error++;
                                        $arrReason[] = '指定した部署IDが存在しない';
                                        break 2;

                                    }else{

                                        if($Departmentnames[$j] != $record->department_name){
                                            // 部署名更新

                                            $item = [
                                                'department_name' => $Departmentnames[$j],
                                                'update_at' => Carbon::now(),
                                                'update_user'=>$user->getFullName(),
                                            ];

                                            $validator = Validator::make($item, [
                                                'department_name' => 'required|max:256'
                                            ]);

                                            if ($validator->fails()){
                                                $message = $validator->messages();
                                                $message_all = $message->all();
                                                $arrReason[] = $message_all;
                                                $num_error ++;

                                                if($message->has('department_name') == true){
                                                    $detail_error['col'] = 1;
                                                    $detail_error['name_error'] = '部署名';
                                                    break 2;
                                                }
                                            }

                                            DB::table('mst_department')
                                                ->where('id',$opeid)
                                                ->update($item);

                                            $num_update ++;
                                        }

                                        // backup
                                        $bak_id = $record->id;
                                        $bak_parent_id = $record->parent_id;
                                    }
                                }else{
                                    // 移動判定
                                    $record = $this->department
                                        ->where('department_name',$Departmentnames[$j])
                                        ->where('mst_company_id',$user->mst_company_id)
                                        ->where('id',$bak_parent_id)
                                        ->where('state','1')
                                        ->first();

                                    if (!$record){
                                        // 実際に移動した部署 TODO 複数の部署の指定
                                        $record = DB::table('mst_department')
                                            ->where('department_name',$Departmentnames[$j])
                                            ->where('mst_company_id',$user->mst_company_id)
                                            ->where('state','1')
                                            ->orderBy('id','asc')
                                            ->first();
                                    }

                                    if(!$record){
                                        // err 移動先部署名存在しない
                                        DB::rollBack();
                                        $num_error++;
                                        $arrReason[] = '指定した部署名が存在しない（'.$Departmentnames[$j].'）';
                                        break 2;

                                    }else{

                                        if($bak_parent_id != $record->id){
                                            // 下レコード.親ID≠本レコード.ID

                                            // 本レコード配下に移動
                                            DB::table('mst_department')
                                                ->where('id',$bak_id)
                                                ->update([
                                                    'parent_id' => $record->id,
                                                    'update_at' => Carbon::now(),
                                                    'update_user'=>$user->getFullName(),
                                                ]);

                                            $num_update ++;
                                            break;
                                        }

                                        // backup
                                        $bak_id = $record->id;
                                        $bak_parent_id = $record->parent_id;
                                    }
                                }
                            }
                        }
                    }

                    if(count($arrReason)){
                        DB::rollBack();
                        $num_insert = 0;
                        $num_update = 0;
                    }else{
                        //更新会社の部署のtree
                        $trees = DepartmentUtils::updateCompanyDepartment($user->mst_company_id);
                        foreach ($trees as $id => $tree){
                            DB::table('mst_department')->where('id',$id)
                                ->update(['tree' => $tree]);
                        }
                        DB::commit();
                    }
                }catch(\Exception $e){
                    DB::rollBack();
                    $num_error ++;
                    $arrReason[] = $e->getMessage();
                }
            }
        }
        $num_normal = $num_insert + $num_update;
        return response()->json(['status' => (count($arrReason) == 0), 'total'=>$total,  'num_insert' => $num_insert,
            'num_update' => $num_update, 'num_error' => $num_error, 'message' => $arrReason , 'num_normal' => $num_normal]);
    }

    /**
     * 移動中の部署の更新
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDepartmentSort(Request $request){
        $sort=$request->post('sort',[]);
        $user   = $request->user();
        //移動中の部署
        $change_department = null;
        $old_tree = '';
        if (count($sort)){
            foreach ($sort as $item){
                if ($item['change_flg']){
                    $change_department = $item;
                    $old_tree = $this->department->where('id',$item['id'])->value('tree');;
                    // 重複チェック
                    $department_count = $this->department
                        ->where('id','!=',$item['id'])
                        ->where('mst_company_id',$user->mst_company_id)
                        ->where('parent_id',$item['parent'])
                        ->where('department_name',$item['department_name'])
                        ->where('state',AppUtils::DEFAULT_DEPARTMENT_STATE)
                        ->count();
                    if ($department_count) {
                        return response()->json(['status' => false, 'message' => [__('message.false.department_name_repeated')]]);
                    }
                }
            }

            DB::beginTransaction();
            try {

                collect($sort)->each(function($item,$key) use ($user) {
                    DB::table('mst_department')
                        ->where('id','=',$item['id'])
                        ->where('mst_company_id',$user->mst_company_id)
                        ->update([
                            'display_no'=>$item['sort'],
                            'parent_id'=>$item['parent']
                        ]);
                });
                //更新対象の子部署
                $trees = DepartmentUtils::getChangeChildTree($user->mst_company_id,$change_department['parent'],$change_department['id'],$old_tree);
                //更新部署ID
                foreach ($trees as $id => $tree) {
                    DB::table('mst_department')->where('id',$id)
                        ->update([
                            'tree' => $tree,
                            'update_user' => $user->getFullName()
                        ]);
                }

                DB::commit();
                return response()->json(['status'=>true]);
            }catch(\Exception $e){
                DB::rollBack();
                Log::error($e->getMessage().$e->getTraceAsString());
            }
        }

        return response()->json(['status'=>false, 'message' => [__('message.false.update_department')]]);

    }

    public function updatePositionSort(Request $request){
        $sort=$request->post('sort',[]);
        $user   = $request->user();
        DB::beginTransaction();
        try {
            collect($sort)->each(function($item,$key) use ($user) {
                DB::table('mst_position')
                    ->where('mst_company_id',$user->mst_company_id)
                    ->where('id','=',$item['id'])
                    ->update([
                        'display_no'=>$item['sort'],
                    ]);
            });
            DB::commit();
            return response()->json([
                'status'=>true
            ]);
        }catch(\Exception $e){
            DB::rollBack();
        }
        return response()->json([
            'status'=>false
        ]);
    }

    public function getDepartment(Request $request){
        $user       = $request->user();
        // get department
        $itemsDepartment = $this->department
            ->where('mst_company_id',$user->mst_company_id)
            ->select('id','parent_id' ,'state', 'department_name as name', 'department_name as sort_name' ,'display_no')
            ->get()
            ->map(function ($sort_name) {
                $sort_name->sort_name = str_replace(\App\Http\Utils\AppUtils::STR_KANJI, \App\Http\Utils\AppUtils::STR_SUUJI, $sort_name->sort_name);

                return $sort_name;
            })
            ->sortBy('display_no');
        return response()->json(['status'=>true,'data'=> CommonUtils::treeToArr(\App\Http\Utils\CommonUtils::arrToTree($itemsDepartment,'1','name'))]);
    }

}
