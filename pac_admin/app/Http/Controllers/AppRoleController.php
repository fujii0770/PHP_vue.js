<?php

namespace App\Http\Controllers;

use App\Http\Utils\ApplicationAuthUtils;
use App\Http\Utils\GwAppApiUtils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\Department;
use App\Models\Position;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Utils\IdAppApiUtils;

class AppRoleController extends AdminController
{

    private $department;
    private $position;

    public function __construct(Department $department, Position $position)
    {
        parent::__construct();
        $this->department = $department;
        $this->position = $position;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request){
        $user   = \Auth::user();
        $failure_message = "";
        $arrHistory  =  null;
        $action = $request->get('action','');

        // get list user
        $limit      = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
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

            if(!$user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_HISTORY_VIEW)){
                //$this->raiseWarning(__('message.not_permission_access'));
                //return redirect()->route('home');
            }

        //アプリ一覧API呼び出し　セレクトボックスのリスト
        $searchResults = GwAppApiUtils::getCompanyAppSearch($user['email'], $user['mst_company_id']);
        if ($searchResults === false){
            Log::error('Search App portalCompanyId:' . $user['mst_company_id']);
            $failure_message = "アプリロール設定を取得できませんでした。";
        }

        $first_app_id = '';
        $list_app = [];
        $roleList = [];
        $listrole = [];
        /*PAC_5-2376 S*/
        ApplicationAuthUtils::getCompanyAppSearch($user['mst_company_id'])
            ->each(function ($auth) use (&$list_app) {
                if ($auth->is_auth == 1 && $auth->id != AppUtils::GW_APPLICATION_ID_FILE_MAIL && $auth->id!=AppUtils::GW_APPLICATION_ID_FAQ_BOARD && $auth->id!=AppUtils::GW_APPLICATION_ID_TIME_CARD
                    && $auth->id!=AppUtils::GW_APPLICATION_ID_TO_DO_LIST && $auth->id!=AppUtils::GW_APPLICATION_ID_ADDRESS_LIST && $auth->id!=AppUtils::GW_APPLICATION_ID_FILE_MAIL_EXTEND) {
                    $list_app[$auth->id]['id'] = $auth->id;
                    $list_app[$auth->id]['appName'] = $auth->app_name;
                }
            });
        /*PAC_5-2376 E*/
        if ($searchResults){
            foreach ($searchResults as $key => $value){
                if ($value['appName'] == '掲示板') continue;
                /*PAC_5-2376 S*/
                if ($value['appName'] == 'ファイルメール便') continue;
                /*PAC_5-2376 E*/
                if ($value['appName'] == 'カレンダー連携') continue;
                if ($value['appName'] == 'Google連携') continue;
                if ($value['appName'] == 'Outlook連携') continue;
                if ($value['appName'] == 'Apple連携') continue;
                if ($value['appName'] == 'グループスケジューラ') continue;
                if ($value['appName'] == 'タイムカード'){
                    if(isset($list_app[$value['id']])){unset($list_app[$value['id']]);}
                    continue;
                }
                if($value['isAuth']){
                    $list_app[$value['id']]['id'] = $value['id'];
                    $list_app[$value['id']]['appName'] = $value['appName'];
                }
                if (!$list_app){
                    $list_app[0]['id']      = '0';
                    $list_app[0]['appName'] = 'なし';
                }
            }
        }
        /*PAC_5-2376 S*/
        ksort($list_app);
        foreach ($list_app as $key => $value) {
            if (!$first_app_id && $key != 0) {
                $first_app_id = $value['id']; //一覧の先頭のapp_idを保存
            };
        }
        /*PAC_5-2376 E*/

