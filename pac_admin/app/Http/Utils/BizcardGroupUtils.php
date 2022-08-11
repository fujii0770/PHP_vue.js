<?php

namespace App\Http\Utils;

use DB;
use Illuminate\Support\Facades\Log;

class BizcardGroupUtils
{
    /**
     * 該当企業のグループ用リストを取得
     * @param $mst_company_id
     * @return array
     */
    public static function getGroupTree ($mst_company_id) {
        // 部署情報取得
        $departments = DB::table('mst_department')
        ->select('id', 'parent_id', 'department_name as name', 'department_name as sort_name')
        ->where('mst_company_id', $mst_company_id)
        ->where('state', 1)
        ->get()
        ->map(function ($sort_name) {
            $sort_name->sort_name = str_replace(AppUtils::STR_KANJI, AppUtils::STR_SUUJI, $sort_name->sort_name);
            return $sort_name;
        })
        ->keyBy('id')
        ->sortBy('sort_name');
        // 部署ツリー取得
        $department_tree = CommonUtils::arrToTree($departments);

        // 利用者情報取得
        $users = DB::table('mst_user')
        ->select('id', 'family_name', 'given_name')
        ->where('mst_company_id', $mst_company_id)
        ->whereNull('delete_at')
        ->get()
        ->toArray();
        $userInfo = DB::table('mst_user_info')
        ->whereIn('mst_user_id', array_column($users, 'id'))
        ->get();

        // 利用者名をmst_user_infoのデータに紐づける
        foreach ($userInfo as &$user) {
            $key = array_search($user->mst_user_id, array_column($users, 'id'));
            $user->name = $users[$key]->family_name . ' ' . $users[$key]->given_name;
        }
        unset($user);
        // 部署ツリーに利用者情報を追加
        BizcardGroupUtils::makeGroupTree($department_tree, $userInfo);
        // $arr_out = CommonUtils::treeToArr($department_tree);
        return $department_tree;
    }
    
    /**
     * 該当企業のグループ用リストを取得
     * @param $mst_company_id
     * @return array
     */
    public static function makeGroupTree (&$department_tree, $userInfo) {
        $noDeptUsers = [];  // 部署なしの利用者情報を保存する配列
        foreach ($userInfo as $user) {
            // 部署IDをparent_idに設定。部署なしの場合は-1に設定
            $user->parent_id = $user->mst_department_id == null ? $user->parent_id = -1 : $user->mst_department_id;
            if ($user->parent_id == -1) {
                $noDeptUser = new \stdClass();
                $noDeptUser->id = 'user' . $user->mst_user_id;
                $noDeptUser->parent_id = -1;
                $noDeptUser->name = $user->name;
                $noDeptUser->sort_name = $user->name;
                $noDeptUsers[] = $noDeptUser;
            } else {
                BizcardGroupUtils::setUserToDepartmentTree($department_tree, $user);
            }
        }
        if (count($noDeptUsers) > 0) {
            // 部署なしの利用者が存在する場合、部署ツリーに「部署なし」を追加
            $noDept = new \stdClass();
            $noDept->id = -1;
            $noDept->parent_id = 0;
            $noDept->name = '部署なし';
            $noDept->sort_name = '部署なし';
            $noDept->data_child = $noDeptUsers;
            $department_tree[] = $noDept;
        }
    }

    /**
     * 部署に利用者が所属しているか調べ、部署ツリーに部署ありの利用者の情報を加える
     * @param $mst_company_id
     * @return array
     */
    private static function setUserToDepartmentTree (&$department_tree, $user) {
        foreach ($department_tree as &$department) {
            // 所属している部署のdata_childに利用者情報を追加
            if ($department->id == $user->mst_department_id) {
                $newItem = new \stdClass();
                $newItem->id = 'user' . $user->mst_user_id;
                $newItem->parent_id = $user->mst_department_id;
                $newItem->name = $user->name;
                $newItem->sort_name = $user->name;
                $department->data_child[] = $newItem;
                // 所属している部署が見つかればreturn
                return true;
            }
            if (isset($department->data_child)) {
                // 子部署がある場合、利用者が子部署に所属しているか調べる
                if (BizcardGroupUtils::setUserToDepartmentTree($department->data_child, $user)) {
                    return true;
                }
            }
        }
        unset($department);
        return false;
    }
}