<?php

namespace App\Chat\Exceptions;

use Illuminate\Http\Response;
use Psr\Http\Message\ResponseInterface;

class FailedHttpAccessException extends ChatException {

    public function getExceptionCodeName(){
        return "CE-FHAE";
    }
}
