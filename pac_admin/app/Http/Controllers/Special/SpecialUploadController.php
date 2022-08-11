<?php

namespace App\Http\Controllers\Special;

use App\Http\Utils\AppUtils;
use App\Http\Controllers\AdminController;
use App\Http\Utils\SpecialAppApiUtils;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use DB;
use App\Models\Department;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use Session;

class SpecialUploadController extends AdminController
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
    public function index(Request $request)
    {
        $user = $request->user();

        $action = $request->get('action', '');

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $orderBy = $request->get('orderBy', "tc.original_template_file_id");
        $orderDir = $request->get('orderDir', "DESC");
        $circular_name = $request->get('circular_name');
        $display_name = $request->get('display_name');
        //登録日時 From
        $create_from = $request->get('create_from');
        //登録日時 To
        $create_to = $request->get('create_to');
        //公開期限 From
        $open_period_from = $request->get('open_period_from');
        //公開期限 To
        $open_period_to = $request->get('open_period_to');
        //利用可能状態
        $is_enable = $request->get('state');

        $fileList = DB::table('template_file')
            ->select(DB::raw('id'))
            ->where(DB::raw('file_name'), 'like', '%' . $circular_name . '%')
            ->get()->pluck('id')->toArray();
        //API開始
        $specialClient = SpecialAppApiUtils::getAuthorizeClient();
        if (!$specialClient) {
            return response()->json(['status' => false,
                'message' => ['Cannot connect to Special App']
            ]);
        }
        //SRS-012 テンプレート文書設定値取得
        $getTemplateInfo = "/sp/api/get-template-circulars-setting";
        $response = $specialClient->post($getTemplateInfo,
            [
                RequestOptions::JSON => [
                    "company_id" => $user->mst_company_id,
                    "env_flg" => config('app.pac_app_env'),
                    "edition_flg" => config('app.pac_contract_app'),
                    "server_flg" => config('app.pac_contract_server'),
                    "search_option" => [
                        "file_name" => $fileList,
                        "display_name" => $display_name,
                        "state" => $is_enable,
                        "create_at_from" => $create_from,
                        "create_at_to" => $create_to,
                        "open_period_from" => $open_period_from,
                        "open_period_to" => $open_period_to,
                        "order_dir" => $orderDir,
                        "order_by" => ($orderBy == "template_file_name" || $orderBy == "is_enable") ? "tc.original_template_file_id" : $orderBy,
                    ]
                ]
            ]);
        $response_dencode = json_decode($response->getBody(), true);  //配列へ

        if ($response->getStatusCode() == 200) {
            $response_body = json_decode($response->getBody(), true);  //配列へ
            if ($response_body['status'] == "success") {
                $itemsUpload = $response_body['result']['template_circulars_setting'];
                $file_names = DB::table('template_file')
                    ->select(DB::raw('id, file_name'))
                    ->get();
                foreach ($itemsUpload as &$item) {
                    $item['open_period'] = Carbon::parse($item['open_period'])->format('Y-m-d');
                    if ($item['open_period'] == '9999-12-31') {
                        $item['open_period'] = '';
                    }
                    foreach ($file_names as $fileItem) {
                        if ($item['template_file_id'] == $fileItem->id) {
                            $item['template_file_name'] = $fileItem->file_name;
                        }
                    }
                }
                if ($orderBy == "template_file_name") {
                    foreach ($itemsUpload as $item) {
                        $val[] = $item[$orderBy];
                    }
                    if (strtolower($orderDir) == "asc") {
                        array_multisort($val, SORT_ASC, SORT_STRING, $itemsUpload);
                    } else {
                        array_multisort($val, SORT_DESC, SORT_STRING, $itemsUpload);
                    }
                } elseif ($orderBy == "is_enable") {
                    foreach ($itemsUpload as $item) {
                        $val[] = $item[$orderBy];
                    }
                    if (strtolower($orderDir) == "asc") {
                        array_multisort($val, SORT_ASC, SORT_NUMERIC, $itemsUpload);
                    } else {
                        array_multisort($val, SORT_DESC, SORT_NUMERIC, $itemsUpload);
                    }
                }

                $itemsUpload = new LengthAwarePaginator(array_slice($itemsUpload, ($page - 1) * $limit, $limit, false), count($itemsUpload), $limit);
                $itemsUpload->setPath($request->url());
                $itemsUpload->appends(request()->input()); // sort params etc
            } else {
                Log::error('Get Template Circulars Setting:' . $response_body['message']);
                Log::error($response_dencode);
                return response()->json(['status' => false, 'message' => [$response_dencode['message']]]);
            }
        } else {
            Log::error('Api storeBoard companyId:' . $user->mst_company_id);
            Log::error($response_dencode);
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
        }
        $company_users = (new User())->getUsersByDepartments($user->mst_company_id);
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', strtolower($orderDir) == "asc" ? "desc" : "asc");
        $this->assign('itemsUpload', $itemsUpload);
        $this->assign('circular_name', $circular_name);
        $this->assign('display_name', $display_name);
        $this->assign("create_from", $create_from);
        $this->assign("create_to", $create_to);
        $this->assign("open_period_from", $open_period_from);
        $this->assign("open_period_to", $open_period_to);
        $this->assign("state", $is_enable);
        $this->assign('company_users', $company_users);
        $this->setMetaTitle('文書登録');

        return $this->render('Special.Upload.index');
    }

    function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $login_user = $request->user();
            //API開始
            $specialClient = SpecialAppApiUtils::getAuthorizeClient();
            if (!$specialClient) {
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to Special App']
                ]);
            }
            if ($request->get('template_update_at') == "") {
                $open_period = Carbon::parse('9999-12-31')->toDateTimeString();
            } else {
                $open_period = $request->get('template_update_at');
            }
            Log::debug($request->get('template_update_at'));
            //更新
            // SRS-011 テンプレート文書登録・更新
            $template_file_local = DB::table('template_file')->where('id', $request->get('id'))->first();
            $arr = explode('.', $request->get('display_name'));
            if ($arr && !in_array($arr[count($arr) - 1], ['xlsx', 'xls', 'docx', 'doc'])) {
                $file_name_arr = explode('.', $template_file_local->storage_file_name);
                $Suffix = $file_name_arr ? $file_name_arr[count($file_name_arr) - 1] : '';
            } else {
                $Suffix = '';
            }

            $storage_file_name = $Suffix ? $request->get('display_name') . '.' . $Suffix : $request->get('display_name');
            Session::flash('file_names', $storage_file_name);
            $receiveInfo = "/sp/api/upsert-template";
            $response = $specialClient->post($receiveInfo,
                [
                    RequestOptions::JSON => [
                        "company_id" => $login_user->mst_company_id,
                        "env_flg" => config('app.pac_app_env'),
                        "edition_flg" => config('app.pac_contract_app'),
                        "server_flg" => config('app.pac_contract_server'),
                        "template_info" => [
                            "original_template_file_id" => $request->get('id'),
                            "display_name" => $storage_file_name,
                            "open_period" => $open_period,
                            "is_enable" => $request->get('state'),
                            "create_admin_user_id" => $request->get('template_create_user'),
                            "receive_circular_users" => json_decode($request->get('receiveCircularUsers')),
                        ]
                    ]
                ]);
            $response_dencode = json_decode($response->getBody(), true);  //配列へ

            if ($response->getStatusCode() == 200) {
                if ($response_dencode['status'] == "error") {
                    Log::error('Upsert Template:' . $response_dencode['message']);
                    Log::error($response_dencode);
                    return response()->json(['status' => false, 'message' => [$response_dencode['message']]]);
                }
            } else {
                Log::error('Api storeBoard companyId:' . $login_user->mst_company_id);
                Log::error($response_dencode);
                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
            }
            DB::commit();
            //更新
            return response()->json(['status' => true, 'message' => [__('message.success.template_save')]]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
        }
    }

    function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $login_user = $request->user();
            //API開始
            $specialClient = SpecialAppApiUtils::getAuthorizeClient();
            if (!$specialClient) {
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to Special App']
                ]);
            }
            $template_file_ids = $request->get('ids');
            $s3_files = DB::table('template_file')
                ->where('mst_company_id', $login_user->mst_company_id)
                ->whereIn('id', $template_file_ids)
                ->select('location','storage_file_name')
                ->get();
            $file_names = [];
            foreach ($s3_files as $s3_file) {
                $file_names[] = $s3_file->storage_file_name;
                $url = str_replace(env('AWS_URL', ''), '', $s3_file->location);
                Storage::disk('s3')->delete($url);
            }
            Session::flash('file_names', $file_names);
            DB::table('template_placeholder_data')
                ->join('template_file', 'template_placeholder_data.template_file_id', '=', 'template_file.id')
                ->where('template_file.mst_company_id', $login_user->mst_company_id)
                ->whereIn('template_placeholder_data.template_file_id', $template_file_ids)
                ->delete();

            DB::table('template_file')
                ->where('mst_company_id', $login_user->mst_company_id)
                ->whereIn('id', $template_file_ids)
                ->delete();

            //削除
            DB::table('template_file')
                ->whereIn('id', $request->get('ids'))
                ->update([
                    'is_generation_flg' => 1,
                    'template_update_user' => $login_user->family_name . $login_user->given_name,
                    'template_update_at' => Carbon::now()
                ]);
            $receiveInfo = "/sp/api/delete-template";
            $response = $specialClient->post($receiveInfo,
                [
                    RequestOptions::JSON => [
                        "company_id" => $login_user->mst_company_id,
                        "env_flg" => config('app.pac_app_env'),
                        "edition_flg" => config('app.pac_contract_app'),
                        "server_flg" => config('app.pac_contract_server'),
                        "template_file_ids" => $template_file_ids
                    ]
                ]);
            $response_dencode = json_decode($response->getBody(), true);  //配列へ

            if ($response->getStatusCode() == 200) {
                if ($response_dencode['status'] == "error") {
                    DB::rollBack();
                    Log::error('Delete Template:' . $response_dencode['message']);
                    Log::error($response_dencode);
                    return response()->json(['status' => false, 'message' => [$response_dencode['message']]]);
                }
            } else {
                DB::rollBack();
                Log::error('Api storeBoard companyId:' . $login_user->mst_company_id);
                Log::error($response_dencode);
                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => [__('message.success.template_delete')]]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
        }
    }

    function upload(Request $request)
    {
        $login_user = $request->user();
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['status' => false, 'message' => 'テンプレート取込失敗しました。時間をおいて再度お試しください。']);
            }
            // ファイル保存
            $file = $request->file('file');
            $file_name = $request->get('fileName');
            $display_name = $request->get('displayName');
            $fileextension = $file->getClientOriginalExtension();

            $edition_flg = config('app.pac_contract_app');
            $env_flg = config('app.pac_app_env');
            $server_flg = config('app.pac_contract_server');
            $company_id = $login_user->mst_company_id;
            $user_id = $login_user->id;
            $templateDirectory = $edition_flg . '/' . $env_flg . '/' . $server_flg;
            if (!in_array($fileextension, ['xlsx', 'xls', 'docx', 'doc',])) {
                return response()->json(['status' => false, 'message' => '対応していない拡張子のファイルです']);
            }

            $altFileName = explode(".", (microtime(true) . ""))[0] . '_' . $user_id . '.' . $fileextension;

            //ローカルで保存
            $file_path = storage_path("app/special/$edition_flg/$env_flg/$server_flg/$company_id/$user_id/");
            if (!is_dir($file_path)) {
                mkdir($file_path, 0755, true);
            }
            $realFileExtension = $file->getClientOriginalExtension();
            $unique = strtoupper(md5(uniqid(session_create_id(), true)));
            $Path = $file_path . "/$unique.$realFileExtension";
            copy($file, $Path);

            $fileDate = \base64_encode(\file_get_contents($Path));
            if (!$fileDate) {
                return response()->json(['status' => false, 'message' => ['文書データ取得処理に失敗しました']]);
            }

            $userName = $login_user->family_name . $login_user->given_name;
            unlink($Path);

            DB::beginTransaction();

            //S3テンプレート用ディレクトリ存在確認
            $s3path = config('filesystems.prefix_path') . '/' .config('app.s3_storage_root_folder');
            $isFolderExist = Storage::disk('s3')->exists($s3path);
            if (!$isFolderExist) {
                Storage::disk('s3')->makeDirectory($s3path);
                Storage::disk('s3')->makeDirectory($s3path . '/template');

                $s3path = $s3path . '/' . 'template/' . $templateDirectory . $company_id;
                Storage::disk('s3')->makeDirectory($s3path);
            } else {
                $s3path = $s3path . '/' . 'template/' . $templateDirectory;
                if (!$isFolderExist) {
                    Storage::disk('s3')->makeDirectory($s3path);
                    Storage::disk('s3')->makeDirectory($s3path . '/' . $company_id);
                    $s3path = $s3path . '/' . $company_id;
                } else {
                    $s3path = $s3path . '/' . $company_id;
                    $isFolderExist = Storage::disk('s3')->exists($s3path);
                    if (!$isFolderExist) {
                        Storage::disk('s3')->makeDirectory($s3path);
                    }
                }
            }

            //S3アップロード処理
            Storage::disk('s3')->putfileAs($s3path . '/', $file, $altFileName, 'pub');
            //保存したS3完全URLの取得
            $s3url = Storage::disk('s3')->url($s3path . '/' . $altFileName);

            //API開始
            $specialClient = SpecialAppApiUtils::getAuthorizeClient();
            if (!$specialClient) {
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to Special App']
                ]);
            }
            $arr = explode('.', $display_name);
            if ($arr && !in_array($arr[count($arr) - 1], ['xlsx', 'xls', 'docx', 'doc',])) {
                $Suffix = $fileextension;
            } else {
                $Suffix = '';
            }
            if (in_array($fileextension, ['xlsx', 'xls'])) {
                $extension = '0';
                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file);
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

                $spreadsheet = $reader->load($file);
                // 読み込むシートを指定(1シート目)
                $sheet = $spreadsheet->getSheet(0);
                //行番号、ループ用
                $row = 1;

                $placeholderList = array();
                //セル番地とセルの情報を取得
                foreach ($sheet->getRowIterator() as $eachrow) {
                    foreach ($sheet->getColumnIterator() as $column) {
                        $column->getColumnIndex() . $eachrow->getRowIndex();
                        $sheetData = $sheet->getCell($column->getColumnIndex() . $row)->getValue();
                        //セル内にデータがある場合かつ、${で始まるデータ(プレースホルダー)とセル番地を保存
                        if ($sheetData) {
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

                $storage_file_name = $Suffix ? $display_name . '.' . $Suffix : $display_name;
                Session::flash('file_name', $storage_file_name);
                $template_id = DB::table('template_file')
                    ->insertGetId(
                        [
                            'mst_company_id' => $login_user->mst_company_id,
                            'mst_user_id' => $login_user->id,
                            'file_name' => $file_name,
                            'storage_file_name' => $storage_file_name,
                            'location' => $s3url,
                            'document_type' => $extension,
                            'document_access_flg' => 0,
                            'template_create_at' => Carbon::now(),
                            'template_create_user' => $userName,
                            'is_generation_flg' => 1,
                            'create_user_type' => 2,
                        ]);

                DB::table('template_file_data')
                    ->insertGetId(
                        [
                            'template_file_id' => $template_id,
                            'file_data' => $fileDate,
                            'created_at' => Carbon::now(),
                            'create_user' => $userName,
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
                $placeholder_datas = DB::table('template_placeholder_data')->where('template_file_id', $template_id)
                    ->select(['id as placeholder_id', 'template_placeholder_name as placeholder_name', 'cell_address'])
                    ->get()
                    ->toArray();
                // SRS-011 テンプレート文書登録・更新
                $receiveInfo = "/sp/api/upsert-template";
                $response = $specialClient->post($receiveInfo,
                    [
                        RequestOptions::JSON => [
                            "company_id" => $login_user->mst_company_id,
                            "env_flg" => config('app.pac_app_env'),
                            "edition_flg" => config('app.pac_contract_app'),
                            "server_flg" => config('app.pac_contract_server'),
                            "template_info" => [
                                "original_template_file_id" => $template_id,
                                "file_data" => $fileDate,
                                "display_name" => $storage_file_name,
                                "open_period" => Carbon::parse($request->get('koukaiDate') ?: '9999-12-31')->format('Y/m/d'),
                                "is_enable" => $request->get('state'),
                                "create_admin_user_id" => $login_user->id,
                                "receive_circular_users" => json_decode($request->get('receiveCircularUsers')),
                                "placeholder_datas" => $placeholder_datas
                            ]
                        ]
                    ]);
                $response_dencode = json_decode($response->getBody(), true);  //配列へ

                if ($response->getStatusCode() == 200) {
                    if ($response_dencode['status'] == "error") {
                        DB::rollBack();
                        Log::error('Upsert Excel Template:' . $response_dencode['message']);
                        Log::error($response_dencode);
                        return response()->json(['status' => false, 'message' => [$response_dencode['message']]]);
                    }
                } else {
                    DB::rollBack();
                    Log::error('Api storeBoard companyId:' . $login_user->mst_company_id);
                    Log::error($response_dencode);
                    return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                }
            } else if (in_array($fileextension, ['docx', 'doc'])) {
                $extension = '1';
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
                $find = '${';
                $placeholderList = array();

                $counter = substr_count($contents, $find);

                for ($i = 0; $i < $counter; $i++) {
                    $phEnd = '}';
                    $start_position = strpos($contents, $find);
                    $phLength = strpos($contents, $phEnd) - $start_position + 1;
                    if ($start_position !== false){
                        $placeholder = substr($contents, $start_position, $phLength);
                        $placeholderList += array($i => $placeholder);
                        $contents = substr($contents, strpos($contents, $phEnd) + 1, strlen($contents) - $start_position );
                    }
                }
                $storage_file_name = $Suffix ? $display_name . '.' . $Suffix : $display_name;
                $template_id = DB::table('template_file')
                    ->insertGetId(
                        [
                            'mst_company_id' => $login_user->mst_company_id,
                            'mst_user_id' => $login_user->id,
                            'file_name' => $file_name,
                            'storage_file_name' => $storage_file_name,
                            'location' => $s3url,
                            'document_type' => $extension,
                            'document_access_flg' => 0,
                            'template_create_at' => Carbon::now(),
                            'template_create_user' => $userName,
                            'is_generation_flg' => 1,
                            'create_user_type' => 2,
                        ]);

                DB::table('template_file_data')
                    ->insertGetId(
                        [
                            'template_file_id' => $template_id,
                            'file_data' => $fileDate,
                            'created_at' => Carbon::now(),
                            'create_user' => $userName,
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

                // SRS-011 テンプレート文書登録・更新
                $placeholder_datas = DB::table('template_placeholder_data')->where('template_file_id', $template_id)
                    ->select(['id as placeholder_id', 'template_placeholder_name as placeholder_name', 'cell_address'])
                    ->get()
                    ->toArray();
                $receiveInfo = "/sp/api/upsert-template";
                $response = $specialClient->post($receiveInfo,
                    [
                        RequestOptions::JSON => [
                            "company_id" => $login_user->mst_company_id,
                            "env_flg" => config('app.pac_app_env'),
                            "edition_flg" => config('app.pac_contract_app'),
                            "server_flg" => config('app.pac_contract_server'),
                            "template_info" => [
                                "original_template_file_id" => $template_id,
                                "file_data" => $fileDate,
                                "display_name" => $storage_file_name,
                                "open_period" => Carbon::parse($request->get('koukaiDate') ?: '9999-12-31')->format('Y/m/d H:i:s'),
                                "is_enable" => $request->get('state'),
                                "create_admin_user_id" => $login_user->id,
                                "receive_circular_users" => json_decode($request->get('receiveCircularUsers')),
                                "placeholder_datas" => $placeholder_datas
                            ]
                        ]
                    ]);
                $response_dencode = json_decode($response->getBody(), true);  //配列へ

                if ($response->getStatusCode() == 200) {
                    if ($response_dencode['status'] == "error") {
                        DB::rollBack();
                        Log::error('Upsert Word Template:' . $response_dencode['message']);
                        Log::error($response_dencode);
                        return response()->json(['status' => false, 'message' => [$response_dencode['message']]]);
                    }
                } else {
                    DB::rollBack();
                    Log::error('Api storeBoard companyId:' . $login_user->mst_company_id);
                    Log::error($response_dencode);
                    return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                }
            }

            DB::commit();
            Log::info('テンプレートアップロード完了');

            return response()->json(['status' => true, 'message' => 'テンプレートファイル登録処理に成功しました']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
        }
    }
}
