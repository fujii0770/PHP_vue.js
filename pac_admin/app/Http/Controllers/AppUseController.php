<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AdminController;
use App\Http\Utils\ApplicationAuthUtils;
use App\Http\Utils\AppUtils;
use App\Http\Utils\GwAppApiUtils;
use App\Http\Utils\UserApiUtils;
use App\Models\TimecardDetail;
use DB;
use App\Models\Company;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\IdAppApiUtils;
use Illuminate\Support\Carbon;


class AppUseController extends AdminController {
    private $department;
    private $position;

    public function __construct(Department $department, Position $position)
    {
        parent::__construct();
        $this->department = $department;
        $this->position = $position;

        $this->assign('use_angular', true);
        $this->assign('show_sidebar', true);
        $this->assign('use_contain', true);
    }

    /**
     * アプリ利用設定一覧
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request){
        $user   = \Auth::user();
        $failure_message = "";

        $limit      = $request->get('limit', 20);
        $orderBy    = $request->get('orderBy') ? $request->get('orderBy') : '';
        $orderDir   = $request->get('orderDir') ? $request->get('orderDir'): 'asc';

        //部門リストの取得
        $listDepartment = $this->department
            ->select('id','parent_id' , 'department_name as name')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('state',1)
            ->get()->keyBy('id');

        $listDepartmentTree = \App\Http\Utils\CommonUtils::arrToTree($listDepartment);

        $listDepartmentTree = \App\Http\Utils\CommonUtils::treeToArr($listDepartmentTree);

        //役職リストの取得
        $listPosition = $this->position
            ->where('state',1)
            ->where('mst_company_id',$user->mst_company_id)
            ->pluck('position_name', 'id')->toArray();

        $this->assign('listDepartment', $listDepartment);
        $this->assign('listDepartmentTree', $listDepartmentTree);
        $this->assign('listPosition', $listPosition);

        //アプリ一覧API呼び出し　セレクトボックスのリスト
        // GW管理＆権限あり（取得）
        $searchResults = GwAppApiUtils::getCompanyAppSearch($user['email'], $user['mst_company_id']);
        if ($searchResults === false){
            Log::error('Search App portalCompanyId:' . $user['mst_company_id']);
            $failure_message = "アプリ利用設定を取得できませんでした。";
        }

        $listapp = [];
        $listappFlg = 0;
        // PAC管理＆権限あり（掲示板除外）
        ApplicationAuthUtils::getCompanyAppSearch($user['mst_company_id'])
            ->each(function ($auth) use (&$listapp, &$listappFlg) {
                if (!in_array($auth->id,[AppUtils::GW_APPLICATION_ID_BOARD,AppUtils::GW_APPLICATION_ID_FILE_MAIL_EXTEND]) && $auth->is_auth==1){
                    $id = $auth->id;
                    $listapp[$id] = $auth->app_name;
                    $listappFlg = 1;
                }
            });

        // GW管理＆権限あり（ループして設定）
        $application_ids_gw = AppUtils::APPLICATION_IDS_GW;
        unset($application_ids_gw[array_search(AppUtils::GW_APPLICATION_ID_SHARED_SCHEDULE, $application_ids_gw)]);
        if ($searchResults){
            foreach($searchResults as $value){
                if (in_array($value['id'], $application_ids_gw)) {
                    if($value['isAuth']){
                        $id = $value['id'];
                        $listapp[$id] = $value['appName'];
                        $listappFlg = 1;
                    }
                }
            }
        }
        if(!$listapp){
            $id = "0";
            $listapp[0] = 'なし';
        }
        ksort($listapp);
        $this->assign('listapp', $listapp);


        switch ($request->get('usestate')) {
            case '0':
            case '1':
                $isValid = $request->get('usestate');
                break;
            default:
                $isValid = -1; //無条件検索
                break;
        }
        if($request->get('usestate')){
            $isValid = $request->get('usestate');
        }

        $listuser=[];
        // 企業権限あり（任意）
        if($listappFlg){
            if (!in_array($request->get('filter_app', key($listapp)),AppUtils::APPLICATION_IDS_PAC)) {
                //ユーザー一覧API呼び出し
                $response_user = GwAppApiUtils::appUsersSearch(
                    $user['email'],
                    $user['mst_company_id'],
                    $request->get('filter_app', key($listapp)),
                    $request->get('email', ''),
                    $request->get('department', ''),
                    $request->get('position', ''),
                    $request->get('username', ''),
                    $isValid);

                if ($response_user === false) {
                    Log::error('Search roleuser portalCompanyId:' . $user['mst_company_id']);
                    $failure_message = "アプリ利用設定を取得できませんでした。";
                }
                if ($response_user) {
                    $listuser = array();
                    foreach ($response_user["mstApplicationUsersStateLists"] as $value_user) {
                        $array = array('id' => $value_user["mstUserId"],
                            'email' => $value_user["mstUser"]["email"],
                            'name' => $value_user["mstUser"]["name"],
                            'department' => $value_user["mstUser"]["mstDepartment"]["name"],
                            'position' => $value_user["mstUser"]["mstPosition"]["name"],
                            'enabled' => $value_user["enabled"],
                            'appUserId' => $value_user["appUserId"]
                        );
                        $listuser[] = $array;
                    }
                }
            } else {
                $listuser=ApplicationAuthUtils::appUsersSearch(
                    $user['mst_company_id'],
                    $request->get('filter_app', key($listapp)),
                    $request->get('email', ''),
                    $request->get('department', ''),
                    $request->get('position', ''),
                    $request->get('username', ''),
                    $isValid);
            }
        }

        // by user id
        $sortFunction = function ($a, $b) {
            return $a['id'] > $b['id'];
        };
        switch ($orderBy) {
            case 'state':
                $sortFunction = function ($a, $b) {
                    return $a['enabled'] > $b['enabled'];
                };
                break;
            case 'email':
                $sortFunction = function ($a, $b) {
                    return $a['email'] > $b['email'];
                };
                break;
            case 'user_name':
                $sortFunction = function ($a, $b) {
                    return $a['name'] > $b['name'];
                };
                break;
            case 'adminDepartment':
                $sortFunction = function ($a, $b) {
                    return $a['department'] > $b['department'];
                };
                break;
            case 'position':
                $sortFunction = function ($a, $b) {
                    return $a['position'] > $b['position'];
                };
                break;
        }
        usort($listuser, $sortFunction);
        if ($orderDir === 'desc') {
            $listuser = array_reverse($listuser);
        }

        //  こんな感じでやればいけるとおもうんだけどね
        $page = $request->get('page','1');
        //  forpage使えないから自前スライス
        $pagelistuser = new LengthAwarePaginator(array_slice( $listuser, ($page - 1) * $limit, $limit, false), count($listuser), $limit);
        $pagelistuser->setPath($request->url());
        $pagelistuser->appends(request()->input()); // sort params etc
        $this->assign('listuser', $pagelistuser);

        //  こんなんでいいんじゃね？
        $usestateval['0'] = "無効";
        $usestateval['1'] = "有効";
        $this->assign('usestate', $usestateval);
        $failure_message= "";
        $this->assign('failure_message', $failure_message);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";  // 入れ替えないといけない謎
        $this->assign('orderDir', $orderDir);

        $this->setMetaTitle('アプリ利用設定');
        $this->assign('user_title', '管理者');

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        return $this->render('SettingGroupware.AppUse.index');
    }

    /**
     * update a appRoleId.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    function update(Request $request){
        $user   = \Auth::user();

        $select_app_id = $request->get('select_app_id'); //選択されたアプリロールid
        $cids           = $request->get('cids');           //選択されたチェックボックスの配列
        $cidoffs        = $request->get('cidoffs');           //非選択されたチェックボックスの配列

        //更新API呼び出し

        if (!in_array($select_app_id,AppUtils::APPLICATION_IDS_PAC)) {
            $response_user = GwAppApiUtils::appUsersSearch($user['email'], $user['mst_company_id'], $select_app_id);
            if ($response_user === false) {
                Log::error('Update App mstApplicationId:' . $select_app_id);
                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
            }
        } else {
            $res = ApplicationAuthUtils::appUsersSearch($user['mst_company_id'], $select_app_id);
            if (count($res) > 0) {

                foreach ($res as $index => $v) {
                    $res[$index]['mstUserId'] = $v['id'];
                    $res[$index]['enabled'] = $v['enabled']==0?false:true;
                }
                $response_user['mstApplicationUsersStateLists'] = $res;
            } else {
                $response_user = false;
            }
        }

        if ($response_user){
            $upd_valid_user_count = 0;//有効なユーザに更新
            $upd_invalid_user_count = 0;//無効なユーザに更新
            $all_valid_user_count = 0;//有効のユーザ数
            foreach($response_user["mstApplicationUsersStateLists"] as $value_user) {
                //有効のユーザ
                if ($value_user['enabled'] == false && in_array($value_user["mstUserId"], $cids)) $upd_valid_user_count++;
                //無効のユーザ
                if ($value_user['enabled'] == true && in_array($value_user["mstUserId"], $cidoffs)) $upd_invalid_user_count++;
                //更新前の有効なすべてのユーザ数
                if ($value_user['enabled']) $all_valid_user_count++;
            }
            //アプリ利用状況取得

            if (!in_array($select_app_id,AppUtils::APPLICATION_IDS_PAC)) {
                $max_usage_results = GwAppApiUtils::getCompanyAppSearch($user['email'], $user['mst_company_id']);
                if ($max_usage_results === false) {
                    return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                }
                $max_valid_count = 0;//購入数
                $is_check_limit = 1;// 1：無制限チェックあり、0：無制限チェックなし,
                if ($max_usage_results) {
                    foreach ($max_usage_results as $max_usage_result) {
                        if ($max_usage_result['id'] == $select_app_id) {
                            $max_valid_count = $max_usage_result['purchaseCount'];
                            $is_check_limit = $max_usage_result['isInfinite'];
                        }
                    }
                }
            } else {
                $max_usage_results = ApplicationAuthUtils::getCompanyAppSearch($user['mst_company_id']);
                $max_valid_count = 0;//購入数
                $is_check_limit = 1;// 1：無制限チェックあり、0：無制限チェックなし,
                if ($max_usage_results) {
                    foreach ($max_usage_results as $max_usage_result) {
                        if ($max_usage_result->id == $select_app_id) {
                            $max_valid_count = $max_usage_result->purchase_count;
                            $is_check_limit = $max_usage_result->is_infinite;
                        }
                    }
                }
            }

            //購入数量を超過する
            if ( !$is_check_limit && ($all_valid_user_count + $upd_valid_user_count - $upd_invalid_user_count > $max_valid_count)){
                return response()->json(['status' => false, 'message' => [__('message.false.api.max_valid_app_user_count',
                    ['count'=> $all_valid_user_count + $upd_valid_user_count - $upd_invalid_user_count , 'limit' => $max_valid_count ])] ]);
            }
        }
        $ids = [];
        if ($response_user){
            foreach($response_user["mstApplicationUsersStateLists"] as $value_user) {
                if(isset($value_user['mstUser'])){
                    $ids[] = $value_user['mstUser']['portalId'];
                }else{
                    $ids[] = $value_user['mstUserId'];
                }
                //  チェック配列にあるかな？
                if( array_search($value_user["mstUserId"], $cids) !== false )
                {
                    //  enabledじゃないなら更新しよ
                    if( $value_user["enabled"] === false ) {
                        //アプリ利用ユーザ更新

                        if (!in_array($select_app_id,AppUtils::APPLICATION_IDS_PAC)) {
                            $update_result = GwAppApiUtils::appUserUpdate($user['email'], $user['mst_company_id'], $select_app_id, $value_user["mstUserId"]);
                            if ($update_result === false) {
                                Log::error('Update App mstApplicationId:' . $select_app_id);
                                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                            }
                        } else {
                            ApplicationAuthUtils::appUserUpdate($user['mst_company_id'], $select_app_id, $value_user["mstUserId"]);
                            if($select_app_id == AppUtils::GW_APPLICATION_ID_FILE_MAIL){
                                DB::table('disk_mail_user_info')->insert([
                                    'mst_user_id' => $value_user["mstUserId"],
                                    'create_at' => Carbon::now(),
                                    'create_user' => $user['email'],
                                    'comment1' => '確認をお願いします。',
                                    'comment2' => 'ご確認をお願い致します。',
                                    'comment3' => '至急確認をお願いします。',
                                    'comment4' => '至急ご確認をお願い致します。',
                                    'comment5' => 'ご確認の程よろしくお願い申し上げます。',
                                ]);
                            }
                        }
                    }
                }
                //  非チェック配列にあるかな？
                else if( array_search($value_user["mstUserId"], $cidoffs) !== false ) {
                    //  enabledなら更新しよ
                    if( $value_user["enabled"] !== false ) {
                        //アプリ利用ユーザ削除
                        if (!in_array($select_app_id,AppUtils::APPLICATION_IDS_PAC)) {
                            $del_result = GwAppApiUtils::appUserDelete($value_user["appUserId"], $user['email'], $user['mst_company_id']);
                            if ($del_result === false) {
                                Log::error('Delete App mstApplicationId:' . $select_app_id);
                                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                            }
                        } else {
                            ApplicationAuthUtils::appUserDelete($user['mst_company_id'], $select_app_id, $value_user["mstUserId"]);
                            if($select_app_id == AppUtils::GW_APPLICATION_ID_FILE_MAIL){
                                DB::table('disk_mail_user_info')
                                    ->where('mst_user_id',$value_user["mstUserId"])
                                    ->delete();
                            }
                        }
                    }
                }
            }
        }
        // ローカル保存したグループウエア有効のユーザID
        $localValidUsers = ApplicationAuthUtils::getAppUsersStateSearch($user['mst_company_id'], $ids);
        // ローカル保存したグループウエアID
        $localAppIds = ApplicationAuthUtils::getAppStateSearch();
        // GW側で保存してグループウエアユーザ情報
        $gwValidUsers = [];
        $gwAppUserStates = GwAppApiUtils::getAppUsersStateSearch($user['email'], $user['mst_company_id'], $ids);
        if ($gwAppUserStates === false) {
//            Log::error('Search App State portalCompanyId:' . $user['mst_company_id']);
//            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
        }else{
            // ユーザループ
            foreach($gwAppUserStates['userApplicationList'] as $userApp){
                $valid = false;
                // グループウエアループ
                foreach($userApp['applicationList'] as $app) {
                    // ローカル保管したグループウエアで、GW側判定対象外
                    // いずれグループウエアが有効が、このユーザは、有効です
                    if(!in_array($app['id'], $localAppIds) && !$valid){
                        $valid = $app['isAuth'];
                    }
                }
                // GW側で、グループウエア有効のユーザ
                if($valid){
                    $gwValidUsers[] = $userApp['mstUserId'];
                }
            }
            // ローカル又はGW側で、グループウエア有効のユーザ
            $validUsers = array_merge($localValidUsers, $gwValidUsers);
            // グループウエア無効のユーザ
            $invalidUsers = array_diff($ids, $validUsers);
            // 有効のユーザ存在すれば、GWフラグに有効を更新する
            if (count($validUsers)){
                DB::table('mst_user_info')
                    ->whereIn('mst_user_id', $validUsers)
                    ->update(['gw_flg' => 1]);
            }
            // 無効のユーザ存在すれば、GWフラグに無効を更新する
            if (count($invalidUsers)){
                DB::table('mst_user_info')
                    ->whereIn('mst_user_id', $invalidUsers)
                    ->update(['gw_flg' => 0]);
            }
        }
        

        return response()->json(['status' => true, 'message' => [__('message.success.update_user_app')]]);
    }

    /**
     * 取得スケジュールの重複予約の配置
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    function showSchedule(){
        $user   = \Auth::user();
        $failure_message = "";

        $res= 0;
        try {

            $res = GwAppApiUtils::getApplicationSchedule($user['email'], $user['mst_company_id']);
            if ($res === false){
                $failure_message='データの取得は失敗しました、リフレシュして再表示してください。';
            }
        }catch (\Exception $ex){
            $failure_message='データの取得は失敗しました、リフレシュして再表示してください。';
        }

        $this->assign('responseBody',$res);
        $this->assign('failure_message', $failure_message);
        $this->setMetaTitle('制限設定');
        return $this->render('SettingGroupware.Schedule.index');
    }

    /**
     * 更新スケジュールの重複予約
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function updateSchedule(Request $request){
        $user   = \Auth::user();

        $repeatFlg=$request->get('repeatFlg',0);
        in_array($repeatFlg,[0,1])?$repeatFlg=$repeatFlg:$repeatFlg=0;
        try {
            $update_result = GwAppApiUtils::updateApplicationSchedule($user['email'], $user['mst_company_id'], $repeatFlg);
            if (!$update_result){
                Log::error('Update "/api/v1/admin/app-restrict/" failed');
                return response()->json(['status' => false, 'message' => ["制限設定の更新は失敗しました、再更新してみてください。"]]);
            }

            return response()->json(['status' => true, 'message' => ["制限設定を更新しました。"]]);
        }catch (\Exception $exception){
            Log::error('Update "/api/v1/admin/app-restrict/" failed');
            return response()->json(['status' => false, 'message' => ["制限設定の更新は失敗しました、再更新してみてください。"]]);
        }
    }
}
