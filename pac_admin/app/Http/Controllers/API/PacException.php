<?php

namespace App\Http\Controllers\API;

class PacException extends \Exception {

    public function getDescribe() : string {
        return __("api.fail.system_error")." : ".$this->getDescribeErrorCode();
    }

    public function getDescribeErrorCode() {
        return $this->getExceptionCodeName()."_".$this->getCode()."-".date('YmdHis');
    }

    public function getExceptionCodeName(){
        return "PE";
    }
}
