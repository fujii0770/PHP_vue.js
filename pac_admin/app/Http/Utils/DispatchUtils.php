<?php namespace App\Http\Utils;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
/**
 * Created by PhpStorm.
 * User: wangc
 * Date: 3/5/2021
 * Time: 12:12 PM
 */

class DispatchUtils
{
    // dispatch_code 区分
    const CODE_KBN_HOLIDAY = 1;         // 休日 
    const CODE_KBN_WELFARE = 2;         //福利厚生
    const CODE_KBN_FRACTION = 3;        // 端数処理
    const CODE_KBN_STATUS = 4;          // ステータス
    const CODE_KBN_INTRO = 5;           // 紹介派遣有無
    const CODE_KBN_PERIOD = 6;          // 期間の定め有無
    const CODE_KBN_UPDATE = 7;          // 契約更新の有無
    const CODE_KBN_WEEK = 8;            // 出勤曜日
    const CODE_KBN_MAX_WORK_M = 9;      // 月最大時間外労働
    const CODE_KBN_MAX_WORK_W = 10;     // 最大週時間外労働
    const CODE_KBN_MAX_WEEK = 11;       // 最大週勤務日数
    const CODE_KBN_DEADLINE = 12;       // 締日
    const CODE_KBN_ROUND = 13;          // 時間丸め単位
    const CODE_KBN_TIMEFLAT = 14;       // 時間定額制
    const CODE_KBN_YESNO = 15;          // 有無
    const CODE_KBN_BUSINESS = 16;       // 業務内容
    const CODE_KBN_INDEFINITE = 17;     // 無期雇用労働者または60歳以上ものに限定するか否かの別
    const CODE_KBN_INDEFINITE_R = 18;   // 無期雇用派遣労働者－理由
    const CODE_KBN_INDEFINITE_D = 19;   // 無期雇用派遣労働者－詳細
    const CODE_KBN_GENDER = 20;         // 性別
    const CODE_KBN_DEFAULTCHAR = 21;    // デフォルト文言
    const CODE_KBN_REGISTKBN = 22;      // 登録区分
    const CODE_KBN_CONTACT= 23;         // 希望連絡方法
    const CODE_KBN_EMPLOYMENT = 24;     // 現在の就業状況
    const CODE_KBN_EMPLOYMENT_KBN= 25;  // 就業状況区分
    const CODE_KBN_ATTENDANCE= 26;      // 受講済未実施区分
    const CODE_KBN_FINISHED= 27;        // 未済区分
    const CODE_KBN_WORKLOCATION= 28;    // 勤務地
    const CODE_KBN_EMPLOYMENTFORM= 29;  // 就業形態
    const CODE_KBN_DESIREDAMOUNT= 30;   // 希望金額
    const CODE_KBN_DESIREDJOB= 31;      // 希望職種
    const CODE_KBN_YEARSOFEXPERIENCE=32;// 経験年数
    const CODE_KBN_EXPERIENCEDJOB= 33;  // 経験職種
    const CODE_KBN_5STAGES= 34;         // 5段階
    const CODE_KBN_ABCSTAGES= 35;       // ABC段階評価
    const CODE_KBN_BASICMANNER= 36;     // 基本態度
    const CODE_KBN_WORKMANNER= 37;      // 勤務態度
    const CODE_KBN_TEAMWORK= 38;        // チームワーク　協調性
    const CODE_KBN_COMMUNICATION= 39;    // コミュニケーション　異文化適応力
    const CODE_KBN_COOPERATION= 40;     // 組織運営への協力度　参画度、理解度
    public static function showSortColumn($title = "title", $name = "name", $isPrioity = false, $addid = "")
    {

        $priority = "";
        if ($isPrioity) $priority = "data-tablesaw-priority='persist'";
        ob_start();
        $ordername = "'".$name."'";
        if ($addid) $addid = ", '".$addid."'";
        echo '<th scope="col" class="sort-column '.$name.' " ng-click="changeSort('.$ordername.''.$addid.')" data-tablesaw-priority="persist" >';
            echo $title;
        echo '<i class="icon fas fa-sort"></i>';
        echo '<i class="icon icon-active icon-down fas fa-caret-down"></i>';
        echo '<i class="icon icon-active icon-up fas fa-caret-up"></i>';
    
        echo '</th>';

        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    public static function showOpenCloseIcon ($title = "title", $name = "name", $iconshow = false){
        ob_start();
        $click = "'".$name."'";
        echo '<div class="card-header">';
        echo '  <div style="float:left">';
        echo $title;
        echo '  </div>';
        if ($iconshow) {
            echo '  <div class="text-right">';
            echo '      <div class="btn btn-success" ng-click="ShowDetail('.$click.')"><i id="icon'.$name.'" class="fas fa-caret-square-up" ></i></div>';
            echo '  </div>';    
        }
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
 
    public static function showDetailLabel($title = "title", $option = [], $itemno="")
    {
        $option = $option?:[];
        ob_start();
        $ifsetting = "";
        if ($itemno){
            $ifsetting = ' ng-if="dispsetting.set_'.$itemno.'" ';
        }
        echo '<div class="form-group" '.$ifsetting.'>';
        echo '  <div class="row">';
        echo self::setTitleLabel($title, [], $itemno);
        echo self::showLabel([], $option);
        echo '  </div>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    public static function showDetailInputText($type="text", $title = "title", $name="name", $required = false, $option = [], $itemno="")
    {
        $option = $option?:[];
        ob_start();
        $ifsetting = "";
        if ($itemno){
            $ifsetting = ' ng-if="dispsetting.set_'.$itemno.'" ';
        }
        echo '<div class="form-group" '.$ifsetting.'>';
        echo '  <div class="row">';
        echo self::setTitleLabel($title, ['required'=>$required], $itemno);
        echo self::showInputText($type, $name, ['required'=>$required], $option);
        echo '  </div>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
    public static function showDetailTextArea($title = "title", $required = false, $option = [], $itemno="")
    {
        $option = $option?:[];
        ob_start();
        $ifsetting = "";
        if ($itemno){
            $ifsetting = ' ng-if="dispsetting.set_'.$itemno.'" ';
        }
        echo '<div class="form-group" '.$ifsetting.'>';
        echo '  <div class="row">';
        echo self::setTitleLabel($title, ['required'=>$required], $itemno);
        echo self::showTextArea(['required'=>$required], $option);
        echo '  </div>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
    public static function showDetailSelect($title = "title", $required = false, $items, $option = [] , $itemno="")
    {
        $option = $option?:[];
        ob_start();
        $ifsetting = "";
        if ($itemno){
            $ifsetting = ' ng-if="dispsetting.set_'.$itemno.'" ';
        }
        echo '<div class="form-group" '.$ifsetting.'>';
        echo '  <div class="row">';
        echo self::setTitleLabel($title, ['required'=>$required], $itemno);
        echo self::showSelect([],$items, $option);
        echo '  </div>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    public static function showDetailDate($title = "title", $required = false, $option = [] , $itemno="")
    {
        $option = $option?:[];
        ob_start();
        $ifsetting = "";
        if ($itemno){
            $ifsetting = ' ng-if="dispsetting.set_'.$itemno.'" ';
        }
        echo '<div class="form-group" '.$ifsetting.'>';
        echo '  <div class="row">';
        echo self::setTitleLabel($title, ['required'=>$required], $itemno);
        echo self::showDate(['required'=>$required], $option);
        echo '  </div>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    public static function showDetailDateFromTo($title = "title", $required = false, $optionF = [],$optionT = [], $itemno="" )
    {
        $optionF = $optionF?:[];
        $optionT = $optionT?:[];
        ob_start();
        $ifsetting = "";
        if ($itemno){
            $ifsetting = ' ng-if="dispsetting.set_'.$itemno.'" ';
        }
        echo '<div class="form-group" '.$ifsetting.'>';
        echo '  <div class="row">';
        echo self::setTitleLabel($title, ['required'=>$required], $itemno);
        echo self::showDateFromTo(['required'=>$required], $optionF, $optionT);
        echo '  </div>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
    public static function showDetailMonthFromTo($title = "title", $required = false, $optionF = [],$optionT = [], $itemno="" )
    {
        $optionF = $optionF?:[];
        $optionT = $optionT?:[];
        ob_start();
        $ifsetting = "";
        if ($itemno){
            $ifsetting = ' ng-if="dispsetting.set_'.$itemno.'" ';
        }
        echo '<div class="form-group" '.$ifsetting.'>';
        echo '  <div class="row">';
        echo self::setTitleLabel($title, ['required'=>$required], $itemno);
        echo self::showMonthFromTo(['required'=>$required], $optionF, $optionT);
        echo '  </div>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
    public static function showDetailRadio($title = "title", $name = "", $required = false, $items, $option = [], $itemno="" )
    {
        $option = $option?:[];
        ob_start();
        $ifsetting = "";
        if ($itemno){
            $ifsetting = ' ng-if="dispsetting.set_'.$itemno.'" ';
        }
        echo '<div class="form-group" '.$ifsetting.'>';
        echo '  <div class="row">';
        echo self::setTitleLabel($title, ['required'=>$required], $itemno);
        echo self::showRadio($name,[],$items, $option);
        echo '  </div>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
    public static function showDetailRadioVertical($title = "title", $name = "", $required = false, $itemslist, $items, $option = [], $itemno="" )
    {
        $option = $option?:[];
        ob_start();
        $ifsetting = "";
        if ($itemno){
            $ifsetting = ' ng-if="dispsetting.set_'.$itemno.'" ';
        }
        echo '<div class="form-group" '.$ifsetting.'>';

        foreach($itemslist as $key => $item){ 
            echo '  <div class="row">';
            if ($key == 0)
            {
                echo self::setTitleLabel($title, ['required'=>$required], $itemno);
            }else{
                echo self::setTitleLabel('', []);
            }
            echo self::setTitleLabel($item['name'], ['cols'=>5, 'textalign'=>'text-left-lg']);
            echo self::showRadioVertical($name, $key, ['cols'=>4],$items, $option);
            echo '  </div>';
        }
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
    public static function showDetailCheckBox($title = "title", $name = "", $required = false, $items, $model, $option = [] , $itemno="")
    {
        $option = $option?:[];
        ob_start();
        $ifsetting = "";
        if ($itemno){
            $ifsetting = ' ng-if="dispsetting.set_'.$itemno.'" ';
        }
        echo '<div class="form-group" '.$ifsetting.'>';
        echo '  <div class="row">';
        echo self::setTitleLabel($title, ['required'=>$required], $itemno);
        echo self::showCheckBox($name, $model,[],$items, $option);
        echo '  </div>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
    public static function showDetailCheckBoxOtherText($title = "title", $name = "name", $textname="textname", $required = false, $items, $model, $option = [], $otherid='otherid', $othertitle='othertitle', $modelc="modelc", $optiont = [], $itemno="" )
    {
        $option = $option?:[];
        $optiont = $optiont?:[];
        ob_start();
        $ifsetting = "";
        if ($itemno){
            $ifsetting = ' ng-if="dispsetting.set_'.$itemno.'" ';
        }
        echo '<div class="form-group" '.$ifsetting.'>';
        echo '  <div class="row">';
        echo self::setTitleLabel($title, ['required'=>$required], $itemno);
        echo self::showCheckBoxOtherText($name, $textname, $model,[],$items, $option, $otherid, $othertitle, $modelc, $optiont);
        echo '  </div>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
    public static function showDetailPostal($title = "title", $name="name", $required = false, $option = [], $itemno=""){
        $option = $option?:[];
        ob_start();
        $ifsetting = "";
        if ($itemno){
            $ifsetting = ' ng-if="dispsetting.set_'.$itemno.'" ';
        }
        echo '<div class="form-group" '.$ifsetting.'>';
        echo '  <div class="row">';
        echo self::setTitleLabel($title, ['required'=>$required], $itemno);
        echo self::showInputText('text', $name, ['required'=>$required, 'cols'=>2], $option);
        echo '<label class="col-md-7 col-sm-7 col-12 label text-left"> 郵便番号(ハイフンあり・なし両方)を入力すると住所が入力されます。</label>';
        echo '  </div>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
    public static function showDetailCheckBoxVerticalCols($title = "title", $name = "", $required = false, $items, $model, $style=[], $option = [] , $class="", $itemno="")
    {
        ob_start();
        $ifsetting = "";
        if ($itemno){
            $ifsetting = ' ng-if="dispsetting.set_'.$itemno.'" ';
        }
        echo '<div class="form-group" '.$ifsetting.'>';
        echo '  <div class="row">';
        echo self::setTitleLabel($title, ['required'=>$required], $itemno);
        echo self::showCheckBoxVertical($name, $model, $style ,$items, $option, $class);
        echo '  </div>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }


    public static function dispOnlyLabel($title="title", $option=[], $itemno=""){
        $option = $option?:[];
        ob_start();
        $ifsetting = "";
        if ($itemno){
            $ifsetting = ' ng-if="dispsetting.set_'.$itemno.'" ';
        }
        echo '<div class="form-group" '.$ifsetting.'>';
        echo '    <div class="row">';
        echo '        <label class="col-md-2 col-sm-2 col-12 text-right-lg">';
        echo $title;
        echo '        </label>';
        echo '        <div class="col-md-8 col-sm-8 col-12">';
        echo '            <p ';
        foreach ($option as $key => $val) {
            echo  " $key=\"$val\"";
        } 
        echo '            ></p>';
        echo '        </div>';
        echo '    </div>';
        echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    public static function showLabel($style=[], $option=[]){

        $option = $option?:[];
        $style = $style?:[];
        $cols =  isset($style['cols'])? $style['cols'] : 8;
        $required =  isset($style['required'])? $style['required'] : false;

        $textalign =  isset($style['textalign'])? $style['textalign'] : 'text-left-lg';
        $input =  '    <div class="col-md-'.$cols.' col-sm-'.$cols.' col-12 '.$textalign.'">';
        $input .= '<p ';
        foreach ($option as $key => $val) {
            $input .= " $key=\"$val\"";
        } 
        $input .=  '></p>';

        $input .= '        </div>';
        return $input;
    }
    public static function showInputText($type="text", $name="name", $style=[], $option=[], $unit=''){

        $option = $option?:[];
        $style = $style?:[];
        $cols =  isset($style['cols'])? $style['cols'] : 8;
        $required =  isset($style['required'])? $style['required'] : false;

        $textalign =  isset($style['textalign'])? $style['textalign'] : 'text-left-lg';
        $input =  '    <div class="col-md-'.$cols.' col-sm-'.$cols.' col-12">';
        $input .= '        <div class="input-group">';
        $input .= '            <input name="'.$name.'" type="'.$type.'" class="form-control '.$textalign.'"  ng-readonly="readonly" ';
        if ($required) {
            $input .= 'required';
        }
        foreach ($option as $key => $val) {
            $input .= " $key=\"$val\"";
        } 
        $input .=  '/>';

        $input .= $unit;
        $input .= '<span class="error '.$name.'-error"></span>';
        $input .= '        </div>';
        $input .= '    </div>';
        return $input;
    }
    public static function showTextArea($style=[], $option=[]){

        $option = $option?:[];
        $style = $style?:[];
        $cols =  isset($style['cols'])? $style['cols'] : 8;
        $required =  isset($style['required'])? $style['required'] : false;
        $input =  '    <div class="col-md-'.$cols.' col-sm-'.$cols.' col-12">';
        $input .= '        <div class="input-group">';
        $input .= '            <textarea type="text" class="form-control "  ng-readonly="readonly"  ';
        if ($required) {
            $input .= 'required';
        }
        foreach ($option as $key => $val) {
            $input .= " $key=\"$val\"";
        } 
        $input .=  '>';
        $input .= '</textarea>';
        $input .= '        </div>';
        $input .= '    </div>';
        return $input;
    }

    public static function showSelect($style=[], $items=[], $option=[]){
        $option = $option?:[];
        $style = $style?:[];
        $cols =  isset($style['cols'])? $style['cols'] : 8;

        $input =  '    <div class="col-md-'.$cols.' col-sm-'.$cols.' col-12">';
        $input .=  '        <div class="input-group">';

        $input .=  '<select class="form-control" ';
        foreach ($option as $key => $val) {
            $input .=  " $key=\"$val\"";
        }         
        $input .=  ' ng-disabled="readonly" >';
        $input .=  '<option></option>';
        foreach($items as $key => $item){        
            $input .=  '<option ng-value="'.$item['id'].'" text="'.$item['name'].'"  ng-readonly="readonly" >';
            $input .=  $item['name'];
            $input .=  '</option>';        
        }
        $input .=  '</select>';
        $input .= '        </div>';
        $input .= '    </div>';
    
        return $input;
    }
    public static function showDate($style=[], $option = [])
    {
        $option = $option?:[];
        $style = $style?:[];
        $required =  isset($style['required'])? $style['required'] : false;
        $cols =  isset($style['cols'])? $style['cols'] : 4;
        $input =  '    <div class="col-md-'.$cols.' col-sm-'.$cols.' col-12">';
        $input .= '        <div class="input-group">';
        $input .= '<input type="date" class="form-control" ';
        if ($required) {
            $input .= 'required';
        }
        foreach ($option as $key => $val) {
            $input .= " $key=\"$val\"";
        }    
        $input .= ' ng-readonly="readonly" >';
        $input .= '        </div>';
        $input .= '    </div>';
        return $input;
    }
    public static function showDateFromTo($style=[], $optionF = [], $optionT = []){
        $optionF = $optionF?:[];
        $optionT = $optionT?:[];
        $style = $style?:[];
        $cols =  isset($style['cols'])? $style['cols'] : 8;
        $required =  isset($style['required'])? $style['required'] : false;
        $input =  '    <div class="col-md-'.$cols.' col-sm-'.$cols.' col-12">';
        $input .= '        <div class="input-group">';
        $input .= '<input type="date" class="form-control" ';
        if ($required) {
            $input .= 'required';
        }
        foreach ($optionF as $key => $val) {
            $input .= " $key=\"$val\"";
        }    
        $input .= ' ng-readonly="readonly" >';
        $input .= '<label for="name" class="col-md-1 control-label">~</label>';
        $input .= '<input type="date" class="form-control" ';
        if ($required) {
            $input .= 'required';
        }
        foreach ($optionT as $key => $val) {
            $input .= " $key=\"$val\"";
        }    
        $input .= ' ng-readonly="readonly" >';
        $input .= '        </div>';
        $input .= '    </div>';
        return $input;
    }

    public static function showMonthFromTo($style=[], $optionF = [], $optionT = []){
        $optionF = $optionF?:[];
        $optionT = $optionT?:[];
        $style = $style?:[];
        $cols =  isset($style['cols'])? $style['cols'] : 8;
        $required =  isset($style['required'])? $style['required'] : false;
        $input =  '    <div class="col-md-'.$cols.' col-sm-'.$cols.' col-12">';
        $input .= '        <div class="input-group">';
        $input .= '<input type="month" class="form-control" ';
        if ($required) {
            $input .= 'required';
        }
        foreach ($optionF as $key => $val) {
            $input .= " $key=\"$val\"";
        }    
        $input .= ' ng-readonly="readonly" >';
        $input .= '<label for="name" class="col-md-1 control-label">~</label>';
        $input .= '<input type="month" class="form-control" ';
        if ($required) {
            $input .= 'required';
        }
        foreach ($optionT as $key => $val) {
            $input .= " $key=\"$val\"";
        }    
        $input .= ' ng-readonly="readonly" >';
        $input .= '        </div>';
        $input .= '    </div>';
        return $input;
    }

    public static function showRadio($name='name', $style=[], $items=[], $option=[]){
        $option = $option?:[];
        $style = $style?:[];
        $cols =  isset($style['cols'])? $style['cols'] : 8;
        $input =  '    <div class="col-md-'.$cols.' col-sm-'.$cols.' col-12">';
        $input .=  '        <div class="input-group">';

        foreach($items as $key => $item){ 
            $for = $name.'_'.$item['id'];    
            $input .=  '<label for="'.$for.'" class="control-label">';
            $input .= '    <input type="radio" id="'.$for.'" ng-value="'.$item['id'].'" ';
            foreach ($option as $key => $val) {
                $input .= " $key=\"$val\"";
            }    
            $input .= '  ng-disabled="readonly" />';
            $input .= $item['name'].'　';
            $input .= '</label>';   
        }
        $input .= '        </div>';
        $input .= '    </div>';
        return $input;
    }
    public static function showRadioVertical($name='name', $index=0, $style=[], $items=[], $option=[]){
        $option = $option?:[];
        $style = $style?:[];
        $cols =  isset($style['cols'])? $style['cols'] : 8;
        $input =  '    <div class="col-md-'.$cols.' col-sm-'.$cols.' col-12">';
        $input .=  '        <div class="input-group">';

        foreach($items as $key => $item){ 
            $radioname = $name.'_'.$index; 
            $for = $radioname.'_'.$item['id'];    
            $input .=  '<label for="'.$for.'" class="control-label">';
            $input .= '    <input type="radio" name="'.$radioname.'" id="'.$for.'" ng-value="'.$item['id'].'"  ng-model="dispatchhr.'.$name.'['.$index.'].checked"';
            foreach ($option as $key => $val) {
                $input .= " $key=\"$val\"";
            }    
            $input .= '  ng-disabled="readonly" />';
            $input .= $item['name'].'　';
            $input .= '</label>';   
        }
        $input .= '        </div>';
        $input .= '    </div>';
        return $input;
    }

    public static function showCheckBoxOnly($name='name', $model='model', $style=[], $value="", $option=[]){
        $option = $option?:[];
        $style = $style?:[];
        $cols =  isset($style['cols'])? $style['cols'] : 8;
        $input =  '    <div class="col-md-'.$cols.' col-sm-'.$cols.' col-12">';
        $input .=  '        <div class="input-group">';

        $input .=  '<label for="'.$name.'" class="control-label">';
        $input .= '    <input type="checkbox" id="'.$name.'" ng-model="'.$model.'.checked"  ng-disabled="readonly" ';
        foreach ($option as $key => $val) {
            $input .= " $key=\"$val\"";
        }    
        $input .= ' />';
        $input .= $value.'　';
        $input .= '</label>';   
        $input .= '        </div>';
        $input .= '    </div>';
        return $input;
    }

    public static function showCheckBox($name='name', $model='model', $style=[], $items=[], $option=[]){
        $option = $option?:[];
        $style = $style?:[];
        $cols =  isset($style['cols'])? $style['cols'] : 8;
        $input =  '    <div class="col-md-'.$cols.' col-sm-'.$cols.' col-12">';
        $input .=  '        <div class="input-group">';

        foreach($items as $key => $item){ 
            $for = $name.'_'.$item['id'];    
            $input .=  '<label for="'.$for.'" class="control-label">';
            $input .= '    <input type="checkbox" id="'.$for.'" ng-model="'.$model.'['.$key.'].checked"  ng-disabled="readonly" ';
            foreach ($option as $key => $val) {
                $input .= " $key=\"$val\"";
            }    
            $input .= ' />';
            $input .= $item['name'].'　';
            $input .= '</label>';   
        }
        $input .= '        </div>';
        $input .= '    </div>';
        return $input;
    }
    public static function showCheckBoxModel($name='name', $style=[], $items=[], $option=[]){
        $option = $option?:[];
        $style = $style?:[];
        $cols =  isset($style['cols'])? $style['cols'] : 8;
        $input =  '    <div class="col-md-'.$cols.' col-sm-'.$cols.' col-12">';
        $input .=  '        <div class="input-group">';

        foreach($items as $key => $item){ 
            $for = $name.'_'.$item['id'];    
            $input .=  '<label for="'.$for.'" class="control-label">';
            $input .= '    <input type="checkbox" id="'.$for.'" ng-model="'.$item["model"].'.checked"  ng-disabled="readonly" ';
            foreach ($option as $key => $val) {
                $input .= " $key=\"$val\"";
            }    
            $input .= ' />';
            $input .= $item['name'].'　';
            $input .= '</label>';   
        }
        $input .= '        </div>';
        $input .= '    </div>';
        return $input;
    }
    public static function showCheckBoxOtherText($name='name', $textname="textname", $model='model', $style=[], $items=[], $option=[], $otherid='otherid', $othertitle='othertitle', $modelc="modelc", $optiont = []){
        $option = $option?:[];
        $optiont = $optiont?:[];
        $style = $style?:[];
        $cols =  isset($style['cols'])? $style['cols'] : 8;
        $input =  '    <div class="col-md-'.$cols.' col-sm-'.$cols.' col-12">';
        $input .=  '        <div class="input-group">';
        $input .=  '        <div class="input-group">';

        foreach($items as $key => $item){ 
            $for = $name.'_'.$item['id'];    
            $input .=  '<label for="'.$for.'" class="control-label">';
            $input .= '    <input type="checkbox" id="'.$for.'" ng-model="'.$model.'['.$key.'].checked" ng-disabled="readonly" ';
            foreach ($option as $key => $val) {
                $input .= " $key=\"$val\"";
            }    
            $input .= ' />';
            $input .= $item['name'].'　';
            $input .= '</label>';   
        }
        $input .= '        </div>';
        $input .= '<label class="control-label" for="';
        $input .= $otherid;
        $input .= '" >';
        $input .= '<input type="checkbox" id="';
        $input .= $otherid;
        $input .= '" ng-model="'.$modelc.'.checked"  ng-disabled="readonly" />';
        $input .= $othertitle;
        $input .= '</label>';
        $input .= self::showInputText('text', $textname, ['required'=>false], $optiont);

        $input .= '        </div>';
        $input .= '    </div>';
        return $input;
    }

    public static function showCheckBoxVertical($name='name', $model='model', $style=[], $items=[], $option=[], $class=""){
        $option = $option?:[];
        $style = $style?:[];
        $cols =  isset($style['cols'])? $style['cols'] : 8;
        $input =  '    <div class="col-md-'.$cols.' col-sm-'.$cols.' col-12">';
        $input .=  '        <div class="input-group">';
        $input .= '<ul class="checkboxlist">';
        foreach($items as $key => $item){ 
            $for = $name.'_'.$item['id']; 
            $input .= '<li ';
            if ($class){
                $input .= ' class="'.$class.'"';
            }
            $input .= '>';
            $input .=  '<label for="'.$for.'" class="control-label lineheight20">';
            $input .= '    <input type="checkbox" id="'.$for.'" ng-model="'.$model.'['.$key.'].checked" ng-disabled="readonly" ';
            foreach ($option as $key => $val) {
                $input .= " $key=\"$val\"";
            }    
            $input .= ' />';
            $input .= $item['name'].'　';
            $input .= '</label>';   
            $input .= '</li>';

        }
        $input .= '        </ul>';
        $input .= '        </div>';
        $input .= '    </div>';
        return $input;
    }
    public static function setTitleLabel($title = "title", $style=[], $itemno=""){
        $style = $style?:[];
        $cols =  isset($style['cols'])? $style['cols'] : 2;
        $textalign =  isset($style['textalign'])? $style['textalign'] : 'text-right-lg';
        $required =  isset($style['required'])? $style['required'] : false;
        $label = '';
        
        if (isset($itemno) && $itemno!==""){
            $disabled = "";
            if ($itemno==0) $disabled = "disabled";
            $label .= '<input type="checkbox" value="'.$itemno.'"  class="cid col-md-1 col-sm-1 col-12" ng-model="settinginfo.set_'.$itemno.'"  ng-true-value="1" ng-false-value="0"  ng-if="setting" '.$disabled.' />';
        }elseif($title == ""){
            $label .= '<label class="cid col-md-1 col-sm-1 col-12" ng-if="setting" ></label>';

        }
        $label .=   '      <label class="col-md-'.$cols.' col-sm-'.$cols.' col-12 '.$textalign.'">';
        $label .=   $title;
        if ($required) {
            $label .=   '        <span style="color: red">*</span>';
        }
        $label .=   '      </label>';
        return $label;
    }
    public static function showTabItem($title='title', $id='id', $disabledClass="", $option=[]){
        ob_start();
        $option = $option?:[];
        if ($disabledClass!="") $disabledClass= ",".$disabledClass;
        $idrep = "'".$id."'";
        echo '<li class="nav-item" ng-click="onShowTab('.$idrep.')">';
        echo '  <a class="nav-link "';
        echo '  ng-class="{active: showTab =='.$idrep.' '.$disabledClass.' }"';
        echo '  data-toggle="tab" href="#'.$id.'" id="link_'.$id.'">';
        echo $title;
        echo '  </a>';

        echo '</li>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;

    }
    public static function showDispNumber($option = [], $isdiv = true)
    {
        $option = $option?:[];
        ob_start();
        if ($isdiv) echo '<div class="col-12 col-md-6 col-xl-12 mb-3 text-right">';
        echo '<label class="d-flex" style="float:left" ><span style="line-height: 27px">表示件数：</span>';
        echo '    <select class="custom-select custom-select-sm form-control form-control-sm" style="width: 100px"';
        foreach ($option as $key => $val) {
            echo " $key=\"$val\"";
        }    
        echo '           >';
        echo '    </select>';
        echo '</label>';
        if ($isdiv) echo '</div>';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
    public static function showPaginate($paginate, $clickevent){
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
    public static function getCode($codeall, $kbn, $where=[]){
        $code = $codeall->filter(function ($value) use ($kbn) {
            return $value['kbn'] == $kbn;
        });
        if ($where) {
            $code = $code->filter(function ($item) use ($where) {
                foreach ($where as $key => $value) {
                    switch($key){
                        case 'codefrom':
                            if ($item['code'] < $value) return false;
                            break;
                        case 'codeto':
                            if ($item['code'] > $value) return false;
                            break;
                        }
                }    
                return true;
            });    
        }
        $codertn = new Collection();
        foreach ($code as $key => $value){
            $codertn->add($value);
        }
        return $codertn->sortBy('order');
    }
}

