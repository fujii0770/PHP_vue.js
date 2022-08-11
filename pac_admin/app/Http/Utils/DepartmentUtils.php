<?php


namespace App\Http\Utils;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartmentUtils
{

    /**
     * 該当企業すべて部署レベル取得
     * @param $mst_company_id
     * @return array
     */
    public static function getDepartmentTree($mst_company_id)
    {
        $items = DB::table('mst_department')
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

        $item_tree = CommonUtils::arrToTree($items);
        $arr_out = CommonUtils::treeToArr($item_tree);
        return $arr_out;
    }

    /**
     * 部署レベル取得（一覧画面の部署項目用）
     * @param $listDepartmentTree
     * @return mixed
     */
    public static function buildDepartmentDetail($listDepartmentTree)
    {
        $mapItems = [];
        //配列の添え字をIDに変更します
        foreach ($listDepartmentTree as $item) {
            $mapItems[$item['id']] = $item;
        }
        $listDepartmentTree = $mapItems;
        //部署をレイヤーに分割する
        foreach ($listDepartmentTree as $key => &$item) {
            if (isset($item['level']) and $item['level'] > 1) {
                $parentId = $item['parent_id'];
                $text = $item['text'];
                while ($parentId) {
                    if (key_exists($parentId, $mapItems)) {
                        $parentItem = $mapItems[$parentId];
                        $text = ($parentItem['text'] . '＞' . $text);
                        $parentId = $parentItem['parent_id'];
                    } else {
                        break;
                    }
                }
            } else {
                $text = $item['text'];
            }
            $item['text'] = $text;
        }
        return $listDepartmentTree;
    }

    /**
     * すべて部署レベルより、プルダウンデータ作成取得（画面用）
     * @param array $items
     * @param string $name
     * @param string $value
     * @param null $default_text
     * @param array $option
     * @return string
     */
    public static function buildDepartmentSelect($items = [], $name = '', $value = '', $default_text = null, $option = [])
    {
        $mapItems = [];
        foreach ($items as $item) {
            $mapItems[$item['id']] = $item;
        }

        $option_text = '';
        foreach ($option as $key => $val) {
            $option_text .= " $key=\"$val\"";
        }
        $id = isset($option['id']) ? $option['id'] : trim(preg_replace('/[^\d\w]/ism', '_', $name), '_');

        $html = '<select name="' . $name . '" ' . $option_text . ' id="' . $id . '">';
        if ($default_text !== null)
            $html .= '<option value="">' . $default_text . '</option>';
        foreach ($items as $key => $item) {
            if (is_array($item)) {
                if (isset($item['level']) and $item['level'] > 1) {
                    $parentId = $item['parent_id'];
                    $text = $item['text'];
                    while ($parentId) {
                        if (key_exists($parentId, $mapItems)) {
                            $parentItem = $mapItems[$parentId];
                            $text = ($parentItem['text'] . '＞' . $text);
                            $parentId = $parentItem['parent_id'];
                        } else {
                            break;
                        }
                    }
                } else {
                    $text = $item['text'];
                }
                $val = $item['id'];
            } else {
                $val = $key;
                $text = $item;
            }

            $html .= '<option value="' . $val . '" ' . (($val == $value and $value != null) ? 'selected' : '') . '>' . htmlspecialchars($text) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
    
    /**
     * 部門の子IDを取得します
     * @param $departmentList
     * @param $departmentId
     * @param $departmentIds
     * @param bool $isFirst
     */
    public static function getDepartmentChildIds($departmentList, $departmentId, &$departmentIds, $isFirst = true)
    {
        if ($isFirst) $departmentIds[] = (int)$departmentId;
        foreach ($departmentList as $item) {
            if ($item->parent_id === (int)$departmentId) {
                $departmentIds[] = $item->id;
                self::getDepartmentChildIds($departmentList, $item->id, $departmentIds,false);
            }
        }
    }

    /**
     * 更新会社の部署tree
     * @param $mst_company_id
     * @return array
     */
    public static function updateCompanyDepartment($mst_company_id): array
    {

        //更新会社の部署のtree
        $departments = DB::table('mst_department')
            ->select('id', 'parent_id')
            ->where('state', AppUtils::DEFAULT_DEPARTMENT_STATE)
            ->where('mst_company_id', $mst_company_id)
            ->get();
        $top_tree = [];
        $child_tree = [];

        foreach ($departments as $department) {
            if ($department->parent_id == 0) {
                $top_tree[$department->id] = $department->id . ',';
            } else {
                $child_tree[$department->id] = $department;
            }
        }
        return DepartmentUtils::getChildDepartment($top_tree, $child_tree);
    }

    /**
     * 部署の「tree」
     * @param $top_departments array 親部署の「tree」
     * @param $departments array 子部署の「tree」
     * @return array
     */
    public static function getChildDepartment($top_departments, $departments): array
    {
        $tree_department = [];
        $next_top_departments = [];
        if (!$departments){
            $tree_department = $top_departments;
        }else{
            foreach ($top_departments as $parent_id => $top_department){
                foreach ($departments as $department_id => $department){
                    if ($department->parent_id == $parent_id){
                        $tree_department[$department_id] = $top_department . $department_id . ',';
                        $next_top_departments[$department_id] = $tree_department[$department_id];
                        $tree_department = $top_departments + $next_top_departments;
                        unset($departments[$department_id]);
                    }
                }
            }

            $child_tree = self::getChildDepartment($next_top_departments,$departments);
            $tree_department = $tree_department + $child_tree;
        }
        return $tree_department;
    }

    /**
     * 更新対象の子部署[tree]
     * @param $mst_company_id int 会社のID
     * @param $parent_id int 父部署のID
     * @param $department_id int 更新部署のID
     * @param $old_tree string old department tree
     * @return array
     */
    public static function getChangeChildTree($mst_company_id, $parent_id, $department_id,$old_tree): array
    {
        $trees = [];
        //更新対象の子部署
        $change_departments = DB::table('mst_department')
            ->where('state',AppUtils::DEFAULT_DEPARTMENT_STATE)
            ->where('mst_company_id',$mst_company_id)
            ->where('tree', 'like',  "$old_tree%")
            ->get();

        //更新対象の父部署
        $parent_department = DB::table('mst_department')->where('id',$parent_id)->first();
        //更新対象のtree
        $self_tree = ($parent_department ? $parent_department->tree : '') . $department_id . ',';

        foreach ($change_departments as $department){
            if ($department->id !=$department_id ){
                //更新対象の子部署のtree
                $tree = str_replace($old_tree, $self_tree, $department->tree);
                $trees[$department->id]  = $tree;
            }else{
                $trees[$department->id] = $self_tree;
            }
        }
        return $trees;
    }


}