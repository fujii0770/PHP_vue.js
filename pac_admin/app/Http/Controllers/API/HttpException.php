<?php

namespace App\Http\Controllers\API;

class HttpException extends PacException {

    public function getExceptionCodeName(){
        return "HE";
    }
}
