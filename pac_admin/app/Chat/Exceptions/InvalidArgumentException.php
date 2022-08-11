<?php

namespace App\Chat\Exceptions;

class InvalidParameterException extends ChatException
{
    public function getExceptionCodeName(){
        return "CE-IPE";
    }
}
