<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Session;

class LogOperation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = null;
        try{
            $needLogOperation = config('logging.enable_log_operation');

            if($needLogOperation){
                $user       = \Auth::user();
                $logs_info = \App\Http\Utils\OperationsHistoryUtils::LOG_INFO;

                $action = $request->route()->getAction();
                $pathInfo = $request->getPathInfo();
                if(!isset($action['controller'])) return $next($request);

                $controller = class_basename($action['controller']);
                $controllers = explode('@', $controller);
                if(count($controllers) == 2) list($controller, $action) = $controllers;
                else $action = "index";
                $controller = \str_replace('Controller',"", $controller);

                $log_info = [
                    'auth_flg' => \App\Http\Utils\OperationsHistoryUtils::HISTORY_FLG_ADMIN,
                    'user_id' => $user?$user->id:0,
                    'mst_display_id' => "",
                    'mst_operation_id' => "",
                    'result' => 1,
                    'detail_info' => "",
//                    'ip_address' => $request->server->get('REMOTE_ADDR'),
                    'ip_address' => $request->getClientIp(),
                    'create_at' => date("Y-m-d H:i:s"),
                ];

                // log operation logout
                if($controller == 'Login' AND $action == 'logout'){
                    $info = $logs_info[$controller][$action];
                    $log_info['mst_display_id']     = $info[0];
                    $log_info['mst_operation_id']   = $info[1];
                    $log_info['detail_info']        = $info[2];
                    $log_info['result']             = 0;

                    Log::channel('logstash')->info(implode(" ", $log_info));
                    $needLogOperation = false;
                }
            }

            // main action
            $response = $next($request);

            $content = '';
            if($needLogOperation){
                $contentType = $response->headers->get('content-type');
                if(\strpos($contentType, "json")){
                    $content = $response->getContent();
                    $content = \json_decode($content);
                }

                if(!$log_info['user_id']){
                    $user = \Auth::user();
                    if($user) $log_info['user_id'] = $user->id;
                }

                if(isset($logs_info[$controller])){
                    $info = Session::has('log_info')?Session::get('log_info'):null;
                    if(!$info){
                        // find info for action and controller
                        $info = null;
                        // Spescial action
                        if($controller == "OperationHistory"){
                            $type = \strtolower($request->route('type')); // admin, user, api
                            $method = \strtolower($request->method());  // get, post
                            if(isset($logs_info[$controller][$type][$method])){
                                $info = $logs_info[$controller][$type][$method];
                            }
                        }else if($controller == "User" OR $controller == 'CommonAddress'){ // index, search
                            $action2 = \strtolower($request->get('action'));
                            $userActions=['destroy','deletes'];
                            if(in_array($action,$userActions)){
                                return $response;
                            }
                            if(isset($logs_info[$controller][$action2])){
                                $info = $logs_info[$controller][$action2];
                            }
                        }else if($controller == "DepartmentTitle"){ // DepartmentTitle: department/position
                            $type = \strtolower($request->get('type'));
                            if(isset($logs_info[$controller][$action][$type])){
                                $info = $logs_info[$controller][$action][$type];
                            }
                        }else if($controller == "UserAssignStamp" || $controller == "CircularsLongTerm"){ // index or search
                            $action2 = \strtolower($request->get('action'));
                            if(isset($logs_info[$controller][$action2])){
                                $info = $logs_info[$controller][$action2];
                            }
                        }else if($controller == "Assignstamps"){
                            if($action == 'store')
                                $stamp_flg     = $request->get('stamp_flg');
                            elseif($action == 'delete'){
                                $stamp_flg     = $content->stamp_flg;
                            }
                            if(isset($logs_info[$controller][$action]) AND isset($logs_info[$controller][$action][$stamp_flg])){
                                $info = $logs_info[$controller][$action][$stamp_flg];
                            }
                        } else if ($controller == 'FormIssuance') {
                            $action2 = \strtolower($request->get('action'));
                            if(isset($logs_info[$controller][$action2])){
                                $info = $logs_info[$controller][$action2];
                            }
                        } else if ($controller == 'CircularsDownloadList'){
                            if ($action == 'export'){
                                if (isset($logs_info[$controller][$action])){
                                    $info = $logs_info[$controller][$action];
                                }
                            }
                        } else if ($controller == 'Login'){
                            if ($action == 'showLoginForm' && Auth::check()){
                                if (isset($logs_info[$controller]['recall'])){
                                    $info = $logs_info[$controller]['recall'];
                                }
                            }
                        } else if ($controller == 'Chat') {
                            $action2 = $request->get('action');
                            if(isset($logs_info[$controller][$action2])){
                                $info = $logs_info[$controller][$action2];
                            }
                        } else if ($controller == 'BoxEnabledAutoStorage') {
                            $action2 = $request->get('action');
                            if(isset($logs_info[$controller][$action2])){
                                $info = $logs_info[$controller][$action2];
                            }
                        }
                    }

                    if(!$info AND isset($logs_info[$controller][$action])){
                        $info = $logs_info[$controller][$action];
                    }

                    if(!$info OR !isset($info[0])) return $response;

                    $log_info['mst_display_id']     = $info[0];
                    $log_info['mst_operation_id']   = $info[1];

                    // status
                    if(\strpos($contentType, "json")){
                        $status = isset($content->status)?$content->status:$response->status();
                        if($status === true OR $status == 200){
                            $log_info['detail_info'] = $info[2];
                            $log_info['result'] = 0;
                        }else{
                            $log_info['detail_info'] = $info[3];
                            $log_info['result'] = 1;
                        }
                    }else{
                        $statusCode = $response->status();
                        if($statusCode == 200 OR $statusCode == 201){
                            $log_info['detail_info'] = $info[2];
                            $log_info['result'] = 0;
                        }else{
                            $log_info['detail_info'] = $info[3];
                            $log_info['result'] = 1;
                            if ($controller == 'Login' && $action == 'showLoginForm' && Auth::check()){
                                $log_info['detail_info'] = $info[2];
                                $log_info['result'] = 0;
                            }
                        }
                    }

                    // Spescial action
                    if($controller == 'Assignstamps' AND $log_info['result'] == 0){
                        $stamp_flg   = $request->get('stamp_flg');
                        $stamps      = $request->get('stamps');

                        $detail_info = "メールアドレス：{$content->userAssign->email}、氏名：{$content->userAssign->name}";
                        if($action == 'store'){
                            if($stamp_flg == 0){
                                foreach($content->stampAssign as $stamp){
                                    $log_info['detail_info'] = "{$detail_info}、印面ID：{$stamp->id}、印面種類：".($stamp->stamp_division == 0?'氏名印':'日付印');
                                    Log::channel('logstash')->info(implode(" ", $log_info));
                                }
                                $needLogOperation = false;
                            }else if($stamp_flg == 1){
                                $detail_info .= "、印面ID：".$stamps[0];
                            }else if($stamp_flg == 2){
                                $detail_info .= "、印面ID：".$stamps[0];
                                $stamp = $content->stampAssign[0];
                                $detail_info .= "、レイアウト：".\App\Http\Utils\AppUtils::STAMP_TYPE[$stamp->pribt_type]."、"
                                            ."上段：".$stamp->face_up1.$stamp->face_up2."、"
                                            ."下段：".$stamp->face_down1.$stamp->face_down2."、"
                                            ."色：".\App\Http\Utils\AppUtils::STAMP_COLOR[$stamp->color]."、"
                                            ."書体：".\App\Http\Utils\AppUtils::STAMP_FONT_LABEL[$stamp->font];
                            }
                        }else{
                            $detail_info .= "、印面ID：".$request->route('id');
                        }

                        if($needLogOperation){
                            $log_info['detail_info'] = $detail_info;
                            Log::channel('logstash')->info(implode(" ", $log_info));
                            $needLogOperation = false;
                        }
                    } elseif ($controller == 'Mfa' AND $action == 'resend') {
                        $statusCode = $response->status();
                        if($statusCode == 200 OR $statusCode == 302){
                            $log_info['detail_info'] = $info[2];
                            $log_info['result'] = 0;
                        }else{
                            $log_info['detail_info'] = $info[3];
                            $log_info['result'] = 1;
                        }
                    } elseif ($controller == 'Mfa' AND $action == 'verify'){
                        if(!session('message')){
                            $log_info['detail_info'] = $info[2];
                            $log_info['result'] = 0;
                        }else{
                            $log_info['detail_info'] = $info[3];
                            $log_info['result'] = 1;
                        }
                    } elseif ($controller == 'DepartmentTitle' AND $action == 'addDepartmentDownload') {
                        $statusCode = $response->status();
                        if($statusCode == 200 OR $statusCode == 302){
                            $log_info['detail_info'] = $info[2];
                            $log_info['result'] = 0;
                        }else{
                            $log_info['detail_info'] = $info[3];
                            $log_info['result'] = 1;
                        }
                    }

                    if($needLogOperation){
                        // process text, bind value
                        if(isset($info[4]) AND count($info[4])){
                            foreach($info[4] as $indexField => $field){
                                $fields = \explode(":",$field);
                                $value = $this->getValueField($field, $request, $content);
                                if(\is_array($value)) $value = implode('、', $value);
                                if(count($fields) >=2)
                                    $log_info['detail_info'] = \str_replace(":".$fields[1],$value, $log_info['detail_info']);
                                else $log_info['detail_info'] = \str_replace(":".$field,$value, $log_info['detail_info']);
                            }
                        }

                        // log multi times
                        if(isset($info[5]) AND count($info[5])){
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
                                Log::channel('logstash')->info(implode(" ", $log_info));
                                $needLogOperation = false;
                            }
                        }

                        if($needLogOperation){
                            Log::channel('logstash')->info(implode(" ", $log_info));
                            $needLogOperation = false;
                        }
                    }
                }
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
            return $list[$request->input($fields[1])];
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
}