        //ロール一覧API呼び出し　セレクトボックスのリスト
        if ($request->get('filter_app', $first_app_id) != '' && !in_array($request->get('filter_app', $first_app_id), ApplicationAuthUtils::APPLICATION_IDS)) {
            $searchRoleResults = GwAppApiUtils::getCompanyAppRoleSearch($user['email'], $user['mst_company_id'], $request->get('filter_app', $first_app_id));
            if ($searchRoleResults === false) {
                Log::error('Search Role portalCompanyId:' . $user['mst_company_id']);
                $failure_message = "アプリロール設定を取得できませんでした。";
            }

        } else {
            $searchRoleResults = ApplicationAuthUtils::getCompanyAppRoleSearch($user['mst_company_id'], $request->get('filter_app', $first_app_id));
        }
        if ($searchRoleResults){
            $roleList = $searchRoleResults[0];
            $listrole = $searchRoleResults[1];
        }
        if (count($roleList)==0 || count($listrole)==0 ){
            $failure_message = "アプリロール設定を取得できませんでした。";
        }

        //ユーザー一覧API呼び出し
        if ($request->get('filter_app', $first_app_id) != '' && !in_array($request->get('filter_app', $first_app_id), ApplicationAuthUtils::APPLICATION_IDS)) {
            $response_user = GwAppApiUtils::getCompanyAppUsersSearch($user['email'], $user['mst_company_id'], $request->get('filter_app', $first_app_id),
                $request->get('filter_role', ''), $request->get('email', ''), $request->get('department'),
                $request->get('position'), $request->get('username'));
            if ($response_user === false) {
                Log::error('Search roleuser portalCompanyId:' . $user['mst_company_id']);
                $failure_message = "アプリロール設定を取得できませんでした。";
            }
            $listuser = [];
            if ($response_user) {
                foreach ($response_user as $value) {
                    $listuser[] = array(
                        'app_role_name' => $value['appRoleName']
                    , 'app_role_users_id' => $value['appRoleUsersId']
                    , 'mstuser_id' => $value['mstUser']['id']
                    , 'stateflg' => $value['mstUser']['stateFlg']
                    , 'email' => $value['mstUser']['email']
                    , 'user_name' => $value['mstUser']['name']
                    , 'department_name' => $value['mstUser']['mstDepartment']['name']
                    , 'position_name' => $value['mstUser']['mstPosition']['name']
                    );
                }
            }
        } else {
            $response_user = ApplicationAuthUtils::getCompanyAppUsersSearch($user['mst_company_id'], $request->get('filter_app', $first_app_id),
                $request->get('filter_role', ''), $request->get('email', ''), $request->get('department'),
                $request->get('position'), $request->get('username'));
            $listuser = [];
            if ($response_user) {
                foreach ($response_user as $value) {
                    $listuser[] = array(
                        'app_role_name' => $value['app_role_name']
                    , 'app_role_users_id' => $value['id']
                    , 'mstuser_id' => $value['id']
                    , 'stateflg' => $value['enabled']
                    , 'email' => $value['email']
                    , 'user_name' => $value['name']
                    , 'department_name' => $value['department']
                    , 'position_name' => $value['position']
                    );
                }
            }
        }


        // by user id
        $sortFunction = function ($a, $b) {
            return $a['mstuser_id'] > $b['mstuser_id'];
        };
        switch ($orderBy) {
            case 'role':
                $sortFunction = function ($a, $b) {
                    return $a['app_role_name'] > $b['app_role_name'];
                };
                break;
            case 'email':
                $sortFunction = function ($a, $b) {
                    return $a['email'] > $b['email'];
                };
                break;
            case 'username':
                $sortFunction = function ($a, $b) {
                    return $a['user_name'] > $b['user_name'];
                };
                break;
            case 'adminDepartment':
                $sortFunction = function ($a, $b) {
                    return $a['department_name'] > $b['department_name'];
                };
                break;
            case 'position':
                $sortFunction = function ($a, $b) {
                    return $a['position_name'] > $b['position_name'];
                };
                break;
        }
        usort($listuser, $sortFunction);
        if ($orderDir === 'desc') {
            $listuser = array_reverse($listuser);
        }

