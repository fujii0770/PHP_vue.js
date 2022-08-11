<?php

namespace App\Http\Controllers\LongTerm;

use App\Http\Controllers\AdminController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\DepartmentUtils;
use App\Http\Utils\LongTermFolderUtils;
use App\Http\Utils\PermissionUtils;
use App\Models\Company;
use App\Models\LongTermFolder;
use App\Models\LongTermFolderAuth;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Session;

class LongTermFolderController extends AdminController
{

    private $user;
    private $position;
    private $company;
    private $longTermFolder;
    private $longTermFolderAuth;

    public function __construct(User $user, Position $position, Company $company, LongTermFolder $longTermFolder, LongTermFolderAuth $longTermFolderAuth)
    {
        parent::__construct();
        $this->user = $user;
        $this->position = $position;
        $this->company = $company;
        $this->longTermFolder = $longTermFolder;
        $this->longTermFolderAuth = $longTermFolderAuth;
    }

    /**
     * 長期保管フォルダ管理の検索
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        if (!$user->can(PermissionUtils::PERMISSION_LONG_TERM_FOLDER_VIEW)) {
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        $folder_id = $request->get('folderId', 0);//フォルダ検索
        $folder = ['parent_folder_id' => 0, 'folder_id' => $folder_id];
        // get list user
        // set limit to 50 for UserSetting page
        $limit = $request->get('limit') ? $request->get('limit') : 50;//config('app.page_limit');
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir') : 'desc';
        $orderBy2 = $request->get('orderBy2') ? $request->get('orderBy2') : 'id';
        $orderDir2 = $request->get('orderDir2') ? $request->get('orderDir2') : 'desc';
        if (!array_search($limit, array_merge(config('app.page_list_limit'), [20]))) {
            $limit = config('app.page_limit');
        }

        $company = $this->company->where('id', $user->mst_company_id)->first();

        $folder_query = function ($query) use ($folder_id) {
            $query->select(DB::raw(1))
                ->from('long_term_folder_auth')
                ->where('long_term_folder_auth.auth_kbn', LongTermFolderUtils::AUTH_KBN_USER)
                ->where('long_term_folder_auth.long_term_folder_id', $folder_id)
                ->whereRaw('long_term_folder_auth.auth_link_id=mu.id');
        };
        //権限あり利用者
        $permission_users = $this->longTermFolder->getFolderPermissionUsers($user->mst_company_id)
            ->whereExists($folder_query)
            ->orderBy($orderBy,$orderDir)
            ->paginate($limit)->appends(request()->input());
        //権限なし利用者
        $permission_not_users = $this->longTermFolder->getFolderPermissionUsers($user->mst_company_id)
            ->whereNotExists($folder_query)
            ->orderBy($orderBy2,$orderDir2)
            ->paginate($limit,['*'],'page2')->appends(request()->input());

        $folder_tree = $this->longTermFolder->where('id', $folder_id)->first();
        if ($folder_id) {
            $folder['parent_folder_id'] = array_filter(explode(',', $folder_tree->tree));
        }
        //画面左側のフォルダツリー
        $itemsFolder = LongTermFolderUtils::getLongTermFolderTree($user->mst_company_id);
        //検索の部署
        $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);
        //検索の役職
        $listPosition = $this->position->getSearchPositionItems($user->mst_company_id);

        // 上位部署の情報を取得する
        $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);

        $this->assign('permission_users', $permission_users);
        $this->assign('permission_not_users', $permission_not_users);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);
        $this->assign('orderBy2', $orderBy);
        $this->assign('orderDir2', $orderDir);
        $this->assign('itemsFolder', $itemsFolder);
        $this->assign('listDepartmentDetail', $listDepartmentDetail);
        $this->assign('listDepartmentTree', $listDepartmentTree);
        $this->assign('listPosition', $listPosition);
        $this->assign('company', $company);
        $this->assign('folder', $folder);
        $this->assign('hasSelectedID', $folder_id);

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        $this->setMetaTitle("長期保管フォルダ管理");

        return $this->render('LongTerm.longTermFolder.index');
    }

    /**
     * フォルダ名称取得
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show($id, Request $request): JsonResponse
    {
        $user = $request->user();

        $item = $this->longTermFolder->where('mst_company_id', $user->mst_company_id)->find($id);

        if (!$item) {
            return response()->json(['status' => false, 'message' => [__('message.warning.not_permission_access')]]);
        }
        return response()->json(['status' => true, 'item' => $item]);
    }

    /**
     * フォルダ登録
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {

        $user = $request->user();
        $item_info = $request->get('item');
        $tree = '';
        $item = new $this->longTermFolder;
        $itemAuth = new $this->longTermFolderAuth;
        // 親フォルダ必須チェック
        if (!isset($item_info['parent_id']) || CommonUtils::isNullOrEmpty($item_info['parent_id'])) {
            return response()->json(['status' => false, 'message' => ['親フォルダを指定してください。']]);
        }
        // フォルダ名必須チェック
        if (!isset($item_info['folder_name']) || CommonUtils::isNullOrEmpty($item_info['folder_name'])) {
            return response()->json(['status' => false, 'message' => ['フォルダ名を指定してください。']]);
        }

        // 重複チェック
        $isRepeated = $this->longTermFolder->isFolderNameRepeated($user->mst_company_id, $item_info['parent_id'], $item_info['folder_name']);
        if ($isRepeated) {
            return response()->json(['status' => false, 'message' => ['フォルダ名が既に存在します。']]);
        }
        $item->fill($item_info);
        $item->mst_company_id = $user->mst_company_id;
        $item->create_user = $user->getFullName();
        $item->update_user = $user->getFullName();

        DB::beginTransaction();
        try {
            $item->save();
            if ($item_info['parent_id'] != 0) {
                $parent_folder = $this->longTermFolder->where('mst_company_id', $user->mst_company_id)->where('id', $item_info['parent_id'])->first();
                if (!$parent_folder) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => [__('message.warning.not_permission_access')]]);
                } else {
                    $tree = $parent_folder->tree;
                    $parent_folder_hierarchy = count(explode(',',$tree))-1;
                    if($parent_folder_hierarchy >= 10){
                        DB::rollBack();
                        return response()->json(['status' => false, 'message' => [__('message.false.folder_hierarchy_exceed')]]);
                    }
                }
                if (isset($item_info['inherit_flg']) && $item_info['inherit_flg']) {
                    $parent_permissions = $this->longTermFolder->getFolderAuth($user->mst_company_id, $parent_folder->id, LongTermFolderUtils::AUTH_KBN_USER);
                    if ($parent_permissions->count() && !CommonUtils::isNullOrEmpty($parent_permissions[0]->id)) {
                        foreach ($parent_permissions as $parent_permission) {
                            $itemAuth = $itemAuth->find($parent_permission->id)->replicate();
                            $itemAuth->create_user = $user->getFullName();
                            $itemAuth->update_user = $user->getFullName();
                            $itemAuth->long_term_folder_id = $item->id;
                            $itemAuth->save();
                        }
                    }
                }
            }else{
                if (isset($item_info['inherit_flg']) && $item_info['inherit_flg']) {
                    $all_permissions = User::select('id')
                        ->where('mst_company_id',$user->mst_company_id)
                        ->where('state_flg',AppUtils::STATE_VALID)
                        ->where('option_flg',AppUtils::USER_NORMAL)->get();
                    foreach ($all_permissions as $permission) {
                        $itemAuth = new $this->longTermFolderAuth;
                        $itemAuth->auth_kbn = LongTermFolderUtils::AUTH_KBN_USER;
                        $itemAuth->auth_link_id = $permission->id;
                        $itemAuth->create_user = $user->getFullName();
                        $itemAuth->update_user = $user->getFullName();
                        $itemAuth->long_term_folder_id = $item->id;
                        $itemAuth->save();
                    }
                }
            }
            $item->tree = $tree . $item->id . ',';
            $item->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.create_folder')]]);
        }

        return response()->json(['status' => true, 'id' => $item->id,
            'message' => [__('message.success.create_folder')]
        ]);
    }

    /**
     * 名称変更
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update($id, Request $request): JsonResponse
    {
        $user = $request->user();
        $item_info = $request->get('item');
        $old_tree = '';
        // フォルダ必須チェック
        if (!$id) {
            return response()->json(['status' => false, 'message' => ['フォルダを指定してください。']]);
        }
        $Folder = $this->longTermFolder
            ->where('mst_company_id', $user->mst_company_id)
            ->where('id', $id)
            ->first();
        if (!$Folder) {
            return response()->json(['status' => false, 'message' => ['フォルダが存在しません。']]);
        }
        // フォルダ名必須チェック
        if (!isset($item_info['folder_name']) || CommonUtils::isNullOrEmpty($item_info['folder_name'])) {
            return response()->json(['status' => false, 'message' => ['フォルダ名を指定してください。']]);
        }

        try {
            // 重複チェック
            $isRepeated = $this->longTermFolder->isFolderNameRepeated($user->mst_company_id, $item_info['parent_id'], $item_info['folder_name']);
            if ($isRepeated) {
                return response()->json(['status' => false, 'message' => ['フォルダ名が既に存在します。']]);
            }

            $item = $this->longTermFolder->where('mst_company_id', $user->mst_company_id)->find($id);

            if (!$item || $item_info['mst_company_id'] != $user->mst_company_id) {
                return response()->json(['status' => false, 'message' => [__('message.warning.not_permission_access')]]);
            }
            $item->fill($item_info);
            $item->update_user = $user->getFullName();
            $item->save();
        } catch (\Exception $e) {
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.update_folder')]]);
        }

        return response()->json(['status' => true, 'id' => $item->id, 'message' => [__('message.success.update_folder')]]);
    }

    /**
     * フォルダ削除
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy($id, Request $request): JsonResponse
    {
        $user = $request->user();
        $item_info = $request->get('item');
        // フォルダ必須チェック
        if (!$id) {
            return response()->json(['status' => false, 'message' => ['フォルダを指定してください。']]);
        }

        $item = $this->longTermFolder->where('mst_company_id', $user->mst_company_id)->find($id);
        if (!$item) {
            return response()->json(['status' => false, 'message' => [__('message.warning.not_permission_access')]]);
        }

        $company = DB::table('mst_company')->select('long_term_default_folder_id')->where('id', $user->mst_company_id)->first();
        //自動保管文書のフォルダ判断
        if ($company->long_term_default_folder_id == $id) {
            return response()->json(['status' => false, 'message' => [__('message.warning.default_folder')]]);
        }

        // 企業配下全フォルダ
        $delFolderChild = $this->longTermFolder
            ->where('mst_company_id', $user->mst_company_id)
            ->where('tree', 'like', "$item->tree%")
            ->count();
        if ($delFolderChild > 1) {
            return response()->json(['status' => false, 'message' => [__('message.warning.folder_in_delect_folder')]]);
        }

        // フォルダ関連ファイル
        $delFolderfile = DB::table('long_term_document')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('long_term_folder_id', $id)
            ->count();
        if ($delFolderfile > 0) {
            return response()->json(['status' => false, 'message' => [__('message.warning.file_in_delect_folder')]]);
        }
        DB::beginTransaction();
        try {
            // フォルダ削除
            DB::table('long_term_folder')
                ->where('mst_company_id', $user->mst_company_id)
                ->where('id', $id)
                ->delete();
            DB::table('long_term_folder_auth')
                ->where('long_term_folder_id', $id)
                ->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.delete_folder')]]);
        }

        return response()->json(['status' => true,
            'message' => [__('message.success.delete_folder')], 'show_folder_id' => $item->parent_id
        ]);
    }

    /**
     * 権限付与対象（部署と役職）
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getParentPermissions($id, Request $request): JsonResponse
    {
        $user = $request->user();
        // フォルダ必須チェック
        if (!$id) {
            return response()->json(['status' => false, 'message' => ['フォルダを指定してください。']]);
        }
        $folder = $this->longTermFolder
            ->where('mst_company_id', $user->mst_company_id)
            ->where('id', $id)
            ->first();
        if (!$folder) {
            return response()->json(['status' => false, 'message' => ['フォルダが存在しません']]);
        }
        $position_permission_data = $this->longTermFolder->searchPermissions($user->mst_company_id, LongTermFolderUtils::AUTH_KBN_POSITION);
        $department_permission_data = $this->longTermFolder->searchPermissions($user->mst_company_id, LongTermFolderUtils::AUTH_KBN_DEPARTMENT);
        $department_permission_data = CommonUtils::arrToTree($department_permission_data);

        return response()->json(['status' => true, 'departmentPermissionsData' => $department_permission_data, 'positionPermissionData' => $position_permission_data]);
    }

    /**
     * ユーザ権限変更
     * @param Request $request
     * @return JsonResponse
     */
    public function saveFolderPermissions(Request $request): JsonResponse
    {
        $user = $request->user();
        $folderId = $request->get('id');
        $position_ids = $request->get('position_ids',[]);
        $department_ids = $request->get('department_ids',[]);
        $company = DB::table('mst_company')->select('id','multiple_department_position_flg')->where('id', $user->mst_company_id)->first();

        if (!$folderId) {
            return response()->json(['status' => false, 'message' => ['フォルダを指定してください。']]);
        }
        $Folder = $this->longTermFolder
            ->where('mst_company_id', $user->mst_company_id)
            ->where('id', $folderId)
            ->first();
        if (!$Folder) {
            return response()->json(['status' => false, 'message' => ['フォルダが存在しません。']]);
        }
        DB::beginTransaction();
        try {
            //部署または役職を取得したすべてのユーザー
            $position_user_ids = $this->longTermFolder->getSavedFolderPermissionUserIds(LongTermFolderUtils::AUTH_KBN_POSITION,$company,$position_ids);
            $department_user_ids = $this->longTermFolder->getSavedFolderPermissionUserIds(LongTermFolderUtils::AUTH_KBN_DEPARTMENT,$company,$department_ids);
            $user_ids = $position_user_ids->merge($department_user_ids);

            $saved_user_ids = $this->longTermFolderAuth
                ->select('auth_link_id as id')
                ->where('auth_kbn', LongTermFolderUtils::AUTH_KBN_USER)
                ->where('long_term_folder_id',$folderId)->pluck('id')->toArray();
            //未選択->選択 権限追加
            if ($user_ids) {
                $add_folder_auths = [];
                foreach ($user_ids as $addcid) {
                    if (in_array($addcid->id,array_values($saved_user_ids))) continue;
                    $add_folder_auths[] = [
                        'long_term_folder_id' => $folderId,
                        'auth_kbn' => LongTermFolderUtils::AUTH_KBN_USER,
                        'auth_link_id' => $addcid->id,
                        'create_user' => $user->getFullName(),
                        'create_at' => Carbon::now(),
                        'update_user' => $user->getFullName(),
                    ];
                }
                DB::table('long_term_folder_auth')->insert($add_folder_auths);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.update_folder_auth')]]);
        }
        return response()->json(['status' => true, 'id' => $folderId,'message' => [__('message.success.update_folder_auth')]]);
    }

    /**
     * ユーザ権限追加
     * @param Request $request
     * @return JsonResponse
     */
    public function addUsersToFolderPermission(Request $request): JsonResponse
    {
        try {
            $user = \Auth::user();
            $add_user_ids = $request->get('user_ids');
            $folder_id = $request->get('folder_id');
            $folder_auth_items = [];
            if (!$folder_id || !$add_user_ids) {
                return response()->json(['status' => false, 'message' => [__('message.false.add_users_to_folder_permission')]]);
            }

            foreach ($add_user_ids as $user_id) {
                $folder_auth_items[] = [
                    'long_term_folder_id' => $folder_id,
                    'auth_kbn' => LongTermFolderUtils::AUTH_KBN_USER,
                    'auth_link_id' => $user_id,
                    'create_user' => $user->getFullName()
                ];
            }
            DB::beginTransaction();
            DB::table('long_term_folder_auth')->insert($folder_auth_items);
            DB::commit();
            return response()->json(['status' => true, 'message' => [__('message.success.add_users_to_folder_permission')]]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.add_users_to_folder_permission')]]);
        }
    }

    /**
     * ユーザ権限削除
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteUsersFromFolderPermission(Request $request): JsonResponse
    {
        try {
            $delete_ids = $request->get('user_ids');
            $folder_id = $request->get('folder_id');
            DB::table('long_term_folder_auth')
                ->where('long_term_folder_id',$folder_id)
                ->where('auth_kbn', LongTermFolderUtils::AUTH_KBN_USER)
                ->whereIn('auth_link_id', $delete_ids)
                ->delete();

            return response()->json(['status' => true, 'message' => [__('message.success.delete_users_from_folder_permission')]]);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.delete_users_from_folder_permission')]]);
        }
    }
}