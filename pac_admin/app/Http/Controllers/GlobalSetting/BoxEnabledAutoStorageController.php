<?php

namespace App\Http\Controllers\GlobalSetting;

use App\Http\Controllers\Controller;
use App\Models\Circular;
use App\Models\CircularDocument;
use App\Models\Limit;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController; 
use App\Http\Utils\PermissionUtils;
use DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Storage;
use App\Models\Company;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use League\OAuth2\Client\Provider\GenericProvider;
use App\Oauth\DropboxProvider;
use Illuminate\Support\Facades\Response;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use App\Http\Utils\BoxUtils;
use GuzzleHttp\RequestOptions;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\StatusCodeUtils;

class BoxEnabledAutoStorageController extends AdminController
{
    /**
     * 初期化(画面表示 と 検索)
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request){
        try{
            $user = \Auth::user();
            $action = $request->get('action',''); // action = ""の場合、初期化     action != "" の場合、検索
            $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'circular.id';
            $orderDir = $request->get('orderDir') ? $request->get('orderDir'): 'desc';

            // 初期設定
            $auto_delete_lists = [];
            $auto_storage_setting = [
                'box_enabled_automatic_storage' => 0, // 自動保管(1:有効 0:無効)
                'box_enabled_folder_to_store' => '',  // 保管先フォルダ
                'box_enabled_output_file_1' => 0,     // 出力ファイル 署名なし・捺印履歴なし(1:チェック、0:チェックしない)
                'box_enabled_output_file_2' => 0,     // 出力ファイル 署名なし・捺印履歴あり(1:チェック、0:チェックしない)
                'box_enabled_output_file_3' => 0,     // 出力ファイル 署名あり・捺印履歴なし(1:チェック、0:チェックしない)
                'box_enabled_output_file_4' => 0,     // 出力ファイル 署名あり・捺印履歴あり(1:チェック、0:チェックしない)
                'box_enabled_automatic_delete' => 0,  // 保管後の自動削除(1:有効、0:無効)
                'box_auto_save_folder_id' => '',      // 保管先フォルダID
                'box_refresh_token' => ''             // Box更新トークン
            ];
            // 企業情報を取得する
            $company = Company::where('id', $user->mst_company_id)->first();
            // 企業設定を取得する
            $domain_limit = Limit::where('mst_company_id', $user->mst_company_id)->first();
            $auto_storage_setting['box_enabled_automatic_storage'] = $domain_limit->box_enabled_automatic_storage; // 自動保管(1:有効 0:無効)
            $auto_storage_setting['box_enabled_folder_to_store'] = $domain_limit->box_enabled_folder_to_store; // 保管先フォルダ
            if($domain_limit->box_enabled_output_file){
                // 出力ファイル変換 1,2,3,4 => [1,2,3,4]
                $box_enabled_output_files = explode(',', $domain_limit->box_enabled_output_file);
            }else{
                $box_enabled_output_files = [];
            }
            foreach($box_enabled_output_files as $box_enabled_output_file){
                // 出力ファイル設定の場合、チェック
                $auto_storage_setting['box_enabled_output_file_'.$box_enabled_output_file] = 1;
            }
            // 電子証明書有効の場合、署名なし 出力しない
//            if($company->signature_flg){
//                $auto_storage_setting['box_enabled_output_file_1'] = 0;
//                $auto_storage_setting['box_enabled_output_file_2'] = 0;
//            }
            // 「PDFへの電子署名付加」にチェックがある場合、署名なし 出力しない
            // 「PDFへの電子署名付加」にチェックがない場合、署名あり 出力しない
            if($company->esigned_flg) {
                $auto_storage_setting['box_enabled_output_file_1'] = 0;
                $auto_storage_setting['box_enabled_output_file_2'] = 0;
            }else{
                $auto_storage_setting['box_enabled_output_file_3'] = 0;
                $auto_storage_setting['box_enabled_output_file_4'] = 0;
            }
            $auto_storage_setting['box_enabled_automatic_delete'] = $domain_limit->box_enabled_automatic_delete; // 保管後の自動削除(1:有効、0:無効)
            $auto_storage_setting['box_auto_save_folder_id'] = $domain_limit->box_auto_save_folder_id; // 保管先フォルダID
            $auto_storage_setting['box_refresh_token'] = $domain_limit->box_refresh_token; // Box更新トークン

            $limit = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
            if(!array_search($limit, config('app.page_list_limit'))){
                $limit = config('app.page_limit');
            }

            // action = ""の場合、初期化     action != "" の場合、検索
            if($action != ""){
                // 自動削除リスト
                $auto_delete_lists = Circular::join('circular_auto_storage_history', 'circular.id', 'circular_auto_storage_history.circular_id')
                    ->where('circular_auto_storage_history.mst_company_id', $user->mst_company_id)
                    ->where('circular_auto_storage_history.result', '!=', BoxUtils::BOX_AUTOMATIC_STORAGE_RESULT_DEFAULT);

                // 検索条件
                if($request->get('search-text')){
                    // ファイル名、件名、申請者氏名（部分一致）
                    $search_text = $request->get('search-text');
                    $auto_delete_lists = $auto_delete_lists->where(function($query) use($search_text){
                        $query->where('circular_auto_storage_history.file_name', 'like', '%'.$search_text.'%')
                            ->orwhere('circular_auto_storage_history.title', 'like', '%'.$search_text.'%')
                            ->orwhere('circular_auto_storage_history.applied_name', 'like', '%'.$search_text.'%');
                    });
                }
                if($request->get('auto-storage-state')){
                    $auto_delete_lists = $auto_delete_lists->where('circular_auto_storage_history.result', $request->get('auto-storage-state'));
                }
                if($request->get('auto-delete-state') && $request->get('auto-delete-state') !== "0"){
                    if($request->get('auto-delete-state') === "1"){
                        $auto_delete_lists = $auto_delete_lists->where('circular.circular_status', '9');
                    }else{
                        $auto_delete_lists = $auto_delete_lists->where('circular.circular_status', '!=', '9');
                    }
                }

                $auto_delete_lists = $auto_delete_lists->select('circular_auto_storage_history.id','circular_auto_storage_history.result','circular_auto_storage_history.route','circular_auto_storage_history.file_name',
                    'circular_auto_storage_history.title','circular_auto_storage_history.applied_email','circular.circular_status','circular_auto_storage_history.applied_name')
                    ->orderBy($orderBy, $orderDir)
                    ->paginate($limit)
                    ->appends(request()->input());
            }

            $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";

            $this->assign('company', $company);
            $this->assign('auto_delete_lists', $auto_delete_lists);
            $this->assign('auto_storage_setting', $auto_storage_setting);

            $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
            $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
            $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
            $this->assign('allow_create', $user->can(PermissionUtils::PERMISSION_BOX_ENABLED_AUTO_STORAGE_SETTING_VIEW));
            $this->assign('allow_update', $user->can(PermissionUtils::PERMISSION_BOX_ENABLED_AUTO_STORAGE_SETTING_UPDATE));

            $this->assign('orderBy', $orderBy);
            $this->assign('orderDir', $orderDir);
            $this->assign('limit', $limit);

            $this->setMetaTitle("外部連携Box");
            return $this->render('GlobalSetting.BoxEnabledAutoStorage.index');
        }catch(\Exception $ex){
            Log::error('BoxEnabledAutoStorageController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->render('GlobalSetting.BoxEnabledAutoStorage.index');
        }
    }

    /**
     * 外部連携Box 設定保存
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveAutoStorageSetting(Request $request){
        try{
            $user = \Auth::user();
            $setting = $request->all();
            $limit = Limit::where('mst_company_id',$user->mst_company_id)->first();

            if(!$limit){
                return response()->json(['status' => false, 'message' => [__('message.success.save_auto_storage_setting')]]);
            } else {
                $setting['update_user'] = $user->getFullName();
            }

            $arr_output_files = [];
            // 署名なし・捺印履歴なし チェックの場合
            if($setting['box_enabled_output_file_1']){
                $arr_output_files[] = 1;
            }
            // 署名なし・捺印履歴あり チェックの場合
            if($setting['box_enabled_output_file_2']){
                $arr_output_files[] = 2;
            }
            // 署名あり・捺印履歴なし チェックの場合
            if($setting['box_enabled_output_file_3']){
                $arr_output_files[] = 3;
            }
            // 署名あり・捺印履歴あり チェックの場合
            if($setting['box_enabled_output_file_4']){
                $arr_output_files[] = 4;
            }
            $box_enabled_output_file = implode(',', $arr_output_files);
            if($setting['box_enabled_automatic_storage'] == 1 && count($arr_output_files) == 0){
                return response()->json(['status' => false, 'message' => ['自動保管を有効にする場合、1つ以上出力ファイルを選択してください']]);
            }
            if($setting['box_enabled_automatic_storage'] == 1 && ($setting['box_auto_save_folder_id'] === "" || $setting['box_enabled_folder_to_store'] === "" || $setting['box_enabled_folder_to_store'] === null)){
                return response()->json(['status' => false, 'message' => ['自動保管を有効にする場合、自動保存のフォルダを選択してください']]);
            }

            $setting['box_enabled_output_file'] = $box_enabled_output_file;
            unset($setting['box_enabled_output_file_1']);
            unset($setting['box_enabled_output_file_2']);
            unset($setting['box_enabled_output_file_3']);
            unset($setting['box_enabled_output_file_4']);

            if(Session::get(BoxUtils::BOX_API_REFRESH_TOKEN, '')){
                $setting['box_refresh_token'] = Session::get(BoxUtils::BOX_API_REFRESH_TOKEN);
                Session::put(BoxUtils::BOX_API_REFRESH_TOKEN, '');
                $setting['box_refresh_token_updated_date'] = Carbon::now();
            }else{
                $setting['box_refresh_token'] = $limit->box_refresh_token;
            }
            $limit->fill($setting);
            $limit->save();

            return response()->json(['status' => true, 'message' => [__('message.success.save_auto_storage_setting')]]);
        }catch (\Exception $ex) {
            Log::error('BoxEnabledAutoStorageController@saveAutoStorageSetting:' . $ex->getMessage().$ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.save_auto_storage_setting')]]);
        }
    }

    private function getBoxProvider(){
        return new GenericProvider(array_merge(config("oauth.box"), ['redirectUri' => config('oauth.return_url')]));
    }

    private function getOneDriveProvider(){
        return new GenericProvider(array_merge(config("oauth.onedrive"), ['redirectUri' => config('oauth.return_url'), 'scopes' => ['Files.ReadWrite.All', 'User.Read.All']]));
    }

    private function getGoogleProvider(){
        return new GenericProvider(array_merge(config("oauth.google"), ['redirectUri' => config('oauth.return_url'), 'scopes' => ['https://www.googleapis.com/auth/drive']]));
    }

    private function getDropBoxProvider(){
        return new DropboxProvider(array_merge(config("oauth.dropbox"), ['redirectUri' => config('oauth.return_url')]));
    }

    private function getProvider($drive){
        if ($drive == 'box'){
            return $this->getBoxProvider();
        }else if ($drive == 'onedrive'){
            return $this->getOneDriveProvider();
        }else if ($drive == 'google'){
            return $this->getGoogleProvider();
        }else if ($drive == 'dropbox'){
            return $this->getDropBoxProvider();
        }
        return null;
    }

    /**
     * Boxフォルダを選択
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function selectFolersExternal(Request $request)
    {
        try{
            $drive = $request->input('drive');
            $provider = $this->getProvider($drive);

            if ($provider){
                // Fetch the authorization URL from the provider; this returns the
                // urlAuthorize option and generates and applies any necessary parameters
                // (e.g. state).
                $authorizationUrl = $provider->getAuthorizationUrl();

                // Get the state generated for you and store it to the session.
                Session::put('oauth2state_auto_storage', $provider->getState());
                Session::put('drive_auto_storage', $drive);

                // Redirect the user to the authorization URL.
                return Redirect::to($authorizationUrl);
            }else{
                abort(404);
            }
        }catch (\Exception $ex){
            Log::error('BoxEnabledAutoStorageController@selectFolersExternal:' . $ex->getMessage().$ex->getTraceAsString());
            abort(500);
        }
    }

    /**
     * selectFolersExternalのコールバック関数
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function externalDriveCallback(Request $request){
        if (empty($request->input('state')) || ($request->input('state') !== Session::get('oauth2state_auto_storage'))) {
            Session::remove('oauth2state_auto_storage');
            Session::remove('drive_auto_storage');
        } else {
            try {
                $drive = Session::get('drive_auto_storage');
                $provider = $this->getProvider($drive);

                //PAC_5-1136 code未取得時にエラー処理
                if ($provider && !empty($request->input('code'))){
                    // Try to get an access token using the authorization code grant.
                    $accessToken = $provider->getAccessToken('authorization_code', [
                        'code' => $request->input('code')
                    ]);

                    $token = $accessToken->getToken();
                    $refresh_token = $accessToken->getRefreshToken();

                    if ($drive == 'box'){
                        Session::put(BoxUtils::BOX_API_TOKEN, $token);
                        Session::put(BoxUtils::BOX_API_REFRESH_TOKEN, $refresh_token);
                    }

                    return view('login-to-cloud-done', ['drive' => $drive,'message' => null]);
                }else{
                    //PAC_5-1136 code未取得時にエラー処理
                    Log::debug('Failed to get the access token');
                    Log::debug('$Provider or \'code\' is null');
                    return view('login-to-cloud-done', ['drive' => $drive,'message'=> 'クラウドストレージの取得に失敗しました']);
                }
            } catch (IdentityProviderException $e) {
                Log::error('BoxEnabledAutoStorageController@externalDriveCallback: Failed to get the access token');
                Log::error($e->getMessage().$e->getTraceAsString());
                return Response::json(['status'=>false, 'message'=> $e->getMessage(), 'data'=> null], 500);
            }
        }
    }

    /**
     * Cloudのファイルリストを取得
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getCloudItems(Request $request){
        try {
            $folderId = $request->get('folder_id');

            $drive = $request->get('drive');

            if ($drive == 'box'){
                return $this->getBoxItems($folderId);
            }
            return null;
        }catch(\Exception $ex){
            Log::error('BoxEnabledAutoStorageController@getCloudItems:' . $ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status'=>false, 'message'=> $ex->getMessage(), 'data'=> null], 500);
        }
    }

    /**
     * Boxのファイルとフォルダを取得
     * @param string $folder_id
     * @return \Illuminate\Http\JsonResponse
     */
    private function getBoxItems($folder_id = "0") {
        try{
            $client = BoxUtils::getAuthorizedApiClient();
            if(!$client){
                return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
            }
            $max_limit = config('oauth.box.item_max_limit');
            $result = $client->get('folders/'.$folder_id.'?limit='.$max_limit);

            $resData = json_decode((string)$result->getBody());
            $statusCode = $result->getStatusCode();
            if ($statusCode == 200){
                $total = $resData->item_collection->total_count;
                if($total > $max_limit ){
                    $result = $client->get('folders/'.$folder_id."?limit=$total");
                    $resData = json_decode((string)$result->getBody());
                }
                $folders_data = [];
                foreach ($resData->item_collection->entries as $data_item){
                    if($data_item->type == "folder"){
                        $folders_data[] = $data_item;
                    }
                }
                $files_total = $resData->item_collection->total_count;
                $api_limit = $resData->item_collection->limit;
                // total exceed api limit
                if($files_total > $api_limit){
                    $max_page = ceil($files_total * 1.0 / $api_limit);
                    for($page = 1; $page < $max_page; $page++){
                        $result = $client->get('folders/'.$folder_id.'?offset='.($api_limit * $page).'&limit='.$api_limit);
                        $resData = json_decode((string)$result->getBody());
                        $statusCode = $result->getStatusCode();
                        if ($statusCode == 200){
                            foreach ($resData->item_collection->entries as $data_item){
                                if($data_item->type == "folder"){
                                    $folders_data[] = $data_item;
                                }
                            }
                        }else{
                            Log::error('BoxEnabledAutoStorageController@getBoxItems:' . $result->getBody());
                            return Response::json(['status' => false, 'message' => $resData ? $resData->message: '', 'data' => null], $statusCode);
                        }
                    }
                }
                return Response::json(['status' => true, 'message' => 'Boxフォルダ取得に成功しました。', 'data' => $folders_data]);
            }else{
                Log::error('BoxEnabledAutoStorageController@getBoxItems:' . $result->getBody());
                return Response::json(['status' => false, 'message' => $resData ? $resData->message: '', 'data' => null], $statusCode);
            }
        }catch (\Exception $ex){
            Log::error('BoxEnabledAutoStorageController@getBoxItems:' . $ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status'=>false, 'message'=> $ex->getMessage(), 'data'=> null], 500);
        }
    }

    /**
     * フォルダを作成
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function createFolder(Request $request){
        try{
            $name = $request->get('name');
            $parent_id = $request->get('parent_id');

            $client = BoxUtils::getAuthorizedApiClient(['Content-Type'=> 'application/json']);
            if(!$client){
                return ['status' => false, 'message' => '', 'data' => null];
            }
            $result = $client->post('folders',[
                RequestOptions::JSON => [
                    'name' => $name,
                    'parent' => [
                        'id' => $parent_id
                    ]
                ]
            ]);
            $resData = json_decode((string)$result->getBody());
            $statusCode = $result->getStatusCode();
            if ($statusCode == 200 OR $statusCode == 201){
                return Response::json(['status' => true, 'message' => 'フォルダの作成に成功しました', 'data' => $resData]);
            }else if($statusCode == 409) {
                return Response::json(['status' => false, 'message' => '重複したフォルダがあります', 'data' => $resData], $statusCode);
            }else{
                Log::error('BoxEnabledAutoStorageController@createFolder:' . $statusCode);
                Log::error($result->getBody());
                return Response::json(['status' => false, 'message' => $resData ? $resData->message : 'フォルダの作成に失敗しました', 'data' => null], $statusCode);
            }
        }catch (\Exception $ex){
            Log::error('BoxEnabledAutoStorageController@createFolder:' . $ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    /**
     * Box自動保管失敗後再保存
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reSaveAutoStorage(Request $request)
    {
        try {
            $user = \Auth::user();
            $cid = $request['cids'];
            $circular_auto_storage_historys = DB::table('circular_auto_storage_history')
                ->select('id','circular_id','mst_company_id','file_name')
                ->where('result', 2)
                ->where('mst_company_id', $user->mst_company_id)
                ->whereIn('id', $cid)
                ->groupBy('id','circular_id','mst_company_id')
                ->get();
            $env_flg = config('app.pac_app_env');
            $server_flg = config('app.pac_contract_server');
            $client = EnvApiUtils::getAuthorizeClient($env_flg, $server_flg);
            if (!$client) {
                return Response::json(['status' => false, 'message' => ['Cannot connect to Env Api']], StatusCodeUtils::HTTP_UNAUTHORIZED);
            }
            $response = $client->post("re-auto-storage", [
                RequestOptions::JSON => [
                    'company_id' => $user->mst_company_id,
                    'auto_storage_history' => $circular_auto_storage_historys,
                ]
            ]);
            $filenames = [];
            foreach($circular_auto_storage_historys as $circular_auto_storage_history){
                $filenames[]        = $circular_auto_storage_history->file_name;
            }
            Session::flash('file_names', $filenames);
            if ($response->getStatusCode() != StatusCodeUtils::HTTP_OK) {
                Log::error($response->getBody());
                return Response::json(['status' => false, 'message' => [__('message.false.re_save_auto_storage')]], StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
            }
            return Response::json(['status' => true, 'message' => [__('message.success.re_save_auto_storage')]]);
        }catch (\Exception $ex){
            Log::error('自動保存:autoStorageBox exception:' . $ex->getMessage() . $ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => [__('message.false.re_save_auto_storage')]], StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