        $page = $request->get('page','1');
        //  forpage使えないから自前スライス
        $pagelistuser = new LengthAwarePaginator(array_slice( $listuser, ($page - 1) * $limit, $limit, false), count($listuser), $limit);
        $pagelistuser->setPath($request->url());
        $pagelistuser->appends(request()->input()); // sort params etc


        // アプリのユーザー一覧取得
        if ($request->get('filter_app', $first_app_id) != '' && !in_array($request->get('filter_app', $first_app_id), ApplicationAuthUtils::APPLICATION_IDS)) {
            $appUsers = GwAppApiUtils::getCompanyAppUsersSearch($user['email'], $user['mst_company_id'], $request->get('filter_app', $first_app_id));
            if ($appUsers === false) {
                Log::error('Search roleuser mstApplicationId:' . $request->get('filter_app', $first_app_id));
                $failure_message = "アプリロール設定を取得できませんでした。";
            }
        } else {
            $appUsers = ApplicationAuthUtils::getCompanyAppUsersSearch($user['mst_company_id'], $request->get('filter_app', $first_app_id), "", "", "", "", "");
            foreach ($appUsers as &$appuser){
                $appuser['appRoleId'] = $appuser['app_role_id'];
            }
        }

        $this->setMetaTitle('アプリロール設定');
        $this->assign('user_title', '管理者');

