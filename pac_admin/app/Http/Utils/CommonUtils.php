<?php

/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/3/19
 * Time: 10:22
 */

namespace App\Http\Utils;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class CommonUtils
{
    /**
     * 一覧画面HTML作成（一覧にソート項目）
     * @param string $title
     * @param string $name
     * @param string $orderBy
     * @param string $orderDir
     * @param string $formName
     * @param string $orderByName
     * @param string $orderDirName
     * @return false|string
     */
    public static function showSortColumn($title = "title", $name = "title", $orderBy = "", $orderDir = "ASC",
                                          $formName = 'adminForm', $orderByName = 'orderBy', $orderDirName = 'orderDir')
    {
        ob_start();
        echo '<div class="sort-column" onclick="javascript:document.'.$formName.'.'.$orderByName.'.value = \''.$name.'\';
            document.'.$formName.'.'.$orderDirName.'.value = \''.$orderDir.'\'; document.'.$formName.'.submit();">';
                echo $title;
        if($name == $orderBy){
            if(strtolower($orderDir) == "asc")
                echo '<i class="icon icon-active fas fa-caret-down"></i>';
            else echo '<i class="icon icon-active fas fa-caret-up"></i>';
        }else{
            echo '<i class="icon fas fa-sort"></i>';
        }
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    public static function showNgSortColumn($title = "title", $name = "name")
    { 
        ob_start();
        $ordername = "'".$name."'";
        echo '<div scope="col" class="sort-column '.$name.' " ng-click="changeSort('.$ordername.')" data-tablesaw-priority="persist" >';
            echo $title;
        echo '<i class="icon fas fa-sort"></i>';
        echo '<i class="icon icon-active icon-down fas fa-caret-down"></i>';
        echo '<i class="icon icon-active icon-up fas fa-caret-up"></i>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
    public static function showNgPaginate($paginate, $clickevent){
        ob_start();
        $disabled = "'disabled'";
        $active = "'active'";
        echo '<div class="mt-3" ng-bind-html="'.$paginate.'.dispnumber"></div>';
        echo '<div class="text-center" ng-if="'.$paginate.'.last_page > 1">';
        echo '    <nav>';
        echo '        <ul class="pagination">';
        echo '            <li class="page-item" ng-class="{'.$disabled.': '.$paginate.'.current_page <= 1}" :aria-disabled="{ '.$paginate.'.current_page <= 1 }" ng-click="'.$clickevent.'('.$paginate.'.current_page - 1)" aria-label="« 前へ">';
        echo '                <span class="page-link" aria-hidden="true">‹</span>';
        echo '            </li>';
        echo '            <li class="page-item" ng-class="{'.$active.' : $index+1 == '.$paginate.'.current_page }" ng-repeat="page_index in range_func('.$paginate.'.last_page) track by $index"';
        echo '                ng-click="'.$clickevent.'($index+1)">';
        echo '                <a class="page-link" ng-bind-html="$index+1"></a>';
        echo '            </li>';
        echo '            <li class="page-item" ng-class="{'.$disabled.': '.$paginate.'.current_page >= '.$paginate.'.last_page }" :aria-disabled="{ '.$paginate.'.current_page >= '.$paginate.'.last_page }" ng-click="'.$clickevent.'('.$paginate.'.current_page + 1)">';
        echo '                <a class="page-link" rel="next" aria-label="次へ »">›</a>';
        echo '            </li>';
        echo '        </ul>';
        echo '    </nav>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
    public static function showSortBtnColumn($status = "status", $value = "value", $sanitizing_state = "sanitizing_state") {
        ob_start();
        if($status == 0){
            echo '<div>処理待ち</div>';
        }elseif($status == 1){
            echo '<div>作成中</div>';
        }elseif(($status == 2 || $status == 3 ||$status == 4) && $sanitizing_state == 0){//ダウンロードボタンを表示すれば
            echo '<button type="button" class="btn btn-warning mb-1 cid" name="cids[]" value='.$value;
            echo ' ng-click="download()"><i class="fas fa-cloud-download-alt"></i> ダウンロード</button>';
        }elseif(($status == 2 || $status == 3 ||$status == 4) && $sanitizing_state == 1){//無害化処理ボタンを表示すれば
            echo '<button type="button" class="btn btn-warning mb-1 cid" name="cids[]" value='.$value;
            echo ' ng-click="sanitizingWaitUpdate()"><i class="fas fa-bell"></i> 無害化処理</button>';
        }elseif(($status == 2 || $status == 3 ||$status == 4) && $sanitizing_state == 2){//無害化状態が2：無害化待ち
            echo '<div>無害化待ち</div>';
        }elseif($status == 10){
            echo '<div>期限切れ</div>';
        }elseif($status == 11){
            echo '<div>無害化待ち</div>';
        }elseif($status == 12){
            echo '<div>無害化中</div>';
        }elseif($status == 13){
            echo '<div>データ取得中</div>';
        }else{
            echo '<button type="button" class="btn btn-secondary mb-1 cid" name="cids[]" value='.$value;
            echo ' ng-click="rerequest()">失敗</button>';
        }
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    public static function showRemoveBtnColumn($value = "value") {
        ob_start();
        echo '<button type="button" class="btn btn-danger mb-1 rid" name="remove" value='.$value;
        echo ' ng-click="delete()"> 削除</button>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    /**
     * 検索画面HTML作成（検索項目）
     * @param $name
     * @param $label
     * @param $value
     * @param string $type
     * @param false $required
     * @param array $option
     * @return false|string
     */
    public static function showFormField($name, $label, $value, $type = "text", $required = false, $option = [])
    {
        $option = $option?:[];
        $id = isset($option['id']) ? $option['id'] : trim(preg_replace('/[^\d\w]/ism', '_', $name), '_');
        $col = isset($option['col']) ? $option['col'] : 4;
        ob_start();
        ?>
        <div class="form-group">
           <div class="row">
                <label for="<?= $id ?>" class="col-md-<?= $col ?> control-label"><?= $label; ?><?php if ($required) {
                        echo '<span style="color: red">*</span>';
                    } ?></label>
              <div class="col-md-8">
                    <input type="<?= $type ?>" <?php if ($required) {
                        echo 'required';
                    } ?> name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>"
                           class="form-control" <?php foreach ($option as $key => $val) {
                        echo " $key=\"$val\"";
                    } ?> />
                    <span class="error <?= $name ?>-error"></span>
              </div>
           </div>
        </div>
        <?php
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    /**
     * 検索画面HTML作成（検索項目垂直レイアウト）
     * @param $name
     * @param $label
     * @param $value
     * @param string $type
     * @param false $required
     * @param array $option
     * @return false|string
     */
    public static function showFormFieldVertical($name, $label, $value, $type = "text", $required = false, $option = [])
    {
        $option = $option?:[];
        $id = isset($option['id'])?$option['id']:trim(preg_replace('/[^\d\w]/ism', '_', $name), '_');;
        ob_start();
        ?>
        <div class="form-group">
            <label for="<?= $id ?>" class="control-label"><?= $label; ?><?php if ($required) {
                    echo '<span style="color: red">*</span>';
                } ?></label>
            <input type="<?= $type ?>" <?php if ($required) {
                echo 'required';
            } ?> name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>"
                   class="form-control" <?php foreach ($option as $key => $val) {
                echo " $key=\"$val\"";
            } ?> />
            <span class="error <?= $name ?>-error"></span>
        </div>
        <?php
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    /**
     * メッセージ表示
     */
    public static function showMessage()
    {
        $arr_message = session('raise-message');
        if(!empty($arr_message)){
            foreach($arr_message as $message){
                echo '<div class="alert alert-'.$message[1].'" data-time-close="5000">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'
                    .trans($message[0]).'
                  </div>';
            }
        }
       session()->forget('raise-message');
     }

     /**
      * Build tree from array
      */
    public static function arrToTree($items)
    {
         if(!count($items)) return $items;
        $childs = [];
        $rootItems = [];
        foreach ($items as $item) {
            if($item->parent_id == null) $item->parent_id = 0;
            $childs[$item->parent_id][$item->id] = $item;

            if (!$item->parent_id){
                $rootItems[] = $item;
            }
        }

        foreach ($items as $item) {
            if (isset($childs[$item->id]))
                $item->data_child = $childs[$item->id];
        }
        if (count($childs)){
            $items = $rootItems;
        }else{
            $items = [];
        }
        return $items;
     }

     /**
      *  Get array tree from last sub to root
      *  $items: array object/array
      *  $onlyText: true - return array text, false - return array object/array
      */
     public static function arrToTreeDown($items, $value, $onlyText = false, $fieldText = 'name'){
        $arr_out = [];
        if(isset($items[$value])){
            $item = $items[$value];
            if($onlyText){
                if(\is_array($items[$value])){
                    $arr_out[] = $items[$value][$fieldText];
                }else if(\is_object($items[$value])) $arr_out[] = $items[$value]->$fieldText;
            }
            else $arr_out[] = $items[$value];
            if($item->parent_id){
                $arr_out = array_merge($arr_out, CommonUtils::arrToTreeDown($items, $item->parent_id, $onlyText));
            }
        }
        return $arr_out;
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
                if(isset($item->data_child) and count($item->data_child)){
                    $arr_out = array_merge($arr_out, CommonUtils::treeToArr($item->data_child, $level + 1, $fieldText));
                }
            }
        }
        return $arr_out;
     }

     /**
      * convert array to atribute of item sub to key of main array
      */
    public static function arrayKeyBy($items = [], $keyBy = "id")
    {
        if(!is_array($items)) return false;
        if(!count($items)) return [];

        $arrOut = [];
        foreach($items as $item){
            if(is_array($item)){
                if(isset($item[$keyBy])){
                    $arrOut[$item['parent_id'].'-'.$item[$keyBy]] = $item;
                }else return false;
            }else if(is_object($item)){
                if(isset($item->$keyBy)){
                    $arrOut[$item->parent_id.'-'.$item->$keyBy] = $item;
                }else return false;
            }else return false;
        }
        return $arrOut;
     }

    /**
     * 検索画面検索項目プルダウン
     * @param array $items
     * @param string $name
     * @param string $value
     * @param null $default_text
     * @param array $option
     * @return string
     */
    public static function buildSelect($items = [], $name = '', $value = '', $default_text = null, $option = [])
    {
         $option_text = '';
        foreach ($option as $key => $val) {
            $option_text .= " $key=\"$val\"";
        }
        $id = isset($option['id'])?$option['id']:trim(preg_replace('/[^\d\w]/ism', '_', $name), '_');

         $html =  '<select name="'.$name.'" '.$option_text.' id="'.$id.'">';
        if ($default_text !== null) {
             $html .= '<option value="">'.$default_text.'</option>';
        } else if ($default_text === '') {
            $html .= '<option></option>';
        }

            foreach($items as $key => $item){
                if (is_array($item)) {
                    if (isset($item['level']) and $item['level'] > 1)
                        $text = str_repeat("&nbsp; &nbsp; &nbsp;", $item['level'] - 1) . "|__" . $item['text'];
                    else $text = $item['text'];
                    $val = $item['id'];
                } else {
                    $val = $key;
                    $text = $item;
                }

                $html .= '<option value="'.$val.'" '.(($val == $value AND $value != null) ?'selected':'').'>'.htmlspecialchars($text).'</option>';
            }
         $html .= '</select>';
         return $html;
     }

    /**
     * 検索画面検索項目プルダウン (利用者操作履歴:利用者プルダウン  管理者操作履歴:管理者プルダウン)
     * @param array $items
     * @param string $name
     * @param string $value
     * @param null $default_text
     * @param array $option
     * @param string $title
     * @return string
     */
    public static function buildSelectHistory($items = [], $name = '', $value = '', $default_text = null, $option = [] ,$title = "")
    {
         $option_text = '';
        foreach ($option as $key => $val) {
            $option_text .= " $key=\"$val\"";
        }
        $id = isset($option['id'])?$option['id']:trim(preg_replace('/[^\d\w]/ism', '_', $name), '_');

         $html =  '<select name="'.$name.'" '.$option_text.' id="'.$id.'">';
        if ($default_text !== null) {
            $html .= '<option value="">'.$default_text.'</option>';    
        } else if ($default_text === '') {
            $html .= '<option></option>';
        }

            foreach($items as $key => $item){    
                if (is_array($item)) {
                    if (isset($item['level']) and $item['level'] > 1)
                        $text = str_repeat("&nbsp; &nbsp; &nbsp;", $item['level'] - 1) . "|__" . $item['text'];
                    else $text = $item['text'];
                    $val = $item['id'];
                } else {
                    $val = $key;
                    $text = $item;
                }

                if ($title == "利用者") {
                    $state_flg_data = DB::table('mst_user')->where('id',$val)->where('state_flg',-1)
                    ->first();
                }elseif ($title == "監査用アカウント"){
                    $state_flg_data = DB::table('mst_audit')->where('id',$val)->where('state_flg',-1)
                        ->first();
                } else {
                    $state_flg_data = DB::table('mst_admin')->where('id',$val)->where('state_flg',-1)
                    ->first();
                }
               
                if($state_flg_data == null) {
                    $html .= '<option value="'.$val.'" '.(($val == $value AND $value != null) ?'selected':'').'>'.htmlspecialchars($text).'</option>';
                } else {
                    $html .= '<option value="'.$val.'" '.(($val == $value AND $value != null) ?'selected':'').'>'.htmlspecialchars($text).' (削除済)</option>';
                }
                
            }
         $html .= '</select>';
         return $html;
     }

    /**
     * 検索画面検索項目プルダウン（空白アイテムなし）
     *
     * @param array $items
     * @param string $name
     * @param string $value
     * @param array $option
     * @return string
     */
    public static function buildSelectNoDefault($items = [], $name = '', $value = '', $option = [])
    {
        $option_text = '';
        foreach ($option as $key => $val) {
            $option_text .= " $key=\"$val\"";
        }
        $id = isset($option['id']) ? $option['id'] : trim(preg_replace('/[^\d\w]/ism', '_', $name), '_');
        $html = '<select name="' . $name . '" ' . $option_text . ' id="' . $id . '">';
        foreach ($items as $key => $item) {
            if (is_array($item)) {
                if (isset($item['level']) and $item['level'] > 1)
                    $text = str_repeat("&nbsp; &nbsp; &nbsp;", $item['level'] - 1) . "|__" . $item['text'];
                else $text = $item['text'];
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

    public static function buildSelectWithoutDefault($items = [], $name ='', $value = '', $option = []){
        $option_text = '';
        foreach($option as $key => $val){ $option_text .= " $key=\"$val\""; }
        $id = isset($option['id'])?$option['id']:trim(preg_replace('/[^\d\w]/ism', '_', $name), '_');

        $html =  '<select name="'.$name.'" '.$option_text.' id="'.$id.'">';
        $html .= '<option></option>';

        foreach($items as $key => $item){
            if(is_array($item)){
                if (isset($item['level']) and $item['level'] > 1)
                    $text = str_repeat("&nbsp; &nbsp; &nbsp;", $item['level'] - 1)."|__".$item['text'];
                else $text = $item['text'];
                $val = $item['id'];
            }else{
                $val =  $key;
                $text =  $item;
            }

            $html .= '<option value="' . $val . '" ' . (($val == $value and $value != null) ? 'selected' : '') . '>' . htmlspecialchars($text) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * SJIS => UTF-8
     * @param $in_charset
     * @param $out_charset
     * @param $arr
     * @return mixed
     */
	public static function convertCode($in_charset, $out_charset, $arr)
	{
		foreach ($arr as $k => &$v) {
			if (is_array($v)) {
				foreach ($v as $kk => &$vv) {
					$vv = mb_convert_encoding($vv, $out_charset, $in_charset);
				}
			} else {
				$v = mb_convert_encoding($vv, $out_charset, $in_charset);
			}
		}
		return $arr;
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
     * 共通印の申込書番号頭文字取得
     * @return int|string
     */
    public static function getPdfNumberFirst()
    {
        if (config('app.pac_app_env') == 0) {
            $unique = (int)config('app.pac_contract_server') + 1;
        } else {
            $unique = 'A';
        }
        return $unique;
    }

    /**
     * 会社又はユーザ無効時、rememberToken削除
     *
     * @param $id int 会社id又はユーザid
     * @param $type string company:会社；mst_admin:管理者；mst_audit:監査用アカウント；mst_user:ユーザ
     * @throws Exception
     */
    public static function rememberTokenClean($id, $type)
    {
        // 会社無効
        if ($type == 'company') {
            // 管理者
            DB::table('mst_admin')->where('mst_company_id', $id)
                ->where('remember_token', '!=', '')
                ->update(['remember_token' => '']);
            // 監査用アカウント
            DB::table('mst_audit')->where('mst_company_id', $id)
                ->where('remember_token', '!=', '')
                ->update(['remember_token' => '']);
            // ユーザ
            DB::table('mst_user')->where('mst_company_id', $id)
                ->where('remember_token', '!=', '')
                ->update(['remember_token' => '']);
        } else {
            // 管理者,監査用アカウント,ユーザ無効
            DB::table($type)->where('id', $id)
                ->where('remember_token', '!=', '')
                ->update(['remember_token' => '']);
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

    public static function buildSelectNonDefault($items = [], $name ='', $value = '', $option = []){
        $option_text = '';
        foreach($option as $key => $val){ $option_text .= " $key=\"$val\""; }
        $id = isset($option['id'])?$option['id']:trim(preg_replace('/[^\d\w]/ism', '_', $name), '_');

        $html =  '<select name="'.$name.'" '.$option_text.' id="'.$id.'">';

        foreach($items as $key => $item){
            if(is_array($item)){
                if(isset($item['level']) AND $item['level'] > 1)
                    $text = str_repeat("&nbsp; &nbsp; &nbsp;", $item['level'] - 1)."|__".$item['text'];
                else $text = $item['text'];
                $val = $item['id'];
            }else{
                $val =  $key;
                $text =  $item;
            }

            $html .= '<option value="'.$val.'" '.(($val == $value AND $value != null) ?'selected':'').'>'.htmlspecialchars($text).'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public static function buildDepartmentSelect($items = [], $name ='', $value = '', $default_text = null, $option = []){
        $mapItems = [];
        foreach($items as $item){
            $mapItems[$item['id']] = $item;
        }

        $option_text = '';
        foreach($option as $key => $val){ $option_text .= " $key=\"$val\""; }
        $id = isset($option['id'])?$option['id']:trim(preg_replace('/[^\d\w]/ism', '_', $name), '_');

        $html =  '<select name="'.$name.'" '.$option_text.' id="'.$id.'">';
        if($default_text !== null)
            $html .= '<option value="">'.$default_text.'</option>';
        foreach($items as $key => $item){
            if(is_array($item)){
                if(isset($item['level']) AND $item['level'] > 1){
                    $parentId = $item['parent_id'];
                    $text = $item['text'];
                    while ($parentId){
                        if (key_exists($parentId, $mapItems)){
                            $parentItem = $mapItems[$parentId];
                            $text = ($parentItem['text'].'＞'.$text);
                            $parentId = $parentItem['parent_id'];
                        }else{
                            break;
                        }
                    }
                }else {
                    $text = $item['text'];
                }
                $val = $item['id'];
            }else{
                $val =  $key;
                $text =  $item;
            }

            $html .= '<option value="'.$val.'" '.(($val == $value AND $value != null) ?'selected':'').'>'.htmlspecialchars($text).'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * 特殊記号は'<','>'を置換
     * @param $name
     * @return array|string|string[]
     */
    public static function replaceCharacter($name)
    {
        $name = str_replace('&lt;', '<', $name);
        return str_replace('&gt;', '>', $name);
    }

    public static function getReceivePlanUrl($mst_company_id){
        $api_host = rtrim(config('app.receive_plan_api_host'), "/");
        $access_code =  config('app.receive_plan_access_code');//"2ZDJfd0897dkd34thio49gj4";
        $client = new Client(['base_uri' => $api_host, 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'multipart/form-data', 'X-Requested-With' => 'XMLHttpRequest']
        ]);
        $appServer = 'app'.((int)config('app.pac_contract_server')+1);
        $param = [
            'accessCode'=>$access_code,
            'domainid'=> $mst_company_id,
            'appServer' => $appServer,
        ];
        Log::info("GET 受信専用プラン URL");
        $result = $client->post('/data/get_received_entry_url',[
            RequestOptions::JSON=>$param
        ]);
        if($result->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
            $response = json_decode((string) $result->getBody());
            if ($response->result_code){
                return $response->result_data->URL;
            }else{
                Log::warning("get 受信専用プラン url failed. Response Body ".$response->result_message);
                return "";
            }
        }else{
            Log::warning("get 受信専用プラン url failed. Response Body ".$result->getBody());
            return "";
        }
    }
}
