<?php

namespace App\Http\Utils;
use Illuminate\Support\Facades\Log;


class CommonUtils
{
    /**
      * Build tree from array
      */
      public static function arrToTree($items){
        if(!count($items)) return $items;
       $childs = [];
       $rootItems = [];
       foreach ($items as $item) {
           if($item->parent_id == null) $item->parent_id = 0;
           $childs[$item->parent_id][] = $item;

           if (!$item->parent_id){
               $rootItems[] = $item;
           }
       }

       foreach ($items as $item) {
           if (isset($childs[$item->id]))
               $item->children = $childs[$item->id];
       }
       if (count($childs)){
           $items = $rootItems;
       }else{
           $items = [];
       }
       return $items;
    }

    /**
     * tree to array. list sub after its parent
     */
    public static function treeToArr($items, $level = 1, $fieldText = 'name')
    {
        $arr_out = [];
        if(count($items)){
            foreach($items as $item){
                $arr_out[] = ['id' => $item->id, 'level' => $level, 'text' => $item->$fieldText, 'parent_id' => $item->parent_id];
                if(isset($item->children) and count($item->children)){
                    $arr_out = array_merge($arr_out, CommonUtils::treeToArr($item->children, $level + 1, $fieldText));
                }
            }
        }
        return $arr_out;
    }

    public static function circularDocumentRoot($items, $id){
       if(!count($items)) return $id;
       $mapParentIds = [];
       foreach ($items as $item) {
            $mapParentIds[$item->id][] = $item->origin_document_id;
       }
 
       if (key_exists($id, $mapParentIds)){
           $parentId = $mapParentIds[$id];
           if($parentId[0] == -1){
                return $id;
            }
           while($parentId[0] != -1 && $parentId[0] != 0){
                if (key_exists($parentId[0] , $mapParentIds)){
                    if($mapParentIds[$parentId[0]][0] == -1 ){
                        return $parentId[0];
                    }else{
                        $parentId = $mapParentIds[$parentId[0]];
                    }
                }else{
                    return $parentId[0];
                }
           }
       }else{
           return $id;
       }
    }


    /**
     * 特殊記号は'_'を置換
     *
     * @param $str
     * @return string|string[]
     */
    public static function changeSymbols($str) {
        // 特殊記号
        $symbols = ['\\', '/', ':', '*', '?', '"', '<', '>', '|'];
        return str_replace($symbols, '_', $str);
    }

    /**
     * null empty 判断する
     * @param $param
     * @return bool
     */
    public static function isNullOrEmpty($param)
    {
        if (is_null($param)) {
            return true;
        }
        if (empty($param) && $param !== 0 && $param !== '0') {
            return true;
        }
        return false;
    }

    /**
     * 特殊記号は'<','>'を置換
     * @param $name
     * @return array|string|string[]
     */
    public static function replaceCharacter($name){
        return str_replace(array('&lt;', '&gt;'), array('<', '>'), $name);
    }
}