<?php

namespace App\Http\Controllers\API\FormIssuance;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateCsvFormImportApiRequest;
use App\Http\Requests\API\CreateCsvFormImportFromContractApiRequest;
use App\Http\Requests\API\CreateFormIssuanceAPIRequest;
use App\Http\Requests\API\CreateFrmTemplateAPIRequest;
use App\Http\Requests\API\CreateExpTemplateAPIRequest;
use App\Http\Requests\API\GetDownloadDataAPIRequest;
use App\Http\Requests\API\GetDownloadListAPIRequest;
use App\Http\Requests\API\SettingFrmTemplateAPIRequest;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\DownloadUtils;
use App\Http\Utils\StampUtils;
use App\Http\Utils\TemplateUtils;
use App\Jobs\FormIssuance\CircularMaker;
use App\Jobs\FormIssuanceImportJob;
use App\Utils\FormIssuanceUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpWord;
use Response;
use SplFileObject;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */

class FormIssuanceAPIController extends AppBaseController
{
    private $rootDirectory;
    private $templateDirectory;
    private $remoteStorage;
    private $storagePutOption;
    private $templateTypeDirectory;
    private $importTypeDirectory;
    private $expTemplateTypeDirectory;

    private $templateFileNamePrefix;
    private $expTemplateFileNamePrefix;
    private $importFileNamePrefix;

    public function __construct()
    {
        $this->rootDirectory = config('app.s3_imprintservice_root_folder');
        $this->templateTypeDirectory = config('app.s3_storage_form_template_folder_type');
        $this->importTypeDirectory = config('app.s3_storage_form_import_folder_type');
        $this->expTemplateTypeDirectory = config('app.s3_storage_exp_template_folder_type');

        $this->templateFileNamePrefix = config('app.s3_storage_template_file_name_prefix');
        $this->expTemplateFileNamePrefix = config('app.s3_storage_exp_template_file_name_prefix');
        $this->importFileNamePrefix = config('app.s3_storage_import_file_name_prefix');

        $this->templateDirectory = config('app.server_env') . '/' . config('app.edition_flg')
            . '/' . config('app.server_flg');
        $this->remoteStorage = 's3'; // TODO s3
        $this->storagePutOption = 'pub'; // TODO pub
    }


