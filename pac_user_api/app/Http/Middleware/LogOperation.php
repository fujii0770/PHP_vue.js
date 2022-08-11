<?php

namespace App\Http\Middleware;

use App\Http\Utils\StatusCodeUtils;
use Closure;
use Illuminate\Support\Facades\Log;
use Session;
use App\Http\Utils\OperationsHistoryUtils;

class LogOperation
{
    const LOG_DESTINATION_WHEN_NOT_PUBLIC = OperationsHistoryUtils::DESTINATION_LOGSTASH;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $needLogOperation = config('logging.enable_log_operation');

        if (!$needLogOperation) {
            return $next($request);
        }

        $response = null;
        try{
            $action = $request->route()->getAction();
            if(!isset($action['controller'])){
                return $next($request);
            }

            $controller = class_basename($action['controller']);
            $controllers = explode('@', $controller);
            if(count($controllers) == 2) {
                list($controller, $action) = $controllers;
            } else {
                $action = "index";
            }
            $controller = \str_replace('Controller',"", $controller);

            $logs_info = OperationsHistoryUtils::LOG_INFO;
            if(!isset($logs_info[$controller])) {
                return $next($request);
            }
            if($controller=='LongTermDocumentApi' && $action=='setIndex'){
                return $next($request);
            }
            $log_info = [
                'auth_flg' => OperationsHistoryUtils::HISTORY_FLG_USER,
                'mst_display_id' => "",
                'mst_operation_id' => "",
                'result' => 1,
                'detail_info' => "",
//              'ip_address' => $request->server->get('REMOTE_ADDR'),
                'ip_address' => $request->server->get('HTTP_X_FORWARDED_FOR') ? $request->server->get('HTTP_X_FORWARDED_FOR'):$request->getClientIp(),
                'create_at' => date("Y-m-d H:i:s"),
            ];

            $user = \Auth::user();
            $login_user_id = $user->id ?? 0;

            // log operation logout
            if($controller == 'Auth' AND $action == 'logout'){
                if($request->has('withbox_flg') && $request->get('withbox_flg') == 1 ){
                    $type = 'withBox';
                }else{
                    $type = 'common';
                }
                $info = $logs_info[$controller][$action][$type];
                $log_info['mst_display_id']     = $info[0];
                $log_info['mst_operation_id']   = $info[1];
                $log_info['detail_info']        = $info[2];
                $log_info['result']             = 0;
                OperationsHistoryUtils::storeRecordsToCurrentEnv([$log_info], $login_user_id, self::LOG_DESTINATION_WHEN_NOT_PUBLIC);

                return $next($request);
            }

            // main action
            $response = $next($request);
            if (method_exists($response,'status')){
                $statusCode = $response->status();
            }else{
                $statusCode = $response->getStatusCode();
            }

            $contentType = $response->headers->get('content-type');
            if(\strpos($contentType, "json")){
                $content = $response->getContent();
                $content = \json_decode($content);
                $status = isset($content->success)?$content->success:(isset($content->status)?$content->status: null);
                if( $status != null AND ($status === true OR $status == StatusCodeUtils::HTTP_OK) ){
                    $log_info['result'] = 0;
                }else{
                    $log_info['result'] = 1;
                }
            }else{
                $content = $response->getContent();
                if($statusCode == StatusCodeUtils::HTTP_OK OR $statusCode == StatusCodeUtils::HTTP_CREATED){
                    $log_info['result'] = 0;
                }else{
                    $log_info['result'] = 1;
                }
            }

            $hashUserInfo = $request->attributes->get(CheckHashing::ATTRIBUTES_KEY_USER, null);
            $is_public = $hashUserInfo !== null;
            if ($is_public) {
                $log_not_needed = OperationsHistoryUtils::getPublicLogDestination($hashUserInfo) == OperationsHistoryUtils::DESTINATION_NULL;
            } else {
                // 通常
                if ($login_user_id === 0) {
                    $user = \Auth::user();
                    $login_user_id = $user->id ?? 0;
                }
                // ログインしていない場合不要
                $log_not_needed = $login_user_id === 0;
            }

            if ($log_not_needed) {
                return $response;
            }

            $info = Session::has('log_info')?Session::get('log_info'):null;
            // find info for action and controller
            if(!$info) {
                // Spescial action
                if($controller == 'CircularDocumentAPI' AND $action == 'updateList'){
                    // public でもログ入れる
                    $circular_status = Session::get('circular_status');
                    $infoStamp = Session::get('infoStamp');
                    $infoText = Session::get('infoText');

                    $addStampOperation = OperationsHistoryUtils::SPECIAL_OPERATION['AddStamp'];
                    $operationType = ($is_public || $circular_status != 0) ? 'CirculationDocument' : 'CreateNew';
                    $infoAddStamp = $addStampOperation[$operationType];

                    $log_records = [];
                    if($infoStamp && count($infoStamp)){
                        foreach($infoStamp as $stamp) {
                            if($stamp['stamp_flg'] == 0){
                                $info = $infoAddStamp['Seal_Normal'];
                            }else if($stamp['stamp_flg'] == 1){
                                $info = $infoAddStamp['Seal_Common'];
                            }else continue;
                            $log_info['mst_display_id']     = $info[0];
                            $log_info['mst_operation_id']   = $info[1];
                            $log_info['detail_info']        = $log_info['result']==0?$info[2]:$info[3];
                            $log_records[] = $log_info;
                        }
                    }

                    if($infoText && count($infoText)){
                        foreach($infoText as $stamp){
                            $info = $infoAddStamp['Text'];
                            $log_info['mst_display_id']     = $info[0];
                            $log_info['mst_operation_id']   = $info[1];
                            $log_info['detail_info']        = $log_info['result']==0?$info[2]:$info[3];
                            $log_info['detail_info']  = \str_replace(":text",$stamp['text'], $log_info['detail_info'] );
                            $log_records[] = $log_info;
                        }

                    }

                    if ($is_public) {
                        OperationsHistoryUtils::storeRecordsPublic($log_records, $hashUserInfo, true);
                    } else {
                        OperationsHistoryUtils::storeRecordsToCurrentEnv($log_records, $login_user_id, self::LOG_DESTINATION_WHEN_NOT_PUBLIC);
                    }
                    return $response;
                } else {
                    // special action
                    if ($controller == 'FormIssuanceAPI') {
                        $action2 = $request->get('action');
                        // action: index search
                        if (isset($logs_info[$controller][$action2])) {
                            $info = $logs_info[$controller][$action2];
                        }

                    }
                }

                  // special action HR
                if ($controller == 'HRWorkDetailAPI') {
                    if ($action == 'registerNewTimeCardDetail') {
                        $vacationType = $this->getSessionFlag('onPaid');
                        if (isset($vacationType) && $vacationType) {
                            $action = 'onPaid';
                            $this->forgetSessionFlag('onPaid');
                        } else {
                            $vacationType = $this->getSessionFlag('onHalfPaid');
                            if (isset($vacationType) && $vacationType) {
                                $action = 'onHalfPaid';
                                $this->forgetSessionFlag('onHalfPaid');
                            } else {
                                $vacationType = $this->getSessionFlag('onSpecialHoliday');
                                if (isset($vacationType) && $vacationType) {
                                    $action = 'onSpecialHoliday';
                                    $this->forgetSessionFlag('onSpecialHoliday');
                                } else {
                                    $vacationType = $this->getSessionFlag('onHalfSpecialHoliday');
                                    if (isset($vacationType) && $vacationType) {
                                        $action = 'onHalfSpecialHoliday';
                                        $this->forgetSessionFlag('onHalfSpecialHoliday');
                                    } else {
                                        $vacationType = $this->getSessionFlag('onSubstituteHoliday');
                                        if (isset($vacationType) && $vacationType) {
                                            $action = 'onSubstituteHoliday';
                                            $this->forgetSessionFlag('onSubstituteHoliday');
                                        } else {
                                            $vacationType = $this->getSessionFlag('onHalfSubstituteHoliday');
                                            if (isset($vacationType) && $vacationType) {
                                                $action = 'onHalfSubstituteHoliday';
                                                $this->forgetSessionFlag('onHalfSubstituteHoliday');
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        if ($action == 'store' || $action == 'update') {
                            $action = 'register';
                        }
                    }

                }

                if ($controller == 'MstHrDailyReportAPI') {
                    if ($action == 'index') {
                        $actionSearch = $this->getSessionFlag('search');
                        if (isset($actionSearch) && $actionSearch) {
                            $action = 'search';
                            $this->forgetSessionFlag('search');
                        }
                    }
                    if ($action == 'update' || $action == 'store') {
                        $action = 'register';
                    }

                }
                if ($controller == 'WorkListAPI' && $action == 'index') {
                    $actionSearch = $this->getSessionFlag('search');
                    if (isset($actionSearch) && $actionSearch) {
                        $action = 'search';
                        $this->forgetSessionFlag('search');
                    }
                }

                if($controller == 'CircularAPI' AND $action == 'actionMultiple'){
                    $type = $request->route('action');
                    if(isset($logs_info[$controller][$action][$type])){
                        $info = $logs_info[$controller][$action][$type];
                    }
                }

                if($controller == 'Auth'){
                    if($request->has('withbox_flg') && $request->get('withbox_flg') == 1 ){
                        $type = 'withBox';
                    }else{
                        $type = 'common';
                    }
                    if(isset($logs_info[$controller][$action][$type])){
                        $info = $logs_info[$controller][$action][$type];
                    }
                }

                if(!$info AND isset($logs_info[$controller][$action])){
                    $info = $logs_info[$controller][$action];
                }
            }

            if(!$info OR !isset($info[0])) return $response;

            if ($is_public) {
                $is_acceptable = in_array("$controller@$action", OperationsHistoryUtils::ACCEPTABLE_LOG_INFO_WHEN_PUBLIC, true);
                if (!$is_acceptable) {
                    // public 時のログ対象外
                    return $response;
                }
            }

            $log_info['mst_display_id']     = $info[0];
            $log_info['mst_operation_id']   = $info[1];

            $log_info['detail_info'] = $log_info['result']==0?$info[2]:$info[3];

            // process text, bind value
            if(isset($info[4]) AND count($info[4])){
                foreach($info[4] as $indexField => $field){
                    $fields = \explode(":",$field);
                    $value = $this->getValueField($field, $request, $content);
                    // Log::debug('-----------'.print_r($value ,true));
                    if(\is_array($value)) {
                        $new_arr = [];
                        array_walk_recursive($value, function($v) use(&$new_arr){ $new_arr[] = $v; });
                        $value = implode('、', $new_arr);
                    }
                    if(count($fields) >= 2) {
                        $log_info['detail_info'] = \str_replace(":".$fields[1],$value, $log_info['detail_info']);
                    } else {
                        $log_info['detail_info'] = \str_replace(":" . $field, $value, $log_info['detail_info']);
                    }
                }
            }

            $log_records = [];
            if(isset($info[5]) AND count($info[5])){
                // log multi times
                $fieldValue = [];
                foreach($info[5] as $indexField => $field){
                    $fieldValue[$indexField] = $this->getValueField($field, $request, $content);
                    $fieldValue[$indexField] = $fieldValue[$indexField]?$fieldValue[$indexField]:[];
                }

                $_detail_info = $log_info['detail_info'];
                foreach($fieldValue[0] as $indexValue => $v){
                    $detail_info = $_detail_info;
                    foreach($info[5] as $indexField => $field){
                        $fields = \explode(":",$field);
                        $value = $fieldValue[$indexField][$indexValue];
                        $fieldName = $field;
                        if(count($fields) >=2) $fieldName = $fields[1];
                        if(\is_array($value)) $value = implode('、', $value);
                        $detail_info = \str_replace(":$fieldName",$value, $detail_info);
                    }
                    $log_info['detail_info'] = $detail_info;
                    $log_records[] = $log_info;
                }
            } else {
                $log_records[] = $log_info;
            }

            if ($is_public) {
                OperationsHistoryUtils::storeRecordsPublic($log_records, $hashUserInfo, true);
            } else {
                OperationsHistoryUtils::storeRecordsToCurrentEnv($log_records, $login_user_id, self::LOG_DESTINATION_WHEN_NOT_PUBLIC);
            }
        }catch (\Exception $ex) {
            Log::warning($ex->getMessage().$ex->getTraceAsString());
        }
        return $response;
    }


    function getValueField($field, $request, $content){
            $fields = \explode(":",$field);
            if($fields[0] == 'enum'){
                $list = ['使用不可','使用可'];
                if(count($fields) == 3){ // enum:name:values
                    $list = \explode(",", $fields[2]);
                    $listNew = [];
                    foreach($list as $indexList => $val){
                        $vals = \explode("|", $val);
                        if(count($vals) == 2)               // enum:name:id1|value1,id2|value2
                            $listNew[$vals[0]] = $vals[1];
                        else $listNew[$indexList] = $val;   // enum:name:value1,value2
                    }
                    $list = $listNew;
                }
                return isset($list[$request->input($fields[1])])?$list[$request->input($fields[1])]:'';
            }else if($fields[0] == 'file'){ // file:field_name
                $fileName = "";
                if($request->file($fields[1])){
                    $file = $request->file($fields[1]);
                    $fileName = $file->getClientOriginalName();
                }
                return $fileName;
            }else if($fields[0] == 'res'){  // res:path_to_name
                $fields_sub = \explode('.',$fields[1]);
                if(\is_object($content)){
                    $content_field = $content;
                    if(count($fields_sub)){
                        foreach($fields_sub as $fs){     // res.data.fileName
                            $content_field = isset($content_field->{$fs})?$content_field->{$fs}:"";
                        }
                    }
                    return $content_field;
                }
                return "";
            }
            else if($fields[0] == 'sess'){  // sess:name
                return Session::get($fields[1]);
            }
            else if($fields[0] == 'route'){  // route:name
                return $request->route($fields[1]);
            }
            else{ // path_to_name
                return $request->input($field); // data.fileName
            }
    }

    function getSessionFlag ($flagName) {
        return Session::get($flagName);
    }
    function forgetSessionFlag ($flagNameArray) {
        Session::forget($flagNameArray);
    }
}
