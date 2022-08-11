<?php

namespace App\Http\Controllers\Expense;

use App\Http\Utils\LongtermIndexUtils;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use DB;
use App\Models\Department;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use League\Flysystem\Filesystem;
use Session;
use Carbon\Carbon;
use App\Http\Utils\DownloadRequestUtils;
use App\Http\Controllers\AppBaseController;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Response;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpWord;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;

class Exp_m_form_expController extends AdminController
{

    private $model;
    private $department;
    
    public function __construct(Department $department)
    {
        parent::__construct();
        $this->department = $department;
        $this->ExpenseDirectory = config('app.pac_app_env') . '/' . config('app.pac_contract_app') 
            . '/' . config('app.pac_contract_server');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        $action = $request->get('action', '');
        $arrHistory = null;

        // get list user
        $limit = AppUtils::normalizeLimit($request->get('limit'), config('app.page_limit'));
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'create_at';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir') : 'desc';
        $arrOrder = ['id' => 'p.id','form_code' => 'P.form_code','form_name' => 'P.form_name','validity_period_from' => 'P.validity_period_from',];
        $filter_code = $request->get('form_code', '');
        $filter_name = $request->get('form_name', '');
        $filter_validity_period_from = $request->get('validity_period_from', '');
        $filter_validity_period_to = $request->get('validity_period_to', '');
        $filter_form_describe = $request->get('form_describe', '');

        



        $arrHistory = DB::table('eps_m_form as P')
            ->orderBy(isset($arrOrder[$orderBy]) ? $arrOrder[$orderBy] : 'P.create_at', $orderDir)
            ->select(DB::raw('P.mst_company_id, P.form_code, P.form_name, P.validity_period_from,P.validity_period_to,P.form_describe'))
            ->where('P.mst_company_id', $user->mst_company_id);

        $where = ['1=1'];
        $where_arg = [];

        if($filter_code) {
            $where[] = 'INSTR(P.form_code, ?)'; 
            $where_arg[] = $filter_code;
        }
        if($filter_name) {
            $where[] = 'INSTR(P.form_name, ?)';
            $where_arg[] = $filter_name;
        }

        if($filter_form_describe){
            $where[] = 'INSTR(P.form_describe, ?)';
            $where_arg[] = $filter_form_describe;
        }

        if($filter_validity_period_from) {
            $where[] =  'P.validity_period_from >= ?';
            $where_arg[] = $filter_validity_period_from;
        }

        if($filter_validity_period_to) {
            $where[] = 'P.validity_period_to <= ?';
            $where_arg[] = $filter_validity_period_to;
        }
      


        //後に対応utilsから取る(2は精算申請用)
        $where[] = 'INSTR(P.form_type, ?)';
        $where_arg[] = 2;

        $arrHistory = $arrHistory->whereRaw(implode(" AND ", $where), $where_arg);
        $arrHistory = $arrHistory->paginate($limit)->appends(request()->input());

        $orderDir = strtolower($orderDir) == "asc" ? "desc" : "asc";

        //目的を抽出
        $purpose = DB::table('eps_m_purpose')
            ->where('mst_company_id', $user->mst_company_id)
            ->get();
        //用途抽出
        $wtsm  = DB::table('eps_m_wtsm')
            ->where('mst_company_id', $user->mst_company_id)
            ->get();

        $form_purpose=array();
        $f_purposes  = DB::table('eps_m_form_purpose')
            ->where('mst_company_id', $user->mst_company_id)
            ->get();

        foreach($f_purposes as $f_purpose){
            $form_purpose[$f_purpose->form_code]=$f_purpose;
        }
        
        //様式毎の用途　eps_m_form_wtsm
        $form_wtsm=array();
        $f_wtsms = DB::table('eps_m_form_wtsm')
            ->where('mst_company_id', $user->mst_company_id)
            ->get();
        
        foreach($f_wtsms as $f_wtsm){
            $form_wtsm[$f_wtsm->form_code]=$f_wtsm;
        }
  
        $this->assign('form_wtsm', $form_wtsm);
        $this->assign('form_purpose', $form_purpose);

        $this->assign('wtsm', $wtsm);
        $this->assign('purpose', $purpose);
        $this->assign('arrHistory', $arrHistory);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);