    /**
     * Display a listing of the template.
     * GET|HEAD /users
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $user_info = DB::table('mst_user_info')
                ->where('mst_user_id', $user->id)->first();
            if(!$user_info) {
                return $this->sendError('Permission denied.',403);
            }
            $department_user_ids = DB::table('mst_user_info')
                ->where('mst_department_id', $user_info->mst_department_id)
                ->pluck('mst_user_id')
                ->toArray();

            $filter_file_name = $request->get('file_name', '');
            $filter_frm_type_invoice = $request->get('invoice');
            $filter_frm_type_other = $request->get('other');
            $filter_frm_template_code = $request->get('frm_template_code', '');
            $filter_remarks = $request->get('remarks', '');
            $filter_frm_type = [];
            if ($filter_frm_type_invoice) {
                array_push($filter_frm_type, "1");
            }
            if ($filter_frm_type_other) {
                array_push($filter_frm_type, "0");
            }

            $limit      = AppUtils::normalizeLimit($request->get('limit', 10), 10);
            $orderBy    = $request->get('orderBy', "");
            $orderDir   =  AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));

            $arrOrder   = ['file_name' => 'file_name','frm_type' => 'frm_type', 'frm_template_code' => 'frm_template_code',
                'remarks' => 'remarks', 'disabled_at' => 'disabled_at', 'update_user' => 'update_user', 'update_at' => 'update_at'];
            $orderBy = isset($arrOrder[$orderBy]) ? $arrOrder[$orderBy] : 'create_at';

            $template_files = DB::table('frm_template')
                ->select(
                    'id',
                    'mst_company_id',
                    'mst_user_id',
                    'file_name',
                    'document_type',
                    'frm_template_code',
                    'frm_type',
                    'frm_template_access_flg',
                    'frm_template_edit_flg',
                    'disabled_at',
                    'remarks',
                    'title',
                    'message',
                    'address_change_flg',
                    'text_append_flg',
                    'hide_thumbnail_flg',
                    'require_print',
                    'access_code_flg',
                    'access_code',
                    'outside_access_code_flg',
                    'outside_access_code',
                    're_notification_day',
                    'auto_ope_flg',
                    'version',
                    'create_at',
                    'create_user',
                    DB::raw('IF(ISNULL(update_at), create_user, update_user) as update_user'),
                    DB::raw('IFNULL(update_at, create_at) as update_at')
                )
                ->where(function ($query) use ($user, $department_user_ids) {
                    $query->where(function ($where) use ($user, $department_user_ids){
                        $where->where('mst_user_id', $user->id)
                            ->where('frm_template_access_flg', TemplateUtils::INDIVIDUAL_ACCESS_TYPE);
                    });
                    $query->orWhere('frm_template_access_flg', TemplateUtils::COMPANY_ACCESS_TYPE);
                    $query->orWhere(function($orWhere) use($user, $department_user_ids){
                        $orWhere->whereIn('mst_user_id', $department_user_ids);
                        $orWhere->where('frm_template_access_flg', TemplateUtils::DEPARTMENT_ACCESS_TYPE);
                    });
                })
                ->where('mst_company_id', $user->mst_company_id);

            $where = ['1=1'];
            $where_arg = [];

            if($filter_file_name) {
                $where[] = 'INSTR(file_name, ?)';
                $where_arg[] = $filter_file_name;
            }
            if(count($filter_frm_type) > 0) {
                $template_files->whereIn('frm_type', $filter_frm_type);
            }
            if($filter_frm_template_code) {
                $where[] = 'INSTR(frm_template_code, ?)';
                $where_arg[] = $filter_frm_template_code;
            }
            if($filter_remarks) {
                $where[] = 'INSTR(remarks, ?)';
                $where_arg[] = $filter_remarks;
            }

            if($orderBy) {
                $template_files->orderBy($orderBy, $orderDir);
            }
            $template_files = $template_files->whereRaw(implode(" AND ", $where), $where_arg)->paginate($limit);
            foreach($template_files as $template){
                $template->storage_file_name = $this->getS3FileName($this->templateFileNamePrefix,$template->id,$template->file_name);
            };
            return $this->sendResponse($template_files,'送信文書の取得処理に成功しました。');


        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('テンプレートファイル情報取得処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($frmTemplateId, Request $request) {
        $frm_template = DB::table('frm_template')
            ->where('id', $frmTemplateId)
            ->get();
        $checkEditPermission = $request['hasEditPermission'];

        $checkSettingStatus = false;
        if ($frm_template[0]->frm_type == AppUtils::FORM_TYPE_INVOICE) {
            $frm_cols = DB::table('frm_invoice_cols')
                ->where('mst_company_id', $frm_template[0]->mst_company_id)
                ->where('frm_template_id', $frm_template[0]->id)
                ->get();
        } else {
            $frm_cols = DB::table('frm_others_cols')
                ->where('mst_company_id', $frm_template[0]->mst_company_id)
                ->where('frm_template_id', $frm_template[0]->id)
                ->get();
        }
        if (count($frm_cols) > 0) {
            $checkSettingStatus = true;
        }

        if (count($frm_template)) {
            $frm_template[0]->storage_file_name = $this->getS3FileName($this->templateFileNamePrefix,$frm_template[0]->id,$frm_template[0]->file_name);
            return $this->sendResponse(['frm_template' => $frm_template[0], 'check_edit_permission' => $checkEditPermission, 'check_setting_status' => $checkSettingStatus], 'get template done');
        } else {
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "template not found" ];
        }
    }

    public function delete($frmTemplateId, Request $request) {
        try {
            $user = $request->user();
            $templateType = $this->templateTypeDirectory;
            $tplFolder = config('app.s3_storage_form_template_folder');
            $file_name = DB::table('frm_template')->where('id', $frmTemplateId)->value('file_name');

            DB::transaction(function () use ($frmTemplateId){
                DB::table('frm_invoice_cols')
                    ->where('frm_template_id', $frmTemplateId)
                    ->delete();
                DB::table('frm_others_cols')
                    ->where('frm_template_id', $frmTemplateId)
                    ->delete();
                DB::table('frm_seqmgr')
                    ->where('frm_template_id', $frmTemplateId)
                    ->delete();
                DB::table('frm_template_stamp')
                    ->where('frm_template_id', $frmTemplateId)
                    ->delete();
                DB::table('frm_template_placeholder')
                    ->where('frm_template_id', $frmTemplateId)
                    ->delete();
                DB::table('frm_template')
                    ->where('id', $frmTemplateId)
                    ->delete();
            });

            $path = $this->rootDirectory.'/'.$tplFolder.'/'.$this->templateDirectory.'/'.$user->mst_company_id.'/'.$templateType;
            $fileNameS3 = $this->getS3FileName($this->templateFileNamePrefix,$frmTemplateId,$file_name);

            $relative_path = $path.'/'.$fileNameS3;
            Storage::disk($this->remoteStorage)->delete($relative_path);

            return $this->sendResponse(true,'明細テンプレートを削除しました。');
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('明細テンプレートを削除するのは失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateTemplateStatus($frmTemplateId, Request $request){
        $enable = $request->get('enable');

        try {
            $user = $request->user();

            if ($enable){
                $message = '明細テンプレートの状態を有効にしました。';
                DB::table('frm_template')
                    ->where('id', $frmTemplateId)
                    ->where('mst_company_id', $user->mst_company_id)
                    ->update(['disabled_at' => null, 'update_at' => Carbon::now(), 'update_user' => $user->getFullName(), 'version' => \DB::raw('version+1')]);
            }else{
                $message = '明細テンプレートの状態を無効にしました。';
                DB::table('frm_template')
                    ->where('id', $frmTemplateId)
                    ->where('mst_company_id', $user->mst_company_id)
                    ->update(['disabled_at' => Carbon::now(), 'update_at' => Carbon::now(), 'update_user' => $user->getFullName(), 'version' => \DB::raw('version+1')]);
            }
            return $this->sendResponse(true , $message);
        }catch (\Exception $ex) {
            if ($enable) {
                $message = '明細テンプレートの状態を有効にするのは失敗しました。';
            } else {
                $message = '明細テンプレートの状態を無効にするのは失敗しました。';
            }
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError($message , \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Upload and store the template files
     * requirement parameter
     *  ・file : file like xxx.xlsx and xxx.docx
     *  ・document_access_flg : 0 or 1 or 2
     */
     public function uploadTemplate(CreateFrmTemplateAPIRequest $request) {
        $login_user = $request->user();
        $template_id = 0;
        try{
            Log::info('テンプレートアップロード開始');
            if(($request['frm_template_access_flg'] == 1 && $request['frm_template_edit_flg'] < 1) || ($request['frm_template_access_flg'] == 2 && $request['frm_template_edit_flg'] < 2)){
                // return $this->sendError('編集権限は使用権限より左側の権限の設定が不可ですので。帳票テンプレートの登録に失敗しました。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                // sendErrorではなくsendResponseすることにより、ダイアログにエラーメッセージを出力する動作にしている
                $data['err_msg'] = '編集権限は使用権限より左側の権限の設定が不可ですので。明細テンプレートの登録に失敗しました。';
                return $this->sendResponse($data, '明細テンプレートの登録に失敗しました。');
            }
            $file = $request->file('file');
            $originName = $file->getClientOriginalName();
            $fileextension = $file->getClientOriginalExtension();
            $templateCode = strtoupper($request['frm_template_code']);

            // コードがブランク
            if($templateCode != ''){
                // validate template code
                if ($this->isValidTemplateCode($templateCode) == false) {
                    // return $this->sendError('帳票テンプレートの登録に失敗しました。英語文字または数字のみご入力してください。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                    // sendErrorではなくsendResponseすることにより、ダイアログにエラーメッセージを出力する動作にしている
                    $data['err_msg'] = '明細テンプレートの登録に失敗しました。英語文字または数字のみご入力してください。';
                    return $this->sendResponse($data, '明細テンプレートの登録に失敗しました。');
                }

                $countFrmTemplate = DB::table('frm_template')->where('mst_company_id',$login_user->mst_company_id)->where('frm_template_code',$templateCode)->count();
                if( $countFrmTemplate > 0 ){
                    $data['err_msg'] = '明細テンプレートコードはすでに使われています。';
                    return $this->sendResponse($data, '明細テンプレートの登録に失敗しました。');
                }
            }

            $max_template_file = (DB::table('mst_constraints')->where('mst_company_id',$login_user->mst_company_id)->value('max_template_file'));
            $countFrmTemplate = DB::table('frm_template')->where('mst_company_id',$login_user->mst_company_id)->count();
            if( $countFrmTemplate >= $max_template_file ){
                // return $this->sendError('テンプレート登録上限数'.$max_template_file.'個まで登録が可能です。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                // sendErrorではなくsendResponseすることにより、ダイアログにエラーメッセージを出力する動作にしている
                $data['err_msg'] = 'テンプレート登録上限数'.$max_template_file.'個まで登録が可能です。';
                return $this->sendResponse($data, '明細テンプレートの登録に失敗しました。');
            }

            if ($fileextension == 'xlsx' ) {
                $extension = AppUtils::DOCUMENT_TYPE_EXCEL;
                $reader = new XlsxReader();
                $spreadsheet = $reader->load($file);
                // 読み込むシートを指定(1シート目)
                $sheet = $spreadsheet->getSheet(0);
                //行番号、ループ用
                $row = 1;

                $placeholderList = array();
                //Get cell address and cell information
                foreach ($sheet->getRowIterator() as $eachrow) {
                    foreach($sheet->getColumnIterator() as $column)
                    {
                        $column->getColumnIndex() . $eachrow->getRowIndex();
                        $sheetData=$sheet->getCell($column -> getColumnIndex() . $row )->getValue();
                        //If there is data in the cell, save the data (placeholder) starting with $ {and the cell address
                        if($sheetData){
                            //Confirm that the target data is "data starting with" $ {""
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
            } elseif ($fileextension == 'docx' ) {
                $extension = AppUtils::DOCUMENT_TYPE_WORD;
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
            } else {
                // return $this->sendError('ファイル形式が異なります。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                $data['err_msg'] = 'ファイル形式が異なります。';
                return $this->sendResponse($data, '明細テンプレートの登録に失敗しました。');
            }

            $company = DB::table('mst_company')
                ->leftJoin('mst_limit', 'mst_company.id', '=', 'mst_limit.mst_company_id')
                ->leftJoin('mst_protection', 'mst_company.id', '=', 'mst_protection.mst_company_id')
                ->select('mst_company.id', 'mst_protection.text_append_flg', 'mst_limit.text_append_flg as limit_text_append_flg', 'mst_limit.require_print', 'mst_protection.destination_change_flg', 'mst_protection.enable_email_thumbnail', 'mst_protection.access_code_protection' )
                ->where('mst_company.id', $login_user->mst_company_id)
                ->first();

            $access_code = '';
            $outside_access_code = '';
            if($company->access_code_protection == 1) {
                $access_code = $this->getRandomString(6);
                $outside_access_code = $this->getRandomString(6);
            }

            //insert table frm_template
            $userName = $login_user->getFullName();
            $data =[
                'mst_company_id' => $login_user->mst_company_id,
                'mst_user_id' => $login_user->id,
                'file_name' => $originName,
                'document_type' => $extension,
                'frm_template_code' => $templateCode,
                'frm_type' => $request['frm_type_flg'],
                'frm_template_access_flg' => $request['frm_template_access_flg'],
                'frm_template_edit_flg' => $request['frm_template_edit_flg'],
                'disabled_at' => Carbon::now(),
                'remarks' => $request['remarks'],
                'create_at' => Carbon::now(),
                'create_user' => $userName,
                // PAC_5-2280
                'auto_ope_flg' => FormIssuanceUtils::AUTO_OPE_SAVE,
                'address_change_flg' => $company->destination_change_flg,
                'text_append_flg' => $company->limit_text_append_flg == 1 ? $company->text_append_flg : CircularUtils::TEXT_APPEND_FLG_INVALID,
                'hide_thumbnail_flg' => CircularUtils::HIDE_THUMBNAIL_INVALID,
                'require_print' => $company->require_print,
                'access_code_flg' => $company->access_code_protection,
                'access_code' => $access_code,
                'outside_access_code_flg' => $company->access_code_protection,
                'outside_access_code' => $outside_access_code,
                // PAC_5-2280
            ];

            $template_id = DB::table('frm_template')->insertGetId($data);
            $data['id'] = $template_id;

            $templatePlaceholderData = [];
            foreach ($placeholderList as $cell => $value) {
                $templatePlaceholderData[] = [
                    'mst_company_id' => $login_user->mst_company_id,
                    'frm_template_id' => $template_id,
                    'frm_template_placeholder_name' => $value,
                    'cell_address' => $fileextension == 'xlsx' ? $cell : null,
                    'create_at' => Carbon::now(),
                    'create_user' => $userName,
                ];
            }

            if(count($templatePlaceholderData)){
                DB::table('frm_template_placeholder')->insert($templatePlaceholderData);
            }

            //the process of saving files to S3
            if($template_id){
                $altFileNameS3 =  $this->getS3FileName($this->templateFileNamePrefix,$template_id,$originName);
                //create directory on S3
                $s3path = $this->getPathToS3($this->templateTypeDirectory,$login_user->mst_company_id);

                //Save to S3
                Storage::disk($this->remoteStorage)->putfileAs($s3path.'/', $file, $altFileNameS3, $this->storagePutOption);

                Log::info('テンプレートアップロード完了');
                return $this->sendResponse($data, '明細テンプレートの登録に成功しました。');
            }else{
                return $this->sendError('明細テンプレートの登録に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            //delete record when save to s3 fails
            DB::table('frm_template_placeholder')->where('frm_template_id',$template_id)->delete();
            DB::table('frm_template')->where('id',$template_id)->delete();
            return $this->sendError('明細テンプレートの登録に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getRandomString($len, $chars=null)
    {
        if (is_null($chars)){
            $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        }
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }

    /**
     * @param $templateId
     * @param Request $request
     * @return array
     */
    public function getFile($templateId, Request $request) {
        $user = $request->user();
        try {
            $file = DB::table('frm_template')
                ->where('id', $templateId)
                ->first();

            $fileEncode = $this->getContentTemplateBase64($file, $user, false);
            $fileName = $file->file_name;
            $mime = $file->document_type == AppUtils::DOCUMENT_TYPE_EXCEL ?  'xlsx' : 'docx';

            return $this->sendResponse(['file_name' => $fileName, 'file_type' => $mime, 'file_data' => $fileEncode], '明細テンプレートダウンロードに成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTemplatePlaceholder($templateId, $frmType, Request $request){
        try {
            $user = $request->user();

            $arrPlaceholders = DB::table("frm_template_placeholder")
                ->where('mst_company_id', $user->mst_company_id)
                ->where('frm_template_id', $templateId)
                ->orderBy('additional_flg')
                ->orderBy('id')
                ->get()->toArray();

            $uniquePlaceholders = [];
            foreach($arrPlaceholders as $placeholder){
                if (!key_exists($placeholder->frm_template_placeholder_name, $uniquePlaceholders)){
                    $uniquePlaceholders[$placeholder->frm_template_placeholder_name] = $placeholder;
                }
            }

            $colTableName = 'frm_others_cols';
            if ($frmType == AppUtils::FORM_TYPE_INVOICE){
                $colTableName = 'frm_invoice_cols';
            }
            $templateSetting =  DB::table($colTableName)
                ->where('mst_company_id', $user->mst_company_id)
                ->where('frm_template_id', $templateId)->first();

            return $this->sendResponse([ 'placeholders' => array_values($uniquePlaceholders), 'templateSetting' => $templateSetting],'get template placeholder done');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTemplateStamp($templateId, Request $request){
        try {
            $user = $request->user();

            $stamps = [];
            $arrStamp = DB::table("frm_template_stamp")
                ->leftJoin('mst_company_stamp', function($join){
                    // 共通印
                    $join->where('frm_template_stamp.stamp_flg', StampUtils::COMMON_STAMP);
                    $join->on('frm_template_stamp.stamp_id', 'mst_company_stamp.id');
                })
                ->leftJoin('department_stamp', function($join){
                    // 部署名入り印
                    $join->where('frm_template_stamp.stamp_flg', StampUtils::DEPART_STAMP);
                    $join->on('frm_template_stamp.stamp_id', 'department_stamp.id');
                })
                ->leftJoin('mst_stamp', function($join){
                    // 氏名印/日付印
                    $join->where('frm_template_stamp.stamp_flg', StampUtils::NORMAL_STAMP);
                    $join->on('frm_template_stamp.stamp_id', 'mst_stamp.id');
                })
                ->leftJoin('mst_company_stamp_convenient', function($join) {
                    // 便利印
                    $join->where('frm_template_stamp.stamp_flg', StampUtils::CONVENIENT_STAMP);
                    $join->on('frm_template_stamp.stamp_id', 'mst_company_stamp_convenient.id');
                })
                ->leftJoin('mst_stamp_convenient', function($join) {
                    // 便利印(画像)
                    $join->on('mst_stamp_convenient.id', 'mst_company_stamp_convenient.mst_stamp_convenient_id');
                })
                ->where('frm_template_stamp.mst_company_id', $user->mst_company_id)
                ->where('frm_template_stamp.frm_template_id', $templateId)
                ->select(
                    'frm_template_stamp.stamp_page',
                    'frm_template_stamp.stamp_left',
                    'frm_template_stamp.stamp_top',
                    'frm_template_stamp.stamp_flg',
                    'frm_template_stamp.stamp_deg',
                    'frm_template_stamp.stamp_date',
                    DB::raw('CASE frm_template_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.stamp_name     WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.stamp_name     WHEN '.StampUtils::DEPART_STAMP.' THEN CONCAT(department_stamp.face_up1,department_stamp.face_up2,department_stamp.face_down1,department_stamp.face_down2) ELSE mst_stamp.stamp_name END stamp_name'),
                    DB::raw('CASE frm_template_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.font           WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.font ELSE  mst_stamp.font END font'),
                    DB::raw('CASE frm_template_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.width          WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.width          WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.width       ELSE mst_stamp.width       END width'),
                    DB::raw('CASE frm_template_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.height         WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.height         WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.height      ELSE mst_stamp.height      END height'),
                    DB::raw('CASE frm_template_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.date_width     WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.date_width     WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.date_width  ELSE mst_stamp.date_width  END date_width'),
                    DB::raw('CASE frm_template_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.date_height    WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.date_height    WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.date_height ELSE mst_stamp.date_height END date_height'),
                    DB::raw('CASE frm_template_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.date_x         WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.date_x         WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.date_x      ELSE mst_stamp.date_x      END date_x'),
                    DB::raw('CASE frm_template_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.date_y         WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.date_y         WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.date_y      ELSE mst_stamp.date_y      END date_y'),
                    DB::raw('CASE frm_template_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.serial         WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_company_stamp_convenient.serial WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.serial      ELSE mst_stamp.serial      END serial'),
                    DB::raw('CASE frm_template_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.stamp_image    WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.stamp_image    WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.stamp_image ELSE mst_stamp.stamp_image END stamp_image'),
                    DB::raw('CASE frm_template_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.stamp_division WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.stamp_date_flg WHEN '.StampUtils::NORMAL_STAMP.' THEN mst_stamp.stamp_division     ELSE null                  END stamp_division'),
                    DB::raw('CASE frm_template_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.create_at      WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.create_at      WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.create_at   ELSE mst_stamp.create_at   END create_at'),
                    DB::raw('CASE frm_template_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.date_color     WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.date_color     WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.color       ELSE null                  END color'),
                )
                ->get()
                ->toArray();
            if (count($arrStamp)){
                $dstamp_style = DB::table('mst_company')->where('id', $user->mst_company_id)
                    ->select('dstamp_style')->pluck('dstamp_style')->first();
                if(!$dstamp_style)  {
                    $dstamp_style = 'y.m.d';
                }

                foreach ($arrStamp as $key => $stamp) {
                    // 画面指定日付
                    $date = \App\Http\Utils\DateJPUtils::convert($stamp->stamp_date, $dstamp_style);

                    $stamp2Imprint = new \stdClass();
                    $stamp2Imprint->page = $stamp->stamp_page;
                    $stamp2Imprint->stamp_data = StampUtils::processStampImage($stamp, $date);
                    $stamp2Imprint->x_axis = $stamp->stamp_left;
                    $stamp2Imprint->y_axis = $stamp->stamp_top;
                    $stamp2Imprint->width = $stamp->width/1000; // convert to mm
                    $stamp2Imprint->height = $stamp->height/1000; // convert to mm
                    $stamp2Imprint->stamp_url = "";
                    $stamp2Imprint->stamp_flg = $stamp->stamp_flg;
                    $stamp2Imprint->serial = $stamp->serial;
                    $stamp2Imprint->rotateAngle = $stamp->stamp_deg;

                    $stamps[] = $stamp2Imprint;
                }
            }
            return $this->sendResponse([ 'stamps' => $stamps],'get template stamp done');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendResponse(['status' => \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
    }

    private function getContentTemplateBase64($template, $user, $isExpTemplate){
        //ファイルの存在を確認してS3から取得をする
        $tplFolder = config('app.s3_storage_form_template_folder');
        $path = $this->rootDirectory.'/'.$tplFolder.'/'.$this->templateDirectory.'/';

        if($isExpTemplate){
            $templateType = $this->expTemplateTypeDirectory;
            $fileNameS3 = $this->getS3FileName($this->expTemplateFileNamePrefix,$template->id,$template->file_name);
        }else{
            $templateType = $this->templateTypeDirectory;
            $fileNameS3 = $this->getS3FileName($this->templateFileNamePrefix,$template->id,$template->file_name);
        }
        $path = $path.$user->mst_company_id.'/'.$templateType;
        if ( Storage::disk($this->remoteStorage)->exists($path)){
            $relative_path = $path.'/'.$fileNameS3;
            $getFile = Storage::disk($this->remoteStorage)->get($relative_path);
            $isStore = Storage::disk('local')->put($fileNameS3, $getFile);
            Log::info('テンプレート取得: '. $relative_path);
        }

        $filePath = storage_path('app/' . $fileNameS3);
        $fileEncode = \base64_encode(\file_get_contents($filePath));
        Storage::disk('local')->delete($fileNameS3);
        return $fileEncode;
    }

    public function getContentTemplate($templateId, Request $request){
        try {
            $user = $request->user();
            $file = DB::table('frm_template')
                ->where('id', $templateId)
                ->first();

            $fileEncode = $this->getContentTemplateBase64($file, $user, false);

            $file->storage_file_name = $this->getS3FileName($this->templateFileNamePrefix,$file->id,$file->file_name);

            return $this->sendResponse(['file' => [$file], 'file_data' => $fileEncode],'get file done');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendResponse(['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST],"ファイルを取得できませんでした。");
        }
    }

    /**
     * edit the template files
     *
     * request parameters
     *  -- templateId and data to replace
     *
     * response data
     *  ・file_name: file name
     *  ・file_data: file data by encoding base64
     */
    public function edit($templateId, Request $request) {

        try {
            $user = $request->user();
            $placeholderList = DB::table('frm_template')
                ->leftjoin('frm_template_placeholder', 'frm_template.id', '=', 'frm_template_placeholder.frm_template_id')
                ->where('frm_template.id', $templateId)
                ->where('frm_template.mst_company_id', $user->mst_company_id)
                ->get();

            $requestVersion = $request->input('version');
            if ($requestVersion != $placeholderList[0]->version){
                return $this->sendError("明細テンプレートの作成に失敗しました。明細テンプレートのバージョンを対応できません。", \Illuminate\Http\Response::HTTP_CONFLICT);
            }

            // PAC_5-2792
            // 帳票発行文書数上限チェック
            $constraints = DB::table('mst_constraints')
                ->where('mst_company_id', '=', $user->mst_company_id)
                ->first();
            // 請求書発行件数
            $frmInvoiceDocumentCnt = DB::table('frm_invoice_data as f')
                ->join('circular as c', 'f.circular_id', 'c.id')
                ->where('f.mst_company_id', '=', $user->mst_company_id)
                // 今月
                ->where(DB::raw("DATE_FORMAT(c.final_updated_date,'%Y-%m-%d')"), '>=', Carbon::now()->format('Y-m-01'))
                // 回覧状態が 削除 以外
                ->where('c.circular_status', '<>', CircularUtils::DELETE_STATUS)
                ->count();
            // その他発行件数
            $frmOtherDocumentCnt = DB::table('frm_others_data as f')
                ->join('circular as c', 'f.circular_id', 'c.id')
                ->where('f.mst_company_id', '=', $user->mst_company_id)
                // 今月
                ->where(DB::raw("DATE_FORMAT(c.final_updated_date,'%Y-%m-%d')"), '>=', Carbon::now()->format('Y-m-01'))
                // 回覧状態が 削除 以外
                ->where('c.circular_status', '<>', CircularUtils::DELETE_STATUS)
                ->count();
            // 確認した際にその件数が文書の発行文書数を超えていた
            if($frmInvoiceDocumentCnt + $frmOtherDocumentCnt >= $constraints->max_frm_document){
                return $this->sendError(__('message.false.form_issuance.max_frm_document'), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            Log::info('template編集開始');
            Log::debug($placeholderList);

            $fileNameS3 =  $this->getS3FileName($this->templateFileNamePrefix,$templateId,$placeholderList[0]->file_name);
            $tplFolder = config('app.s3_storage_form_template_folder');
            $path = $this->rootDirectory.'/'.$tplFolder.'/';
            $path = $path.$this->templateDirectory.'/'.$user->mst_company_id.'/'.$this->templateTypeDirectory;
            if ( Storage::disk($this->remoteStorage)->exists($path)){
                $relative_path = $path.'/'.$fileNameS3;
                $getFile = Storage::disk($this->remoteStorage)->get($relative_path);
                $isStore = Storage::disk('local')->put($tplFolder.'/'.$fileNameS3, $getFile);
                Log::info('テンプレート取得: '. $relative_path);
            }

            $filePath = storage_path('app/'.$tplFolder.'/'.$fileNameS3);

            // excel file
            if($placeholderList[0]->document_type === AppUtils::DOCUMENT_TYPE_EXCEL){
                Log::info('Excelファイル編集開始');

                $reader = new XlsxReader();
                $reader->setReadDataOnly(false);
                $spreadsheet = $reader->load($filePath);
                $sheet = $spreadsheet->getActiveSheet();

                foreach ($placeholderList as $value) {
                    if ($value->cell_address && !$value->additional_flg){
                        if (isset($request['placeholder'][$value->frm_template_placeholder_name])){
                            $sheet->setCellValue($value->cell_address, $request['placeholder'][$value->frm_template_placeholder_name]);
                        }else{
                            $sheet->setCellValue($value->cell_address, '');
                        }
                    }
                }

                $writer = new XlsxWriter($spreadsheet);
                $path = storage_path('app/'.$tplFolder.'/'.explode(".", (microtime(true) . ""))[0] . '_' .$user->id . '.xlsx');
                $writer->save($path);

                $result = ['status' => \Illuminate\Http\Response::HTTP_OK, 'file_name' => $placeholderList[0]->file_name,
                    'file_data' => \base64_encode(\file_get_contents($path)), 'message' => 'ファイルを編集しました。' ];

                unlink($path);
                Storage::disk('local')->delete($tplFolder.'/'.$fileNameS3);

                Log::info('編集済みExcelファイル送信');

                return $result;

            // word file
            } else {
                Log::info('Wordファイル編集開始');

                $templateProcessor = new PhpWord\TemplateProcessor($filePath);
                foreach ($placeholderList as $value) {
                    if (!$value->additional_flg){
                        if (isset($request['placeholder'][$value->frm_template_placeholder_name])){
                            $templateProcessor->setValue($value->frm_template_placeholder_name, $request['placeholder'][$value->frm_template_placeholder_name]);
                        }else{
                            $templateProcessor->setValue($value->frm_template_placeholder_name, '');
                        }
                    }

                }
                $path = storage_path('app/'.$tplFolder.'/'.explode(".", (microtime(true) . ""))[0] . '_' .$user->id . '.docx');
                $templateProcessor->saveAs($path);
                ob_end_clean(); //バッファ消去

                $result = ['status' => \Illuminate\Http\Response::HTTP_OK, 'file_name' => $placeholderList[0]->file_name,
                'file_data' => \base64_encode(\file_get_contents($path)), 'message' => 'ファイルを編集しました。' ];

                unlink($path);
                Storage::disk('local')->delete($tplFolder.'/'.$fileNameS3);
                Log::info('編集済みWordファイル送信');

                return $result;

            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError("明細テンプレートの作成に失敗しました。もう一度作成してみてください。", \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function saveInputData($templateId, CreateFormIssuanceAPIRequest $request) {
        try {
            $user = $request->user();
            $circularId = $request['circularId'];
            $frmName = $request['frm_name'];

            $placeholderList = DB::table('frm_template')
                ->leftjoin('frm_template_placeholder', 'frm_template.id', '=', 'frm_template_placeholder.frm_template_id')
                ->where('frm_template.id', $templateId)
                ->where('frm_template.mst_company_id', $user->mst_company_id)
                ->get();

            Log::info('template編集保存開始');

            if( !count($placeholderList)){
                Log::info('template編集保存完了');

                return $this->sendResponse(true,'テンプレート入力情報はありません。');
            }

            $countCircular = DB::table('circular')->select('id')->where('id', $circularId)->where('mst_user_id', $user->id)->count();
            if (!$countCircular){
                return $this->sendResponse(true,'Circular Id is invalid。');
            }

            $colTableName = 'frm_others_cols';
            $colDataName = 'frm_others_data';
            $fixColNames = ['reference_date_col' => ['name' => 'reference_date', 'type' => 'date'],
                            'customer_name_col' => ['name' => 'customer_name', 'type' => 'text'],
                            'customer_code_col' => ['name' => 'customer_code', 'type' => 'text']];
            $frm_indexs = DB::table('frm_index')
                ->where('mst_company_id', $user->mst_company_id)
                ->get();
            foreach ($frm_indexs as $frm_index){
                $frmindex = 'frm_index'.$frm_index->frm_index_number;
                $frmindexcol = $frmindex.'_col';
                if($frm_index->data_type == FormIssuanceUtils::DATA_TYPE_NUMBER){
                    $type = 'number';
                }else if($frm_index->data_type == FormIssuanceUtils::DATA_TYPE_TEXT){
                    $type = 'text';
                }else{
                    $type = 'date';
                }
                $fixColNames[$frmindexcol] = ['name' => $frmindex, 'type' => $type];
            }
            if ($placeholderList[0]->frm_type == AppUtils::FORM_TYPE_INVOICE){
                $colTableName = 'frm_invoice_cols';
                $colDataName = 'frm_invoice_data';
                $fixColNames = ['trading_date_col' => ['name' => 'trading_date', 'type' => 'date'],
                                'invoice_no_col' => ['name' => 'invoice_no', 'type' => 'text'],
                                'invoice_date_col' => ['name' => 'invoice_date', 'type' => 'date'],
                                'customer_name_col' => ['name' => 'customer_name', 'type' => 'text'],
                                'customer_code_col' => ['name' => 'customer_code', 'type' => 'text'],
                                'invoice_amt_col' => ['name' => 'invoice_amt', 'type' => 'number'],
                                'payment_date_col' => ['name' => 'payment_date', 'type' => 'date']];
            }

            $templateSetting =  DB::table($colTableName)
                ->where('mst_company_id', $user->mst_company_id)
                ->where('frm_template_id', $templateId)->first();

            DB::transaction(function () use ($templateId, $user, $request, $circularId, $frmName, $placeholderList, $fixColNames, $templateSetting, $colDataName){
                $frmSeq = DB::table('frm_seqmgr')->where('frm_template_id', $templateId)
                    ->where('mst_company_id', $user->mst_company_id)
                    ->value('frm_seq');
                if ($frmSeq){
                    $frmSeq++;
                    DB::table('frm_seqmgr')->where('frm_template_id', $templateId)
                        ->where('mst_company_id', $user->mst_company_id)
                        ->update(['frm_seq' => $frmSeq, 'update_at' => Carbon::now(), 'update_user' => $user->getFullName()]);
                }else{
                    $frmSeq = 1;
                    DB::table('frm_seqmgr')->insert(['frm_seq' => $frmSeq,
                        'create_at' => Carbon::now(),
                        'create_user' => $user->getFullName(),
                        'frm_template_id' => $templateId,
                        'mst_company_id' => $user->mst_company_id]);
                }

                $frmData = $request->input('placeholder', []);
                $data = [
                    'frm_template_id' => $templateId,
                    'circular_id' => $circularId,
                    'mst_company_id' => $user->mst_company_id,
                    'frm_name' => $frmName,
                    'frm_template_code' => $placeholderList[0]->frm_template_code,
                    'company_frm_id' => $placeholderList[0]->frm_template_code.'-'.str_pad($frmSeq, 8, "0", STR_PAD_LEFT),
                    'frm_seq' => $frmSeq,
                    'create_at' => Carbon::now(),
                    'create_user' => $user->getFullName(),
                    'update_at' => Carbon::now(),
                    'update_user' => $user->getFullName(),
                ];
                foreach ($fixColNames as $placeholderCol => $dataCol){
                    $placeholderName = $templateSetting->$placeholderCol;
                    if ($placeholderName){
                        if (isset($request['placeholder'][$placeholderName])){
                            if ($dataCol['type'] == 'date'){
                                $value = FormIssuanceUtils::to_date($request['placeholder'][$placeholderName]) ;
                                if ($value !== false){
                                    $data[$dataCol['name']] = $value;
                                }
                            }elseif ($dataCol['type'] == 'number'){
                                $value = FormIssuanceUtils::to_numeric_str($request['placeholder'][$placeholderName]) ;
                                if ($value !== false){
                                    $data[$dataCol['name']] = $value;
                                }
                            }else{
                                $data[$dataCol['name']] = $request['placeholder'][$placeholderName];
                            }
                        }
                    }
                }

                $placeholderNames = [];
                foreach ($placeholderList as $value) {
                    if(!isset($frmData[$value->frm_template_placeholder_name])) {
                        $frmData[$value->frm_template_placeholder_name] = null;
                    }
                    $placeholderNames[$value->frm_template_placeholder_name] = null;
                }
                $filterFrmData = array_intersect_key($frmData, $placeholderNames);
                $data['frm_data'] = json_encode($filterFrmData);
                DB::table($colDataName)->insert($data);
            });
            Log::info('template編集保存完了');

            return $this->sendResponse(true,'テンプレート入力情報保存に成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError("明細テンプレートの作成に失敗しました。もう一度作成してみてください。", \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 自動実施
     * @param Request $request
     */
    public function autoCircularSave(Request $request){
        $user = $request->user();
        $circularId = $request['circularId'];
        $templateId = $request['templateId'];

        // 企業情報
        $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
        // stamp情報
        $stamps = DB::table('frm_template_stamp AS fts')
            ->leftJoin('mst_company_stamp', function($join){
                // 共通印
                $join->where('fts.stamp_flg', StampUtils::COMMON_STAMP);
                $join->where('mst_company_stamp.del_flg', '!=', 1);
                $join->on('fts.stamp_id', 'mst_company_stamp.id');
            })
            ->leftJoin('department_stamp', function($join){
                // 部署名入り印
                $join->where('fts.stamp_flg', StampUtils::DEPART_STAMP);
                $join->on('fts.stamp_id', 'department_stamp.id');
            })
            ->leftJoin('mst_stamp', function($join){
                // 氏名印/日付印
                $join->where('fts.stamp_flg', StampUtils::NORMAL_STAMP);
                $join->on('fts.stamp_id', 'mst_stamp.id');
            })
            ->leftJoin('mst_company_stamp_convenient', function($join) {
                // 便利印
                $join->where('fts.stamp_flg', StampUtils::CONVENIENT_STAMP);
                $join->on('fts.stamp_id', 'mst_company_stamp_convenient.id');
            })
            ->leftJoin('mst_stamp_convenient', function($join) {
                // 便利印(画像)
                $join->on('mst_stamp_convenient.id', 'mst_company_stamp_convenient.mst_stamp_convenient_id');
            })
            ->where("fts.frm_template_id", $templateId)
            ->where("fts.mst_company_id", $user->mst_company_id)
            ->orderBy("fts.id")
            ->select(
                'fts.id',
                'fts.stamp_top as y_axis',
                'fts.stamp_left as x_axis',
                'fts.stamp_deg as rotateAngle',
                'fts.stamp_page as page',
                'fts.stamp_id as stamp_id',
                DB::raw(' CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.stamp_image WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.stamp_image    WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.stamp_image ELSE mst_stamp.stamp_image END stamp_data'),
                DB::raw('(CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.width       WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.width          WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.width       ELSE mst_stamp.width       END) / 1000 width'),
                DB::raw('(CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.height      WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.height         WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.height      ELSE mst_stamp.height      END) / 1000 height'),
                DB::raw(' CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.serial      WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_company_stamp_convenient.serial WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.serial      ELSE mst_stamp.serial      END serial'),
            )
            ->get()
            ->toArray();

        // 帳票情報
        $tpl = DB::table("frm_template")
            ->where("id", $templateId)
            ->where("mst_company_id", $user->mst_company_id)
            ->first();
        $cids = [];

        // 回覧自動登録を実施
        $cmaker = new CircularMaker();
        $cmaker->save_circular('', '', '', $stamps, '', $cids, $circularId, $company, $user, $tpl);

    }

    public function settingTemplate($templateId, SettingFrmTemplateAPIRequest $request) {
        try {
            $user = $request->user();
            $templateSetting = $request['templateSetting'];
            $placeholderNew = $request['placeholderNew'];
            $editItem = $request['editItem'];
            $stamps = $request['stamps'];
            $template = DB::table('frm_template')->where('id',$templateId)->first();
            $templatePlaceholder = DB::table('frm_template_placeholder')
                        ->where('frm_template_id',$templateId)
                        ->where('additional_flg',AppUtils::ADDITIONAL_FLG_DEFAULT)
                        ->distinct()
                        ->pluck('frm_template_placeholder_name')
                        ->toArray();
            $placeholdersNameArr = [];
            foreach($templatePlaceholder as $name){
                if($name === '${}' && !in_array($name, $placeholdersNameArr)){
                    $placeholdersNameArr[] = $name;
                }else{
                    $placeholdersNameArr[] = trim(strtoupper(str_replace(['${','}'], "", $name)));
                    $placeholdersNameArr[] = trim(strtoupper($name));
                }
            }

            if($editItem['frm_template_code'] == ''){
                return $this->sendError(__('message.false.form_issuance.template_code_input'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }else{
                // テンプレートコード内容チェック
                if ($this->isValidTemplateCode($editItem['frm_template_code']) == false) {
                    return $this->sendError(__('message.false.form_issuance.template_code_check'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                // テンプレートコードユニークチェック
                $countFrmTemplate = DB::table('frm_template')->where('mst_company_id',$user->mst_company_id)->where('frm_template_code',$editItem['frm_template_code'])
                    ->where('id', '!=', $templateId)
                    ->count();
                if( $countFrmTemplate > 0 ){
                    return $this->sendError(__('message.false.form_issuance.template_code_used'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
            if($template->version != $request['version']){
                return $this->sendError(' 明細テンプレートの設定に失敗しました。明細テンプレート状態は有効にしています。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $data = [];
            if(count($placeholderNew)){
                foreach($placeholderNew as $placeholde){
                    if($placeholde['frm_template_placeholder_name'] === '${}'){
                        $placeholdersReplace = trim($placeholde['frm_template_placeholder_name']);
                    }else{
                        $placeholdersReplace  = trim(str_replace(['${','}'], "", $placeholde['frm_template_placeholder_name']));
                    }


                    if(in_array(trim(strtoupper($placeholdersReplace)),$placeholdersNameArr)){
                        return $this->sendError('項目名は上にあるテンプレート項目および項目名の中で重複が出来ません。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                    }
                    $placeholdersNameArr[] = strtoupper($placeholdersReplace);
                    if(($placeholde['frm_imp_cols'] || $placeholde['frm_invoice_cols']) && !$placeholde['frm_template_placeholder_name']){
                        return $this->sendError('明細テンプレートの設定に失敗しました。非表示の項目名が入力されていません。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                    }

                    if(!$placeholde['frm_imp_cols'] && !$placeholde['frm_invoice_cols'] && !$placeholde['frm_template_placeholder_name']){
                        continue;
                    }
                    $data[] = [
                        'mst_company_id' => $user->mst_company_id,
                        'frm_template_id' => $templateId,
                        'frm_template_placeholder_name' => $placeholde['frm_template_placeholder_name'],
                        'additional_flg' => AppUtils::ADDITIONAL_FLG,
                        'create_at' => Carbon::now(),
                        'create_user' => $user->getFullName()
                    ];
                }
            }

            $templateSetting2Save = [];
            $templateSetting2Save['mst_company_id'] = $user->mst_company_id;
            $templateSetting2Save['frm_template_id'] = $templateId;
            $templateSetting2Save['create_at'] = Carbon::now();
            $templateSetting2Save['create_user'] = $user->getFullName();
            $templateSetting2Save['frm_default_name'] = $templateSetting['frm_default_name'];
            $templateSetting2Save['to_email_addr_imp'] = $templateSetting['to_email_addr_imp'];
            $templateSetting2Save['to_email_name_imp'] = $templateSetting['to_email_name_imp'];
            $templateSetting2Save['frm_imp_cols'] = $templateSetting['frm_imp_cols'];

            $colTableName = 'frm_others_cols';
            $fixColNames = ['reference_date_col','customer_name_col','customer_code_col','frm_index1_col','frm_index2_col','frm_index3_col'];
            if ($templateSetting && $request['frmType'] == AppUtils::FORM_TYPE_INVOICE){
                $colTableName = 'frm_invoice_cols';
                $fixColNames = ['trading_date_col','invoice_no_col','invoice_date_col','customer_name_col','customer_code_col','invoice_amt_col','payment_date_col'];
            }
            foreach ($fixColNames as $fixColName){
                if (isset($templateSetting[$fixColName])){
                    $templateSetting2Save[$fixColName] = $templateSetting[$fixColName];
                }
            }

            $stamp2Saves = [];
            if ($stamps && count($stamps)){

                // assign_id -> stamp_id
                $stamp_ids = DB::table('mst_assign_stamp')
                ->whereIn('id',array_column($stamps,'stamp_assign_id'))
                ->select('id','stamp_id')
                ->get()
                ->keyBy('id')
                ->toArray();


                $companyStampIds = [];
                foreach ($stamps as $stamp){
                    // $companyStampIds[] = $stamp['mst_company_stamp_id'];
                    $stamp2Saves[] = [
                        'stamp_flg' => $stamp['stamp_flg'],
                        'stamp_id' => $stamp_ids[$stamp['stamp_assign_id']]->stamp_id,
                        'stamp_deg' => $stamp['stamp_deg'],
                        'stamp_left' => $stamp['stamp_left'],
                        'stamp_page' => $stamp['stamp_page'],
                        'stamp_top' => $stamp['stamp_top'],
                        'mst_company_id' => $user->mst_company_id,
                        'frm_template_id' => $templateId,
                        'stamp_date' => $stamp['stamp_date'],
                        'create_at' => Carbon::now(),
                        'create_user' => $user->getFullName(),
                    ];
                }
                // $myCompanyStampId = DB::table('mst_assign_stamp')->where('mst_user_id', $user->id)->where('stamp_flg', StampUtils::COMMON_STAMP)->pluck('stamp_id')->toArray();
                // $diff = array_diff($companyStampIds, $myCompanyStampId);
                // if (count($diff)){
                //     return $this->sendError('明細テンプレートの設定に失敗しました。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                // }
            }
            DB::transaction(function() use ($colTableName, $templateId, $templateSetting2Save, $user, $data, $editItem, $template, $stamp2Saves){
                DB::table($colTableName)
                    ->where('frm_template_id',$templateId)
                    ->delete();
                DB::table($colTableName)->insert($templateSetting2Save);

                DB::table('frm_template_placeholder')
                    ->where('frm_template_id',$templateId)
                    ->where('mst_company_id',$user->mst_company_id)
                    ->where('additional_flg',AppUtils::ADDITIONAL_FLG)
                    ->delete();

                if(count($data)){
                    DB::table('frm_template_placeholder')
                        ->insert($data);
                }

                // update table frm_template
                if(count($editItem)){
                    DB::table('frm_template')
                        ->where('id',$templateId)
                        ->update([
                            'frm_template_code' => $editItem['frm_template_code'],
                            'frm_type' => $editItem['frm_type'],
                            'frm_template_edit_flg' => $editItem['frm_template_edit_flg'],
                            'frm_template_access_flg' => $editItem['frm_template_access_flg'],
                            // PAC_5-2280
                            'title' => $editItem['title'] ?? "", // 件名
                            'message' => $editItem['message'] ?? "", // メッセージ
                            'address_change_flg' => $editItem['address_change_flg'] ?? CircularUtils::ADDRESS_CHANGE_FLG_INVALID, // 回覧順変更許可
                            'text_append_flg' => $editItem['text_append_flg'] ?? CircularUtils::TEXT_APPEND_FLG_INVALID, // テキスト追加許可
                            'hide_thumbnail_flg' => $editItem['hide_thumbnail_flg'] ?? CircularUtils::HIDE_THUMBNAIL_INVALID, // サムネイル非表示
                            'require_print' => $editItem['require_print'] ?? CircularUtils::REQUIRE_PRINT, // 捺印設定
                            'access_code_flg' => $editItem['access_code_flg'] ?? CircularUtils::ACCESS_CODE_INVALID, // アクセス_社内利用
                            'access_code' => $editItem['access_code'] ?? "", // アクセス_社内コード
                            'outside_access_code_flg' => $editItem['outside_access_code_flg'] ?? CircularUtils::OUTSIDE_ACCESS_CODE_INVALID, // アクセス_社外利用
                            'outside_access_code' => $editItem['outside_access_code'] ?? "", // アクセス_社外コード
                            're_notification_day' => $editItem['re_notification_day'] ?? null, // 再通知日
                            'auto_ope_flg' => $editItem['auto_ope_flg'] ?? FormIssuanceUtils::AUTO_OPE_SAVE, // 自動操作フラグ
                            // PAC_5-2280
                            'remarks'=> $editItem['remarks'],
                            'disabled_at'=>null,
                            'version'=> ($template->version + 1),
                            'update_at'=>Carbon::now(),
                            'update_user'=> $user->getFullName()
                        ]);
                }else{
                    DB::table('frm_template')
                        ->where('id',$templateId)
                        ->update([
                            'disabled_at'=>null,
                            'version'=> ($template->version + 1),
                            'update_at'=>Carbon::now(),
                            'update_user'=> $user->getFullName()
                        ]);
                }

                // DB::table('frm_template_stamp')->where('frm_template_id', $templateId)->delete();
                if (count($stamp2Saves)){
                    DB::table('frm_template_stamp')->insert($stamp2Saves);
                }
            });
            $template->storage_file_name = $this->getS3FileName($this->templateFileNamePrefix,$template->id,$template->file_name);
            return $this->sendResponse($template,'明細テンプレートを設定しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => " 明細テンプレートの設定に失敗しました。" ];
        }
    }

    public function templateUseHistory($templateId, Request $request) {
        $requestTime = Carbon::now()->format('Y/m/d H:i:s');
        $formUseHistory = DB::table('frm_imp_mgr')
            ->where('frm_template_id', $templateId)
            ->orderBy('request_datetime', 'desc')
            ->limit(10)->get();

        $result['request_time'] = $requestTime;
        $result['formUseHistory'] = $formUseHistory;
        return $this->sendResponse($result, '取得処理に成功しました。');

    }

    // PAC_5-2257

    /**
     * ダウンロード一覧取得
     * @param GetDownloadListAPIRequest $request
     * @return mixed
     */
    public function getDownloadList(GetDownloadListAPIRequest $request)
    {
        try {
            //アクセス認証
            $api_authority = DB::table('api_authentication')->where('api_name', 'HomePage')->first();
            if (!$api_authority || $api_authority->access_id != $request->accessId || $api_authority->access_code != $request->accessCode) {
                return $this->ApiResponse(__('message.false.api_authentication'), 0, '');
            }
            //ユーザ情報チェック
            $user_info = DB::table('mst_user')->where('email', $request->email)->where('state_flg', AppUtils::STATE_VALID)->first();
            if(!$user_info) {
                return $this->ApiResponse(__('message.false.userCheck', ['email' => $request->email]),0, '');
            }

            //条件と並び順
            $frm_template_code = $request->get('frm_template_code', '');
            $download_request_code = $request->get('download_request_code', '');

            $orderBy    = $request->get('orderBy', "request_datetime");
            $orderDir   =  AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));

            $download_info = DB::table('frm_imp_mgr')
                ->leftJoin('frm_template', 'frm_imp_mgr.frm_template_id', '=', 'frm_template.id')
                ->leftJoin('download_request', 'download_request.id', '=', 'frm_imp_mgr.download_request_id')
                ->select([
                    'frm_template.file_name as frm_template_name',
                    'frm_template.frm_template_code',
                    'frm_imp_mgr.download_request_code',
                    'frm_imp_mgr.imp_filename as input_filename',
                    'frm_imp_mgr.request_datetime',
                    'download_request.state',
                    'frm_imp_mgr.download_request_message as message',
                    'download_request.id as download_request_id'
                ])
                ->where('frm_imp_mgr.mst_user_id', $user_info->id)
                ->where(function ($download_info) {
                    $download_info->where(function ($download_info) {
                        $download_info->whereNull('download_request.id')
                            ->whereNotNull('frm_imp_mgr.download_request_message');
                    })
                        ->orWhere(function ($download_info) {
                            $download_info->where(DB::raw("download_request.download_period"), '>', Carbon::now())
                                ->where('download_request.state', '!=', DB::raw(DownloadUtils::REQUEST_DELETED));
                        });
                });

            $where = ['1=1'];
            $where_arg = [];

            $where[] = 'INSTR(frm_imp_mgr.download_request_code, ?)';
            $where_arg[] = "issue_bill_";

            if($frm_template_code) {
                $where[] = 'INSTR(frm_template.frm_template_code, ?)';
                $where_arg[] = $frm_template_code;
            }
            if($download_request_code) {
                $where[] = 'INSTR(frm_imp_mgr.download_request_code, ?)';
                $where_arg[] = $download_request_code;
            }

            if($orderBy) {
                $download_info->orderBy($orderBy, $orderDir);
            }
            $download_info = $download_info->whereRaw(implode(" AND ", $where), $where_arg)->get()->toArray();

            foreach ($download_info as &$download) {
                $download->download_request_id = AppUtils::encrypt($download->download_request_id);
                if($download->message != null){
                    $download->state = 9;
                }else{
                    if($download->frm_template_code == null){
                        $download->message = __('message.warning.form_issuance.no_template');
                    }
                    if($download->state == null){
                        $download->state = 0;
                    }
                }
            }

            $result = ["download_info" => $download_info];

            return $this->ApiResponse( '', 1, $result);
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->ApiResponse(__('message.false.download_request.get_data'), 0, '');
        }
    }

    /**
     * ダウンロード
     * @param GetDownloadDataAPIRequest $request
     * @return mixed
     */
    public function getDownloadData(GetDownloadDataAPIRequest $request)
    {
        try {
            //アクセス認証
            $api_authority = DB::table('api_authentication')->where('api_name', 'HomePage')->first();
            if (!$api_authority || $api_authority->access_id != $request->accessId || $api_authority->access_code != $request->accessCode) {
                return $this->ApiResponse(__('message.false.api_authentication'), 0, '');
            }

            $id = AppUtils::decrypt($request->download_request_id);

            $download_data = DB::table('download_request')
                ->Join('download_wait_data', 'download_wait_data.download_request_id', '=', 'download_request.id')
                ->select([
                    'download_request.file_name',
                    'download_wait_data.data',
                    'download_request.download_period'
                ])
                ->where('download_request.id', $id)
                ->first();

            if($download_data->download_period < Carbon::now()){
                return $this->ApiResponse(__('message.false.download_request.download_period'), 0, '');
            }

            $result = ["download_data" => $download_data];

            return $this->ApiResponse( '', 1, $result);
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->ApiResponse(__('message.false.download_request.file_detail_get'), 0, '');
        }
    }

    /**
     * レスポンス
     * @param $result_message
     * @param $result_code
     * @param $result_data
     * @return mixed
     */
    private function ApiResponse($result_message, $result_code, $result_data)
    {
        return response()->json(['result_code' => $result_code, 'result_message' => $result_message, 'result_data' => $result_data]);
    }

    /**
     * 契約サイト帳票CSV取込
     * @param CreateCsvFormImportFromContractApiRequest $request
     * @return mixed
     */
    public function uploadContractCsv(CreateCsvFormImportFromContractApiRequest $request)
    {
        try {
            //アクセス認証
            $api_authority = DB::table('api_authentication')->where('api_name', 'HomePage')->first();
            if (!$api_authority || $api_authority->access_id != $request->accessId || $api_authority->access_code != $request->accessCode) {
                return $this->ApiResponse(__('message.false.api_authentication'), 0, '');
            }
            $login_user = DB::table('mst_user')->where('email', $request->email)->where('state_flg', AppUtils::STATE_VALID)->first();
            // ユーザ存在チェック
            if(!$login_user){
                return $this->ApiResponse(__('message.false.userCheck', ['email' => $request->email]), 0, '');
            }
            // 帳票テンプレート存在チェック
            $frm_template = DB::table('frm_template')
                ->where('frm_template_code', $request->frm_template_code)
                ->where('mst_company_id', $login_user->mst_company_id)
                ->first();
            if(!$frm_template){
                return $this->ApiResponse(__('message.false.form_issuance.template_code'), 0, '');
            }
            // 帳票テンプレート有効チェック
            if($frm_template->disabled_at != null){
                return $this->ApiResponse(__('message.false.form_issuance.template_disabled'), 0, '');
            }
            // 帳票テンプレート完了保存チェック
            if($frm_template->auto_ope_flg != 1){
                return $this->ApiResponse(__('message.false.form_issuance.template_complete'), 0, '');
            }
            //追加テキスト桁数チェック
            $text = '';
            if(isset($request->text)){
                $text = $request->text;
                if(is_numeric($request->text)){
                    $text = (string)$request->text;
                }
                if($text != '' && mb_strlen($text) > 30){
                    return $this->ApiResponse(__('message.false.form_issuance.text_size'), 0, '');
                }
            }
            // PAC_5-2792
            // 帳票発行文書数上限チェック
            $constraints = DB::table('mst_constraints')
                ->where('mst_company_id', '=', $login_user->mst_company_id)
                ->first();
            // 請求書発行件数
            $frmInvoiceDocumentCnt = DB::table('frm_invoice_data as f')
                ->join('circular as c', 'f.circular_id', 'c.id')
                ->where('f.mst_company_id', '=', $login_user->mst_company_id)
                // 今月
                ->where(DB::raw("DATE_FORMAT(c.final_updated_date,'%Y-%m-%d')"), '>=', Carbon::now()->format('Y-m-01'))
                // 回覧状態が 削除 以外
                ->where('c.circular_status', '<>', CircularUtils::DELETE_STATUS)
                ->count();
            // その他発行件数
            $frmOtherDocumentCnt = DB::table('frm_others_data as f')
                ->join('circular as c', 'f.circular_id', 'c.id')
                ->where('f.mst_company_id', '=', $login_user->mst_company_id)
                // 今月
                ->where(DB::raw("DATE_FORMAT(c.final_updated_date,'%Y-%m-%d')"), '>=', Carbon::now()->format('Y-m-01'))
                // 回覧状態が 削除 以外
                ->where('c.circular_status', '<>', CircularUtils::DELETE_STATUS)
                ->count();
            // 確認した際にその件数が文書の発行文書数を超えていた
            if($frmInvoiceDocumentCnt + $frmOtherDocumentCnt >= $constraints->max_frm_document){
                return $this->ApiResponse(__('message.false.form_issuance.max_frm_document'), 0, '');
            }

            // ダウンロードコードユニークを作成
            $download_request_code = 'issue_bill_'
                .config('app.edition_flg').'_'
                .config('app.server_env').'_'
                .config('app.server_flg').'_'
                .strtoupper(md5(uniqid(session_create_id(), true))).'_'
                .$login_user->mst_company_id.'_'
                .Carbon::now()->format('YmdHisms').'_'
                .$login_user->id;
            if($text != ''){
                $download_request_code = $download_request_code.'_'.$text;
            }

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();
            if ($fileExtension != 'csv') {
                return $this->ApiResponse(__('message.false.form_issuance.csv_upload'), 0, '');
            }
            $spl_object = new SplFileObject($file, 'rb');
            $spl_object->seek(filesize($file));
            $uploadDataCnt = $spl_object->key();
            if($frmInvoiceDocumentCnt + $frmOtherDocumentCnt + $uploadDataCnt > $constraints->max_frm_document){
                // 作成を中止
                return $this->sendError(__('message.false.form_issuance.over_max_frm_document', ['max_frm_document件' => $constraints->max_frm_document]), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            $data = [
                'mst_company_id' => $login_user->mst_company_id,
                'frm_template_id' => $frm_template->id,
                'frm_template_ver' => $frm_template->version,
                'mst_user_id' => $login_user->id,
                'request_datetime' => Carbon::now(),
                'request_method' => AppUtils::REQUEST_METHOD_WEB_SCREEN,
                'imp_filename' => $originalName,
                'imp_status' => AppUtils::FORM_IMPORT_WAITING,
                'imp_rows' => 0,
                'registered_rows' => 0,
                'create_at' => Carbon::now(),
                'create_user' => $login_user->family_name.' '.$login_user->given_name,
                'version' => 0,
                'download_request_code' => $download_request_code,
            ];
            $frm_imp_mgr_id = DB::table('frm_imp_mgr')->insertGetId($data);

            if ($frm_imp_mgr_id) {
                $altFileNameS3 = $this->getS3FileName($this->importFileNamePrefix, $frm_imp_mgr_id, $originalName);
                //create directory on S3
                $s3path = $this->getPathToS3($this->importTypeDirectory, $login_user->mst_company_id);

                //Save to S3
                Storage::disk($this->remoteStorage)->putfileAs($s3path.'/', $file, $altFileNameS3, $this->storagePutOption);

                $job = new FormIssuanceImportJob($frm_imp_mgr_id, $login_user->mst_company_id, 'Contract');
                $job->onQueue("form_issuance_import");
                dispatch($job);
                return $this->ApiResponse( '', 1, $result = ["download_request_code" => $download_request_code]);
            } else {
                return $this->ApiResponse('CSVのアップロードに失敗しました。', 0, '');
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->ApiResponse(__('message.false.system_error'), 0, '');
        }
    }
    // PAC_5-2257

    public function uploadCSVImport($frm_template_id, CreateCsvFormImportApiRequest $request)
    {
        $login_user = $request->user();
        $frm_template_version = $request->get('frm_template_version');
        Log::info('frm_template_version: ' . $frm_template_version);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();
            if ($fileExtension != 'csv') {
                return $this->sendError('CSVのアップロードに失敗しました。CSVファイルであるかご確認ください。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            $frm_template = DB::table('frm_template')->where('id', $frm_template_id)->first();
            if($frm_template->version != $frm_template_version){
                return $this->sendError('明細インポートに失敗しました。明細テンプレートのバージョンを対応できません。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            // PAC_5-2792
            // 帳票発行文書数上限チェック
            $constraints = DB::table('mst_constraints')
                ->where('mst_company_id', '=', $login_user->mst_company_id)
                ->first();
            // 請求書発行件数
            $frmInvoiceDocumentCnt = DB::table('frm_invoice_data as f')
                ->join('circular as c', 'f.circular_id', 'c.id')
                ->where('f.mst_company_id', '=', $login_user->mst_company_id)
                // 今月
                ->where(DB::raw("DATE_FORMAT(c.final_updated_date,'%Y-%m-%d')"), '>=', Carbon::now()->format('Y-m-01'))
                // 回覧状態が 削除 以外
                ->where('c.circular_status', '<>', CircularUtils::DELETE_STATUS)
                ->count();
            // その他発行件数
            $frmOtherDocumentCnt = DB::table('frm_others_data as f')
                ->join('circular as c', 'f.circular_id', 'c.id')
                ->where('f.mst_company_id', '=', $login_user->mst_company_id)
                // 今月
                ->where(DB::raw("DATE_FORMAT(c.final_updated_date,'%Y-%m-%d')"), '>=', Carbon::now()->format('Y-m-01'))
                // 回覧状態が 削除 以外
                ->where('c.circular_status', '<>', CircularUtils::DELETE_STATUS)
                ->count();
            // 確認した際にその件数が文書の発行文書数を超えていた
            if($frmInvoiceDocumentCnt + $frmOtherDocumentCnt >= $constraints->max_frm_document){
                // 作成を中止
                return $this->sendError(__('message.false.form_issuance.max_frm_document'), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }
            $spl_object = new SplFileObject($file, 'rb');
            $spl_object->seek(filesize($file));
            $uploadDataCnt = $spl_object->key();
            // CSV読み込んだ時点で上限数を超える場合
            if($frmInvoiceDocumentCnt + $frmOtherDocumentCnt + $uploadDataCnt > $constraints->max_frm_document){
                // 作成を中止
                return $this->sendError(__('message.false.form_issuance.over_max_frm_document', ['max_frm_document件' => $constraints->max_frm_document]), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            $data = [
                'mst_company_id' => $login_user->mst_company_id,
                'frm_template_id' => $frm_template_id,
                'frm_template_ver' => $frm_template->version,
                'mst_user_id' => $login_user->id,
                'request_datetime' => Carbon::now(),
                'request_method' => AppUtils::REQUEST_METHOD_WEB_SCREEN,
                'imp_filename' => $originalName,
                'imp_status' => AppUtils::FORM_IMPORT_WAITING,
                'imp_rows' => 0,
                'registered_rows' => 0,
                'create_at' => Carbon::now(),
                'create_user' => $login_user->getFullName(),
                'version' => 0
            ];
            $frm_imp_mgr_id = DB::table('frm_imp_mgr')->insertGetId($data);

            if ($frm_imp_mgr_id) {
                $altFileNameS3 = $this->getS3FileName($this->importFileNamePrefix, $frm_imp_mgr_id, $originalName);
                //create directory on S3
                $s3path = $this->getPathToS3($this->importTypeDirectory,$login_user->mst_company_id);

                //Save to S3
                Storage::disk($this->remoteStorage)->putfileAs($s3path.'/', $file, $altFileNameS3, $this->storagePutOption);

                $job = new FormIssuanceImportJob($frm_imp_mgr_id, $login_user->mst_company_id);
                $job->onQueue("form_issuance_import");
                dispatch($job);
                return $this->sendResponse( $frm_imp_mgr_id,'CSVのアップロードに成功しました。');
            } else {
                return $this->sendError('CSVのアップロードに失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('CSVのアップロードに失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getCSVFormImportUploadStatus($templateId, $csvId, Request $request)
    {
        try {
            $csvFile = DB::table('frm_imp_mgr')
                ->where([
                    'id' => $csvId,
                    'frm_template_id' => $templateId,
                ])->first();

            return $this->sendResponse($csvFile, 'get csv form import done');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getFileCSVImport($templateId, $csvId, Request $request) {
        $login_user = $request->user();
        try {
            $csvFile = DB::table('frm_imp_mgr')
                ->where([
                    'id' => $csvId,
                    'frm_template_id' => $templateId,
                    ])->whereNotIn('imp_status', [0, 1])->first();
            if (isset($csvFile)) {
                //create directory on S3
                $path = $this->getPathToS3($this->importTypeDirectory,$login_user->mst_company_id);
                $fileName = "form_imp_$csvFile->id.csv";
                $path = $path . "/".$fileName;
                if (Storage::disk($this->remoteStorage)->exists($path)) {
                    $getFile = Storage::disk($this->remoteStorage)->get($path);
                    return $this->sendResponse([
                        'file_name' => $csvFile->imp_filename,
                        'file_data' => \base64_encode($getFile)
                    ], 'CSVファイルダウンロードに成功しました。');
                }else{
                    return $this->sendError('CSVファイルダウンロードに失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                return $this->sendError('CSVファイルダウンロードに失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('CSVファイルダウンロードに失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getLogTemplateCSV($templateId, $logId, Request $request) {
        $login_user = $request->user();
        try {
            $logFile = DB::table('frm_imp_mgr')
                ->where([
                    'id' => $logId,
                    'frm_template_id' => $templateId,
                ])->whereNotIn('imp_status', [0, 1])->first();
            if (isset($logFile)) {
                //create directory on S3
                $path = $this->getPathToS3($this->importTypeDirectory,$login_user->mst_company_id);

                $fileName = "form_imp_$logFile->id.log";
                if (Storage::disk($this->remoteStorage)->exists($path)) {
                    $relative_path = $path.'/'.$fileName;
                    $getFile = Storage::disk($this->remoteStorage)->get($relative_path);

                    return $this->sendResponse([
                        'file_name' => $fileName,
                        'file_data' => \base64_encode($getFile)
                    ], 'ログファイルダウンロードに成功しました。');
                }else{
                    return $this->sendError('ログファイルダウンロードに失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                return $this->sendError('ログファイルダウンロードに失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('ログファイルダウンロードに失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTemplateDepartment($templateId, Request $request){
        try {
            $user = $request->user();
            $user_create_id = DB::table('frm_template')->where('id',$templateId)->value('mst_user_id');
            $user_create = DB::table('mst_user_info')->select('mst_user_id','mst_department_id')->where('mst_user_id',$user_create_id)->first();

            return $this->sendResponse($user_create,'check department done');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendResponse(['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST],"ファイルを取得できませんでした。");
        }
    }

    private function isValidTemplateCode($code) {
        $result = false;
        if (preg_match('/^[a-zA-Z0-9]*$/', $code)) {
            $result = true;
        }
        return $result;

    }

    public function uploadExpTemplate(CreateExpTemplateAPIRequest $request) {
        $login_user = $request->user();
        $template_id = 0;
        try{
            $file = $request->file('file');
            $originName = $file->getClientOriginalName();
            $fileextension = $file->getClientOriginalExtension();

            if ($fileextension != 'xlsx') {
                return $this->sendError('明細Expテンプレートの登録に失敗しました。xlsxファイルであるかご確認ください。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            $exp_max_template_file = (DB::table('mst_constraints')->where('mst_company_id',$login_user->mst_company_id)->value('exp_max_template_file'));
            $countExpFrmTemplate = DB::table('frm_exp_template')->where('mst_company_id',$login_user->mst_company_id)->count();
            if( $countExpFrmTemplate >= $exp_max_template_file ){
                return $this->sendError('Expテンプレート登録上限数'.$exp_max_template_file.'個まで登録が可能です。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            //insert table frm_exp_template
            $userName = $login_user->getFullName();
            $data =[
                'mst_company_id' => $login_user->mst_company_id,
                'mst_user_id' => $login_user->id,
                'file_name' => $originName,
                'remarks' => $request['remarks'],
                'display_order' => 0,
                'create_at' => Carbon::now(),
                'create_user' => $userName,
            ];

            $template_id = DB::table('frm_exp_template')->insertGetId($data);
            $data['id'] = $template_id;

            //the process of saving files to S3
            if($template_id){
                $altFileNameS3 = $this->getS3FileName($this->expTemplateFileNamePrefix,$template_id,$originName);
                //create directory on S3
                $s3path = $this->getPathToS3($this->expTemplateTypeDirectory,$login_user->mst_company_id);

                //Save to S3
                Storage::disk($this->remoteStorage)->putfileAs($s3path.'/', $file, $altFileNameS3, $this->storagePutOption);

                return $this->sendResponse($data, '明細Expテンプレートの登録に成功しました。');
            }else{
                return $this->sendError('明細Expテンプレートの登録に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            //delete record when save to s3 fails
            DB::table('frm_exp_template')->where('id',$template_id)->delete();
            return $this->sendError('明細Expテンプレートの登録に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getListExpTemplate(Request $request)
    {
        try {
            $user = $request->user();
            $user_info = DB::table('mst_user_info')
                ->where('mst_user_id', $user->id)->first();
            if(!$user_info) {
                return $this->sendError('Permission denied.',403);
            }

            $filter_file_name = $request->get('file_name', '');
            $filter_remarks = $request->get('remarks', '');

            $limit      = AppUtils::normalizeLimit($request->get('limit', 10), 10);
            $orderBy    = $request->get('orderBy', "");
            $orderDir   =  AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));

            $arrOrder   = ['file_name' => 'file_name', 'remarks' => 'remarks', 'update_user' => 'update_user', 'update_at' => 'update_at'];
            $orderBy = isset($arrOrder[$orderBy]) ? $arrOrder[$orderBy] : 'create_at';

            $template_files = DB::table('frm_exp_template')
                ->select(
                    'id',
                    'mst_company_id',
                    'mst_user_id',
                    'file_name',
                    'display_order',
                    'remarks',
                    'version',
                    'create_at',
                    'create_user',
                    DB::raw('IF(ISNULL(update_at), create_user, update_user) as update_user'),
                    DB::raw('IFNULL(update_at, create_at) as update_at')
                )->where('mst_company_id', $user->mst_company_id);

            $where = ['1=1'];
            $where_arg = [];

            if($filter_file_name) {
                $where[] = 'INSTR(file_name, ?)';
                $where_arg[] = $filter_file_name;
            }
            if($filter_remarks) {
                $where[] = 'INSTR(remarks, ?)';
                $where_arg[] = $filter_remarks;
            }

            if($orderBy) {
                $template_files->orderBy($orderBy, $orderDir);
            }
            $template_files = $template_files->whereRaw(implode(" AND ", $where), $where_arg)->paginate($limit);

            return $this->sendResponse($template_files,'送信文書の取得処理に成功しました。');


        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('テンプレートファイル情報取得処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function showExpTemplate($frmTemplateId, Request $request) {
        $frm_template = DB::table('frm_exp_template')
            ->where('id', $frmTemplateId)
            ->get();

        if (count($frm_template)) {
            return $this->sendResponse(['frm_template' => $frm_template[0]], 'get template done');
        } else {
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "template not found" ];
        }
    }

    public function deleteExpTemplate($frmTemplateId, Request $request) {
        try {
            $user = $request->user();
            $tplFolder = config('app.s3_storage_form_template_folder');
            $templateType = $this->expTemplateTypeDirectory;
            $template = DB::table('frm_exp_template')->where('id', $frmTemplateId)->first();
            DB::table('frm_exp_template')
                ->where('id', $frmTemplateId)
                ->delete();
            $path = $this->rootDirectory.'/'.$tplFolder.'/'.$this->templateDirectory.'/'.$user->mst_company_id.'/'.$templateType;
            $fileNameS3 = $this->getS3FileName($this->expTemplateFileNamePrefix,$template->id,$template->file_name);

            $relative_path = $path.'/'.$fileNameS3;
            Storage::disk($this->remoteStorage)->delete($relative_path);

            return $this->sendResponse(true,'明細Expテンプレートを削除しました。');
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('明細Expテンプレートの削除に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getS3FileName($file_name_directory, $template_id, $file_name){
        $split_name = explode(".", $file_name);
        $fileName = $file_name_directory.$template_id . '.' . end($split_name);
        return $fileName;
    }

    private function getPathToS3($fileTypeDirectory,$companyId) {
        $s3path = $this->rootDirectory;
        $s3frmTemplateFolder = config('app.s3_storage_form_template_folder');

        $isFolderExist = Storage::disk($this->remoteStorage)->exists($s3path);
        if (!$isFolderExist) {
            Storage::disk($this->remoteStorage)->makeDirectory($s3path);
            Storage::disk($this->remoteStorage)->makeDirectory($s3path.'/'.$s3frmTemplateFolder);

            $s3path = $s3path.'/'.$s3frmTemplateFolder.'/'. $this->templateDirectory.'/'. $companyId.'/'.$fileTypeDirectory;
            Storage::disk($this->remoteStorage)->makeDirectory($s3path);
        }else{
            $s3path = $s3path.'/'.$s3frmTemplateFolder.'/'. $this->templateDirectory;
            $isFolderExist = Storage::disk($this->remoteStorage)->exists($s3path);
            if (!$isFolderExist){
                Storage::disk($this->remoteStorage)->makeDirectory($s3path);
                Storage::disk($this->remoteStorage)->makeDirectory($s3path.'/'. $companyId);

                $s3path = $s3path.'/'.$companyId.'/'.$fileTypeDirectory;
                Storage::disk($this->remoteStorage)->makeDirectory($s3path);
            }else{
                $s3path = $s3path.'/'.$companyId.'/'.$fileTypeDirectory;
                $isFolderExist = Storage::disk($this->remoteStorage)->exists($s3path);
                if (!$isFolderExist){
                    Storage::disk($this->remoteStorage)->makeDirectory($s3path);
                }
            }
        }

        return $s3path;
    }

    public function getExpTemplate($templateId, Request $request) {
        $user = $request->user();
        try {
            $file = DB::table('frm_exp_template')
                ->where('id', $templateId)
                ->first();

            $fileEncode = $this->getContentTemplateBase64($file, $user, true);
            $fileName = $file->file_name;

            return $this->sendResponse(['file_name' => $fileName, 'file_type' => 'xlsx', 'file_data' => $fileEncode], '明細テンプレートダウンロードに成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getFrmIndex(Request $request) {
        $user = $request->user();
        try {
            $frmIndex = DB::table('frm_index')
                ->where('mst_company_id', $user->mst_company_id)
                ->get();
            return $this->sendResponse($frmIndex,'明細項目設定の取得に成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
