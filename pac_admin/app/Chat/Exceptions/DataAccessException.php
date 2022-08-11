<?php

namespace App\Chat\Exceptions;

class DataAccessException extends ChatException {

    public function getExceptionCodeName(){
        return "CE-DAE";
    }


    public function getDescribe(): string
    {
        return __("api.fail.system_error");
    }
}