        $this->setMetaTitle("様式画面");
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        return $this->render('Expense.expense_exp_index');
    }

    public function post(Request $request){
        $login_user = $request->user();
        DB::beginTransaction();
        try{
            //form_code重複判定
            $check=DB::table('eps_m_form')
            ->where('form_code',$request->get('form_code'))
            ->where('mst_company_id', $login_user->mst_company_id)
            ->first();

            if(!($check==null)){
                return response()->json(['status' => false, 'message' =>[__("様式コードが重複しています。")]]);
            }

            $purpose=$request->get('purpose',[]);
            $wtsm=$request->get('wtsm',[]);
    
            if(!$request->has('purpose') || $wtsm==[]){
                DB::rollBack();
                return response()->json(['status' => false, 'message' =>[__("目的は必ず選択してください。")]]);
            }
    
            if(!$request->has('wtsm') || $purpose==[]){
                DB::rollBack();
                return response()->json(['status' => false, 'message' =>[__("用途は必ず選択してください。")]]);
            }
            //$disk = Storage::disk('S3'); //S3宣言
            Log::info('様式アップロード開始');
            Log::info($request);
            $file = $request->file('uploadFile');
            $originName = $file->getClientOriginalName();
            $fileextension = $file->getClientOriginalExtension();
            $fileExe=['xlsx'];
            if(!in_array($fileextension,$fileExe)){
                return response()->json(['status'=>false,'message'=>[__('アップロード可能な形式はxlsxのみです。')]]);
            }

            $userName = $login_user->family_name . $login_user->given_name;
            $altFileName = explode(".", (microtime(true) . ""))[0] . '_' .$login_user->mst_company_id .'_1'. '.' . $fileextension;
            
            //S3テンプレート用ディレクトリ存在確認
            $s3path = config('app.s3_storage_root_folder');
            $isFolderExist = Storage::disk('s3')->exists($s3path);
            if (!$isFolderExist) {
                Storage::disk('s3')->makeDirectory($s3path);
                Storage::disk('s3')->makeDirectory($s3path.'/expense');

                $s3path = $s3path.'/'.'expense/'. $this->ExpenseDirectory . $login_user->mst_company_id;
                Storage::disk('s3')->makeDirectory($s3path);
            }else{
                $s3path = $s3path.'/'.'expense/'. $this->ExpenseDirectory;
                if (!$isFolderExist){
                    Storage::disk('s3')->makeDirectory($s3path);
                    Storage::disk('s3')->makeDirectory($s3path.'/'. $login_user->mst_company_id);

                    $s3path = $s3path.'/'.$login_user->mst_company_id;
                }else{
                    $s3path = $s3path.'/'.$login_user->mst_company_id;
                    $isFolderExist = Storage::disk('s3')->exists($s3path);
                    if (!$isFolderExist){
                        Storage::disk('s3')->makeDirectory($s3path);
                    }
                }
            }
            DB::table('eps_m_form')
                    ->insert(
                    [
                        'mst_company_id' => $login_user->mst_company_id,
                        'form_code' => $request->get('form_code'),
                        'form_name' => $request->get('form_name'),
                        'form_type'=> 2,
                        'form_describe' => $request->get('form_describe',''),
                        'total_amt_min' => $request->get('total_amt_min'),
                        'total_amt_max' => $request->get('total_amt_max'),
                        'items_max' => $request->get('items_max'),
                        'validity_period_from' => $request->get('validity_period_from'),
                        'validity_period_to' => $request->get('validity_period_to'),
                        'remarks' => $request->get('remarks',''),
                        's3_path' => $s3path,
                        's3_file_name' => $altFileName,
                        'origin_file_name' => $originName,
                        'create_at' => Carbon::now(),
                        'create_user' => $userName,
                        'update_at' => Carbon::now(),
                        'update_user' => $userName,
                    ]);
            

            $purpose = explode(",", $purpose);
            foreach ($purpose as $value) {
                DB::table('eps_m_form_purpose')
                    ->insert([
                        'mst_company_id' => $login_user->mst_company_id,
                        'purpose_name' => $value,
                        'form_code' => $request->get('form_code'),
                        'create_at' => Carbon::now(),
                        'create_user' => $userName,
                        'update_at' => Carbon::now(),
                        'update_user' => $userName,
                    ]);
            }
                //用途
            $wtsm = explode(",", $wtsm);
            foreach ($wtsm as $value) {
                DB::table('eps_m_form_wtsm')
                ->insert([
                    'mst_company_id' => $login_user->mst_company_id,
                    'wtsm_name' =>$value,
                    'form_code' => $request->get('form_code'),
                    'create_at' => Carbon::now(),
                    'create_user' => $userName,
                    'update_at' => Carbon::now(),
                    'update_user' => $userName,
                ]);
            }


            if($request->has('form_code_adv') && !($request->get('form_code_adv') == "undefined") && !($request->get('form_code_adv') == "")){
                
                $relation_data=DB::table('eps_m_form')
                ->where('form_code',$request->get('form_code_adv'))
                ->where('mst_company_id',$login_user->mst_company_id)
                ->first();
            
                if($request->get('total_amt_min')> $relation_data->total_amt_min){
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' =>[__("最小金額を $relation_data->total_amt_min より小さい数値で設定してください。")]]);
                }
                    
                if($request->get('total_amt_max')< $relation_data->total_amt_max){
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' =>[__("最大金額を $relation_data->total_amt_max より大きい数値で設定してください。")]]);
                }
            
                if($request->get('items_max')< $relation_data->items_max){
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' =>[__("最大明細行数を$relation_data->items_max より大きい数値に設定してください")]]);
                }
                    
                if($request->get('validity_period_from')> $relation_data->validity_period_from){
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' =>[__("開始日を$relation_data->validity_period_from より早い日付けを設定してください。")]]);
                }
            
                if($request->get('validity_period_to')< $relation_data->validity_period_to){
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' =>[__("終了日を$relation_data->validity_period_to より後の日付を選択してください。")]]);
                }
        
                $rele_purpose=DB::table('eps_m_form_purpose')
                ->where('mst_company_id', $login_user->mst_company_id)
                ->where('form_code',$request->get('form_code_adv'))
                ->get();
        
                foreach($rele_purpose as $rele_pur){
        
                    (bool)$hantei=in_array($rele_pur->purpose_name, $purpose);
                    if(!$hantei){
                        DB::rollBack();
                        return response()->json(['status' => false, 'message' =>[__("$rele_pur->purpose_name は必ず選択してください。")]]);
                    }
                }
        
                $rele_wtsm=DB::table('eps_m_form_wtsm')
                ->where('mst_company_id', $login_user->mst_company_id)
                ->where('form_code',$request->get('form_code_adv'))
                ->get();
        
                foreach($rele_wtsm as $rele_wtsm){
        
                    (bool)$hantei=in_array($rele_wtsm->wtsm_name, $wtsm);
                    if(!$hantei){
                        DB::rollBack();
                        return response()->json(['status' => false, 'message' =>[__("$rele_wtsm->wtsm_name は必ず選択してください。")]]);
                    }
                }
        
                DB::table('eps_m_form_relation')
                ->insert([
                    'mst_company_id' => $login_user->mst_company_id,
                    'form_code' => $request->get('form_code'),
                    'relation_form_code' => $request->get('form_code_adv'),
                    'create_at' => Carbon::now(),
                    'create_user' => $userName,
                    'update_at' => Carbon::now(),
                    'update_user' => $userName,
                ]);
            }

            if (in_array($fileextension, ['xlsx', 'xls'])) {
                $extension ='0';
                //$move = $file->storeAs('template', $name);
                //
                $reader = new XlsxReader();
                $spreadsheet = $reader->load($file);
                // 読み込むシートを指定(1シート目)
                $sheet = $spreadsheet->getSheet(0); 
                //行番号、ループ用
                $row = 1; 

                $placeholderList = array();

                //セル番地とセルの情報を取得
                foreach ($sheet->getRowIterator() as $eachrow) {
                    foreach($sheet->getColumnIterator() as $column)
                    {
                        $column->getColumnIndex() . $eachrow->getRowIndex();
                        $sheetData=$sheet->getCell($column -> getColumnIndex() . $row )->getValue();
                        //セル内にデータがある場合かつ、${で始まるデータ(プレースホルダー)とセル番地を保存
                        if($sheetData){
                            //対象のデータである「「${」から始まるデータ」ことを確認
                            $find = '${';
                            if (strpos($sheetData, $find) !== false) {
                                $phEnd = '}';
                                $start_position = strpos($sheetData, $find);
                                $phLength = strpos($sheetData, $phEnd) - $start_position + 1;
                                $placeholder = substr($sheetData, $start_position, $phLength);
                                $placeholderList += array($column->getColumnIndex() . $eachrow->getRowIndex() => $placeholder);
                            }
                           
                        }
                    }
                    $row++;
                }
                
                //テスト用ファイル格納処理
                //$move = $file->storeAs('template', $altFileName);
                
                //S3アップロード処理
                Storage::disk('s3')->putfileAs($s3path.'/', $file, $altFileName, 'pub');
                //保存したS3完全URLの取得
                $s3url = Storage::disk('s3')->url($s3path.'/'.$altFileName);
                Log::info('様式保存URL'. $s3url);
                
                //form_code,form_type Utils
                
                foreach ($placeholderList as $cell =>$value) {
                
                    $check=$this->placeholder_check($value);
                    
                    if($check==false){
                        return response()->json(['status' => false, 'message' =>[__('使用できないプレースホルダがあります。')]]);
                    }
                    DB::table('expense_placeholder_data')
                        ->insert([
                            'mst_company_id' =>$login_user->mst_company_id,
                            'eps_m_form_code' =>$request->get('form_code'),
                            'template_placeholder_name' => $value,
                            'cell_address' => $cell,
                            'create_at' => Carbon::now(),
                            'create_user' => $userName,
                            'update_at' => Carbon::now(),
                            'update_user' => $userName,
                        ]);
                }

                DB::commit();
                Log::info('様式アップロード完了');

                return response()->json(['status' => true, 'message' =>[__('様式の登録が完了しました。')]]);

            }elseif (in_array($fileextension, ['docx', 'doc'])) {
                $extension ='1';
                $contents = "";
                $zip = new \ZipArchive();

                if ($zip->open($file) === true) {
	                $xml = $zip->getFromName("word/document.xml");
	                if ($xml) {
		                $dom = new \DOMDocument();
		                $dom->loadXML($xml);
		                $paragraphs = $dom->getElementsByTagName("p");
		                foreach ($paragraphs as $p) {
			                $texts = $p->getElementsByTagName("t");
			                foreach ($texts as $t) {
				                $contents .= $t->nodeValue;
			                }
		                }
	                }
                }

                $contents_copy = $contents;
                //
                $find = '${';
                $placeholderList = array();
                
                $counter = substr_count($contents, $find);

                for($i =0; $i < $counter; $i++) {
                    $phEnd = '}';
                    $start_position = strpos($contents, $find);
                    $phLength = strpos($contents, $phEnd) - $start_position + 1;
                    $placeholder = substr($contents, $start_position, $phLength);
                    $placeholderList += array($i => $placeholder);
                    $contents = substr($contents, strpos($contents, $phEnd) + 1, strlen($contents) - $start_position );
                }

                //テスト用ファイル格納処理
                //$move = $file->storeAs('template', $altFileName);
                
                //S3アップロード処理
                
                Storage::disk('s3')->putfileAs($s3path.'/', $file, $altFileName, 'pub');
                
                //保存したS3完全URLの取得
                $s3url = Storage::disk('s3')->url($s3path.'/'.$altFileName);
                Log::info('テンプレート保存URL'. $s3url);

                foreach ($placeholderList as $value) {
                    
                    $check=$this->placeholder_check($value);

                    if($check==false){
                        return response()->json(['status' => false, 'message' =>[__('使用できないプレースホルダがあります。')]]);
                    }

                    DB::table('expense_placeholder_data')
                        ->insert([
                            'mst_company_id' =>$login_user->mst_company_id,
                            'eps_m_form_code' => $request->get('form_code'),
                            'template_placeholder_name' => $value,
                            'create_at' => Carbon::now(),
                            'create_user' => $userName,
                            'update_at' => Carbon::now(),
                            'update_user' => $userName,
                        ]);
                }


                DB::commit();
                Log::info('様式アップロード完了');

                return response()->json(['status' => true, 'message' =>[__('様式の登録が完了しました。')]]);
            } else {
                DB::rollBack();
                Log::error($ex->getMessage().$ex->getTraceAsString());
                return $this->raiseDanger(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
            }


        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->raiseDanger(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
        
    }

    public function delete(Request $request) {
        DB::beginTransaction();
        try {
            $requestCodes = $request->get('cids',[]);
            $user = $request->user();
            $res = 0;
            $count=0;
            $code=array();
            log::debug($requestCodes);
            foreach($requestCodes as $requestCode){
                log::debug($requestCode);
                $count++;
                $ck=$requestCode;
                $check=DB::table('eps_t_app')
                ->where('form_code', $ck)
                ->first();
                
                if($check==null){

                    $code[]=$ck;
                    $res ++;

                }
                
            }

            //経費精算用プレースホルダデータ
            DB::table('expense_placeholder_data')
                ->where('mst_company_id', $user->mst_company_id)
                ->whereIn('eps_m_form_code', $code)
                ->delete();

            DB::table('eps_m_form_purpose')
                ->where('mst_company_id', $user->mst_company_id)
                ->whereIn('form_code',$code)
                ->delete();

            DB::table('eps_m_form_wtsm')
                ->where('mst_company_id', $user->mst_company_id)
                ->whereIn('form_code',$code)
                ->delete();

            DB::table('eps_m_form_relation')
                ->where('mst_company_id', $user->mst_company_id)
                ->whereIn('form_code',$code)
                ->delete();

            DB::table('eps_m_form')
                ->where('mst_company_id', $user->mst_company_id)
                ->whereIn('form_code',$code)
                ->delete();

            DB::commit();
            Log::info('様式データ削除完了');
            /*DB::commit();
            return $this->sendResponse(true,'テンプレートファイル削除処理に成功しました。');*/
            if($count==$res){
                return response()->json(['status' => true, 'message' => [__('様式の削除に成功しました。')]]);
            }else{
                return response()->json(['status' => false, 'message' =>[ __('削除できないレコードが存在します。')]]);
            }
               
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->raiseDanger(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
    }

    public function show($form_code)
    {
        $user   = \Auth::user();

        $item = DB::table('eps_m_form')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('form_code',$form_code)
            ->first();

         $check=DB::table('eps_m_form_relation')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('form_code',$form_code)
            ->first();
        
        $relation=false;

        if(!($check==null)){
            $relation=true;
        }
                    

        //様式毎の目的　eps_m_form_purpose

        $f_purpose  = DB::table('eps_m_form_purpose')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('form_code',$form_code)
            ->get();

        $pur_name=array();
        foreach($f_purpose as $pur){

            $pur_name[]=$pur->purpose_name;
        }

        $f_wtsm = DB::table('eps_m_form_wtsm')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('form_code',$form_code)
            ->get();

        $wtsm_name=array();
        foreach($f_wtsm as $wm){
            $wtsm_name[]=$wm->wtsm_name;
        }
        //目的抽出
        $purpose = DB::table('eps_m_purpose')
            ->where('mst_company_id', $user->mst_company_id)
            ->whereNotIn("purpose_name",$pur_name)
            ->get();
        //用途抽出
        $wtsm  = DB::table('eps_m_wtsm')
            ->where('mst_company_id', $user->mst_company_id)
            ->whereNotIn("wtsm_name",$wtsm_name)
            ->get();
        
        return response()->json(['status' => true, 'item' => $item, 'form_wtsm' => $wtsm_name, 'form_purpose' => $pur_name,'use_form_code' => $form_code ,'wtsm' => $wtsm,'purpose' => $purpose,'relation' => $relation]);

    }

    public function update(request $request)
    {
        Log::info($request);
        $user   = \Auth::user();
        $userName = $user->family_name . $user->given_name;
        $purpose=$request->get('purpose',[]);
        $wtsm=$request->get('wtsm',[]);

        if(!$request->has('purpose') || $wtsm==[]){
            return response()->json(['status' => false, 'message' =>[__("目的は必ず選択してください。")]]);
        }

        if(!$request->has('wtsm') || $purpose==[]){
            return response()->json(['status' => false, 'message' =>[__("用途は必ず選択してください。")]]);
        }


        
        $purpose = explode(",", $purpose);
        $wtsm = explode(",", $wtsm);

        $relation_check=DB::table('eps_m_form_relation')
        ->where('mst_company_id', $user->mst_company_id)
        ->where('form_code',$request->get('form_code'))
        ->first();

        
    
        if(!($relation_check==null)){

            $relation_data=DB::table('eps_m_form')
            ->where('form_code',$relation_check->relation_form_code)
            ->where('mst_company_id', $user->mst_company_id)
            ->first();
    
            if($request->get('total_amt_min')> $relation_data->total_amt_min){
                return response()->json(['status' => false, 'message' =>[__("最小金額を $relation_data->total_amt_min より小さい数値で設定してください。")]]);
            }
            
            if($request->get('total_amt_max')< $relation_data->total_amt_max){
                return response()->json(['status' => false, 'message' =>[__("最大金額を $relation_data->total_amt_max より大きい数値で設定してください。")]]);
            }
    
            if($request->get('items_max')< $relation_data->items_max){
                return response()->json(['status' => false, 'message' =>[__("最大明細行数を$relation_data->items_max より大きい数値に設定してください")]]);
            }
            
            if($request->get('validity_period_from')> $relation_data->validity_period_from){
                return response()->json(['status' => false, 'message' =>[__("開始日を$relation_data->validity_period_from より早い日付けを設定してください。")]]);
            }
    
            if($request->get('validity_period_to')< $relation_data->validity_period_to){
                return response()->json(['status' => false, 'message' =>[__("終了日を$relation_data->validity_period_to より後の日付を選択してください。")]]);
            }

            $rele_purpose=DB::table('eps_m_form_purpose')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('form_code',$request->get('form_code'))
            ->get();

            foreach($rele_purpose as $rele_pur){

                (bool)$hantei=in_array($rele_pur->purpose_name, $purpose);
                if(!$hantei){
                    return response()->json(['status' => false, 'message' =>[__("$rele_pur->purpose_name は必ず選択してください。")]]);
                }
            }

            $rele_wtsm=DB::table('eps_m_form_wtsm')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('form_code',$request->get('form_code'))
            ->get();

            foreach($rele_wtsm as $rele_wtsm){

                (bool)$hantei=in_array($rele_wtsm->wtsm_name, $wtsm);
                if(!$hantei){
                    return response()->json(['status' => false, 'message' =>[__("$rele_wtsm->wtsm_name は必ず選択してください。")]]);
                }
            }

        }

        try{
            DB::beginTransaction();

            DB::table('eps_m_form')
                ->where('mst_company_id', $user->mst_company_id)
                ->where('form_code',$request->get('form_code'))
                    ->update(
                    [
                        'mst_company_id' => $user->mst_company_id,
                        'form_name' => $request->get('form_name'),
                        'form_type'=> 2,
                        'form_describe' => $request->get('form_describe',''),
                        'total_amt_min' => $request->get('total_amt_min'),
                        'total_amt_max' => $request->get('total_amt_max'),
                        'items_max' => $request->get('items_max'),
                        'validity_period_from' => $request->get('validity_period_from'),
                        'validity_period_to' => $request->get('validity_period_to'),
                        'remarks' => $request->get('remarks',''),
                        'update_at' => Carbon::now(),
                        'update_user' => $userName,
                        
                    ]);

            DB::table('eps_m_form_purpose')
                    ->where('mst_company_id', $user->mst_company_id)
                    ->where('form_code',$request->get('form_code'))
                    ->delete();
    
            DB::table('eps_m_form_wtsm')
                    ->where('mst_company_id', $user->mst_company_id)
                    ->where('form_code',$request->get('form_code'))
                    ->delete();

            $purpose=$request->get('purpose',[]);
            $purpose = explode(",", $purpose);
            
                    
            foreach ($purpose as $value) {
                    DB::table('eps_m_form_purpose')
                        ->insert([
                            'mst_company_id' => $user->mst_company_id,
                            'purpose_name' => $value,
                            'form_code' => $request->get('form_code'),
                            'create_at' => Carbon::now(),
                            'create_user' => $userName,
                            'update_at' => Carbon::now(),
                            'update_user' => $userName,
                        ]);
            }

            //用途

            foreach ($wtsm as $value) {
                    DB::table('eps_m_form_wtsm')
                    ->insert([
                        'mst_company_id' => $user->mst_company_id,
                        'wtsm_name' =>$value,
                        'form_code' => $request->get('form_code'),
                        'create_at' => Carbon::now(),
                        'create_user' => $userName,
                        'update_at' => Carbon::now(),
                        'update_user' => $userName,
                    ]);
            }
            DB::commit();
            return response()->json(['status' => true, 'message' =>[__('様式の更新が完了しました。')]]);
        }catch (\Exception $ex) {
                DB::rollBack();
                Log::error($ex->getMessage().$ex->getTraceAsString());
                return $this->raiseDanger(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
    }


    public function check(request $request){

        $user   = \Auth::user();

        $check=DB::table('eps_m_form')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('form_code',$request->get('form_code_adv'))
            ->where('form_type',1)
            ->first();

        if($check){
            $f_purpose = DB::table('eps_m_form_purpose')
                ->where('mst_company_id', $user->mst_company_id)
                ->where('form_code',$request->get('form_code_adv'))
                ->get();

            $pur_name=array();
            foreach($f_purpose as $pur){

                $pur_name[]=$pur->purpose_name;
            }

            $f_wtsm = DB::table('eps_m_form_wtsm')
                ->where('mst_company_id', $user->mst_company_id)
                ->where('form_code',$request->get('form_code_adv'))
                ->get();

            $wtsm_name=array();
            foreach($f_wtsm as $wm){
                $wtsm_name[]=$wm->wtsm_name;
            }

            //目的抽出
            $purpose = DB::table('eps_m_purpose')
            ->where('mst_company_id', $user->mst_company_id)
            ->whereNotIn("purpose_name",$pur_name)
            ->get();
            //用途抽出
            $wtsm  = DB::table('eps_m_wtsm')
            ->where('mst_company_id', $user->mst_company_id)
            ->whereNotIn("wtsm_name",$wtsm_name)
            ->get();

            return response()->json(['status' => true, 'item' => $check, 'form_wtsm' => $wtsm_name, 'form_purpose' => $pur_name,'wtsm' => $wtsm,'purpose' => $purpose]);

        }else{

            return response()->json(['status' => false, 'message' =>[__('指定された様式コードは存在しません。')]]);

        }
    }

    private function placeholder_check($value){

        $array=['${会社名}','${部署名}','${名前}','${合計}'];

        $check=false;
    
        if(strpos("/$value/",'{用途') == true){
            $check=true;
        }

        if(strpos("/$value/","{日付") == true){
       
            $check=true;
        }

        if(strpos("/$value/",'{金額') == true){
            $check=true;
        }

        if(strpos("/$value/",'{内容') == true){
            $check=true;   
        }

        if(in_array($value, $array)) {
            $check=true;
        }

        return $check;

    }

}