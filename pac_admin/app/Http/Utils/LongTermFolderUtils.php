<?php

namespace App\Http\Utils;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LongTermFolderUtils
{
    const AUTH_KBN_NOTHING = '-1';
    const AUTH_KBN_ALL = '0';
    const AUTH_KBN_POSITION = '1';
    const AUTH_KBN_DEPARTMENT = '2';
    const AUTH_KBN_USER = '3';
    const CHECK_FLG_TRUE = 1;

    /**
     * 該当企業すべてフォルダレベル取得
     * @param $mst_company_id
     * @return array
     */
    public static function getLongTermFolderTree($mst_company_id)
    {
        $items = DB::table('long_term_folder')
            ->select('id', 'parent_id', 'folder_name as name', 'folder_name as sort_name')
            ->where('mst_company_id', $mst_company_id)
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        $item_tree = CommonUtils::arrToTree($items);
        return $item_tree;
    }
}