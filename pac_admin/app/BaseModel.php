<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class BaseModel extends Model
{ 
    var $logRetry       = false;
    var $logCreate      = false;
    var $logUpdate      = false;
    var $logDelete      = false;

    public static function boot()
    {
        parent::boot();

        self::retrieved(function($model){           
            if($model->logRetry){
                $model->operationLogging(['action' => 'retrieved']);
            }
        });

        self::creating(function($model){ });

        self::created(function($model){
            if($model->logCreate){
                $info = [];
                foreach($model->original as $field => $data){
                    if(isset($model->logIncludes[$field]))
                        $info[$model->logIncludes[$field]] = [$model->original[$field]];
                    else if(in_array($field, $model->logIncludes)){
                        $info[$field] = [$model->original[$field]];
                    }
                }
                $model->operationLogging(['action' => 'created', 'info' => $info]);
            }
        });

        self::updating(function($model){ });

        self::updated(function($model){
            if($model->logUpdate){
                $info = [];
                foreach($model->getDirty() as $field => $data){
                    if($textField = $model->isFieldLog()){

                    }

                    if(isset($model->logIncludes[$field]))
                        $info[$model->logIncludes[$field]] = [$model->original[$field], $data];
                    else if(in_array($field, $model->logIncludes)){
                        $info[$field] = [$model->original[$field], $data];
                    }
                }
                $model->operationLogging(['action' => 'updated','info' => $info]);
            }
        });

        self::deleting(function($model){ });

        self::deleted(function($model){
            if($model->logDelete){
                $info = [];
                foreach($model->original as $field => $data){
                    if(isset($model->logIncludes[$field]))
                        $info[$model->logIncludes[$field]] = [$model->original[$field]];
                    else if(in_array($field, $model->logIncludes)){
                        $info[$field] = [$model->original[$field]];
                    }
                }
                $model->operationLogging(['action' => 'deleted', 'info' => $info]);
            }
        });

        self::saving(function($model){
          
        });

        self::saved(function($model){
             
        });
    }

    /** 
     * Input: [mst_operation_id, info, action, tableName]
    */
    public function operationLogging($options = []){
        $user = \Auth::user();
       
        $info = [            
            'user_id' => $user->id,
            'mst_display_id' => app('request')->header('screen'),
            'mst_operation_id' => isset($options['mst_operation_id'])?isset($options['mst_operation_id']):"",
            'result' => "",
            'detail_info' => isset($options['info'])?"\"".\json_encode($options['info'])."\"":"",
            'ip_address' => app('request')->server->get('REMOTE_ADDR'),
        ];

        if(!isset($options['mst_operation_id']) AND isset($options['action'])){
            $tableName = isset($options['tableName']) ?$options['tableName']:$this->table;

            if(isset(\App\Http\Utils\OperationsHistoryUtils::OPERATION_ID[$tableName])){
                $operations = \App\Http\Utils\OperationsHistoryUtils::OPERATION_ID[$tableName];
                if(isset($operations[$options['action']])){
                    $info['mst_operation_id'] = $operations[$options['action']];
                }
            }
        }        
        $info = implode(" ", $info);
        Log::channel('logstash')->info($info);
    }

    public function isFieldLog($fieldName){
        if(isset($this->logIncludes[$fieldName]))
            return $this->logIncludes[$fieldName];
        else if(in_array($fieldName, $this->logIncludes)){
            return $fieldName;
        }
        return false;
    }
}
