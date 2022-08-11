<?php

namespace App\Chat\Exceptions;

class DataNotFoundException extends DataAccessException {

    public function getExceptionCodeName(){
        return "CE-DNFE";
    }

    public function getDescribe(): string
    {
        return __("api.fail.data_not_found");
    }
}
