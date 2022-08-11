<?php

namespace App\Http\Controllers\LongTerm;

use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\LongTermFolderUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\LongTermDocumentUtils;
use App\Http\Utils\DownloadUtils;
use App\Http\Utils\LongtermIndexUtils;
use App\Http\Utils\UserApiUtils;
use App\Models\UploadData;
use Complex\Exception;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Department;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Session;
use Carbon\Carbon;
use App\Http\Utils\DownloadRequestUtils;
use App\Http\Controllers\AdminController;

class CircularsLongTermController extends AdminController
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

        $documentName   = $request->get('documentName');
        $fromdate   = $request->get('fromdate');
        $todate   = $request->get('todate');
        $fromMoney   = $request->get('frommoney');
        $toMoney   = $request->get('tomoney');
        $customer   = $request->get('customer');
        $keyword   = $request->get('keyword');
        $limit      = $request->get('limit', 20);
        $orderBy    = $request->get('orderBy', "LTD.create_at");
        $orderDir   = $request->get('orderDir', "DESC");
        $action     = $request->get('action','');
        $folder_id   = $request->get('folderId', 0);//フォルダ検索
        $folder = [ 'parent_folder_id' => 0 , 'folder_id' => $folder_id];

        $keys = $request->keys();
        $indexes = [];
        $subIndexes = [];
        $indexId = 1;
        foreach ($keys as $key) {
            if (strpos($key, 'longIndex') !== false) {
                $from_index_id = str_replace('longIndex','fromkeyword', $key);
                $to_index_id = str_replace('longIndex','tokeyword', $key);
                $subIndexes['index'] = $indexId;
                $subIndexes['id'] = $request->get($key);
                $subIndexes['fromvalue'] = $request->get($from_index_id);
                $subIndexes['tovalue'] = $request->get($to_index_id);
                $mstLongtermIndex = DB::table('mst_longterm_index')
                    ->where('id', $subIndexes['id'])
                    ->first();
                if($mstLongtermIndex){
                    if($mstLongtermIndex->data_type === LongtermIndexUtils::NUMERIC_TYPE || $mstLongtermIndex->data_type === LongtermIndexUtils::DATE_TYPE){
                        $subIndexes['display'] = 'block';
                    }else{
                        $subIndexes['display'] = 'none';
                    }
                }
                if ($request->get($key) && ($request->get($from_index_id)||$request->get($to_index_id))){
                    array_push($indexes,$subIndexes);
                    $indexId++;
                }
            }
        }
        // 削除処理判定
        if($request->isMethod('post') && $action){
            // 削除の場合
            if($action == "delete"){
                $cids = $request->get('cids',[]);
                // 削除処理を行う。
                $this->deletes($cids, $user);
            }
            // タイムスタンプ自動更新の場合
            if($action == "automaticUpdateClick"){
                $cids = $request->get('cids',[]);
                $automaticOnFlg = $request->get('automaticOnFlg');
                // タイムスタンプ自動更を行う。
                $this->automaticUpdate($cids, $user, $automaticOnFlg);
            }
        }

        // タイムスタンプの自動更新機能の表示制御の為
        // 会社情報取得（[長期保存オプション]と[タイムスタンプ付き署名]）
        $companyData = DB::table('mst_company')
            ->select(['stamp_flg','long_term_storage_option_flg','long_term_storage_flg', 'frm_srv_flg', 'template_flg','company_name','long_term_folder_flg', 'sanitizing_flg'])
            ->where('id', $user->mst_company_id)
            ->first();
        //「タイムスタンプ付与日時」を表示する
        $isShowDate = $companyData->stamp_flg && $companyData->long_term_storage_flg && $companyData->long_term_storage_option_flg;
        // 初期化
        $isShowAutomaticUpdate = '0';
        $isShowLongTermindex = '0';
        $longTermStorageOptionFlg = $companyData->long_term_storage_option_flg == 1 ? $companyData->long_term_storage_option_flg : 0;
        // 長期保存オプションが「1」の場合
        if($companyData->long_term_storage_option_flg == '1'){
            // タイムスタンプ付き署名が「1」の場合
            if($companyData->stamp_flg == '1'){
                // タイムスタンプの自動更新機能が表示制御
                $isShowAutomaticUpdate = '1';
            }
            // 長期保存インデックス検索条件が表示されること
            $isShowLongTermindex = '1';
        }

        $data = DB::table('long_term_document as LTD')
            ->select(['LTD.id','LTD.circular_id','LTD.mst_company_id','LTD.sender_name','LTD.sender_email','LTD.file_name','LTD.file_size','LTD.keyword','LTD.request_at','LTD.completed_at','LTD.title',
                'LTD.create_at','LTD.destination_name','LTD.destination_email','LTD.sender_name','LTD.sender_email', 'LTD.create_user', 'LTD.timestamp_automatic_flg','LTD.circular_attachment_json', 'LTD.add_timestamp_automatic_date','LTD.upload_status','LTD.upload_id','LTD.is_del']);

        if (!$companyData->long_term_folder_flg) $folder_id = -1;
        if($folder_id >= 0){
            $folder_tree = DB::table('long_term_folder')->select('tree','parent_id')->where('id',$folder_id)->first();
            if ($folder_tree) $folder['parent_folder_id'] = array_filter(explode(',',$folder_tree->tree));
            $data = $data->where('LTD.long_term_folder_id',$folder_id);
        }


        if($documentName){
            $data = $data->where('LTD.title', 'like', '%' . $documentName . '%');
        }
        if($keyword){
            $data = $data->where('LTD.keyword', 'like', '%' . $keyword . '%');
        }

        $data = $data->where('LTD.mst_company_id', $user->mst_company_id );

        // 金額　と　取引年月日　の実装検討要todo　▼
        $ids = array();
        $condition = false;
        // 金額　と　取引年月日　の実装検討要todo　▲

        // Drop downの値取得
        $mst_longterm_index = DB::table('mst_longterm_index')
            ->select('id', 'index_name', 'data_type')
            ->orWhere('mst_company_id', 0)
            // 通常長期保管インデックス
            ->orwhere(function($query) use ($user){
                $query->where('mst_company_id',$user->mst_company_id)
                    ->Where('template_flg', 0);
            });
        // テンプレートフラグが1の場合
        if($companyData->template_flg == 1){
            // 有効のテンプレートインデックス
            $mst_longterm_index = $mst_longterm_index->orwhere(function($query) use ($user){
                $query->where('mst_company_id',$user->mst_company_id)
                    ->where('template_valid_flg',1) // 有効
                    ->Where('template_flg', 1); // テンプレート
            });
        }
        // 帳票発行サービスの使用許可が1の場合
        if($companyData->frm_srv_flg == 1){
            // 有効の帳票インデックス
            $mst_longterm_index = $mst_longterm_index->orwhere(function($query) use ($user){
                $query->where('mst_company_id',$user->mst_company_id)
                    ->where('template_valid_flg',1) // 有効
                    ->Where('template_flg', 2); // 帳票
            });
        }
        $mst_longterm_index = $mst_longterm_index->orderBy('sort_id', 'asc')
            ->get()
            ->keyBy('id');
        $longTermIndex = [];
        $longTermIndexSub = [];
        $longTermIndexall = [];
        foreach ($mst_longterm_index as $key => $item) {
            $longTermIndex[$key] = $item->index_name;
            $longTermIndexSub['id'] = $key;
            $longTermIndexSub['value'] = $item->index_name;
            $longTermIndexSub['type'] = $item->data_type;
            array_push($longTermIndexall, $longTermIndexSub);
        }
        //検索条件入力判定flg
        $date_condition = false;
        $money_condition = false;
        $customer_condition = false;
        $index_condition= false;
        $long_term_document_id=[];
        try{

            // 金額　と　取引年月日　の実装検討要todo　▼
            if($fromdate || $todate){
                $condition = true;
                $longtermIndexDate = DB::table('longterm_index')
                    ->where('mst_company_id', $user->mst_company_id)
                    ->where('longterm_index_id', 1);
                if($fromdate){
                    $longtermIndexDate = $longtermIndexDate->whereDate('date_value', '>=', $fromdate);
                }
                if($todate){
                    $longtermIndexDate = $longtermIndexDate->whereDate('date_value', '<=', $todate);
                }
                $longtermIndexDate = $longtermIndexDate->get();

                if(count($longtermIndexDate)){
                    foreach ($longtermIndexDate as $value) {
                        if(isset($value->long_term_document_id)&& $value->long_term_document_id){
                            $long_term_document_id[]=$value->long_term_document_id;
                        }else{
                            $ids[] = $value->circular_id;
                        }
                    }
                }
                $date_condition = true;

            }

            if($fromMoney || $toMoney){
                $condition = true;
                $longtermIndexMoney = DB::table('longterm_index')
                    ->where('mst_company_id', $user->mst_company_id)
                    ->where('longterm_index_id', 2);
                if($fromMoney){
                    $longtermIndexMoney = $longtermIndexMoney->where('num_value', '>=', $fromMoney);
                }
                if($toMoney){
                    $longtermIndexMoney = $longtermIndexMoney->where('num_value', '<=', $toMoney);
                }
                if(count($ids) || $date_condition){
                    $longtermIndexMoney = $longtermIndexMoney->where(function ($query)use ($ids,$long_term_document_id){
                        $query->whereIn('circular_id', $ids)->orWhereIn('long_term_document_id',$long_term_document_id);
                    });
                }
                $longtermIndexMoney = $longtermIndexMoney->get();

                $ids = array();
                $long_term_document_id=[];
                if(count($longtermIndexMoney)){
                    foreach ($longtermIndexMoney as $value) {
                        if(isset($value->long_term_document_id)&& $value->long_term_document_id){
                            $long_term_document_id[]=$value->long_term_document_id;
                        }else{
                            $ids[] = $value->circular_id;
                        }
                    }
                }
                $money_condition = true;
            }

            //PAC_5_2134 取引先検索
            if($customer){
                $condition = true;
                $longtermIndexCustomer = DB::table('longterm_index')
                    ->join('mst_longterm_index','mst_longterm_index.id','longterm_index.longterm_index_id')
                    ->where('mst_longterm_index.permission',LongtermIndexUtils::CUSTOMER_PERMISSION)
                    ->where('mst_longterm_index.index_name', LongtermIndexUtils::CUSTOMER_NAME)
                    ->where('longterm_index.mst_company_id', $user->mst_company_id)
                    // PAC_5-2496 add
                    ->where('longterm_index.string_value','like', '%'.$customer.'%')
                    ->select('longterm_index.circular_id','longterm_index.long_term_document_id');

                if(count($ids) || $date_condition || $money_condition){
                    $longtermIndexCustomer = $longtermIndexCustomer->where(function ($query)use ($ids,$long_term_document_id){
                        $query->whereIn('longterm_index.circular_id', $ids)->orWhereIn('longterm_index.long_term_document_id',$long_term_document_id);
                    });
                }
                $longtermIndexCustomer = $longtermIndexCustomer->get();

                //以上の検索条件を満たすcirular_id。
                $ids = array();
                $long_term_document_id=[];
                if(count($longtermIndexCustomer)){
                    foreach ($longtermIndexCustomer as $value) {
                        if(isset($value->long_term_document_id)&& $value->long_term_document_id){
                            $long_term_document_id[]=$value->long_term_document_id;
                        }else{
                            $ids[] = $value->circular_id;
                        }
                    }
                }
                $customer_condition = true;
            }

            if($indexes){
                $condition = true;
                foreach ($indexes as $value) {
                    $mstLongtermIndex = DB::table('mst_longterm_index')
                        ->where('id', $value['id'])
                        ->first();
                    $longtermIndex = DB::table('longterm_index')
                        ->where('mst_company_id', $user->mst_company_id)
                        ->where('longterm_index_id', $value['id']);
                    if($mstLongtermIndex->data_type === LongtermIndexUtils::NUMERIC_TYPE){
                        if($value['fromvalue']){
                            $longtermIndex->where('num_value', '>=', $value['fromvalue']);
                        }
                        if($value['tovalue']){
                            $longtermIndex->where('num_value', '<=', $value['tovalue']);
                        }
                    } else if($mstLongtermIndex->data_type === LongtermIndexUtils::STRING_TYPE){
                        // PAC_5-2496 add
                        $longtermIndex->where('string_value','like', '%'.$value['fromvalue'].'%');
                    } else if($mstLongtermIndex->data_type === LongtermIndexUtils::DATE_TYPE){
                        if($value['fromvalue']){
                            $longtermIndex->whereDate('date_value', '>=', $value['fromvalue']);
                        }
                        if($value['tovalue']){
                            $longtermIndex->whereDate('date_value', '<=', $value['tovalue']);
                        }
                    }
                    if(count($ids) || $date_condition || $money_condition || $customer_condition || $index_condition){
                        $longtermIndex = $longtermIndex->where(function ($query)use ($ids,$long_term_document_id){
                            $query->whereIn('circular_id', $ids)->orWhereIn('long_term_document_id',$long_term_document_id);
                        });
                    }
                    $longtermIndex = $longtermIndex->get();

                    $ids = $ids = array();
                    $long_term_document_id=[];
                    if(count($longtermIndex)){
                        foreach ($longtermIndex as $v) {
                            if(isset($v->long_term_document_id)&& $v->long_term_document_id){
                                $long_term_document_id[]=$v->long_term_document_id;
                            }else{
                                $ids[] = $v->circular_id;
                            }
                        }
                    }
                    $index_condition = true;

                }
            }

            if($condition){
                $data = $data->whereIn('circular_id', $ids)->orWhereIn('id',$long_term_document_id);
            }
            // 金額　と　取引年月日　の実装検討要todo　▲

            $data = $data->groupBy('LTD.id','LTD.circular_id','LTD.mst_company_id','LTD.sender_name','LTD.sender_email','LTD.file_name','LTD.file_size','LTD.keyword','LTD.request_at','LTD.completed_at','LTD.title',
                'LTD.create_at','LTD.destination_name','LTD.destination_email','LTD.sender_name','LTD.sender_email', 'LTD.create_user', 'LTD.timestamp_automatic_flg');

            $data = $data->orderBy($orderBy,$orderDir)->paginate($limit)->appends(request()->input());

            foreach($data as $key=>$val){
                $val->circular_attachment_name_string = '';
                $ValTmpQuery= DB::table('longterm_index as li')
                    ->leftJoin('mst_longterm_index as mli','li.longterm_index_id','=','mli.id')
                    ->where('li.circular_id',$val->circular_id);
                 if($val->upload_status){
                     $ValTmpQuery=$ValTmpQuery->where('li.long_term_document_id',$val->id);
                 }
                $val->circular_index=$ValTmpQuery->select('mli.index_name','mli.data_type','mli.id','li.longterm_index_id','li.circular_id','li.string_value','li.num_value','li.date_value')->get();
                $val->circular_index->each(function ($item, $key) {
                    if(!empty($item)){
                        if(isset($item->num_value)){
                            $item->num_value = floatval($item->num_value);
                        }
                    }
                });
                if(!empty($val->circular_attachment_json) ){
                    $arrTemp = json_decode($val->circular_attachment_json,true);
                    foreach ($arrTemp as $k=>$v){
                        if(!isset($v['type'])){
                            if(!DownloadRequestUtils::handlerAttachment($val->circular_id,$user->mst_company_id,$v['server_url'])){
                                unset($arrTemp[$k]);
                            }
                        }
                    }
                    $val->circular_attachment_name_string = implode("<br />",array_column($arrTemp,'file_name'));
                }else{
                    $val->circular_attachment_json = '';
                }
                $data[$key] = $val;
            }

            $itemsFolder = LongTermFolderUtils::getLongTermFolderTree($user->mst_company_id);
            $companyLimit = DB::table('mst_limit')
                ->where('mst_company_id', $user->mst_company_id)
                ->first();
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->raiseDanger(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
        }

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', strtolower($orderDir)=="asc"?"desc":"asc");
        $this->assign('itemsCircular', $data);
        $this->assign('longTermIndex', $longTermIndex);
        $this->assign('longTermIndexall', $longTermIndexall);
        $this->assign('itemsFolder', $itemsFolder);
        $this->assign('company', $companyData);
        $this->assign('folder', $folder);
        $this->assign('company_limit',$companyLimit);

        $this->assign('isShowAutomaticUpdate', $isShowAutomaticUpdate);
        $this->assign('isShowLongTermindex', $isShowLongTermindex);
        $this->assign('longTermStorageOptionFlg', $longTermStorageOptionFlg);
        $this->assign('rowIndexCnt', count($indexes));
        $this->assign('indexes', $indexes);
        $this->assign('isShowDate', $isShowDate);
        $this->assign('loginUser', $user);

        $this->setMetaTitle('長期保管文書一覧');
        return $this->render('LongTerm.longterm');

    }

    /**
     * @param $cids
     * @param $user
     */
    public function deletes($cids, $user){

        try{
            // ================
            // ファイル削除
            // ================
            $longTermDocumentDatas = DB::table('long_term_document')
                ->where('id', $cids)
                ->where('mst_company_id', $user->mst_company_id)
                ->select(['id','circular_id','upload_status','upload_id'])
                ->get();
            $type='s3';
            if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_K5){
                $type='k5';
            }
            foreach($longTermDocumentDatas as $longTermDocumentData){
                // S3サーバのフォルダパス編集
                $s3path= config('filesystems.prefix_path') . '/' .config('app.s3_storage_root_folder') . '/' . config('app.pac_app_env') . '/' . config('app.pac_contract_app')
                    . '/' . config('app.pac_contract_server') . '/' . $user->mst_company_id;
                $path =  $s3path. '/' .($longTermDocumentData->upload_status? ('upload_'.$longTermDocumentData->upload_id) :$longTermDocumentData->circular_id);

                // 存在する場合
                if ( Storage::disk($type)->exists($path)){
                    // フォルダパス下のファイルを取得して、
                    $file_names = Storage::disk($type)->files($path);
                    foreach($file_names as $file_name){
                        if (Storage::disk($type)->exists($file_name)){
                            Storage::disk($type)->delete($file_name);
                            Log::info("s3ファイルが削除しました：".$file_name);
                        }
                    }
                }
            }

            // ================
            // DB削除
            // ================
            $long_term_documents = DB::table('long_term_document')->whereIn('id', $cids)->select(['id','file_name','upload_status','upload_id','circular_id'])->get();
            $filenames = [];
            $documentIds=[];
            $upload_ids=[];
            $circular_ids=[];
            foreach ($long_term_documents as $item){
                $filenames[]= $item->file_name;
                $documentIds[]= $item->id;
                if($item->upload_status){
                    $upload_ids[]=$item->upload_id;
                }
                if($item->circular_id){
                    $circular_ids[]=$item->circular_id;
                }
            }
            Session::flash('file_names', $filenames);
            DB::beginTransaction();
            $arrDocumentAttachment = DB::table("long_term_document")->whereIn("id",$documentIds)
                ->where("circular_attachment_json",'<>','')->select("circular_attachment_json")->get();
            DB::table('long_term_department')->whereIn('long_term_document_id',$documentIds)->delete();
            DB::table('long_term_document')->whereIn('id', $documentIds)->delete();
            DB::table('upload_data')->whereIn('id', $upload_ids)->delete();
            DB::table('long_term_circular')->whereIn('id',$circular_ids)->delete();
            DB::table('long_term_circular_operation_history')->whereIn('long_term_document_id',$documentIds)->delete();
            DB::table('long_term_text_info')->whereIn('long_term_document_id',$documentIds)->delete();
            DB::table('long_term_stamp_info')->whereIn('long_term_document_id',$documentIds)->delete();
            DB::table('long_term_document_comment_info')->whereIn('long_term_document_id',$documentIds)->delete();
            DB::table('long_term_circular_user')->whereIn('circular_id',$circular_ids)->delete();

            $arrPath = [];
            foreach($arrDocumentAttachment as $attachment){
                $arrItem = json_decode($attachment->circular_attachment_json,true);
                foreach ($arrItem as $item){
                    if(!isset($arrPath[$item['server_url']])){
                        $arrPath[$item['server_url']] = true;
                    }
                }

            }
            if(!empty($arrPath)){
                foreach(array_keys($arrPath) as $strPath){
                    if($strPath == '' || mb_strlen($strPath,'UTF-8') < 30){
                        continue;
                    }
                    Storage::disk(config('app.pac_app_env') == EnvApiUtils::ENV_FLG_K5 ? 'k5':'s3')->delete($strPath);
                }
            }
            DB::commit();

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::warning($ex->getMessage().$ex->getTraceAsString());
            $this->raiseWarning(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
            return;
        }
        $this->raiseSuccess(__('message.success.delete_long_term_document'));
    }

    /**
     * 自動タイムスタンプ更新
     * @param $cids
     * @param $user
     * @param $automaticOnFlg
     */
    public function automaticUpdate($cids, $user, $automaticOnFlg)
    {
        try{
            $ids = [];

            $long_term_circulars = DB::table('long_term_document')->select('id','circular_id','timestamp_automatic_flg','add_timestamp_automatic_date')
                ->whereIn('id',$cids)
                ->get()->keyBy('id');

            if ($long_term_circulars){
                foreach ($long_term_circulars as $id => $long_term_circular){
                    //文書を完了した際にタイムスタンプを付与していた場合
                    $is_time_stamp =  DB::table('circular_document as cd')
                        ->join('time_stamp_info as tsi','tsi.circular_document_id','cd.id')
                        ->where('cd.circular_id',$long_term_circular->circular_id)
                        ->count();

                    if ($is_time_stamp || ($long_term_circular->timestamp_automatic_flg ||$long_term_circular->add_timestamp_automatic_date )){
                        $ids[] = $id;
                    }elseif ($automaticOnFlg==1){
                        $this->raiseDanger('タイムスタンプが付与されていない文書には「自動更新：ON」にすることはできません。');
                        return ;
                    }
                }
            }
            DB::beginTransaction();

            DB::table('long_term_document')
                ->whereIn('id', $ids)
                ->whereNull('add_timestamp_automatic_date')
                ->update([
                    'timestamp_automatic_flg' => $automaticOnFlg,
                    'update_user' => $user->email,
                    'update_at' => Carbon::now(),
                    'add_timestamp_automatic_date' => $automaticOnFlg == "1" ? DB::raw("`completed_at`") : null,
                    'is_del' => 0,
                ]);

            DB::table('long_term_document')
                ->whereIn('id', $ids)
                ->whereNotNull('add_timestamp_automatic_date')
                ->update([
                    'timestamp_automatic_flg' => $automaticOnFlg,
                    'update_user' => $user->email,
                    'update_at' => Carbon::now(),
                    'is_del' => 0,
                ]);
            DB::commit();
            $this->raiseSuccess($automaticOnFlg == "1" ? 'ON自動タイムスタンプ更新に成功しました。' : 'OFF自動タイムスタンプ更新に成功しました。');

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            $this->raiseWarning(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
    }



    /**
     * 回覧プレビューを取得
     * @param Request $request
     * @return mixed
     */
    public function getPreview(Request $request)
    {
        try{
            // 入力ID取得
            $id = $request->get('id');
            $user = $request->user();
            // 回覧完了日時
            $finishedDateKey = $request->get('finishedDate');
            // 当月
            if (!$finishedDateKey) {
                $finishedDate = '';
            } else {
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            }
            $longTermData=DB::table('long_term_document')->where('id',$id)->first();
            if(!$longTermData->upload_status) {
                $circular = DB::table("circular$finishedDate")->where('id', $longTermData->circular_id)->first();
                if (!$circular || !$circular->id) {
                    return response()->json(['status' => false, 'message' => [__('message.false.circular_get')]]);
                }
                $firstDocument = DB::table("circular_document$finishedDate")
                    ->where('circular_id', $longTermData->circular_id)
                    ->orderBy('id')->first();

                if ($firstDocument && $firstDocument->confidential_flg
                    && $firstDocument->origin_edition_flg == config('app.pac_contract_app')
                    && $firstDocument->origin_env_flg == config('app.pac_app_env')
                    && $firstDocument->origin_server_flg == config('app.pac_contract_server')
                    && $firstDocument->create_company_id == $user->mst_company_id) {
                    $first_page_data = AppUtils::decrypt($circular->first_page_data);
                } else if ($firstDocument && !$firstDocument->confidential_flg) {
                    $first_page_data = AppUtils::decrypt($circular->first_page_data);
                } else {
                    $noPreviewPath = public_path() . "/images/no-preview.png";
                    $data = file_get_contents($noPreviewPath);
                    $base64 = 'data:image/png;base64,' . base64_encode($data);
                    $first_page_data = $base64;
                }
            }else{
                $circular= DB::table('upload_data as ud')->leftJoin('long_term_document as ltd','ud.id','=','ltd.upload_id')
                    ->where('ud.id',$longTermData->upload_id)
                    ->select(['ud.id','ud.first_img_review as first_page_data','ltd.upload_id'])->first();
                $first_page_data = AppUtils::decrypt($circular->first_page_data);
            }

            return response()->json(['status' => true,
                'first_page_data' => $first_page_data]);
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.long_term_preview')]]);
        }
    }

    /**
     * キーワード更新
     * @param Request $request
     * @return mixed
     */
    public function updateDocument(Request $request){
        $user = $request->user();
        $document = $request->get('detail');

        try{
            DB::beginTransaction();
            DB::table('long_term_document')->where('id', $document['id'])->update([
                'keyword' => $document['keyword'],
                'update_user' => $user->email,
                'update_at' => Carbon::now(),
                'is_del' => 0,
            ]);
            DB::commit();
            return response()->json(['status' => true,'message' => [__('message.success.long_term.update_keyword')]]);
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.long_term.update_keyword')]]);
        }
    }

    /**
     * 文書削除
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request) {
        $user = $request->user();
        $document = $request->get('detail');
        try{
            $this->deletes([$document['id']], $user);
            return response()->json(['status' => true, 'message' => [__('message.success.long_term.delete_circular')]]);
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.long_term.delete_circular')]]);
        }
    }

    /**
     * 添付ファイルをダウンロード
     * @param Request $request
     * @return mixed
     */
    public function downloadAttachment(Request $request)
    {
        try{
            $user     = $request->user();
            $fileName = "download-" . time() . ".zip";
            // ダウンロードJob登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadRequestUtils', 'getDownloadAttachment', $fileName,
                $user, $request->all()
            );
            if(!($result === true)){
                return response()->json(['status' => false,
                    'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }
            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                    ['attribute' => $fileName])]
            ]);
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $ex->getMessage()])]]);
        }
    }

    /**
     * ファイルダウンロード
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function download(Request $request)
    {
        // 入力ファイル名取得
        $reqFileName = $request->get('fileName', '');
        // ID取得
        $ids = $request->get('ids', []);
        $checkHistory = $request->get('checkHistory', false);

        $circularIds=[];
        // 回覧ID取得
        $circularIdsStdClass = DB::table('long_term_document')
            ->whereIn('id', $ids)
            ->select('circular_id')->get()->toArray();
        for($i = 0; $i < count($circularIdsStdClass); $i++){
            $circularIds[$i] = $circularIdsStdClass[$i]->circular_id;
        }

        // ダウンロード予約処理
        return DownloadRequestUtils::reserveDownload($circularIds, $reqFileName, '', $checkHistory, '1',$ids);
    }

    /**
     * 文書の移動
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFolder(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user      = $request->user();
            $folder_id = $request->get('folder_id',0);
            $long_term_id = $request->get('ids');

            DB::table('long_term_document')->whereIn('id',$long_term_id)
                ->update([
                    'long_term_folder_id' => $folder_id,
                    'update_user' => $user->email,
                    'update_at' => Carbon::now()
                ]);

            return response()->json(['status' => true, 'message' => [__('message.success.long_term.long_term_folder_move')]]);
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.long_term.long_term_folder_move')]]);
        }
    }
    public function LongTermUpload(Request $request)
    {
        $user = Auth::user();
        $file=$request->file('file');
        $fileExe=['doc','docx','pdf','xls','xlsx'];
        $stored_basename = hash('SHA256', rand(). AppUtils::getUnique()). '.pdf';
        try {
            if ($request->hasFile('file') ) {
                $realExe=$file->getClientOriginalExtension();
                if(!in_array($realExe,$fileExe)){
                    return response()->json(['status'=>false,'message'=>['アップロード可能な形式は、doc、docx、pdf、xls、xlsxのみです。']]);
                }
                $realName=$file->getClientOriginalName();
                $pdf= LongTermDocumentUtils::ToPdfAndImg($user,$file,$stored_basename);
                $folder=storage_path('app/'.pathinfo($pdf)['dirname'].'/'.pathinfo($pdf)['filename']);
                if (file_exists($folder)){
                    //小さいサムネイル
                    $thumb=$folder.'/1.jpg';
                    //大さいサムネイル
                    $thumbnail=$folder.'/1-thumbnail.jpg';
                    $file_size=filesize(storage_path('app/'.$pdf));
//                    $thumbBase=LongTermDocumentUtils::getFolderToBase($thumb);
                    $thumbnailBase=LongTermDocumentUtils::getFolderToBase($thumbnail);
                    // put S3

                    $thumbFile=File::get($thumb);
                    $thumbnailFile=File::get($thumbnail);
                    $stampApiClient = UserApiUtils::getStampApiClient();
                    $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
                    $username = $user->family_name.' '. $user->given_name;
                    $signatureReason = $this->getSignatureReason($username, $user->email);
                    $result = $stampApiClient->post("signatureAndImpress", [
                        RequestOptions::JSON => [
                            'signature' => 1,
                            'data' => [
                                [
//                                    'pdf_data' => AppUtils::decrypt($pdfBase),
                                    'pdf_data' => LongTermDocumentUtils::getFileToBase(storage_path('app/'.$pdf)),
                                    'append_pdf_data' => null,
                                    'stamps' => [],
                                    'texts' => [],
                                    'usingTas'=>0,
                                    'usingDTS'=>true
                                ]
                            ],
                            'signatureReason' => $signatureReason,
                            'signatureKeyFile' => $company->certificate_flg?$company->certificate_destination:null,
                            'signatureKeyPassword' => $company->certificate_flg?$company->certificate_pwd:null,
                            'documentTimestampSignatureReason' => $this->getDTSSignatureReason()
                        ]
                    ]);
                    $resData = json_decode((string)$result->getBody());

                    if ($result->getStatusCode() == 200) {

                        $upload=  UploadData::create([
                            'upload_data'=>AppUtils::encrypt($resData->data[0]->pdf_data),
                            'first_img_review'=>$thumbnailBase,
                            'file_size'=>$file_size,
                        ]);
                        $path=config('filesystems.prefix_path') . '/' .config('app.s3_storage_root_folder') . '/' . config('app.pac_app_env') . '/' . config('app.pac_contract_app')
                            . '/' . config('app.pac_contract_server') . '/' . $user->mst_company_id;
                        $s3path =  $path. '/' . 'upload_'.$upload->id;

                       if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_AWS){
                           $isFolderExist = Storage::disk('s3')->exists($s3path);
                           if(!$isFolderExist){
                               Storage::disk('s3')->makeDirectory($s3path);
                           }
                           Storage::disk('s3')->put($s3path.'/'.$stored_basename, base64_decode($resData->data[0]->pdf_data), 'pub');
                           Storage::disk('s3')->put($s3path.'/'.$thumb, $thumbFile, 'pub');
                           Storage::disk('s3')->put($s3path.'/'.$thumbnail, $thumbnailFile, 'pub');
                        }else if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_K5){
                           $isFolderExist = Storage::disk('k5')->exists($s3path);
                           if(!$isFolderExist){
                               Storage::disk('k5')->makeDirectory($s3path);
                           }
                         Storage::disk('k5')->put($s3path.'/'.$stored_basename, base64_decode($resData->data[0]->pdf_data));
                           Storage::disk('k5')->put($s3path.'/'.$thumb, $thumbFile);
                           Storage::disk('k5')->put($s3path.'/'.$thumbnail, $thumbnailFile);
                       }

                        $data=['upload_id'=>$upload->id,'file_name'=>$realName];
                        return response()->json(['status'=>true,'data'=>$data,'message'=>['ファイルアップロード成功']]);


                    } else {
                        Log::debug("Update circulars response body: " . $result->getBody());
                        return response()->json(['status'=>false,'data'=>[],'message'=>['ファイルアップロード失敗']]);
                    }

                }
            }
        }catch (\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status'=>false,'data'=>[],'message'=>['ファイルアップロード失敗']]);
        }
    }
    public function saveLongTermDocument(Request $request)
    {
        $user = Auth::user();
        $indexes = $request->input('indexes',[]);
        $keywords=$request->input('keyword');
        $upload_id=$request->input('upload_id');
        $file_name=$request->input('file_name');
        $folder_id=$request->input('folder_id')?:0;
        $file_name=strpos($file_name,'.pdf')?$file_name:str_replace('.','_',$file_name).'.pdf';
        try {
            DB::beginTransaction();
            $upload_data=UploadData::where('id',$upload_id)->first();
            $long_term_document_id=DB::table('long_term_document')->insertGetId([
                'circular_id'           =>      0,
                'mst_company_id'        =>      $user->mst_company_id,
                'sender_name'           =>      '',
                'sender_email'          =>      '',
                'destination_name'      =>      '',
                'destination_email'     =>      '',
                'file_name'             =>      $file_name,
                'file_size'             =>      $upload_data->file_size,
                'keyword'               =>      $keywords,
                'request_at'            =>      Carbon::now(),
                'completed_at'          =>      Carbon::now(),
                'title'                 =>      $file_name,
                'create_user'           =>      $user->email,
                'update_user'           =>      null,
                'create_at'             =>      Carbon::now(),
                'update_at'             =>      Carbon::now() ,
                'upload_status'         =>      1 ,
                'upload_id'             =>      $upload_id,
                'long_term_folder_id'   =>      $folder_id
            ]);
            $this->setLongTermIndex($long_term_document_id,$indexes,$user);
            DB::commit();
            return response()->json(['status'=>true,'message'=>['アップロード処理に成功しました。']]);
        }catch (\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status'=>false,'message'=>['アップロード処理に失敗しました。']],500);
        }
    }

    private function setLongTermIndex($id,$index,$user)
    {
        $indexes = Arr::where($index, function ($value, $key) {
            return $value['longterm_index_id']!='' && $value['value']!='';
        });
        $data=[];
        foreach ($indexes  as $k=>$value) {
            if(strlen($value['value'])>128){
                $this->raiseDanger('長期保管インデックス文字列の長さは128ビット以上に設定できません。');
                throw new \Exception('長期保管インデックス文字列の長さは128ビット以上に設定できません。');
            }
            $tmp= [
                'mst_company_id' => $user->mst_company_id,
                'mst_user_id' => $user->id,
                'circular_id' => 0,
                'long_term_document_id' => $id,
                'longterm_index_id' => $value['longterm_index_id'],
                'create_at' => Carbon::now(),
                'create_user' => $user->family_name . $user->given_name,
                'num_value'=>$value['type'] === LongtermIndexUtils::NUMERIC_TYPE ?str_replace(',', '', $value['value']):0,
                'string_value'=>$value['type'] === LongtermIndexUtils::STRING_TYPE ?$value['value']:null,
                'date_value'=>$value['type'] === LongtermIndexUtils::DATE_TYPE ?Carbon::parse( $value['value']??''):null
            ];
            $data[]=$tmp;
        }
        DB::table('longterm_index')->insert($data);
    }
    private function getSignatureReason($username, $email){
        return sprintf("%s（%s）により%sに署名されています。", $username, $email, Carbon::now()->format("Y-m-d H:i:s.u"));
    }

    private function getShachihataSignatureReason(){
        return sprintf("Shachihata Cloudにより%sに署名されています。", Carbon::now()->format("Y-m-d H:i:s.u"));
    }

    private function getDTSSignatureReason(){
        return sprintf("MIND Timestamp Service DiaStamp A2E01により%sに署名されています", Carbon::now()->format("Y-m-d H:i:s.u"));
    }
}
