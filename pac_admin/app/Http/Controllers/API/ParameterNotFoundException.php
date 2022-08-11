<?php

namespace App\Http\Controllers\API;

class ParameterNotFoundException extends PacException {

    public $key = null;
    public $parameter_name = "";

    public function __construct(string $parameter_name, $message="", $code=0, $previous=null)
    {
        parent::__construct($message, $code, $previous);
        $this->parameter_name = $parameter_name;
    }


    public function getDescribe() : string {
        return __("api.fail.request_parameter_is_missing",["parameter_name"=>$this->parameter_name]);
    }

    public function getExceptionCodeName(){
        return "PNE";
    }
}
