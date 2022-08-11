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
}