        $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
        $this->assign('filter_app', $request->get('filter_app',$first_app_id)); //第2引数：ページを開いた直後の選択は、一覧の先頭のapp_idを渡す
        $this->assign('listapp', $list_app);
        $this->assign('listuser', $pagelistuser);
        $this->assign('failure_message', $failure_message);
        $this->assign('listrole', $listrole);
        $this->assign('roleList', $roleList);   // APIから取得したロールの一覧
        $this->assign('listDepartment', $listDepartment);
        $this->assign('listDepartmentTree', $listDepartmentTree);
        $this->assign('listPosition', $listPosition);

        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);

        $this->assign('appUsers', $appUsers);

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        return $this->render('SettingGroupware.AppRole.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id ロールID
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, $app_id)
    {
        $user   = \Auth::user();

        try {
            if(!$user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_HISTORY_VIEW)){
                //$this->raiseWarning(__('message.not_permission_access'));
                //return redirect()->route('home');
            }

            if (!in_array($app_id, ApplicationAuthUtils::APPLICATION_IDS)) {
                $response_role_arry = GwAppApiUtils::getCompanyAppUserDetail($id, $user['email'], $user['mst_company_id']);
                if (!$response_role_arry) {
                    Log::error('Search Role portalCompanyId:' . $user['mst_company_id']);
                    return response()->json(['status' => false, 'message' => [Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                }
            } else {
                $response_role_arry = ApplicationAuthUtils::getCompanyAppUserDetail($id, $user['mst_company_id']);
            }
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        return response()->json(['status' => true, 'item2' => $response_role_arry ]);
    }


    /**
     * update a appRoleId.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    function update(Request $request){
        $user = \Auth::user();

        try{
            $select_role_id = $request->get('select_role_id'); //選択されたアプリロールid
            $cdis           = $request->get('cids');           //選択されたチェックボックスの配列
            $mst_application_id = $request->get('appid');
            if (!in_array($mst_application_id, ApplicationAuthUtils::APPLICATION_IDS)) {//ロール更新API呼び出し
                $update_result = GwAppApiUtils::updateCompanyAppUser($user['email'], $user['mst_company_id'], $select_role_id, $cdis);
                if (!$update_result) {
                    Log::error('Update Role appRoleId:' . $select_role_id);
                    return response()->json(['status' => false, 'message' => [Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                }
            } else {
                ApplicationAuthUtils::updateCompanyAppUser($user['mst_company_id'], $mst_application_id, $select_role_id, $cdis);
            }

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true, 'message' => [__('message.success.update_user_app_role')]]);
    }

    /**
     * store a appRoleDetail.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    function detailstore(Request $request){
        try{
            $user = \Auth::user();

            $item = $request->get('item','');

            $memo = $item['memo']; //入力されたmemo
            $mstApplicationId = $item['mstApplicationId']; //入力されたmstApplicationId
            $cids = $item['cids']; //選択されたチェックボックスの配列
            $name = $item['name']; //入力されたname


            //ロール詳細登録API呼び出し
            if (!in_array($mstApplicationId, ApplicationAuthUtils::APPLICATION_IDS)) {
                $result_code = GwAppApiUtils::storeCompanyAppDetail($user['email'], $user['mst_company_id'], $memo, $mstApplicationId, $cids, $name);
                if ($result_code == 200) {
                } elseif ($result_code == 400) {
                    Log::error('Update Role Rolename Duplicate:' . $name);
                    return response()->json(['status' => false, 'message' => [__('同じ名前のロールが既に存在します')]]);
                } else {
                    Log::error('Store Role mstApplicationId:' . $mstApplicationId);
                    return response()->json(['status' => false, 'message' => [Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                }
            } else {
                $result_code = ApplicationAuthUtils::storeCompanyAppDetail($user['mst_company_id'], $memo, $mstApplicationId, $cids, $name);
                if (!$result_code) {
                    return response()->json(['status' => false, 'message' => [Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                }
            }
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        return response()->json(['status' => true, 'message' => [__('message.success.create_app_role')]]);
    }

    /**
     * update a appRoleDetail.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
     function detailupdate($id, Request $request){
        try{
            $user = \Auth::user();

            $item = $request->get('item','');

            $memo = $item['memo']; //入力されたmemo
            $mstApplicationId = $item['mstApplicationId']; //入力されたmstApplicationId
            $cids = $item['cids']; //選択されたチェックボックスの配列
            $name = $item['name']; //入力されたname


            //ロール詳細登録API呼び出し
            if (!in_array($mstApplicationId, ApplicationAuthUtils::APPLICATION_IDS)) {
                $result_code = GwAppApiUtils::updateCompanyAppDetail($user['email'], $user['mst_company_id'], $id, $memo, $mstApplicationId, $cids, $name);
                if ($result_code == 200) {
                } elseif ($result_code == 400) {
                    Log::error('Update Role Rolename Duplicate:' . $name);
                    return response()->json(['status' => false, 'message' => [__('同じ名前のロールが既に存在します')]]);
                } else {
                    Log::error('Update Role mstApplicationId:' . $mstApplicationId);
                    return response()->json(['status' => false, 'message' => [Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                }
            } else {
                $result_code = ApplicationAuthUtils::updateCompanyAppDetail($id, $user['mst_company_id'], $memo, $mstApplicationId, $cids, $name);
                if (!$result_code) {
                    return response()->json(['status' => false, 'message' => [Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                }
            }
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        return response()->json(['status' => true, 'message' => [__('message.success.update_app_role')]
            ]);
    }

    /**
     * delete a appRole.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    function delete($id,$app_id, Request $request){
        try {
            $user = \Auth::user();
            //ロール詳細登録API呼び出し
            if (!in_array($app_id, ApplicationAuthUtils::APPLICATION_IDS)) {
                $delete_result = GwAppApiUtils::deleteCompanyAppDetail($user['email'], $user['mst_company_id'], $id);
                if (!$delete_result) {
                    Log::error('Update Role portalCompanyId:' . $user['mst_company_id']);
                    return response()->json(['status' => false, 'message' => [Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                }
            } else {
                $delete_result = ApplicationAuthUtils::deleteCompanyAppDetail($id, $user['mst_company_id'], $app_id);
                if (!$delete_result) {
                    return response()->json(['status' => false, 'message' => [Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                }
            }
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        return response()->json(['status' => true, 'message' => [__('message.success.delete_app_role')]
            ]);
    }

}
