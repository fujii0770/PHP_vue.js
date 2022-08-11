<?php

namespace App\Chat\Exceptions;

class ExclusiveException extends DataAccessException {

    public function getExceptionCodeName(){
        return "CE-EXCE";
    }

    public function getDescribe(): string
    {
        return __("api.fail.exclusive");
    }
}