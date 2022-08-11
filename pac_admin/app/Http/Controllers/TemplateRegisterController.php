<?php

namespace App\Http\Controllers;

use App\Http\Utils\LongtermIndexUtils;
use App\Http\Utils\TemplateAdminControllerUtils;
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
use App\Http\Utils\TemplateRouteUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Response;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpWord;
use Illuminate\Support\Facades\File;

class TemplateRegisterController extends AdminController
{

    private $model;
    private $department;
    
    public function __construct(Department $department)
    {
        parent::__construct();
        $this->department = $department;
        $this->templateDirectory = config('app.pac_app_env') . '/' . config('app.pac_contract_app') 
            . '/' . config('app.pac_contract_server');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){

        $user       = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_TEMPLATE_INDEX_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        $documentName   = $request->get('documentName');
        $keyword   = $request->get('keyword');
        $limit      = $request->get('limit', 20);
        $orderBy    = $request->get('orderBy', "LTD.template_create_at");
        $orderDir   = $request->get('orderDir', "DESC");
        $action     = $request->get('action','');
        $keys = $request->keys();
        $indexes = [];
        $subIndexes = [];
        $indexId = 1;

        try{

            $template_file = DB::table('template_file as LTD')
                ->leftjoin('circular_user_templates','LTD.template_route_id','circular_user_templates.id')
                ->select('LTD.*','circular_user_templates.name as route_name')
                ->where('LTD.mst_company_id', $user->mst_company_id);

            if($documentName){
                $template_file = $template_file->where('LTD.file_name', 'like', '%' . $documentName . '%');
            }
            $intCompanyVal = DB::table("mst_company")->where("id",$user->mst_company_id)->value("multiple_department_position_flg");

            $template_file = $template_file->orderBy($orderBy,$orderDir)->paginate($limit)->appends(request()->input());
            $arrRangeName = ["社内","部署","個人"]; 
 
            foreach($template_file as $tf){
                $tf->multiple_department_position_flg = $intCompanyVal;
                $tf->rangeName = $arrRangeName[$tf->document_access_flg];
                $tf->displayStr = [];
                if($tf->document_access_flg == 0 || $tf->create_user_type != 1){
                    continue;
                }
                $objUserInfo = DB::table("mst_user_info")->join("mst_user","mst_user.id",'=','mst_user_info.mst_user_id')->where("mst_user_id",$tf->mst_user_id)->select("email","family_name","given_name","mst_department_id","mst_position_id","mst_department_id_1","mst_position_id_1","mst_department_id_2","mst_position_id_2")->first();
                $newDepartment=[
                    ['did'=>$objUserInfo->mst_department_id,'dpid'=>$objUserInfo->mst_position_id],
                    ['did'=>$objUserInfo->mst_department_id_1,'dpid'=>$objUserInfo->mst_position_id_1],
                    ['did'=>$objUserInfo->mst_department_id_2,'dpid'=>$objUserInfo->mst_position_id_2]
                ];
                $tf->email = $objUserInfo->email;
                $tf->departmentData = [];
                $tf->fullName = $objUserInfo->family_name .$objUserInfo->given_name;
                $tf->displayStr[0] = $tf->fullName."({$tf->email})";
                $tf->departmentData[0] = '';
                if($tf->document_access_flg == 2){
                    //個人
                    continue;
                }
                foreach($newDepartment as $kv=>$val){
                    if(!$val['did']){
                        if($val['dpid']){
                            $departmentId = '';
                        }else{
                            continue;
                        }
                    }else{
                        $departmentId = $val['did'];
                    }
                    if(empty($departmentId)){
                        $tf->displayStr[$kv] = $tf->fullName."({$tf->email})";
                        $tf->departmentData[$kv] = '';
                        continue;
                    }
                    //部署
                    $userDepartment = DB::table("mst_department")->where("id",$departmentId)->select("id","department_name")->where("state",1)->first();
                    $tf->displayStr[$kv] = $userDepartment->department_name;
                    $tf->departmentData[$kv] = $userDepartment->department_name;
                }                
            }
            $company = DB::table('mst_company')
                ->where('id', $user->mst_company_id)
                ->first();

            $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
            $this->assign('company', $company);
            $this->assign('multiple_department_position_flg', $intCompanyVal);
            $this->assign('orderBy', $orderBy);
            $this->assign('orderDir', $orderDir);
            $this->assign('template_file', $template_file);

            $this->setMetaTitle('テンプレート保存一覧');
            return $this->render('Circulars.template');

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->raiseDanger(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
    }

    public function upload(Request $request){
        $login_user = $request->user();
        if(!$login_user->can(PermissionUtils::PERMISSION_TEMPLATE_INDEX_CREATE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        try{
            Log::info('テンプレートアップロード開始');
            $file = $request->file('uploadFile');

            $access_flg = $request['document_access_flg'];
            $originName = $file->getClientOriginalName();
            $fileextension = $file->getClientOriginalExtension();
            $fileExe=['doc','docx','xlsx'];
            if(!in_array($fileextension,$fileExe)){
                return response()->json(['status'=>false,'message'=>['アップロード可能な形式はdoc、docx、xlsxのみです。']]);
            }
            $userName = $login_user->family_name . $login_user->given_name;
            $altFileName = explode(".", (microtime(true) . ""))[0] . '_' .$login_user->id .'_1'. '.' . $fileextension;

            //S3テンプレート用ディレクトリ存在確認
            $s3path =  config('filesystems.prefix_path') . '/' .config('app.s3_storage_root_folder');
            $isFolderExist = Storage::disk('s3')->exists($s3path);
            if (!$isFolderExist) {
                Storage::disk('s3')->makeDirectory($s3path);
                Storage::disk('s3')->makeDirectory($s3path.'/template');

                $s3path = $s3path.'/'.'template/'. $this->templateDirectory . $login_user->mst_company_id;
                Storage::disk('s3')->makeDirectory($s3path);
            }else{
                $s3path = $s3path.'/'.'template/'. $this->templateDirectory;
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
                Log::info('テンプレート保存URL'. $s3url);

                DB::beginTransaction();

                $template_id = DB::table('template_file')
                    ->insertGetId(
                        [
                            'mst_company_id' => $login_user->mst_company_id,
                            'mst_user_id' => $login_user->id,
                            'file_name' => $originName,
                            'storage_file_name'=> $altFileName,
                            'location' => $s3url,
                            'document_type' => $extension,
                            'document_access_flg' => $access_flg,
                            'template_create_at' => Carbon::now(),
                            'template_create_user' => $userName,
                            'is_generation_flg' => 1,
                            'create_user_type' => TemplateRouteUtils::CREATE_APP,
                            'auth_flg' => TemplateRouteUtils::AUTH_FLG,
                        ]);

                foreach ($placeholderList as $cell => $value) {
                    DB::table('template_placeholder_data')
                        ->insert([
                            'template_file_id' => $template_id,
                            'template_placeholder_name' => $value,
                            'cell_address' => $cell,
                            'template_create_at' => Carbon::now(),
                            'template_create_user' => $userName,
                        ]);
                }
            
                DB::commit();
                Log::info('テンプレートアップロード完了');

                return response()->json(['status' => true, 'message' => __('message.success.download_request.download_ordered', ['attribute' => $originName])]);

            } elseif (in_array($fileextension, ['docx', 'doc'])) {
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
                    if ($start_position !== false){
                        $placeholder = substr($contents, $start_position, $phLength);
                        $placeholderList += array($i => $placeholder);
                        $contents = substr($contents, strpos($contents, $phEnd) + 1, strlen($contents) - $start_position );
                    }
                }

                //テスト用ファイル格納処理
                //$move = $file->storeAs('template', $altFileName);
                
                //S3アップロード処理
                Storage::disk('s3')->putfileAs($s3path.'/', $file, $altFileName, 'pub');
                //保存したS3完全URLの取得
                $s3url = Storage::disk('s3')->url($s3path.'/'.$altFileName);
                Log::info('テンプレート保存URL'. $s3url);

                DB::beginTransaction();

                $template_id = DB::table('template_file')
                    ->insertGetId(
                        [
                            'mst_company_id' => $login_user->mst_company_id,
                            'mst_user_id' => $login_user->id,
                            'file_name' => $originName,
                            'storage_file_name'=> $altFileName,
                            'location' => $s3url,
                            'document_type' => $extension,
                            'document_access_flg' => $access_flg,
                            'template_create_at' => Carbon::now(),
                            'template_create_user' => $userName,
                            'is_generation_flg' => 1,
                            'create_user_type' => TemplateRouteUtils::CREATE_APP,
                            'auth_flg' => TemplateRouteUtils::AUTH_FLG,
                        ]);

                foreach ($placeholderList as $value) {
                    DB::table('template_placeholder_data')
                        ->insert([
                            'template_file_id' => $template_id,
                            'template_placeholder_name' => $value,
                            'template_create_at' => Carbon::now(),
                            'template_create_user' => $userName,
                        ]);
                }

                DB::commit();
                Log::info('テンプレートアップロード完了');

                return response()->json(['status' => true, 'message' => __('message.success.template_save', ['attribute' => $originName])]);
            } else {
                Log::error($ex->getMessage().$ex->getTraceAsString());
                return $this->raiseDanger(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
            }


        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->raiseDanger(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
        
    }

    public function delete(Request $request) {
        $user       = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_TEMPLATE_INDEX_DELETE)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        DB::beginTransaction();
        try {
            $requestIds = $request->get('cids',[]);
            $user = $request->user();
            $res = "";

            DB::table('template_placeholder_data')
                ->join('template_file', 'template_placeholder_data.template_file_id', '=', 'template_file.id')
                ->where('template_file.mst_company_id', $user->mst_company_id)
                ->whereIn('template_placeholder_data.template_file_id', $requestIds)
                ->delete();

            DB::table('template_file')
                ->where('mst_company_id', $user->mst_company_id)
                ->whereIn('id', $requestIds)
                ->delete();

            DB::commit();
            Log::info('テンプレートデータ削除完了');
            /*DB::commit();
            return $this->sendResponse(true,'テンプレートファイル削除処理に成功しました。');*/
            return response()->json(['status' => true, 'message' => __('message.success.template_delete', ['attribute' => $res])]);
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->raiseDanger(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
    }

}