<?php

namespace App\Http\Controllers\API;

class InvalidParameterException extends PacException {

    public function getExceptionCodeName(){
        return "IP";
    }
}
